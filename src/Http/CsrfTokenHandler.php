<?php

declare(strict_types=1);

namespace App\Http;

use App\Security\Csrf;

final class CsrfTokenHandler
{
    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'csrf_token' => Csrf::token(),
        ], JSON_UNESCAPED_UNICODE);
    }
}