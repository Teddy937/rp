<?php
namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasAuditLog;

    // Account status constants
    const STATUS_ACTIVE    = 'active';
    const STATUS_INACTIVE  = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING   = 'pending';

    // Security constants
    const PASSWORD_EXPIRY_DAYS = 7;
    const OTP_EXPIRY_MINUTES   = 5;
    const MAX_OTP_ATTEMPTS     = 3;
    const MAX_LOGIN_ATTEMPTS   = 5;
    const LOCKOUT_MINUTES      = 30;

    protected $guarded = [];

    protected $guard_name = "sanctum";

    protected $hidden = [
        'password', 'remember_token',
        'otp', 'login_token', 'password_reset_token',
    ];

    protected $casts = [
        'email_verified_at'               => 'datetime',
        'password'                        => 'hashed',
        'date_of_birth'                   => 'date',
        'password_changed_at'             => 'datetime',
        'password_expires_at'             => 'datetime',
        'password_reset_token_expires_at' => 'datetime',
        'otp_expires_at'                  => 'datetime',
        'login_token_expires_at'          => 'datetime',
        'last_login_at'                   => 'datetime',
        'locked_until'                    => 'datetime',
        'is_online'                       => 'boolean',
        'otp_attempts'                    => 'integer',
        'failed_login_attempts'           => 'integer',
        'today_logins_count'              => 'integer',
        'all_time_logins_count'           => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    public function isAdministrator(): bool
    {return $this->hasRole('Administrator');}

    public function isBranchManager(): bool
    {return $this->hasRole('Branch Manager');}

    public function isStoreManager(): bool
    {return $this->hasRole('Store Manager');}

    public function canAccessStore(int $storeId): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->isBranchManager()) {
            return Store::where('id', $storeId)->where('branch_id', $this->branch_id)->exists();
        }
        return $this->store_id === $storeId;
    }

    public function canAccessBranch(int $branchId): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->branch_id === $branchId;
    }

    // ─── Account Status Helpers ───────────────────────────────────────────────

    public function isActive(): bool
    {return $this->account_status === self::STATUS_ACTIVE;}

    public function isSuspended(): bool
    {return $this->account_status === self::STATUS_SUSPENDED;}

    public function isPending(): bool
    {return $this->account_status === self::STATUS_PENDING;}

    public function isLockedOut(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    // ─── Password Helpers ─────────────────────────────────────────────────────

    public function isPasswordExpired(): bool
    {
        return $this->password_expires_at && $this->password_expires_at->isPast();
    }

    public function refreshPasswordExpiry(): void
    {
        $this->updateQuietly([
            'password_changed_at' => now(),
            'password_expires_at' => now()->addDays(self::PASSWORD_EXPIRY_DAYS),
        ]);
    }

    public function generatePasswordResetToken(): string
    {
        $plain = bin2hex(random_bytes(32));
        $this->updateQuietly([
            'password_reset_token'            => bcrypt($plain),
            'password_reset_token_expires_at' => now()->addHour(),
        ]);
        return $plain;
    }

    public function verifyPasswordResetToken(string $plain): bool
    {
        if (! $this->password_reset_token_expires_at || $this->password_reset_token_expires_at->isPast()) {
            return false;
        }
        return password_verify($plain, $this->password_reset_token);
    }

    public function clearPasswordResetToken(): void
    {
        $this->updateQuietly([
            'password_reset_token'            => null,
            'password_reset_token_expires_at' => null,
        ]);
    }

    // ─── OTP Helpers ──────────────────────────────────────────────────────────

    public function generateOtp(): string
    {
        $plain = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->updateQuietly([
            'otp'            => bcrypt($plain),
            'otp_expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'otp_attempts'   => 0,
        ]);
        return $plain;
    }

    public function verifyOtp(string $plain): bool
    {
        if ($this->otp_attempts >= self::MAX_OTP_ATTEMPTS) {
            return false;
        }

        if (! $this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false;
        }

        if (! password_verify($plain, $this->otp)) {
            $this->increment('otp_attempts');
            return false;
        }

        // Clear OTP after successful verification
        $this->updateQuietly([
            'otp'            => null,
            'otp_expires_at' => null,
            'otp_attempts'   => 0,
        ]);
        return true;
    }

    // ─── Login Attempt Helpers ────────────────────────────────────────────────

    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        if ($this->fresh()->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $this->updateQuietly([
                'account_status' => self::STATUS_SUSPENDED,
                'locked_until'   => now()->addMinutes(self::LOCKOUT_MINUTES),
            ]);
        }
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('account_status', self::STATUS_ACTIVE);
    }
}
