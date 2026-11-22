<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use App\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

final class JsonResponseProductionTest extends TestCase
{
    public function testRemoveCamposSensiveisQuandoDebugDesligado(): void
    {
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, "APP_DEBUG=false\n");
        Config::resetForTests();
        Config::load($path);

        $json = JsonResponse::build(500, [
            'sucesso' => false,
            'erro_mysql' => 'detalhe',
            'mensagem' => 'falha',
        ]);

        self::assertStringNotContainsString('detalhe', $json);
        self::assertStringContainsString('falha', $json);
    }
}