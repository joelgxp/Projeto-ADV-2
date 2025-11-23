<?php
/**
 * Script de verificação de migrations
 * 
 * USO: php verificar_migrations.php
 * 
 * IMPORTANTE: Remover este arquivo após o uso!
 */

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
} else {
    die("❌ Arquivo .env não encontrado!\n");
}

$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

echo "========================================\n";
echo "VERIFICAÇÃO DE MIGRATIONS\n";
echo "========================================\n\n";

$conn = @mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("❌ Erro de conexão: " . mysqli_connect_error() . "\n");
}

// Verificar se tabela migrations existe
$result = mysqli_query($conn, "SHOW TABLES LIKE 'migrations'");
if (!$result || mysqli_num_rows($result) == 0) {
    echo "⚠️  Tabela 'migrations' não existe. Criando...\n";
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS migrations (version BIGINT(20) NOT NULL)");
    echo "✅ Tabela 'migrations' criada!\n\n";
}

// Verificar migrations executadas
echo "=== MIGRATIONS EXECUTADAS ===\n";
$result = mysqli_query($conn, "SELECT * FROM migrations ORDER BY version DESC LIMIT 15");
if ($result && mysqli_num_rows($result) > 0) {
    echo "Últimas migrations:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  ✅ " . $row['version'] . "\n";
    }
} else {
    echo "⚠️  Nenhuma migration executada ainda!\n";
}
echo "\n";

// Verificar tabelas principais
echo "=== VERIFICAÇÃO DE TABELAS ===\n";
$tabelas = [
    'processos' => 'Processos',
    'prazos' => 'Prazos',
    'audiencias' => 'Audiências',
    'processos_cache' => 'Cache de Processos',
    'partes_processo' => 'Partes do Processo',
    'documentos_processuais' => 'Documentos Processuais',
    'clientes' => 'Clientes',
    'usuarios' => 'Usuários',
    'configuracoes' => 'Configurações'
];

$faltando = [];
foreach ($tabelas as $tabela => $nome) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tabela'");
    if ($result && mysqli_num_rows($result) > 0) {
        // Contar registros
        $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM $tabela");
        $row = mysqli_fetch_assoc($count);
        echo "✅ $nome ($tabela) - {$row['total']} registros\n";
    } else {
        echo "❌ $nome ($tabela) NÃO EXISTE\n";
        $faltando[] = $tabela;
    }
}

if (!empty($faltando)) {
    echo "\n⚠️  ATENÇÃO: As seguintes tabelas estão faltando:\n";
    foreach ($faltando as $tabela) {
        echo "   - $tabela\n";
    }
    echo "\n💡 Execute as migrations: php index.php tools migrate\n";
} else {
    echo "\n✅ Todas as tabelas principais existem!\n";
}

// Verificar configurações
echo "\n=== VERIFICAÇÃO DE CONFIGURAÇÕES ===\n";
$configs = [
    'processo_notification' => 'Notificação de Processos',
    'prazo_notification' => 'Notificação de Prazos',
    'audiencia_notification' => 'Notificação de Audiências'
];

foreach ($configs as $config => $nome) {
    $result = mysqli_query($conn, "SELECT * FROM configuracoes WHERE config = '$config'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ $nome: {$row['valor']}\n";
    } else {
        echo "⚠️  $nome: não configurado\n";
    }
}

mysqli_close($conn);

echo "\n========================================\n";
echo "FIM DA VERIFICAÇÃO\n";
echo "========================================\n";

