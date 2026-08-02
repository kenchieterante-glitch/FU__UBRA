<?php

namespace App\Libraries;

/**
 * Per-request holder for the user resolved by ApiAuthFilter from a bearer
 * token. Always overwritten at the start of the filter before any read,
 * so it's safe even under persistent PHP workers.
 */
class ApiAuth
{
    private static ?array $user = null;

    public static function set(?array $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?array
    {
        return self::$user;
    }

    public static function userId(): ?int
    {
        return self::$user['department_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return strtolower((string) (self::$user['role'] ?? '')) === 'administrator';
    }
}
