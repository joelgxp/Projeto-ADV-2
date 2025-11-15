<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Planos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idPlanos', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->like('nome', $where);
            $this->db->or_like('descricao', $where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->where('idPlanos', $id);
        $this->db->limit(1);
        return $this->db->get('planos')->row();
    }

    public function getAll()
    {
        $this->db->where('status', 1);
        $this->db->order_by('valor_mensal', 'ASC');
        return $this->db->get('planos')->result();
    }

    public function getAtivos()
    {
        $this->db->where('status', 1);
        $this->db->order_by('valor_mensal', 'ASC');
        return $this->db->get('planos')->result();
    }

    public function add($table, $data)
    {
        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }
        $data['dataAtualizacao'] = date('Y-m-d H:i:s');
        
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $data['dataAtualizacao'] = date('Y-m-d H:i:s');
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        // Verificar se há clientes usando este plano
        $this->db->where('planos_id', $ID);
        $clientes = $this->db->get('clientes')->result();
        
        if (count($clientes) > 0) {
            return false; // Não pode deletar se houver clientes usando
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table, $where = '')
    {
        if ($where) {
            $this->db->like('nome', $where);
            $this->db->or_like('descricao', $where);
        }
        return $this->db->count_all_results($table);
    }

    /**
     * Verifica se o cliente pode criar mais processos baseado no plano
     */
    public function podeCriarProcesso($cliente_id)
    {
        $cliente = $this->db->where('idClientes', $cliente_id)->get('clientes')->row();
        
        if (!$cliente || !$cliente->planos_id) {
            return true; // Sem plano = sem limite
        }

        $plano = $this->getById($cliente->planos_id);
        
        if (!$plano || $plano->limite_processos == 0) {
            return true; // Ilimitado
        }

        // Contar processos do cliente
        $this->db->where('clientes_id', $cliente_id);
        $total_processos = $this->db->count_all_results('processos');

        return $total_processos < $plano->limite_processos;
    }

    /**
     * Verifica se o cliente pode criar mais prazos baseado no plano
     */
    public function podeCriarPrazo($cliente_id)
    {
        $cliente = $this->db->where('idClientes', $cliente_id)->get('clientes')->row();
        
        if (!$cliente || !$cliente->planos_id) {
            return true;
        }

        $plano = $this->getById($cliente->planos_id);
        
        if (!$plano || $plano->limite_prazos == 0) {
            return true;
        }

        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id');
        $this->db->where('processos.clientes_id', $cliente_id);
        $total_prazos = $this->db->count_all_results('prazos');

        return $total_prazos < $plano->limite_prazos;
    }

    /**
     * Retorna informações do plano do cliente
     */
    public function getPlanoCliente($cliente_id)
    {
        $this->db->select('planos.*');
        $this->db->from('planos');
        $this->db->join('clientes', 'clientes.planos_id = planos.idPlanos');
        $this->db->where('clientes.idClientes', $cliente_id);
        return $this->db->get()->row();
    }
}

