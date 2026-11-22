<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Database\ConnectionFactory;
use App\Payment\MockPaymentGateway;
use App\Repository\ClienteRepository;
use App\Service\CadastroService;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

final class CadastroServiceTest extends TestCase
{
    protected function setUp(): void
    {
        ConnectionFactory::reset();
        putenv('DB_DRIVER=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
    }

    public function testRegistrarComSucesso(): void
    {
        $service = new CadastroService(new ClienteRepository(), new MockPaymentGateway());
        $result = $service->registrar(SamplePayload::valid());

        self::assertTrue($result['sucesso']);
        self::assertArrayHasKey('id', $result);
    }

    public function testRegistrarComDadosInvalidos(): void
    {
        $service = new CadastroService(new ClienteRepository(), new MockPaymentGateway());
        $result = $service->registrar(['nome' => '']);

        self::assertFalse($result['sucesso']);
        self::assertArrayHasKey('erros', $result);
    }
}