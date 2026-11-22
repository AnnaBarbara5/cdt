<?php

declare(strict_types=1);

namespace Tests\Support;

final class SamplePayload
{
    /** @return array<string, string> */
    public static function valid(): array
    {
        return [
            'nome' => 'Maria Souza',
            'email' => 'maria@example.com',
            'telefone' => '(21) 98888-7777',
            'cpf_pessoal' => '529.982.247-25',
            'data_nascimento' => '1995-06-15',
            'cep' => '22760-000',
            'logradouro' => 'Av. das Américas',
            'numero' => '1000',
            'pagamento' => 'debito',
            'cartao' => '4111-1111-1111-1111',
            'validade' => '12/30',
            'cvv' => '123',
            'nome_cartao' => 'Maria Souza',
            'cpf_cartao' => '529.982.247-25',
        ];
    }
}