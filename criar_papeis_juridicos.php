<?php
/**
 * Script para criar papéis RBAC (Advogado e Assistente)
 * 
 * USO: php criar_papeis_juridicos.php
 * OU: Acesse via navegador: http://localhost/mapos/criar_papeis_juridicos.php
 * 
 * IMPORTANTE: Este script deve ser executado apenas uma vez
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env';
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

echo "<h1>Criação de Papéis RBAC - Sistema Jurídico</h1>\n";
echo "<hr>\n\n";

// Função para criar permissões serializadas
function criarPermissoes($perms_array) {
    return serialize($perms_array);
}

// ============================================
// PAPEL 2: ADVOGADO
// ============================================
$advogado_check = mysqli_query($conn, "SELECT idPermissao FROM permissoes WHERE idPermissao = 2");
if (mysqli_num_rows($advogado_check) > 0) {
    echo "<p>⚠️ Papel 'Advogado' já existe (ID: 2)</p>\n";
} else {
    $permissoes_advogado = [
        'aCliente' => '1',
        'eCliente' => '1',
        'vCliente' => '1',
        'vClienteDadosSensiveis' => '1',
        'eClienteDadosSensiveis' => '1',
        'vClienteProcessos' => '1',
        'vClienteDocumentos' => '1',
        'vClienteFinanceiro' => '1',
        'rCliente' => '1',
        'aProcesso' => '1',
        'eProcesso' => '1',
        'vProcesso' => '1',
        'sProcesso' => '1',
        'aPrazo' => '1',
        'ePrazo' => '1',
        'vPrazo' => '1',
        'dPrazo' => '1',
        'aAudiencia' => '1',
        'eAudiencia' => '1',
        'vAudiencia' => '1',
        'dAudiencia' => '1',
        'cConsultaProcessual' => '1',
        'vLancamento' => '1',
        'rFinanceiro' => '1',
        'aCobranca' => '1',
        'eCobranca' => '1',
        'vCobranca' => '1',
        'cEmail' => '0',
        'cUsuario' => '0',
        'cPermissao' => '0',
        'cSistema' => '0',
        'cBackup' => '0',
        'cAuditoria' => '0',
        'cEmitente' => '0',
    ];

    $permissoes_serializadas = criarPermissoes($permissoes_advogado);
    $permissoes_escaped = mysqli_real_escape_string($conn, $permissoes_serializadas);

    $sql = "INSERT INTO permissoes (idPermissao, nome, permissoes, situacao, data) VALUES (
        2,
        'Advogado',
        '$permissoes_escaped',
        1,
        '" . date('Y-m-d') . "'
    )";

    if (mysqli_query($conn, $sql)) {
        echo "<p>✅ Papel 'Advogado' criado com sucesso (ID: 2)</p>\n";
    } else {
        echo "<p>❌ Erro ao criar papel 'Advogado': " . mysqli_error($conn) . "</p>\n";
    }
}

// ============================================
// PAPEL 3: ASSISTENTE
// ============================================
$assistente_check = mysqli_query($conn, "SELECT idPermissao FROM permissoes WHERE idPermissao = 3");
if (mysqli_num_rows($assistente_check) > 0) {
    echo "<p>⚠️ Papel 'Assistente' já existe (ID: 3)</p>\n";
} else {
    $permissoes_assistente = [
        'aCliente' => '0',
        'eCliente' => '0',
        'vCliente' => '1',
        'vClienteDadosSensiveis' => '0',
        'eClienteDadosSensiveis' => '0',
        'vClienteProcessos' => '1',
        'vClienteDocumentos' => '0',
        'vClienteFinanceiro' => '0',
        'rCliente' => '1',
        'aProcesso' => '0',
        'eProcesso' => '0',
        'vProcesso' => '1',
        'sProcesso' => '0',
        'aPrazo' => '1',
        'ePrazo' => '1',
        'vPrazo' => '1',
        'dPrazo' => '0',
        'aAudiencia' => '1',
        'eAudiencia' => '1',
        'vAudiencia' => '1',
        'dAudiencia' => '1',
        'cConsultaProcessual' => '0',
        'vLancamento' => '0',
        'rFinanceiro' => '0',
        'aCobranca' => '0',
        'eCobranca' => '0',
        'vCobranca' => '1',
        'cEmail' => '0',
        'cUsuario' => '0',
        'cPermissao' => '0',
        'cSistema' => '0',
        'cBackup' => '0',
        'cAuditoria' => '0',
        'cEmitente' => '0',
    ];

    $permissoes_serializadas = criarPermissoes($permissoes_assistente);
    $permissoes_escaped = mysqli_real_escape_string($conn, $permissoes_serializadas);

    $sql = "INSERT INTO permissoes (idPermissao, nome, permissoes, situacao, data) VALUES (
        3,
        'Assistente',
        '$permissoes_escaped',
        1,
        '" . date('Y-m-d') . "'
    )";

    if (mysqli_query($conn, $sql)) {
        echo "<p>✅ Papel 'Assistente' criado com sucesso (ID: 3)</p>\n";
    } else {
        echo "<p>❌ Erro ao criar papel 'Assistente': " . mysqli_error($conn) . "</p>\n";
    }
}

mysqli_close($conn);

echo "<hr>\n";
echo "<p><strong>✅ Processo concluído!</strong></p>\n";
echo "<p>Agora você pode atribuir esses papéis aos usuários através da interface administrativa:</p>\n";
echo "<ul>\n";
echo "<li><strong>Administrador</strong> (ID: 1) - Acesso total ao sistema</li>\n";
echo "<li><strong>Advogado</strong> (ID: 2) - Acesso completo a processos, prazos, audiências e clientes</li>\n";
echo "<li><strong>Assistente</strong> (ID: 3) - Acesso limitado para apoio administrativo</li>\n";
echo "<li><strong>Cliente</strong> - Gerenciado separadamente no portal do cliente (Mine)</li>\n";
echo "</ul>\n";
echo "<p><em>Nota: Após criar os papéis, você pode atribuí-los aos usuários em: Usuários > Editar > Selecionar Permissão</em></p>\n";

