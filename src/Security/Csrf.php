<?php

declare(strict_types=1);

namespace App\Security;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        SessionManager::start();
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(?string $submitted): bool
    {
        SessionManager::start();
        if ($submitted === null || $submitted === '') {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY] ?? '';

        return is_string($stored) && $stored !== '' && hash_equals($stored, $submitted);
    }
}