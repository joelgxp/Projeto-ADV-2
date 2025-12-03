<?php
/**
 * Script de debug para reset de senha
 * 
 * Este script testa o fluxo completo de recuperação de senha
 * 
 * USO: Acesse via navegador: http://localhost/mapos/debug_reset_senha.php?email=seu@email.com
 * IMPORTANTE: Remover este arquivo após o debug!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

$email = $_GET['email'] ?? 'joelvieirasouza@gmail.com';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Reset Senha</title>";
echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .ok{color:green;} .erro{color:red;} .warn{color:orange;} pre{background:#f5f5f5;padding:10px;overflow:auto;}</style>";
echo "</head><body>";

echo "<h1>🔍 Debug - Reset de Senha</h1>";
echo "<h2>Email testado: <strong>$email</strong></h2>";
echo "<hr>";

// Testar conexão com banco
echo "<h3>1. Teste de Conexão com Banco de Dados</h3>";
$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

$conn = @mysqli_connect($hostname, $username, $password, $database);
if ($conn) {
    echo "<p class='ok'>✅ Conexão OK</p>";
} else {
    echo "<p class='erro'>❌ ERRO DE CONEXÃO: " . mysqli_connect_error() . "</p>";
    die("Não é possível continuar sem conexão ao banco.");
}
echo "<hr>";

// Verificar se cliente existe
echo "<h3>2. Verificar se Cliente Existe</h3>";
$query = mysqli_query($conn, "SELECT idClientes, nomeCliente, email FROM clientes WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
if ($query && mysqli_num_rows($query) > 0) {
    $cliente = mysqli_fetch_assoc($query);
    echo "<p class='ok'>✅ Cliente encontrado:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $cliente['idClientes'] . "</li>";
    echo "<li><strong>Nome:</strong> " . $cliente['nomeCliente'] . "</li>";
    echo "<li><strong>Email:</strong> " . $cliente['email'] . "</li>";
    echo "</ul>";
    $cliente_id = $cliente['idClientes'];
} else {
    echo "<p class='erro'>❌ Cliente NÃO encontrado com email: $email</p>";
    echo "<p>Verificando todos os clientes...</p>";
    $all = mysqli_query($conn, "SELECT idClientes, email FROM clientes LIMIT 5");
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($all)) {
        echo "<li>ID: {$row['idClientes']}, Email: {$row['email']}</li>";
    }
    echo "</ul>";
    mysqli_close($conn);
    die("</body></html>");
}
echo "<hr>";

// Verificar estrutura da tabela email_queue
echo "<h3>3. Verificar Estrutura da Tabela email_queue</h3>";
$result = mysqli_query($conn, "DESCRIBE email_queue");
if ($result) {
    echo "<p class='ok'>✅ Tabela email_queue existe. Estrutura:</p>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='erro'>❌ Erro ao verificar tabela: " . mysqli_error($conn) . "</p>";
}
echo "<hr>";

// Verificar emails na fila
echo "<h3>4. Verificar Emails na Fila</h3>";
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM email_queue");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total de emails na fila: <strong>{$row['total']}</strong></p>";
    
    $result = mysqli_query($conn, "SELECT id, `to`, subject, status, created_at FROM email_queue ORDER BY id DESC LIMIT 10");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<p>Últimos 10 emails:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Para</th><th>Assunto</th><th>Status</th><th>Data</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            $highlight = ($row['to'] == $email) ? "style='background: yellow'" : "";
            echo "<tr $highlight>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['to']}</td>";
            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warn'>⚠️ Nenhum email na fila</p>";
    }
} else {
    echo "<p class='erro'>❌ Erro ao verificar fila: " . mysqli_error($conn) . "</p>";
}
echo "<hr>";

// Verificar resets_de_senha
echo "<h3>5. Verificar Tokens de Reset de Senha</h3>";
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM resets_de_senha WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total de tokens para este email: <strong>{$row['total']}</strong></p>";
    
    $result = mysqli_query($conn, "SELECT id, email, token, token_utilizado, data_expiracao, created_at FROM resets_de_senha WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' ORDER BY id DESC LIMIT 5");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<p>Últimos tokens:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Email</th><th>Token</th><th>Utilizado</th><th>Expira</th><th>Criado</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            $expired = strtotime($row['data_expiracao']) < time() ? " <span class='erro'>(EXPIRADO)</span>" : "";
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>" . substr($row['token'], 0, 10) . "...</td>";
            echo "<td>" . ($row['token_utilizado'] ? 'Sim' : 'Não') . "</td>";
            echo "<td>{$row['data_expiracao']}$expired</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warn'>⚠️ Nenhum token encontrado para este email</p>";
    }
} else {
    echo "<p class='erro'>❌ Erro ao verificar tokens: " . mysqli_error($conn) . "</p>";
}
echo "<hr>";

// Testar inserção manual
echo "<h3>6. Testar Inserção Manual na Fila</h3>";
$test_data = [
    'to' => $email,
    'subject' => 'TESTE - Debug Reset Senha',
    'message' => '<html><body>Email de teste</body></html>',
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

$sql = "INSERT INTO email_queue (`to`, subject, message, status, created_at) VALUES (
    '" . mysqli_real_escape_string($conn, $test_data['to']) . "',
    '" . mysqli_real_escape_string($conn, $test_data['subject']) . "',
    '" . mysqli_real_escape_string($conn, $test_data['message']) . "',
    '" . mysqli_real_escape_string($conn, $test_data['status']) . "',
    '" . mysqli_real_escape_string($conn, $test_data['created_at']) . "'
)";

if (mysqli_query($conn, $sql)) {
    $test_id = mysqli_insert_id($conn);
    echo "<p class='ok'>✅ Inserção manual bem-sucedida! ID: $test_id</p>";
    
    // Verificar se aparece na consulta
    $verify = mysqli_query($conn, "SELECT * FROM email_queue WHERE id = $test_id");
    if ($verify && mysqli_num_rows($verify) > 0) {
        echo "<p class='ok'>✅ Email encontrado após inserção</p>";
    } else {
        echo "<p class='erro'>❌ Email NÃO encontrado após inserção (isso é estranho!)</p>";
    }
} else {
    echo "<p class='erro'>❌ Erro na inserção manual: " . mysqli_error($conn) . "</p>";
    echo "<pre>SQL: " . htmlspecialchars($sql) . "</pre>";
}
echo "<hr>";

// Verificar logs
echo "<h3>7. Verificar Logs Recentes</h3>";
$log_dir = __DIR__ . '/application/logs/';
if (is_dir($log_dir)) {
    $files = glob($log_dir . 'log-*.php');
    if ($files) {
        rsort($files);
        $latest = $files[0];
        echo "<p>Último arquivo de log: <strong>" . basename($latest) . "</strong></p>";
        echo "<p>Últimas 30 linhas relacionadas a reset/email:</p>";
        echo "<pre>";
        $lines = file($latest);
        $relevant = [];
        foreach ($lines as $line) {
            if (stripos($line, 'reset') !== false || stripos($line, 'email') !== false || stripos($line, 'senha') !== false || stripos($line, 'gerarToken') !== false || stripos($line, 'enviarRecuperar') !== false) {
                $relevant[] = $line;
            }
        }
        if (count($relevant) > 0) {
            echo htmlspecialchars(implode('', array_slice($relevant, -30)));
        } else {
            echo "Nenhuma linha relevante encontrada nos logs.";
        }
        echo "</pre>";
    } else {
        echo "<p class='warn'>⚠️ Nenhum arquivo de log encontrado</p>";
    }
} else {
    echo "<p class='warn'>⚠️ Diretório de logs não encontrado: $log_dir</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<h3>8. Próximos Passos</h3>";
echo "<ol>";
echo "<li>Tente fazer uma solicitação de reset de senha via formulário</li>";
echo "<li>Verifique os logs em <code>application/logs/log-YYYY-MM-DD.php</code></li>";
echo "<li>Procure por linhas com '=== INÍCIO gerarTokenResetarSenha ==='</li>";
echo "<li>Verifique se o email aparece na tabela email_queue após a solicitação</li>";
echo "</ol>";

echo "<p><strong>Debug concluído!</strong></p>";
echo "<p>Se encontrar problemas, verifique os logs acima e compare com o comportamento esperado.</p>";
echo "</body></html>";
