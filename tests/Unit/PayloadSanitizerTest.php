<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use App\Http\PayloadSanitizer;
use PHPUnit\Framework\TestCase;

final class PayloadSanitizerTest extends TestCase
{
    public function testRemoveDepuracaoQuandoDebugDesligado(): void
    {
        $envFile = $this->tempEnv(['APP_DEBUG=false']);
        Config::resetForTests();
        Config::load($envFile);

        $out = PayloadSanitizer::forClient([
            'sucesso' => false,
            'erros' => [
                [
                    'codigo' => 'email_invalido',
                    'campo' => 'email',
                    'mensagem' => 'E-mail inválido',
                    'depuracao' => 'segredo interno',
                ],
            ],
        ]);

        self::assertArrayNotHasKey('depuracao', $out['erros'][0]);
        self::assertSame('E-mail inválido', $out['erros'][0]['mensagem']);
    }

    public function testMantémDepuracaoComDebug(): void
    {
        $envFile = $this->tempEnv(['APP_DEBUG=true']);
        Config::resetForTests();
        Config::load($envFile);

        $payload = [
            'erros' => [['codigo' => 'x', 'mensagem' => 'y', 'depuracao' => 'z']],
        ];
        $out = PayloadSanitizer::forClient($payload);
        self::assertSame('z', $out['erros'][0]['depuracao']);
    }

    /** @param list<string> $lines */
    private function tempEnv(array $lines): string
    {
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, implode("\n", $lines) . "\n");

        return $path;
    }
}