<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\ConnectionFactory;
use App\Database\DatabaseException;
use PHPUnit\Framework\TestCase;

final class ConnectionFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        ConnectionFactory::reset();
    }

    public function testConexaoSqliteEmMemoria(): void
    {
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        $pdo = ConnectionFactory::get();
        self::assertSame('sqlite', $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }

    public function testFalhaConexaoMysqlInvalida(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            self::markTestSkipped('Extensão pdo_mysql não disponível.');
        }

        ConnectionFactory::reset();
        \App\Config::resetForTests();
        $path = sys_get_temp_dir() . '/cdt-env-' . uniqid('', true) . '.env';
        file_put_contents($path, implode("\n", [
            'DB_DRIVER=mysql',
            'DB_HOST=127.0.0.1',
            'DB_PORT=1',
            'DB_DATABASE=nao_existe',
            'DB_USERNAME=root',
            'DB_PASSWORD=root',
        ]) . "\n");
        \App\Config::load($path);

        $this->expectException(DatabaseException::class);
        ConnectionFactory::get();
    }
}