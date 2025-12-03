<?php

class Email_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('id', 'desc');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        // Verificar se houve erro na query
        if (!$query) {
            $error = $this->db->error();
            log_message('error', 'Erro ao buscar emails: ' . json_encode($error));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();
        
        // Garantir que sempre retorne um array quando não for 'one'
        // O result() retorna um array de objetos, então verificar se é array ou se está vazio
        if (!$one) {
            // Se result() retornar false ou null, retornar array vazio
            if ($result === false || $result === null) {
                return [];
            }
            // Se não for array, converter para array
            if (!is_array($result)) {
                return [];
            }
        }

        return $result;
    }

    public function getById($id)
    {
        $this->db->where('id', $id);
        $this->db->limit(1);

        return $this->db->get('email_queue')->row();
    }

    public function add($table, $data)
    {
        $result = $this->db->insert($table, $data);
        
        if (!$result) {
            $error = $this->db->error();
            log_message('error', 'Erro ao inserir na tabela ' . $table . ': ' . json_encode($error));
            log_message('error', 'Dados que tentaram ser inseridos: ' . json_encode($data));
            return false;
        }
        
        if ($this->db->affected_rows() == '1') {
            log_message('info', 'Registro inserido com sucesso na tabela ' . $table . '. ID: ' . $this->db->insert_id());
            return true;
        }

        log_message('warning', 'Insert executado mas affected_rows não é 1. Tabela: ' . $table . ', affected_rows: ' . $this->db->affected_rows());
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
