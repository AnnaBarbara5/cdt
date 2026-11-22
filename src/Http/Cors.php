<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;

final class Cors
{
    public static function apply(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = self::allowedOrigins();

        if ($origin !== '' && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /** @return list<string> */
    public static function allowedOrigins(): array
    {
        $raw = Config::getString('CORS_ALLOWED_ORIGINS', '');
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}