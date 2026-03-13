<?php
namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Notifications\PasswordResetNotification;
use App\Support\AuthCache;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * STEP 1 — Validate credentials, issue OTP + login_token.
     * Does NOT return a Sanctum token yet.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return $this->unauthorized('Invalid credentials.');
            }

            if ($user->account_status === User::STATUS_INACTIVE) {
                return $this->forbidden('Your account has been deactivated. Contact the administrator.');
            }

            if ($user->account_status === User::STATUS_PENDING) {
                return $this->forbidden('Your account is pending approval.');
            }

            if ($user->isLockedOut()) {
                $minutesLeft = now()->diffInMinutes($user->locked_until, false) + 1;
                return $this->forbidden("Account temporarily locked. Try again in {$minutesLeft} minute(s).");
            }

            if (! Hash::check($request->password, $user->password)) {
                $user->recordFailedLogin();
                $remaining = User::MAX_LOGIN_ATTEMPTS - $user->fresh()->failed_login_attempts;
                $msg       = $remaining > 0
                    ? "Invalid credentials. {$remaining} attempt(s) remaining."
                    : 'Account suspended due to too many failed login attempts.';
                return $this->unauthorized($msg);
            }

            if ($user->fresh()->account_status === User::STATUS_SUSPENDED) {
                return $this->forbidden('Account suspended. Contact the administrator.');
            }

            if ($user->isPasswordExpired()) {
                return $this->error(
                    'Your password has expired. Please reset your password to continue.',
                    403,
                    ['requires_password_reset' => true]
                );
            }

            // ── Credentials valid — generate login_token + OTP ────────────
            $loginToken = Str::random(64);

            $user->updateQuietly([
                'login_token'            => $loginToken,
                'login_token_expires_at' => now()->addMinutes(10),
                'failed_login_attempts'  => 0,
            ]);

            $otp = $user->generateOtp(); // hashes & stores OTP on user

            $user->notify(new OtpNotification($otp, $user->name));

            return $this->success([
                'hex' => $loginToken,
                'otp' => $otp, // remove in production
            ], 'Credentials verified. OTP sent to your registered contact.');

        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * STEP 2 — Verify OTP using login_token, issue Sanctum token.
     * Borrowed from reference: login_token acts as the 2FA session bridge.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'login_token' => ['required', 'string'],
                'otp'         => ['required', 'string'],
            ]);

            // Look up user by login_token (not by auth — user has no token yet)
            $user = User::where('login_token', $request->login_token)->first();

            if (! $user) {
                return $this->error('Invalid or expired login session. Please log in again.', 400);
            }

            if (now()->greaterThan($user->login_token_expires_at)) {
                $user->updateQuietly([
                    'login_token'            => null,
                    'login_token_expires_at' => null,
                ]);
                return $this->error('Your login session has expired. Please log in again.', 400);
            }

            if (! $user->otp) {
                return $this->error('OTP has not been issued. Please log in again.', 400);
            }

            if ($user->otp_attempts >= User::MAX_OTP_ATTEMPTS) {
                return $this->error('Too many incorrect OTP attempts. Please log in again.', 400);
            }

            if (! $user->verifyOtp($request->otp)) {
                return $this->error('Incorrect OTP provided.', 422);
            }

            // ── OTP valid — issue Sanctum token ───────────────────────────
            $tokenResult = $user->createToken('api-token');
            $token       = $tokenResult->plainTextToken;

            // Update login stats (borrowed from reference)
            $user->today_logins_count     += 1;
            $user->all_time_logins_count  += 1;
            $user->last_login_at           = now();
            $user->last_login_ip           = $request->ip();
            $user->is_online               = true;
            $user->login_token             = null;
            $user->login_token_expires_at  = null;
            $user->saveQuietly();

            // Cache user session (borrowed from reference)
            Cache::put(
                AuthCache::key($user->id),
                [
                    'user'        => $user->loadMissing('branch', 'store', 'roles'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles'       => $user->getRoleNames(),
                ],
                now()->addHours(12)
            );

            // Token issued as a secure httpOnly cookie named 'vcf'
            // Never exposed in response body — frontend reads user data only
            $response = $this->success([
                'user' => new UserResource($user->load(['branch', 'store'])),
            ], 'OTP verified successfully. Welcome back!');

            return $response->cookie(
                'vcf',                 // cookie name
                $token,                // sanctum plain text token
                60 * 12,               // 12 hours (in minutes)
                '/',                   // path
                null,                  // domain — null uses current domain
                request()->isSecure(), // secure — true on HTTPS in production
                true,                  // httpOnly — JS cannot read it
                false,                 // raw
                'Lax'                  // sameSite
            );

        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Resend OTP — reuses existing login_token, issues fresh OTP.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'login_token' => ['required', 'string'],
            ]);

            $user = User::where('login_token', $request->login_token)->first();

            if (! $user) {
                return $this->error('Invalid or expired login session. Please log in again.', 400);
            }

            if (now()->greaterThan($user->login_token_expires_at)) {
                return $this->error('Your login session has expired. Please log in again.', 400);
            }

            // Refresh login_token expiry + issue new OTP
            $user->updateQuietly([
                'login_token_expires_at' => now()->addMinutes(10),
            ]);

            $otp = $user->generateOtp();

            $user->notify(new OtpNotification($otp, $user->name));
            return $this->success(
                ['otp' => $otp], // remove in production
                'A new OTP has been sent to your registered contact.'
            );

        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Heartbeat — keep session alive, return fresh cached user data.
     */
    public function me(): JsonResponse
    {
        try {
            $user   = User::find(Auth::id());
            $cached = Cache::get(AuthCache::key($user->id));

            if (! $cached) {
                $cached = [
                    'user'        => $user->loadMissing('branch', 'store', 'roles'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles'       => $user->getRoleNames(),
                ];
                Cache::put(AuthCache::key($user->id), $cached, now()->addHours(12));
            }

            return $this->success(
                new UserResource($user->load(['branch', 'store'])),
                'User retrieved successfully.'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Logout — revoke token, clear cache, mark offline.
     */
    public function logout(): JsonResponse
    {
        try {
            $user = User::find(Auth::id());
            $user->updateQuietly(['is_online' => false]);
            Cache::forget(AuthCache::key($user->id));
            $user->tokens()->delete();
            return $this->success(null, 'Logged out successfully.')
                ->withoutCookie('vcf');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Forgot password — generate reset token.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

            $user  = User::where('email', $request->email)->first();
            $token = $user->generatePasswordResetToken();

            $user->notify(new PasswordResetNotification($token, $user->name));

            return $this->success(
                ['reset_token' => $token], // remove in production
                'Password reset instructions sent to your email.'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email'                 => ['required', 'email', 'exists:users,email'],
                'token'                 => ['required', 'string'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user->verifyPasswordResetToken($request->token)) {
                return $this->error('Invalid or expired reset token.', 422);
            }

            $user->update(['password' => $request->password]);
            $user->refreshPasswordExpiry();
            $user->clearPasswordResetToken();
            $user->tokens()->delete();

            return $this->success(null, 'Password reset successfully. Please log in.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Change own password (authenticated).
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password'      => ['required', 'string'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ]);

            $user = User::find(Auth::id());

            if (! Hash::check($request->current_password, $user->password)) {
                return $this->error('Current password is incorrect.', 422);
            }

            $user->update(['password' => $request->password]);
            $user->refreshPasswordExpiry();

            return $this->success(null, 'Password changed successfully.');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
