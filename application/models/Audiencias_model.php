<?php

class Audiencias_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->select($fields . ', audiencias.*');
        $this->db->from($table);

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto, processos.status as statusProcesso');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }

        // Join com clientes através de processos
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios (responsável)
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('usuarios.nome as nomeResponsavel');
            $this->db->join('usuarios', 'usuarios.idUsuarios = audiencias.usuarios_id', 'left');
        }

        $this->db->order_by('audiencias.dataHora', 'ASC');
        $this->db->limit($perpage, $start);

        if ($where) {
            if (is_string($where)) {
                // Se for string, usar where direto
                if (strpos($where, 'AND') !== false || strpos($where, 'OR') !== false) {
                    $this->db->where($where, null, false);
                } else {
                    $this->db->group_start();
                    $this->db->like('audiencias.tipo', $where);
                    $this->db->or_like('audiencias.local', $where);
                    $this->db->or_like('audiencias.observacoes', $where);
                    if ($this->db->table_exists('processos')) {
                        $this->db->or_like('processos.numeroProcesso', $where);
                    }
                    $this->db->group_end();
                }
            } else {
                $this->db->where($where);
            }
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Audiencias_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        if (!$this->db->table_exists('audiencias')) {
            return false;
        }

        $this->db->where('idAudiencias', $id);
        $this->db->limit(1);

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.*');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }

        // Join com clientes
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.*');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('usuarios.nome as nomeResponsavel, usuarios.email as emailResponsavel');
            $this->db->join('usuarios', 'usuarios.idUsuarios = audiencias.usuarios_id', 'left');
        }

        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query Audiencias_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::add");
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
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::edit");
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
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::delete");
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
     * Busca audiências de hoje
     */
    public function getAudienciasHoje()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('DATE(dataHora)', date('Y-m-d'));
        $this->db->where('status', 'agendada');
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasHoje: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca próximas audiências (próximos 7 dias)
     */
    public function getAudienciasProximas()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('DATE(dataHora) >', date('Y-m-d'));
        $this->db->where('DATE(dataHora) <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->where('status', 'agendada');
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasProximas: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca próximas audiências por cliente
     */
    public function getAudienciasProximasByCliente($cliente_id, $limit = 5)
    {
        if (!$this->db->table_exists('audiencias') || !$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de clientes e verificar se processos.clientes_id existe
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        // Verificar se tabelas existem
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        if ($this->db->table_exists('clientes')) {
            $this->db->select('audiencias.*, processos.numeroProcesso');
            $this->db->from('audiencias');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
            $this->db->where('processos.clientes_id', $cliente_id);
            $this->db->where('audiencias.status', 'agendada');
            $this->db->where('DATE(audiencias.dataHora) >=', date('Y-m-d'));
            $this->db->where('DATE(audiencias.dataHora) <=', date('Y-m-d', strtotime('+7 days')));
            $this->db->order_by('audiencias.dataHora', 'ASC');
            $this->db->limit($limit);
            $query = $this->db->get();
            
            if ($query === false) {
                log_message('error', 'Erro na query getAudienciasProximasByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
                return [];
            }
            
            return $query->result();
        }

        return [];
    }

    /**
     * Busca audiências por processo
     */
    public function getAudienciasByProcesso($processo_id)
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('processos_id', $processo_id);
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca audiências por cliente
     */
    public function getAudienciasByCliente($cliente_id, $perpage = 0, $start = 0)
    {
        if (!$this->db->table_exists('audiencias') || !$this->db->table_exists('processos')) {
            return [];
        }

        $this->db->select('audiencias.*');
        // Verificar se as colunas existem antes de selecioná-las
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
        // Verificar se processos.clientes_id existe
        if (!$this->db->table_exists('processos')) {
            return [];
        }
        
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where("processos.clientes_id", $cliente_id);
        $this->db->order_by('audiencias.dataHora', 'DESC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }
}

