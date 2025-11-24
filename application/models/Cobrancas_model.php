<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cobrancas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        
        // Join com processos se a tabela existir
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = cobrancas.processos_id', 'left');
        }
        
        $this->db->limit($perpage, $start);
        $this->db->order_by('idCobranca', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Cobrancas_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->select('cobrancas.*, clientes.*');
        $this->db->from('cobrancas');
        $this->db->where('cobrancas.idCobranca', $id);
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getByProcesso($processo_id)
    {
        if (!$this->db->table_exists('cobrancas')) {
            return [];
        }
        
        $this->db->select('cobrancas.*, clientes.*, processos.numeroProcesso, processos.classe, processos.assunto');
        $this->db->from('cobrancas');
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left');
        $this->db->join('processos', 'processos.idProcessos = cobrancas.processos_id', 'left');
        $this->db->where('cobrancas.processos_id', $processo_id);
        $this->db->order_by('cobrancas.idCobranca', 'desc');
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }

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

    public function atualizarStatus($idCobranca)
    {
        $this->session->set_flashdata('error', 'Funcionalidade de payment gateways foi removida.');
        return false;
    }

    public function confirmarPagamento($idCobranca)
    {
        $this->session->set_flashdata('error', 'Funcionalidade de payment gateways foi removida.');
        return false;
    }

    public function cancelarPagamento($idCobranca)
    {
        $this->session->set_flashdata('error', 'Funcionalidade de payment gateways foi removida.');
        return false;
    }

    public function enviarEmail($idCobranca)
    {
        $this->session->set_flashdata('error', 'Funcionalidade de payment gateways foi removida.');
        return false;
    }
}
