<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testCarregaArquivoEnv(): void
    {
        Config::resetForTests();
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, "APP_DEBUG=true\nCORS_ALLOWED_ORIGINS=http://localhost\n");

        Config::load($path);

        self::assertTrue(Config::getBool('APP_DEBUG', false));
        self::assertSame('http://localhost', Config::getString('CORS_ALLOWED_ORIGINS'));
    }

    public function testDefaultsQuandoChaveAusente(): void
    {
        self::assertFalse(Config::getBool('CHAVE_INEXISTENTE_XYZ', false));
        self::assertSame('padrao', Config::getString('CHAVE_INEXISTENTE_XYZ', 'padrao'));
    }
}