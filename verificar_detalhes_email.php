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
echo "DETALHES DOS EMAILS ENVIADOS\n";
echo "========================================\n\n";

// Verificar estrutura da tabela
$result = mysqli_query($conn, "DESCRIBE email_queue");
echo "Campos da tabela email_queue:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}

echo "\n========================================\n\n";

// Verificar emails enviados
$result = mysqli_query($conn, "SELECT * FROM email_queue WHERE id IN (1, 2, 3) ORDER BY id DESC");

while ($email = mysqli_fetch_assoc($result)) {
    echo "ID: {$email['id']}\n";
    echo "Para: {$email['to']}\n";
    echo "Assunto: {$email['subject']}\n";
    echo "Status: {$email['status']}\n";
    echo "Tentativas: " . ($email['attempts'] ?? 'não registrado') . "\n";
    echo "Criado: {$email['created_at']}\n";
    echo "Última tentativa: " . ($email['last_attempt'] ?? 'não registrado') . "\n";
    
    // Verificar se há mensagem de erro ou detalhes
    if (isset($email['error_message'])) {
        echo "Erro: {$email['error_message']}\n";
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

mysqli_close($conn);

