<?php

class Partes_processo_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todas as partes de um processo
     */
    public function getByProcesso($processos_id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return [];
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->order_by('tipo', 'ASC');
        $this->db->order_by('dataCadastro', 'ASC');
        
        $query = $this->db->get('partes_processo');
        
        if ($query === false) {
            log_message('error', 'Erro na query Partes_processo_model::getByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Busca partes por polo (ativo ou passivo)
     */
    public function getByPolo($processos_id, $tipo_polo)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return [];
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->where('tipo_polo', $tipo_polo);
        $this->db->order_by('dataCadastro', 'ASC');
        
        $query = $this->db->get('partes_processo');
        
        if ($query === false) {
            log_message('error', 'Erro na query Partes_processo_model::getByPolo: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Busca parte por ID
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return false;
        }

        $this->db->where('idPartes', $id);
        $this->db->limit(1);
        
        $query = $this->db->get('partes_processo');
        
        if ($query === false) {
            log_message('error', 'Erro na query Partes_processo_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    /**
     * Adiciona uma parte ao processo
     */
    public function add($data)
    {
        if (!$this->db->table_exists('partes_processo')) {
            log_message('error', "Tabela 'partes_processo' não existe em Partes_processo_model::add");
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('partes_processo', $data);
        
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Atualiza uma parte
     */
    public function edit($data, $id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            log_message('error', "Tabela 'partes_processo' não existe em Partes_processo_model::edit");
            return false;
        }

        $this->db->where('idPartes', $id);
        $this->db->update('partes_processo', $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    /**
     * Remove uma parte
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            log_message('error', "Tabela 'partes_processo' não existe em Partes_processo_model::delete");
            return false;
        }

        $this->db->where('idPartes', $id);
        $this->db->delete('partes_processo');
        
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    /**
     * Remove todas as partes de um processo
     */
    public function deleteByProcesso($processos_id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return false;
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->delete('partes_processo');
        
        return true;
    }

    /**
     * Conta partes por processo
     */
    public function countByProcesso($processos_id)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return 0;
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->from('partes_processo');
        return $this->db->count_all_results();
    }

    /**
     * Conta partes por polo
     */
    public function countByPolo($processos_id, $tipo_polo)
    {
        if (!$this->db->table_exists('partes_processo')) {
            return 0;
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->where('tipo', $tipo_polo);
        $this->db->from('partes_processo');
        return $this->db->count_all_results();
    }

    /**
     * Adiciona múltiplas partes de uma vez
     */
    public function addMultiple($partes)
    {
        if (!$this->db->table_exists('partes_processo') || empty($partes)) {
            return false;
        }

        $data_cadastro = date('Y-m-d H:i:s');
        
        foreach ($partes as &$parte) {
            if (!isset($parte['dataCadastro'])) {
                $parte['dataCadastro'] = $data_cadastro;
            }
        }

        $this->db->insert_batch('partes_processo', $partes);
        
        return $this->db->affected_rows() > 0;
    }
}

