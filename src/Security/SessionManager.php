<?php

declare(strict_types=1);

namespace App\Security;

use App\Config;

final class SessionManager
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;

            return;
        }

        $secure = Config::getBool('SESSION_SECURE', false);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('cdt_sess');
        session_start();
        self::$started = true;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}