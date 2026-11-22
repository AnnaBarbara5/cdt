<?php

declare(strict_types=1);

namespace App\Security;

use App\Database\ConnectionFactory;
use PDO;

final class RateLimiter
{
    public function allow(string $bucket, int $maxAttempts, int $windowSeconds): bool
    {
        $this->ensureSchema();
        $pdo = ConnectionFactory::get();
        $now = time();
        $key = hash('sha256', $bucket);

        $pdo->beginTransaction();
        try {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $sql = $driver === 'sqlite'
                ? 'SELECT attempts, window_start FROM rate_limits WHERE bucket_key = :k'
                : 'SELECT attempts, window_start FROM rate_limits WHERE bucket_key = :k FOR UPDATE';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                $insert = $pdo->prepare('INSERT INTO rate_limits (bucket_key, attempts, window_start) VALUES (:k, 1, :w)');
                $insert->execute([':k' => $key, ':w' => $now]);
                $pdo->commit();

                return true;
            }

            $attempts = (int) $row['attempts'];
            $windowStart = (int) $row['window_start'];

            if ($now - $windowStart > $windowSeconds) {
                $update = $pdo->prepare('UPDATE rate_limits SET attempts = 1, window_start = :w WHERE bucket_key = :k');
                $update->execute([':w' => $now, ':k' => $key]);
                $pdo->commit();

                return true;
            }

            if ($attempts >= $maxAttempts) {
                $pdo->commit();

                return false;
            }

            $update = $pdo->prepare('UPDATE rate_limits SET attempts = attempts + 1 WHERE bucket_key = :k');
            $update->execute([':k' => $key]);
            $pdo->commit();

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    private function ensureSchema(): void
    {
        $pdo = ConnectionFactory::get();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS rate_limits (
                    bucket_key TEXT PRIMARY KEY,
                    attempts INTEGER NOT NULL,
                    window_start INTEGER NOT NULL
                )'
            );

            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS rate_limits (
                bucket_key CHAR(64) PRIMARY KEY,
                attempts INT NOT NULL,
                window_start INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public static function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        return is_string($ip) ? $ip : '0.0.0.0';
    }
}