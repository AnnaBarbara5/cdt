<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\View\PainelView;
use PHPUnit\Framework\TestCase;

final class PainelViewTest extends TestCase
{
    public function testRenderLoginComErro(): void
    {
        $html = (new PainelView())->renderLogin('Falha');
        self::assertStringContainsString('Falha', $html);
        self::assertStringContainsString('Painel administrativo', $html);
    }

    public function testRenderDashboardVazio(): void
    {
        $html = (new PainelView())->renderDashboard([]);
        self::assertStringContainsString('Nenhum cadastro encontrado', $html);
    }

    public function testRenderDashboardComLinha(): void
    {
        $html = (new PainelView())->renderDashboard([
            [
                'id' => 1,
                'nome' => 'Ana',
                'telefone' => '21999999999',
                'cpf_pessoal' => '529.982.247-25',
                'tipo_pagamento' => 'credito',
                'cartao_mascarado' => '**** **** **** 1111',
                'validade' => '12/30',
                'nome_cartao' => 'Ana',
                'cpf_cartao' => '529.982.247-25',
                'email' => 'ana@test.com',
            ],
        ]);

        self::assertStringContainsString('Ana', $html);
        self::assertStringContainsString('credito', $html);
    }
}