<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Payment\MockPaymentGateway;
use PHPUnit\Framework\TestCase;

final class MockPaymentGatewayTest extends TestCase
{
    public function testTokenizeRetornaTokenOpaco(): void
    {
        $gateway = new MockPaymentGateway();
        $result = $gateway->tokenize([
            'cartao' => '4111111111111111',
            'validade' => '12/30',
            'cvv' => '123',
            'pagamento' => 'debito',
            'nome_cartao' => 'Teste',
            'cpf_cartao' => '529.982.247-25',
        ]);

        self::assertSame('em_analise', $result['status']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['token']);
    }
}