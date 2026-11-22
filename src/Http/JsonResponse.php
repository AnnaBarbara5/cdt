<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    /** @param array<string, mixed> $payload */
    public static function build(int $status, array $payload): string
    {
        $payload = PayloadSanitizer::forClient($payload);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        return $json !== false ? $json : '{"sucesso":false,"mensagem":"Erro ao serializar resposta."}';
    }

    /** @param array<string, mixed> $payload */
    public static function send(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo self::build($status, $payload);
        exit;
    }
}