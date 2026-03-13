<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

if (! function_exists('generate_reference')) {
    /**
     * Generate a unique movement reference number.
     * Format: MOV-YYYYMMDD-XXXXX
     *
     * @param  string  $prefix  e.g. 'MOV', 'SLE', 'TRF', 'ADJ'
     */
    function generate_reference(string $prefix = 'MOV'): string
    {
        $date   = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        return "{$prefix}-{$date}-{$random}";
    }
}

if (! function_exists('generate_sku_code')) {
    /**
     * Generate a SKU code from a product name.
     * e.g. "Cooking Oil 1L" → "SKU-COO-001"
     */
    function generate_sku_code(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $suffix = str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);
        return "SKU-{$prefix}-{$suffix}";
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format a number as KES currency.
     */
    function format_currency(float $amount, string $currency = 'KES'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (! function_exists('api_version')) {
    /**
     * Get current API version prefix.
     */
    function api_version(string $version = 'v1'): string
    {
        return "api/{$version}";
    }
}

if (! function_exists('paginate_limit')) {
    /**
     * Get safe pagination limit from request.
     */
    function paginate_limit(int $default = 15, int $max = 100): int
    {
        $requested = (int) request('per_page', $default);
        return min($requested, $max);
    }
}

if (! function_exists('api_response')) {
    /**
     * Return a standardized API response.
     *
     * @param bool $status
     * @param int $code
     * @param string $message
     * @param mixed $data
     * @param mixed $error
     * @return \Illuminate\Http\JsonResponse
     */
    function api_response(string $status, int $code, string $message = '', $data = null, $error = null)
    {
        $response = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'errors'  => $error,
        ];

        return response()->json($response, $code);
    }
}

if (! function_exists('user_can')) {
    /**
     * Check if the given (or currently authenticated) user has a permission
     * via their roles, without relying on Spatie's hasPermissionTo().
     *
     * Usage:
     *   user_can('Can view only own sector data')
     *   user_can('Can view only own sector data', $user)
     */
    function user_can(string $permission, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        if (! $user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }

        return $user->roles
            ->flatMap->permissions
            ->pluck('name')
            ->flip()
            ->has($permission);
    }
}
