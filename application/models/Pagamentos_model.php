<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pagamentos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Faturas_model');
    }

    public function get($where = '', $perpage = 0, $start = 0, $one = false)
    {
        $this->db->select('pagamentos.*, faturas.numero as fatura_numero, faturas.clientes_id, clientes.nomeCliente');
        $this->db->from('pagamentos');
        $this->db->join('faturas', 'faturas.id = pagamentos.faturas_id', 'left');
        $this->db->join('clientes', 'clientes.idClientes = faturas.clientes_id', 'left');
        
        if ($where) {
            $this->db->where($where);
        }
        
        $this->db->order_by('pagamentos.data_pagamento', 'DESC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        if ($one) {
            return $query->row();
        }
        
        return $query->result();
    }

    public function getById($id)
    {
        return $this->get(['pagamentos.id' => $id], 0, 0, true);
    }

    public function getByFatura($fatura_id)
    {
        return $this->get(['pagamentos.faturas_id' => $fatura_id]);
    }

    public function add($data)
    {
        $data['created_by'] = $this->session->userdata('id_admin');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('pagamentos', $data);
        
        if ($this->db->affected_rows() == 1) {
            $pagamento_id = $this->db->insert_id();
            
            // Recalcular saldo da fatura (trigger faz automaticamente, mas garantimos)
            $this->Faturas_model->calcularSaldo($data['faturas_id']);
            
            return $pagamento_id;
        }
        
        return false;
    }

    public function edit($id, $data)
    {
        $pagamento = $this->getById($id);
        if (!$pagamento) {
            return false;
        }
        
        $data['updated_by'] = $this->session->userdata('id_admin');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update('pagamentos', $data);
        
        if ($this->db->affected_rows() >= 0) {
            // Recalcular saldo da fatura
            $this->Faturas_model->calcularSaldo($pagamento->faturas_id);
            return true;
        }
        
        return false;
    }

    public function delete($id)
    {
        $pagamento = $this->getById($id);
        if (!$pagamento) {
            return false;
        }
        
        $fatura_id = $pagamento->faturas_id;
        
        $this->db->where('id', $id);
        $this->db->delete('pagamentos');
        
        if ($this->db->affected_rows() == 1) {
            // Recalcular saldo da fatura
            $this->Faturas_model->calcularSaldo($fatura_id);
            return true;
        }
        
        return false;
    }

    public function count($where = '')
    {
        $this->db->from('pagamentos');
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }

    public function getTotalPagamentosFatura($fatura_id)
    {
        $this->db->select_sum('valor');
        $this->db->where('faturas_id', $fatura_id);
        $query = $this->db->get('pagamentos');
        $result = $query->row();
        return $result->valor ?: 0;
    }
}

