<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;

final class PublicConfigHandler
{
    public function handle(): void
    {
        Config::load();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'debug' => Config::isDebug(),
            'csrf_url' => 'csrf-token.php',
        ], JSON_UNESCAPED_UNICODE);
    }
}