<?php
/**
 * Script para executar banco.sql e criar o banco de dados
 * 
 * Uso: php executar_banco.php
 */

echo "========================================\n";
echo "Executando banco.sql no MySQL/MariaDB\n";
echo "========================================\n\n";

// ============================================
// CONFIGURAÇÕES - AJUSTE AQUI
// ============================================
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '', // Deixe vazio se não tiver senha
    'database' => 'mapos',
    
    // Dados do administrador (serão substituídos no SQL)
    'admin_name' => 'Administrador',
    'admin_email' => 'admin@admin.com',
    'admin_password' => '123456', // Será convertido para hash
    'admin_created_at' => date('Y-m-d H:i:s'),
];

// ============================================
// VALIDAÇÕES
// ============================================
if (!file_exists('banco.sql')) {
    die("ERRO: Arquivo banco.sql não encontrado!\n");
}

// ============================================
// CONECTAR AO MYSQL
// ============================================
echo "Conectando ao MySQL...\n";
$mysqli = @new mysqli($config['host'], $config['user'], $config['pass']);

if ($mysqli->connect_error) {
    die("ERRO: Não foi possível conectar ao MySQL: " . $mysqli->connect_error . "\n");
}

echo "✓ Conectado com sucesso!\n\n";

// ============================================
// CRIAR BANCO DE DADOS
// ============================================
echo "Criando banco de dados '{$config['database']}'...\n";
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `{$config['database']}` 
                  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

if (!$mysqli->query($sql_create_db)) {
    die("ERRO: Não foi possível criar o banco: " . $mysqli->error . "\n");
}

echo "✓ Banco de dados criado!\n\n";

// ============================================
// SELECIONAR BANCO
// ============================================
if (!$mysqli->select_db($config['database'])) {
    die("ERRO: Não foi possível selecionar o banco: " . $mysqli->error . "\n");
}

// ============================================
// LER E PROCESSAR banco.sql
// ============================================
echo "Lendo arquivo banco.sql...\n";
$sql = file_get_contents('banco.sql');

if ($sql === false) {
    die("ERRO: Não foi possível ler o arquivo banco.sql!\n");
}

echo "✓ Arquivo lido com sucesso!\n\n";

// ============================================
// SUBSTITUIR PLACEHOLDERS
// ============================================
echo "Substituindo placeholders...\n";
$admin_password_hash = password_hash($config['admin_password'], PASSWORD_DEFAULT);

$sql = str_replace('admin_name', $config['admin_name'], $sql);
$sql = str_replace('admin_email', $config['admin_email'], $sql);
$sql = str_replace('admin_password', $admin_password_hash, $sql);
$sql = str_replace('admin_created_at', $config['admin_created_at'], $sql);

echo "✓ Placeholders substituídos!\n";
echo "  - Nome: {$config['admin_name']}\n";
echo "  - Email: {$config['admin_email']}\n";
echo "  - Senha: {$config['admin_password']} (hash gerado)\n";
echo "  - Data: {$config['admin_created_at']}\n\n";

// ============================================
// EXECUTAR SQL
// ============================================
echo "Executando SQL (isso pode levar alguns minutos)...\n";
echo "Aguarde...\n\n";

// Executar múltiplas queries
if ($mysqli->multi_query($sql)) {
    $query_count = 0;
    do {
        $query_count++;
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    if ($mysqli->errno) {
        echo "ERRO na execução: " . $mysqli->error . "\n";
        echo "Query número: $query_count\n";
    } else {
        echo "✓ SQL executado com sucesso!\n";
        echo "  - Total de queries executadas: $query_count\n\n";
    }
} else {
    die("ERRO ao executar SQL: " . $mysqli->error . "\n");
}

// ============================================
// VERIFICAR RESULTADO
// ============================================
echo "Verificando tabelas criadas...\n";
$result = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "✓ Tabelas criadas: " . count($tables) . "\n";
if (count($tables) > 0) {
    echo "  Principais: " . implode(', ', array_slice($tables, 0, 10));
    if (count($tables) > 10) {
        echo " e mais " . (count($tables) - 10) . "...";
    }
    echo "\n\n";
}

// ============================================
// VERIFICAR USUÁRIO ADMIN
// ============================================
echo "Verificando usuário administrador...\n";
$result = $mysqli->query("SELECT idUsuarios, nome, email FROM usuarios WHERE idUsuarios = 1");
if ($result && $row = $result->fetch_assoc()) {
    echo "✓ Usuário admin criado:\n";
    echo "  - ID: {$row['idUsuarios']}\n";
    echo "  - Nome: {$row['nome']}\n";
    echo "  - Email: {$row['email']}\n\n";
} else {
    echo "⚠ Aviso: Não foi possível verificar o usuário admin\n\n";
}

// ============================================
// FINALIZAR
// ============================================
$mysqli->close();

echo "========================================\n";
echo "Banco de dados criado com sucesso! ✓\n";
echo "========================================\n\n";
echo "Credenciais de acesso:\n";
echo "  Email: {$config['admin_email']}\n";
echo "  Senha: {$config['admin_password']}\n\n";
echo "Próximos passos:\n";
echo "1. Configure o arquivo application/.env com as credenciais do banco\n";
echo "2. Acesse o sistema e faça login\n\n";

