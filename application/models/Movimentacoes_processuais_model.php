<?php

class Movimentacoes_processuais_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        if (!$this->db->table_exists('movimentacoes_processuais')) {
            return [];
        }

        $this->db->select($fields . ', movimentacoes_processuais.*');
        $this->db->from($table);
        $this->db->order_by('movimentacoes_processuais.dataMovimentacao', 'DESC');
        $this->db->limit($perpage, $start);

        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Movimentacoes_processuais_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        if (!$this->db->table_exists('movimentacoes_processuais')) {
            return false;
        }

        $this->db->where('idMovimentacoes', $id);
        $this->db->limit(1);
        $query = $this->db->get('movimentacoes_processuais');
        
        if ($query === false) {
            log_message('error', 'Erro na query Movimentacoes_processuais_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function getByProcesso($processoId)
    {
        if (!$this->db->table_exists('movimentacoes_processuais')) {
            return [];
        }

        $this->db->where('processos_id', $processoId);
        $this->db->order_by('dataMovimentacao', 'DESC');
        $query = $this->db->get('movimentacoes_processuais');
        
        if ($query === false) {
            log_message('error', 'Erro na query Movimentacoes_processuais_model::getByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    public function add($table, $data)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Movimentacoes_processuais_model::add");
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Movimentacoes_processuais_model::edit");
            return false;
        }

        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Movimentacoes_processuais_model::delete");
            return false;
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        if (!$this->db->table_exists($table)) {
            return 0;
        }
        return $this->db->count_all($table);
    }

    /**
     * Verifica se movimentação já existe
     */
    public function verificarMovimentacaoExistente($processoId, $dataMovimentacao, $titulo)
    {
        if (!$this->db->table_exists('movimentacoes_processuais')) {
            return false;
        }

        $this->db->where('processos_id', $processoId);
        if ($dataMovimentacao) {
            $this->db->where('dataMovimentacao', $dataMovimentacao);
        }
        if ($titulo) {
            $this->db->where('titulo', $titulo);
        }
        $this->db->limit(1);
        $query = $this->db->get('movimentacoes_processuais');
        
        return $query->num_rows() > 0;
    }
}

