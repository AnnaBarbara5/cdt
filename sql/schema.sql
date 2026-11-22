CREATE TABLE IF NOT EXISTS clientes_cartao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(32) NOT NULL,
    cpf_pessoal VARCHAR(14) NOT NULL,
    data_nascimento DATE NOT NULL,
    cep VARCHAR(9) NOT NULL,
    logradouro VARCHAR(255) NOT NULL,
    numero VARCHAR(32) NOT NULL,
    tipo_pagamento ENUM('debito', 'credito') NOT NULL,
    cartao_ultimos4 CHAR(4) NOT NULL,
    cartao_bandeira VARCHAR(32) NOT NULL,
    pagamento_token VARCHAR(64) NOT NULL,
    validade CHAR(5) NOT NULL,
    nome_cartao VARCHAR(255) NOT NULL,
    cpf_cartao VARCHAR(14) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_criado_em (criado_em),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rate_limits (
    bucket_key CHAR(64) PRIMARY KEY,
    attempts INT NOT NULL,
    window_start INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;