<?php
/**
 * Script para criar emitente de teste
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

// Verificar se já existe
$check = mysqli_query($conn, "SELECT id FROM emitente LIMIT 1");
if ($check && mysqli_num_rows($check) > 0) {
    echo "⚠️ Emitente já existe!\n";
    $existing = mysqli_fetch_assoc($check);
    echo "ID: {$existing['id']}\n\n";
    echo "Para atualizar, use a interface administrativa:\n";
    echo "http://localhost/mapos/index.php/adv/emitente\n";
    mysqli_close($conn);
    exit;
}

// Dados do emitente de teste
$dados = [
    'nome' => 'Escritório de Advocacia - Teste',
    'cnpj' => '00.000.000/0001-00',
    'ie' => '',
    'cep' => '01000-000',
    'rua' => 'Rua Exemplo',
    'numero' => '123',
    'bairro' => 'Centro',
    'cidade' => 'São Paulo',
    'uf' => 'SP',
    'telefone' => '(11) 0000-0000',
    'celular' => '(11) 90000-0000',
    'email' => 'contato@escritorio.com.br',
    'site' => 'https://www.escritorio.com.br',
    'logo' => 'assets/uploads/logo.png' // Você pode atualizar depois
];

// Criar emitente
$sql = "INSERT INTO emitente (
    nome, cnpj, ie, cep, rua, numero, bairro, cidade, uf, 
    telefone, celular, email, site, logo
) VALUES (
    '" . mysqli_real_escape_string($conn, $dados['nome']) . "',
    '" . mysqli_real_escape_string($conn, $dados['cnpj']) . "',
    '" . mysqli_real_escape_string($conn, $dados['ie']) . "',
    '" . mysqli_real_escape_string($conn, $dados['cep']) . "',
    '" . mysqli_real_escape_string($conn, $dados['rua']) . "',
    '" . mysqli_real_escape_string($conn, $dados['numero']) . "',
    '" . mysqli_real_escape_string($conn, $dados['bairro']) . "',
    '" . mysqli_real_escape_string($conn, $dados['cidade']) . "',
    '" . mysqli_real_escape_string($conn, $dados['uf']) . "',
    '" . mysqli_real_escape_string($conn, $dados['telefone']) . "',
    '" . mysqli_real_escape_string($conn, $dados['celular']) . "',
    '" . mysqli_real_escape_string($conn, $dados['email']) . "',
    '" . mysqli_real_escape_string($conn, $dados['site']) . "',
    '" . mysqli_real_escape_string($conn, $dados['logo']) . "'
)";

if (mysqli_query($conn, $sql)) {
    $id = mysqli_insert_id($conn);
    echo "✅ Emitente criado com sucesso!\n\n";
    echo "ID: $id\n";
    echo "Nome: {$dados['nome']}\n";
    echo "Email: {$dados['email']}\n";
    echo "CNPJ: {$dados['cnpj']}\n";
    echo "Cidade: {$dados['cidade']}/{$dados['uf']}\n\n";
    echo "⚠️ IMPORTANTE:\n";
    echo "1. Este é um emitente de TESTE com dados fictícios\n";
    echo "2. Atualize os dados reais através da interface:\n";
    echo "   http://localhost/mapos/index.php/adv/emitente\n";
    echo "3. Faça login como administrador para editar\n\n";
    echo "Agora o reset de senha deve funcionar!\n";
} else {
    echo "❌ Erro ao criar emitente: " . mysqli_error($conn) . "\n";
    echo "SQL: " . htmlspecialchars($sql) . "\n";
}

mysqli_close($conn);

