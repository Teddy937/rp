<?php

use App\Http\Controllers\V1\AuditLogController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\BranchController;
use App\Http\Controllers\V1\SkuController;
use App\Http\Controllers\V1\StockLedgerController;
use App\Http\Controllers\V1\StockMovementController;
use App\Http\Controllers\V1\StoreController;
use App\Http\Controllers\V1\UserController;
use App\Support\AuthCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public (no token required) ───────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']); // 2FA step 2
        Route::post('resend-otp', [AuthController::class, 'resendOtp']); // resend OTP
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // ── Authenticated (Sanctum token required) ───────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Heartbeat — keep session alive, return cached user state
        Route::get('heartbeat', function () {
            $user   = request()->user();
            $cached = Cache::get(AuthCache::key($user->id));

            if (! $cached) {
                $cached = [
                    'user'        => $user->loadMissing('branch', 'store', 'roles'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles'       => $user->getRoleNames(),
                ];
                Cache::put(AuthCache::key($user->id), $cached, now()->addHours(12));
            }

            return response()->json([
                'status'    => 'active',
                'code'      => 200,
                'message'   => 'Session active.',
                'timestamp' => now(),
                'data'      => $cached,
                'errors'    => null,
            ]);
        });

        // Branches
        Route::apiResource('branches', BranchController::class);

        // Stores
        Route::apiResource('stores', StoreController::class);

        // SKUs
        Route::apiResource('skus', SkuController::class);

        // Stock Ledger
        Route::prefix('stock')->group(function () {
            Route::get('stores/{store}/ledger', [StockLedgerController::class, 'storeStock']);
            Route::get('stores/{store}/skus/{sku}', [StockLedgerController::class, 'skuStock']);
            Route::get('stores/{store}/low-stock', [StockLedgerController::class, 'lowStock']);
        });

        // Stock Movements
        Route::prefix('movements')->group(function () {
            Route::get('/', [StockMovementController::class, 'index']);
            Route::get('pending-transfers', [StockMovementController::class, 'pendingTransfers']);
            Route::get('{id}', [StockMovementController::class, 'show']);
            Route::post('sale', [StockMovementController::class, 'sale']);
            Route::post('transfer', [StockMovementController::class, 'transfer']);
            Route::post('transfer/{id}/approve', [StockMovementController::class, 'approveTransfer']);
            Route::post('transfer/{id}/reject', [StockMovementController::class, 'rejectTransfer']);
            Route::post('adjustment', [StockMovementController::class, 'adjustment']);
            Route::post('procurement', [StockMovementController::class, 'procurement']);
        });

        // users
        Route::get('users/roles', [UserController::class, 'roles']);
        Route::resource('users', UserController::class)->except(['store']); // or manual routes
        Route::post('users', [UserController::class, 'store']);
        Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('users/{id}/status', [UserController::class, 'toggleStatus']);
        // logs
        Route::get('audit-logs', [AuditLogController::class, 'index']);
    });
});
