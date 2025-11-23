<?php
/**
 * Script de teste de conexão com banco de dados
 * 
 * USO: php test_db.php
 * 
 * IMPORTANTE: Remover este arquivo após o teste!
 */

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
} else {
    die("❌ Arquivo .env não encontrado em: $envFile\n");
}

$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

echo "========================================\n";
echo "TESTE DE CONEXÃO COM BANCO DE DADOS\n";
echo "========================================\n\n";

echo "Host: $hostname\n";
echo "Database: $database\n";
echo "User: $username\n";
echo "Password: " . (empty($password) ? '❌ NÃO CONFIGURADO' : '✅ Configurado') . "\n\n";

echo "Testando conexão...\n";

$conn = @mysqli_connect($hostname, $username, $password, $database);

if ($conn) {
    echo "✅ CONEXÃO OK!\n\n";
    
    // Testar query
    $result = mysqli_query($conn, "SELECT 1 as test");
    if ($result) {
        echo "✅ Query de teste OK\n";
    } else {
        echo "❌ Erro na query: " . mysqli_error($conn) . "\n";
    }
    
    // Verificar tabelas
    echo "\nVerificando tabelas principais...\n";
    $tables = ['usuarios', 'clientes', 'processos', 'configuracoes'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "✅ Tabela '$table' existe\n";
        } else {
            echo "⚠️ Tabela '$table' não encontrada\n";
        }
    }
    
    mysqli_close($conn);
} else {
    echo "❌ ERRO DE CONEXÃO!\n";
    echo "Mensagem: " . mysqli_connect_error() . "\n";
    echo "Código: " . mysqli_connect_errno() . "\n\n";
    
    echo "Possíveis causas:\n";
    echo "- Credenciais incorretas\n";
    echo "- Host incorreto\n";
    echo "- Banco de dados não existe\n";
    echo "- Firewall bloqueando conexão\n";
    echo "- Servidor MySQL não está rodando\n";
}

echo "\n========================================\n";

