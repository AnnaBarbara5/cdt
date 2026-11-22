<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;
use App\Security\Csrf;
use App\Security\RateLimiter;
use App\Service\CadastroService;
use App\Validation\ValidationErrorCatalog;

final class CadastroApiHandler
{
    public function __construct(
        private readonly CadastroService $service,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    /** @return array{status: int, payload: array<string, mixed>|null} */
    public function execute(): array
    {
        Config::load();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            return ['status' => 204, 'payload' => null];
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return [
                'status' => 405,
                'payload' => ['sucesso' => false, 'mensagem' => 'Método não permitido.'],
            ];
        }

        $max = Config::getInt('RATE_LIMIT_CADASTRO_MAX', 20);
        $window = Config::getInt('RATE_LIMIT_CADASTRO_WINDOW', 3600);
        $ip = RateLimiter::clientIp();
        if (!$this->rateLimiter->allow('cadastro:' . $ip, $max, $window)) {
            return [
                'status' => 429,
                'payload' => [
                    'sucesso' => false,
                    'mensagem' => 'Muitas tentativas. Tente novamente mais tarde.',
                    'erros' => [ValidationErrorCatalog::entry('rate_limit')],
                ],
            ];
        }

        $csrf = $_POST['csrf_token'] ?? null;
        if (!Csrf::validate(is_string($csrf) ? $csrf : null)) {
            return [
                'status' => 403,
                'payload' => [
                    'sucesso' => false,
                    'mensagem' => 'Token de segurança inválido.',
                    'erros' => [ValidationErrorCatalog::entry('csrf_invalido')],
                ],
            ];
        }

        /** @var array<string, mixed> $post */
        $post = $_POST;
        $result = $this->service->registrar($post);

        return [
            'status' => $result['sucesso'] ? 200 : 422,
            'payload' => $result,
        ];
    }

    public function handle(): void
    {
        Cors::apply();
        $response = $this->execute();

        if ($response['status'] === 204) {
            http_response_code(204);
            exit;
        }

        JsonResponse::send($response['status'], $response['payload'] ?? []);
    }

    public static function createDefault(): self
    {
        return new self(CadastroService::createDefault(), new RateLimiter());
    }
}