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
echo "VERIFICAÇÃO DA FILA DE EMAILS\n";
echo "========================================\n\n";

// Verificar total de emails
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM email_queue");
$row = mysqli_fetch_assoc($result);
echo "Total de emails na fila: {$row['total']}\n\n";

// Verificar por status
$result = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM email_queue GROUP BY status");
echo "Status dos emails:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "- {$row['status']}: {$row['total']}\n";
}

// Listar últimos 10 emails
echo "\nÚltimos 10 emails:\n";
echo str_repeat("=", 80) . "\n";
$result = mysqli_query($conn, "SELECT * FROM email_queue ORDER BY id DESC LIMIT 10");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: {$row['id']}\n";
        echo "Para: {$row['to']}\n";
        echo "Assunto: " . substr($row['subject'], 0, 50) . "...\n";
        echo "Status: {$row['status']}\n";
        echo "Criado: {$row['created_at']}\n";
        if (!empty($row['last_attempt'])) {
            echo "Última tentativa: {$row['last_attempt']}\n";
        }
        echo str_repeat("-", 80) . "\n";
    }
} else {
    echo "Nenhum email na fila.\n";
}

mysqli_close($conn);

