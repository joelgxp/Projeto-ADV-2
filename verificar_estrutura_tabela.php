<?php
define('BASEPATH', true);
require 'index.php';

$CI =& get_instance();
$CI->load->database();

echo "=== Estrutura da Tabela usuarios ===\n\n";

if ($CI->db->table_exists('usuarios')) {
    $columns = $CI->db->list_fields('usuarios');
    
    echo "Colunas encontradas:\n";
    foreach($columns as $col) {
        echo "- $col\n";
    }
    
    echo "\n=== Verificando dados ===\n";
    $total = $CI->db->count_all('usuarios');
    echo "Total de registros: $total\n";
    
    if ($total > 0) {
        $CI->db->limit(1);
        $user = $CI->db->get('usuarios')->row();
        if ($user) {
            echo "\nPrimeiro registro:\n";
            print_r($user);
        }
    }
} else {
    echo "❌ Tabela 'usuarios' não existe!\n";
}

