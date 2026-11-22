<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\ClienteCadastro;
use PHPUnit\Framework\TestCase;
use Tests\Support\SamplePayload;

final class ClienteCadastroTest extends TestCase
{
    public function testFromValidatedPost(): void
    {
        $cadastro = ClienteCadastro::fromValidatedPost(SamplePayload::valid(), 'token', '1111', 'visa');
        self::assertSame('Maria Souza', $cadastro->nome);
        self::assertSame('1111', $cadastro->cartaoUltimos4);
        self::assertSame('token', $cadastro->pagamentoToken);
    }
}