<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Trait para Soft Delete (RN 12.1)
 * 
 * Permite que models usem soft delete (deleted_at, deleted_by)
 * em vez de exclusão física do banco de dados
 */
trait Soft_delete
{
    /**
     * Marca registro como deletado (soft delete)
     * 
     * @param string $table Nome da tabela
     * @param string $fieldID Nome do campo ID
     * @param int $ID ID do registro
     * @return bool
     */
    public function soft_delete($table, $fieldID, $ID)
    {
        // Traits têm acesso a propriedades do objeto que os usa
        // Se usado em um model, $this->db e $this->session estarão disponíveis
        if (!isset($this->db) || !$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em soft_delete");
            return false;
        }
        
        // Verifica se colunas existem
        $columns = $this->db->list_fields($table);
        $tem_deleted_at = in_array('deleted_at', $columns);
        $tem_deleted_by = in_array('deleted_by', $columns);
        
        $data = [];
        
        if ($tem_deleted_at) {
            $data['deleted_at'] = date('Y-m-d H:i:s');
        }
        
        if ($tem_deleted_by) {
            $user_id = isset($this->session) ? $this->session->userdata('id_admin') : null;
            $data['deleted_by'] = $user_id;
        }
        
        if (empty($data)) {
            // Se não tem colunas de soft delete, faz exclusão física
            if (method_exists($this, 'delete')) {
                return $this->delete($table, $fieldID, $ID);
            }
            // Fallback: exclusão física direta
            $this->db->where($fieldID, $ID);
            $this->db->delete($table);
            return $this->db->affected_rows() >= 0;
        }
        
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);
        
        return $this->db->affected_rows() >= 0;
    }
    
    /**
     * Restaura registro deletado (soft delete)
     * 
     * @param string $table Nome da tabela
     * @param string $fieldID Nome do campo ID
     * @param int $ID ID do registro
     * @return bool
     */
    public function restore($table, $fieldID, $ID)
    {
        if (!isset($this->db) || !$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em restore");
            return false;
        }
        
        $columns = $this->db->list_fields($table);
        $tem_deleted_at = in_array('deleted_at', $columns);
        $tem_deleted_by = in_array('deleted_by', $columns);
        
        $data = [];
        
        if ($tem_deleted_at) {
            $data['deleted_at'] = null;
        }
        
        if ($tem_deleted_by) {
            $data['deleted_by'] = null;
        }
        
        if (empty($data)) {
            return false;
        }
        
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);
        
        return $this->db->affected_rows() >= 0;
    }
    
    /**
     * Filtra query para excluir registros deletados (soft delete)
     * 
     * @param object $db Instância do database
     * @param string $table Nome da tabela
     * @param bool $incluir_deletados Se true, inclui registros deletados
     * @return object
     */
    public function with_deleted($incluir_deletados = false)
    {
        if (!isset($this->db)) {
            return $this;
        }
        
        if (!$incluir_deletados) {
            // Precisa do nome da tabela - será aplicado no método get() do model
            // Este método é usado para configurar o filtro
            $this->db->where('deleted_at IS NULL');
        }
        
        return $this;
    }
    
    /**
     * Filtra query para incluir apenas registros deletados
     * 
     * @return $this
     */
    public function only_deleted()
    {
        if (isset($this->db)) {
            $this->db->where('deleted_at IS NOT NULL');
        }
        
        return $this;
    }
}

