<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Enderecos_cliente_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca endereços de um cliente
     * 
     * @param int $cliente_id
     * @param string|null $tipo Filtro por tipo
     * @param bool $apenas_ativos
     * @return array
     */
    public function getByCliente($cliente_id, $tipo = null, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return [];
        }

        $this->db->where('clientes_id', $cliente_id);
        
        if ($tipo) {
            $this->db->where('tipo', $tipo);
        }
        
        if ($apenas_ativos) {
            $this->db->where('ativo', 1);
        }
        
        $this->db->order_by('principal', 'DESC');
        $this->db->order_by('dataCadastro', 'ASC');
        
        $query = $this->db->get('enderecos_cliente');
        
        if ($query === false) {
            log_message('error', 'Erro na query Enderecos_cliente_model::getByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca endereço principal de um cliente
     * 
     * @param int $cliente_id
     * @return object|null
     */
    public function getPrincipal($cliente_id)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return null;
        }

        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('principal', 1);
        $this->db->where('ativo', 1);
        $this->db->limit(1);
        
        $query = $this->db->get('enderecos_cliente');
        
        if ($query === false || $query->num_rows() === 0) {
            return null;
        }
        
        return $query->row();
    }

    /**
     * Adiciona um endereço
     * 
     * @param array $data
     * @return int|false ID do endereço ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            log_message('error', 'Tabela enderecos_cliente não existe');
            return false;
        }

        $data['dataCadastro'] = date('Y-m-d H:i:s');
        
        // Se for marcado como principal, desmarcar outros
        if (isset($data['principal']) && $data['principal'] == 1) {
            $this->db->where('clientes_id', $data['clientes_id']);
            $this->db->set('principal', 0);
            $this->db->update('enderecos_cliente');
        }
        
        $this->db->insert('enderecos_cliente', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Atualiza um endereço
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return false;
        }

        // Se for marcado como principal, desmarcar outros
        if (isset($data['principal']) && $data['principal'] == 1) {
            $endereco_atual = $this->getById($id);
            if ($endereco_atual) {
                $this->db->where('clientes_id', $endereco_atual->clientes_id);
                $this->db->where('idEndereco !=', $id);
                $this->db->set('principal', 0);
                $this->db->update('enderecos_cliente');
            }
        }
        
        $this->db->where('idEndereco', $id);
        $this->db->update('enderecos_cliente', $data);
        
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Busca endereço por ID
     * 
     * @param int $id
     * @return object|null
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return null;
        }

        $this->db->where('idEndereco', $id);
        $this->db->limit(1);
        
        $query = $this->db->get('enderecos_cliente');
        
        if ($query === false || $query->num_rows() === 0) {
            return null;
        }
        
        return $query->row();
    }

    /**
     * Remove endereço (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return false;
        }

        // Soft delete
        $this->db->where('idEndereco', $id);
        $this->db->set('ativo', 0);
        $this->db->update('enderecos_cliente');
        
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Conta quantos endereços ativos o cliente tem
     * 
     * @param int $cliente_id
     * @return int
     */
    public function count($cliente_id)
    {
        if (!$this->db->table_exists('enderecos_cliente')) {
            return 0;
        }

        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('ativo', 1);
        
        return $this->db->count_all_results('enderecos_cliente');
    }
}

