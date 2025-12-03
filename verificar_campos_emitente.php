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
echo "ESTRUTURA DA TABELA emitente\n";
echo "========================================\n\n";

$result = mysqli_query($conn, "DESCRIBE emitente");
echo "Campos da tabela:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "- {$row['Field']} ({$row['Type']}) - Null: {$row['Null']} - Default: {$row['Default']}\n";
}

echo "\n========================================\n";
echo "DADOS DO EMITENTE\n";
echo "========================================\n\n";

$result = mysqli_query($conn, "SELECT * FROM emitente LIMIT 1");
if ($result && mysqli_num_rows($result) > 0) {
    $emitente = mysqli_fetch_assoc($result);
    foreach ($emitente as $campo => $valor) {
        echo "$campo: " . (empty($valor) ? '(vazio)' : $valor) . "\n";
    }
}

mysqli_close($conn);

