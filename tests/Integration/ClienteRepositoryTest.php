<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\ClienteCadastro;
use App\Database\ConnectionFactory;
use App\Repository\ClienteRepository;
use PHPUnit\Framework\TestCase;

final class ClienteRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
    }

    public function testInsertAndFindAll(): void
    {
        $repo = new ClienteRepository();
        $repo->ensureSchema();

        $cadastro = new ClienteCadastro(
            'Nome',
            'a@a.com',
            '219999',
            '529.982.247-25',
            '1990-01-01',
            '22760000',
            'Rua A',
            '10',
            'debito',
            '1111',
            'visa',
            'token123',
            '12/30',
            'Nome',
            '529.982.247-25',
        );

        $id = $repo->insert($cadastro);
        self::assertGreaterThan(0, $id);

        $all = $repo->findAll();
        self::assertCount(1, $all);
        self::assertSame('1111', $all[0]['cartao_ultimos4']);
        self::assertArrayNotHasKey('cvv', $all[0]);
    }
}