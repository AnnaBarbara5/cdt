-- Execute apenas se a tabela antiga existir com PAN/CVV em texto claro.
ALTER TABLE clientes_cartao
    DROP COLUMN IF EXISTS numero_cartao,
    DROP COLUMN IF EXISTS cvv;

ALTER TABLE clientes_cartao
    ADD COLUMN IF NOT EXISTS cartao_ultimos4 CHAR(4) NOT NULL DEFAULT '0000',
    ADD COLUMN IF NOT EXISTS cartao_bandeira VARCHAR(32) NOT NULL DEFAULT 'desconhecida',
    ADD COLUMN IF NOT EXISTS pagamento_token VARCHAR(64) NOT NULL DEFAULT '';