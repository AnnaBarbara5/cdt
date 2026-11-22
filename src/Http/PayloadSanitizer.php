<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;

final class PayloadSanitizer
{
    /** @param array<string, mixed> $payload */
    public static function forClient(array $payload): array
    {
        if (Config::isDebug()) {
            return $payload;
        }

        if (isset($payload['erros']) && is_array($payload['erros'])) {
            $payload['erros'] = array_map(static function ($item): array {
                if (!is_array($item)) {
                    return [
                        'codigo' => 'legado',
                        'campo' => 'geral',
                        'mensagem' => (string) $item,
                    ];
                }
                unset($item['depuracao']);

                return $item;
            }, $payload['erros']);
        }

        unset($payload['erro_mysql'], $payload['detalhes'], $payload['diagnostico']);

        return $payload;
    }
}