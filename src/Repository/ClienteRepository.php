<?php

declare(strict_types=1);

namespace App\Repository;

use App\ClienteCadastro;
use App\Database\ConnectionFactory;
use PDO;

final class ClienteRepository
{
    public function ensureSchema(): void
    {
        $pdo = ConnectionFactory::get();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS clientes_cartao (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    nome TEXT NOT NULL,
                    email TEXT NOT NULL,
                    telefone TEXT NOT NULL,
                    cpf_pessoal TEXT NOT NULL,
                    data_nascimento TEXT NOT NULL,
                    cep TEXT NOT NULL,
                    logradouro TEXT NOT NULL,
                    numero TEXT NOT NULL,
                    tipo_pagamento TEXT NOT NULL,
                    cartao_ultimos4 TEXT NOT NULL,
                    cartao_bandeira TEXT NOT NULL,
                    pagamento_token TEXT NOT NULL,
                    validade TEXT NOT NULL,
                    nome_cartao TEXT NOT NULL,
                    cpf_cartao TEXT NOT NULL,
                    criado_em TEXT DEFAULT CURRENT_TIMESTAMP
                )'
            );

            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS clientes_cartao (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                telefone VARCHAR(32) NOT NULL,
                cpf_pessoal VARCHAR(14) NOT NULL,
                data_nascimento DATE NOT NULL,
                cep VARCHAR(9) NOT NULL,
                logradouro VARCHAR(255) NOT NULL,
                numero VARCHAR(32) NOT NULL,
                tipo_pagamento ENUM(\'debito\', \'credito\') NOT NULL,
                cartao_ultimos4 CHAR(4) NOT NULL,
                cartao_bandeira VARCHAR(32) NOT NULL,
                pagamento_token VARCHAR(64) NOT NULL,
                validade CHAR(5) NOT NULL,
                nome_cartao VARCHAR(255) NOT NULL,
                cpf_cartao VARCHAR(14) NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function insert(ClienteCadastro $cadastro): int
    {
        $pdo = ConnectionFactory::get();
        $sql = 'INSERT INTO clientes_cartao (
            nome, email, telefone, cpf_pessoal, data_nascimento, cep, logradouro, numero,
            tipo_pagamento, cartao_ultimos4, cartao_bandeira, pagamento_token, validade,
            nome_cartao, cpf_cartao
        ) VALUES (
            :nome, :email, :telefone, :cpf_pessoal, :data_nascimento, :cep, :logradouro, :numero,
            :tipo_pagamento, :cartao_ultimos4, :cartao_bandeira, :pagamento_token, :validade,
            :nome_cartao, :cpf_cartao
        )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $cadastro->nome,
            ':email' => $cadastro->email,
            ':telefone' => $cadastro->telefone,
            ':cpf_pessoal' => $cadastro->cpfPessoal,
            ':data_nascimento' => $cadastro->dataNascimento,
            ':cep' => $cadastro->cep,
            ':logradouro' => $cadastro->logradouro,
            ':numero' => $cadastro->numero,
            ':tipo_pagamento' => $cadastro->tipoPagamento,
            ':cartao_ultimos4' => $cadastro->cartaoUltimos4,
            ':cartao_bandeira' => $cadastro->cartaoBandeira,
            ':pagamento_token' => $cadastro->pagamentoToken,
            ':validade' => $cadastro->validade,
            ':nome_cartao' => $cadastro->nomeCartao,
            ':cpf_cartao' => $cadastro->cpfCartao,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @return list<array<string, mixed>> */
    public function findAll(): array
    {
        return $this->findPage(1, PHP_INT_MAX);
    }

    public function countAll(): int
    {
        $pdo = ConnectionFactory::get();
        $stmt = $pdo->query('SELECT COUNT(*) AS total FROM clientes_cartao');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        return $row ? (int) ($row['total'] ?? 0) : 0;
    }

    /** @return list<array<string, mixed>> */
    public function findPage(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $pdo = ConnectionFactory::get();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare('SELECT * FROM clientes_cartao ORDER BY id DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT * FROM clientes_cartao ORDER BY id DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function deleteById(int $id): bool
    {
        $pdo = ConnectionFactory::get();
        $stmt = $pdo->prepare('DELETE FROM clientes_cartao WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Exclui registros com criado_em anterior ao prazo (dias). Retorna linhas afetadas. */
    public function purgeOlderThanDays(int $days): int
    {
        if ($days <= 0) {
            return 0;
        }

        $pdo = ConnectionFactory::get();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->prepare(
                "DELETE FROM clientes_cartao WHERE datetime(criado_em) < datetime('now', '-' || :days || ' days')"
            );
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        }

        $stmt = $pdo->prepare(
            'DELETE FROM clientes_cartao WHERE criado_em < DATE_SUB(NOW(), INTERVAL :days DAY)'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}