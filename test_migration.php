<?php
/**
 * Script de teste de migrations
 * 
 * USO: php test_migration.php
 * 
 * IMPORTANTE: Remover este arquivo após o uso!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
} else {
    die("❌ Arquivo .env não encontrado!\n");
}

// Carregar CodeIgniter
define('ENVIRONMENT', $_ENV['APP_ENVIRONMENT'] ?? 'development');
define('BASEPATH', __DIR__ . '/application/vendor/codeigniter/framework/system/');
define('APPPATH', __DIR__ . '/application/');

// Bootstrap CodeIgniter
require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/config.php';
require_once APPPATH . 'config/database.php';

echo "========================================\n";
echo "TESTE DE MIGRATIONS\n";
echo "========================================\n\n";

// Conectar ao banco
$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

$conn = @mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("❌ Erro de conexão: " . mysqli_connect_error() . "\n");
}

echo "✅ Conexão com banco estabelecida\n\n";

// Verificar tabela migrations
echo "=== VERIFICAÇÃO INICIAL ===\n";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'migrations'");
if (!$result || mysqli_num_rows($result) == 0) {
    echo "⚠️  Tabela 'migrations' não existe. Criando...\n";
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS migrations (version BIGINT(20) NOT NULL)");
    echo "✅ Tabela 'migrations' criada!\n\n";
} else {
    echo "✅ Tabela 'migrations' existe\n";
    
    // Ver migrations executadas
    $result = mysqli_query($conn, "SELECT * FROM migrations ORDER BY version DESC LIMIT 5");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "Últimas migrations executadas:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "  - " . $row['version'] . "\n";
        }
    }
    echo "\n";
}

// Verificar diretório de migrations
$migrationPath = __DIR__ . '/application/database/migrations/';
echo "=== VERIFICAÇÃO DE ARQUIVOS ===\n";
if (!is_dir($migrationPath)) {
    die("❌ Diretório de migrations não existe: $migrationPath\n");
}
echo "✅ Diretório existe: $migrationPath\n";

if (!is_readable($migrationPath)) {
    die("❌ Sem permissão de leitura: $migrationPath\n");
}
echo "✅ Permissão de leitura OK\n";

// Contar arquivos de migration
$files = glob($migrationPath . '*.php');
echo "✅ Encontrados " . count($files) . " arquivos de migration\n\n";

// Verificar algumas migrations importantes
echo "=== VERIFICAÇÃO DE MIGRATIONS IMPORTANTES ===\n";
$importantMigrations = [
    '20251115061242_criar_tabela_processos.php' => 'Processos',
    '20251115061244_criar_tabela_prazos.php' => 'Prazos',
    '20251115061245_criar_tabela_audiencias.php' => 'Audiências',
    '20251122150000_create_processos_cache.php' => 'Cache de Processos',
    '20251122160000_adaptar_notificacoes_juridicas.php' => 'Notificações'
];

foreach ($importantMigrations as $file => $name) {
    if (file_exists($migrationPath . $file)) {
        echo "✅ $name: arquivo existe\n";
        
        // Verificar sintaxe PHP
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($migrationPath . $file) . " 2>&1", $output, $return);
        if ($return === 0) {
            echo "   ✅ Sintaxe PHP OK\n";
        } else {
            echo "   ❌ Erro de sintaxe:\n";
            foreach ($output as $line) {
                echo "      $line\n";
            }
        }
    } else {
        echo "❌ $name: arquivo NÃO encontrado\n";
    }
}

mysqli_close($conn);

echo "\n========================================\n";
echo "FIM DO TESTE\n";
echo "========================================\n";
echo "\n💡 Se tudo estiver OK, tente executar:\n";
echo "   php index.php tools migrate\n";
echo "\n";

