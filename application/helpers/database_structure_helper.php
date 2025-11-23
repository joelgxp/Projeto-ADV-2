<?php
/**
 * Helper para verificação de estrutura do banco de dados
 * 
 * ATENÇÃO: Este helper é uma solução temporária para lidar com estruturas
 * inconsistentes do banco de dados. O ideal é garantir que todas as migrations
 * sejam executadas e remover essas verificações.
 * 
 * @package    CodeIgniter
 * @subpackage Helpers
 * @category   Database
 */

if (!function_exists('get_table_columns')) {
    /**
     * Obtém as colunas de uma tabela (com cache)
     * 
     * @param string $table Nome da tabela
     * @return array|false Array de colunas ou false se a tabela não existir
     */
    function get_table_columns($table)
    {
        static $cache = [];
        
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        
        $CI =& get_instance();
        
        if (!$CI->db->table_exists($table)) {
            $cache[$table] = false;
            return false;
        }
        
        $cache[$table] = $CI->db->list_fields($table);
        return $cache[$table];
    }
}

if (!function_exists('column_exists')) {
    /**
     * Verifica se uma coluna existe em uma tabela
     * 
     * @param string $table Nome da tabela
     * @param string $column Nome da coluna
     * @return bool
     */
    function column_exists($table, $column)
    {
        $columns = get_table_columns($table);
        return $columns !== false && in_array($column, $columns);
    }
}

if (!function_exists('get_clientes_pk')) {
    /**
     * Obtém a chave primária da tabela clientes
     * 
     * @return string|null 'idClientes', 'id' ou null
     */
    function get_clientes_pk()
    {
        $columns = get_table_columns('clientes');
        if ($columns === false) {
            return null;
        }
        
        if (in_array('idClientes', $columns)) {
            return 'idClientes';
        }
        if (in_array('id', $columns)) {
            return 'id';
        }
        return null;
    }
}

if (!function_exists('get_clientes_nome_column')) {
    /**
     * Obtém a coluna de nome da tabela clientes
     * 
     * @return string|null 'nomeCliente', 'nome' ou null
     */
    function get_clientes_nome_column()
    {
        $columns = get_table_columns('clientes');
        if ($columns === false) {
            return null;
        }
        
        if (in_array('nomeCliente', $columns)) {
            return 'nomeCliente';
        }
        if (in_array('nome', $columns)) {
            return 'nome';
        }
        return null;
    }
}

if (!function_exists('get_usuarios_pk')) {
    /**
     * Obtém a chave primária da tabela usuarios
     * 
     * @return string|null 'idUsuarios', 'id' ou null
     */
    function get_usuarios_pk()
    {
        $columns = get_table_columns('usuarios');
        if ($columns === false) {
            return null;
        }
        
        if (in_array('idUsuarios', $columns)) {
            return 'idUsuarios';
        }
        if (in_array('id', $columns)) {
            return 'id';
        }
        return null;
    }
}

