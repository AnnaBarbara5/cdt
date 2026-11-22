<?php

declare(strict_types=1);

namespace App;

final readonly class ClienteCadastro
{
    public function __construct(
        public string $nome,
        public string $email,
        public string $telefone,
        public string $cpfPessoal,
        public string $dataNascimento,
        public string $cep,
        public string $logradouro,
        public string $numero,
        public string $tipoPagamento,
        public string $cartaoUltimos4,
        public string $cartaoBandeira,
        public string $pagamentoToken,
        public string $validade,
        public string $nomeCartao,
        public string $cpfCartao,
    ) {
    }

    /** @param array<string, mixed> $post */
    public static function fromValidatedPost(array $post, string $token, string $last4, string $brand): self
    {
        return new self(
            nome: trim((string) $post['nome']),
            email: trim((string) $post['email']),
            telefone: trim((string) $post['telefone']),
            cpfPessoal: trim((string) $post['cpf_pessoal']),
            dataNascimento: (string) $post['data_nascimento'],
            cep: trim((string) $post['cep']),
            logradouro: trim((string) $post['logradouro']),
            numero: trim((string) $post['numero']),
            tipoPagamento: (string) $post['pagamento'],
            cartaoUltimos4: $last4,
            cartaoBandeira: $brand,
            pagamentoToken: $token,
            validade: (string) $post['validade'],
            nomeCartao: trim((string) $post['nome_cartao']),
            cpfCartao: trim((string) $post['cpf_cartao']),
        );
    }
}