<?php

declare(strict_types=1);

namespace App;

final class Config
{
    private static bool $loaded = false;

    public static function resetForTests(): void
    {
        self::$loaded = false;
    }

    public static function load(?string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath ??= dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (!str_contains($line, '=')) {
                        continue;
                    }
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (
                        (str_starts_with($value, '"') && str_ends_with($value, '"'))
                        || (str_starts_with($value, "'") && str_ends_with($value, "'"))
                    ) {
                        $value = substr($value, 1, -1);
                    }
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    public static function getString(string $key, string $default = ''): string
    {
        self::load();
        $value = $_ENV[$key] ?? getenv($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $raw = self::getString($key, (string) $default);

        return is_numeric($raw) ? (int) $raw : $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $raw = self::getString($key, $default ? '1' : '0');

        return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
    }

    public static function isDebug(): bool
    {
        return self::getBool('APP_DEBUG', false);
    }

    public static function isTesting(): bool
    {
        return self::getString('APP_ENV', 'local') === 'testing';
    }
}