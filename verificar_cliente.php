<?php
require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

$conn = @mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}

echo "========================================\n";
echo "VERIFICAÇÃO DE CLIENTES E USUÁRIOS\n";
echo "========================================\n\n";

// Verificar total de clientes
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM clientes");
$row = mysqli_fetch_assoc($result);
echo "Total de CLIENTES na tabela 'clientes': {$row['total']}\n\n";

if ($row['total'] > 0) {
    echo "Listando todos os clientes:\n";
    echo str_repeat("-", 80) . "\n";
    $all = mysqli_query($conn, "SELECT idClientes, nomeCliente, email, documento FROM clientes ORDER BY idClientes DESC LIMIT 20");
    while ($row = mysqli_fetch_assoc($all)) {
        echo "ID: {$row['idClientes']} | Nome: {$row['nomeCliente']} | Email: {$row['email']} | Doc: {$row['documento']}\n";
    }
} else {
    echo "⚠️ Nenhum cliente cadastrado na tabela 'clientes'\n";
    echo "\nPara testar o reset de senha, você precisa:\n";
    echo "1. Cadastrar um cliente via formulário: http://localhost/mapos/index.php/mine/cadastrar\n";
    echo "2. Ou criar manualmente no banco de dados\n\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Verificar usuários administrativos
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios");
$row = mysqli_fetch_assoc($result);
echo "Total de USUÁRIOS ADMINISTRATIVOS na tabela 'usuarios': {$row['total']}\n\n";

if ($row['total'] > 0) {
    echo "Listando usuários administrativos:\n";
    echo str_repeat("-", 80) . "\n";
    $all = mysqli_query($conn, "SELECT idUsuarios, nome, email FROM usuarios ORDER BY idUsuarios DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($all)) {
        echo "ID: {$row['idUsuarios']} | Nome: {$row['nome']} | Email: {$row['email']}\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "NOTA: O reset de senha funciona apenas para CLIENTES (tabela 'clientes'),\n";
echo "não para usuários administrativos (tabela 'usuarios').\n";

mysqli_close($conn);

