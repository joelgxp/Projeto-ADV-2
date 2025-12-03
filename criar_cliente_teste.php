<?php
/**
 * Script para criar cliente de teste
 * 
 * IMPORTANTE: Remover este arquivo após o uso!
 */

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

$email = 'joelvieirasouza@gmail.com';
$nome = 'Joel Vieira de Souza';
$senha_hash = password_hash('123456', PASSWORD_DEFAULT);

// Verificar se já existe
$check = mysqli_query($conn, "SELECT idClientes FROM clientes WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
if ($check && mysqli_num_rows($check) > 0) {
    echo "Cliente já existe com este email!\n";
    mysqli_close($conn);
    exit;
}

// Criar cliente
$sql = "INSERT INTO clientes (nomeCliente, email, senha, dataCadastro) VALUES (
    '" . mysqli_real_escape_string($conn, $nome) . "',
    '" . mysqli_real_escape_string($conn, $email) . "',
    '" . mysqli_real_escape_string($conn, $senha_hash) . "',
    NOW()
)";

if (mysqli_query($conn, $sql)) {
    $id = mysqli_insert_id($conn);
    echo "✅ Cliente criado com sucesso!\n\n";
    echo "ID: $id\n";
    echo "Nome: $nome\n";
    echo "Email: $email\n";
    echo "Senha: 123456\n\n";
    echo "Agora você pode testar o reset de senha!\n";
} else {
    echo "❌ Erro ao criar cliente: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);

