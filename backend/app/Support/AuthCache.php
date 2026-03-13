<?php
namespace App\Support;

class AuthCache
{
    /**
     * Generate a consistent cache key for a user's session data.
     */
    public static function key(int $userId): string
    {
        return "auth_session:user:{$userId}";
    }
}
