<?php
/**
 * Script para testar getEmitente diretamente
 */

define('BASEPATH', true);
define('ENVIRONMENT', 'development');

require_once 'index.php';

$CI =& get_instance();
$CI->load->model('sistema_model');

echo "========================================\n";
echo "TESTE getEmitente()\n";
echo "========================================\n\n";

// Verificar conexão
if (!$CI->db->conn_id) {
    die("❌ Erro: Banco de dados não conectado!\n");
}

echo "✅ Banco de dados conectado\n\n";

// Verificar se tabela existe
if (!$CI->db->table_exists('emitente')) {
    die("❌ Erro: Tabela 'emitente' não existe!\n");
}

echo "✅ Tabela 'emitente' existe\n\n";

// Contar registros
$CI->db->from('emitente');
$total = $CI->db->count_all_results();
echo "Total de registros na tabela: $total\n\n";

if ($total == 0) {
    die("❌ Nenhum emitente cadastrado!\n");
}

// Buscar diretamente
echo "Testando query direta:\n";
$query = $CI->db->get('emitente');
if ($query) {
    $result = $query->result();
    echo "Resultados encontrados: " . count($result) . "\n";
    if (count($result) > 0) {
        $emitente = $result[0];
        echo "ID: " . ($emitente->id ?? 'N/A') . "\n";
        echo "Nome: " . ($emitente->nome ?? 'N/A') . "\n";
        echo "Email: " . ($emitente->email ?? 'N/A') . "\n";
    }
} else {
    $error = $CI->db->error();
    echo "❌ Erro na query: " . ($error['message'] ?? 'Erro desconhecido') . "\n";
}

echo "\n----------------------------------------\n";
echo "Testando método getEmitente():\n";
$emitente = $CI->sistema_model->getEmitente();

if ($emitente) {
    echo "✅ Emitente encontrado!\n";
    echo "ID: " . ($emitente->id ?? 'N/A') . "\n";
    echo "Nome: " . ($emitente->nome ?? 'N/A') . "\n";
    echo "Email: " . ($emitente->email ?? 'N/A') . "\n";
} else {
    echo "❌ getEmitente() retornou NULL/vazio\n";
    echo "Verifique os logs para mais detalhes\n";
}

