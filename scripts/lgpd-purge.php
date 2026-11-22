<?php

declare(strict_types=1);

use App\Config;
use App\Service\DataRetentionService;

require dirname(__DIR__) . '/src/bootstrap.php';

Config::load();

$days = Config::getInt('LGPD_RETENTION_DAYS', 0);
if ($days <= 0) {
    fwrite(STDERR, "LGPD_RETENTION_DAYS não configurado (0 = desativado).\n");
    exit(0);
}

$deleted = DataRetentionService::createDefault()->purgeNow();
echo sprintf("Purge concluído: %d registro(s) com mais de %d dias removido(s).\n", $deleted, $days);
exit(0);