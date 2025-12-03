<?php
/**
 * Script para verificar se existe emitente cadastrado
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

echo "========================================\n";
echo "VERIFICAÇÃO DE EMITENTE\n";
echo "========================================\n\n";

// Verificar se a tabela existe
$result = mysqli_query($conn, "SHOW TABLES LIKE 'emitente'");
if (!$result || mysqli_num_rows($result) == 0) {
    echo "❌ Tabela 'emitente' não existe!\n";
    echo "Verificando estrutura do banco...\n\n";
    
    // Listar todas as tabelas
    $tables = mysqli_query($conn, "SHOW TABLES");
    echo "Tabelas disponíveis:\n";
    while ($row = mysqli_fetch_array($tables)) {
        echo "- " . $row[0] . "\n";
    }
    mysqli_close($conn);
    exit;
}

// Verificar se existe emitente
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM emitente");
$row = mysqli_fetch_assoc($result);
echo "Total de emitentes cadastrados: {$row['total']}\n\n";

if ($row['total'] > 0) {
    echo "✅ Emitente encontrado:\n";
    echo str_repeat("-", 80) . "\n";
    $emitente = mysqli_query($conn, "SELECT * FROM emitente LIMIT 1");
    $data = mysqli_fetch_assoc($emitente);
    foreach ($data as $key => $value) {
        if (!empty($value)) {
            echo "$key: $value\n";
        }
    }
} else {
    echo "❌ Nenhum emitente cadastrado!\n\n";
    echo "Para cadastrar o emitente:\n";
    echo "1. Faça login como administrador\n";
    echo "2. Acesse: Configurações > Emitente\n";
    echo "3. Preencha os dados do escritório e clique em 'Cadastrar'\n\n";
    
    // Mostrar estrutura da tabela
    echo "Estrutura da tabela 'emitente':\n";
    echo str_repeat("-", 80) . "\n";
    $structure = mysqli_query($conn, "DESCRIBE emitente");
    echo "Campo | Tipo | Null | Key | Default\n";
    while ($row = mysqli_fetch_assoc($structure)) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
    }
}

mysqli_close($conn);

