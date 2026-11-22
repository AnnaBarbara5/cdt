<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Repository\ClienteRepository;

/**
 * Purga cadastros mais antigos que o prazo LGPD configurado (LGPD_RETENTION_DAYS).
 */
final class DataRetentionService
{
    private const THROTTLE_FILE = 'retention.last';

    public function __construct(
        private readonly ClienteRepository $repository,
        private readonly ?string $dataDir = null,
    ) {
    }

  /**
     * Executa purge se passou o intervalo de throttle (24h). Retorna quantidade excluída.
     */
    public function purgeIfDue(int $throttleSeconds = 86400): int
    {
        $days = Config::getInt('LGPD_RETENTION_DAYS', 0);
        if ($days <= 0) {
            return 0;
        }

        if (!$this->shouldRunNow($throttleSeconds)) {
            return 0;
        }

        $this->repository->ensureSchema();
        $deleted = $this->repository->purgeOlderThanDays($days);
        $this->markRan();

        return $deleted;
    }

    /** Purga imediata (cron / CLI), sem throttle. */
    public function purgeNow(): int
    {
        $days = Config::getInt('LGPD_RETENTION_DAYS', 0);
        if ($days <= 0) {
            return 0;
        }

        $this->repository->ensureSchema();

        return $this->repository->purgeOlderThanDays($days);
    }

    private function shouldRunNow(int $throttleSeconds): bool
    {
        $path = $this->throttlePath();
        if (!is_readable($path)) {
            return true;
        }

        $raw = file_get_contents($path);
        if ($raw === false || !is_numeric(trim($raw))) {
            return true;
        }

        return time() - (int) trim($raw) >= $throttleSeconds;
    }

    private function markRan(): void
    {
        $path = $this->throttlePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        file_put_contents($path, (string) time());
    }

    private function throttlePath(): string
    {
        $base = $this->dataDir ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data';

        return $base . DIRECTORY_SEPARATOR . self::THROTTLE_FILE;
    }

    public static function createDefault(): self
    {
        return new self(new ClienteRepository());
    }
}