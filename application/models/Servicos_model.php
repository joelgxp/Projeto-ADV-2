<?php

class Servicos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        // Usa servicos_juridicos se a tabela existir, senão tenta servicos
        $tableName = $this->db->table_exists('servicos_juridicos') ? 'servicos_juridicos' : ($this->db->table_exists('servicos') ? 'servicos' : $table);
        
        $this->db->select($fields);
        $this->db->from($tableName);
        $this->db->order_by('idServicos', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->like('nome', $where);
            $this->db->or_like('descricao', $where);
            if ($this->db->table_exists($tableName)) {
                $columns = $this->db->list_fields($tableName);
                if (in_array('tipo_servico', $columns)) {
                    $this->db->or_like('tipo_servico', $where);
                }
            }
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Servicos_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $tableName = $this->db->table_exists('servicos_juridicos') ? 'servicos_juridicos' : ($this->db->table_exists('servicos') ? 'servicos' : 'servicos');
        
        $this->db->where('idServicos', $id);
        $this->db->limit(1);

        $query = $this->db->get($tableName);
        
        if ($query === false) {
            log_message('error', 'Erro na query Servicos_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all($table);
    }
}
