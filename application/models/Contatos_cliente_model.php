<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Contatos_cliente_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca contatos de um cliente
     * 
     * @param int $cliente_id
     * @param string|null $tipo Filtro por tipo (email, telefone, celular)
     * @param bool $apenas_ativos
     * @return array
     */
    public function getByCliente($cliente_id, $tipo = null, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
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
        
        $query = $this->db->get('contatos_cliente');
        
        if ($query === false) {
            log_message('error', 'Erro na query Contatos_cliente_model::getByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca contato principal de um cliente por tipo
     * 
     * @param int $cliente_id
     * @param string $tipo email, telefone ou celular
     * @return object|null
     */
    public function getPrincipal($cliente_id, $tipo)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            return null;
        }

        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('tipo', $tipo);
        $this->db->where('principal', 1);
        $this->db->where('ativo', 1);
        $this->db->limit(1);
        
        $query = $this->db->get('contatos_cliente');
        
        if ($query === false || $query->num_rows() === 0) {
            return null;
        }
        
        return $query->row();
    }

    /**
     * Adiciona um contato
     * 
     * @param array $data
     * @return int|false ID do contato ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            log_message('error', 'Tabela contatos_cliente não existe');
            return false;
        }

        $data['dataCadastro'] = date('Y-m-d H:i:s');
        
        // Se for marcado como principal, desmarcar outros do mesmo tipo
        if (isset($data['principal']) && $data['principal'] == 1) {
            $this->db->where('clientes_id', $data['clientes_id']);
            $this->db->where('tipo', $data['tipo']);
            $this->db->set('principal', 0);
            $this->db->update('contatos_cliente');
        }
        
        $this->db->insert('contatos_cliente', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Atualiza um contato
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            return false;
        }

        // Se for marcado como principal, desmarcar outros do mesmo tipo
        if (isset($data['principal']) && $data['principal'] == 1) {
            $contato_atual = $this->getById($id);
            if ($contato_atual) {
                $this->db->where('clientes_id', $contato_atual->clientes_id);
                $this->db->where('tipo', $contato_atual->tipo);
                $this->db->where('idContato !=', $id);
                $this->db->set('principal', 0);
                $this->db->update('contatos_cliente');
            }
        }
        
        $this->db->where('idContato', $id);
        $this->db->update('contatos_cliente', $data);
        
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Busca contato por ID
     * 
     * @param int $id
     * @return object|null
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            return null;
        }

        $this->db->where('idContato', $id);
        $this->db->limit(1);
        
        $query = $this->db->get('contatos_cliente');
        
        if ($query === false || $query->num_rows() === 0) {
            return null;
        }
        
        return $query->row();
    }

    /**
     * Remove contato (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            return false;
        }

        // Soft delete
        $this->db->where('idContato', $id);
        $this->db->set('ativo', 0);
        $this->db->update('contatos_cliente');
        
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Conta quantos contatos ativos o cliente tem (por tipo)
     * 
     * @param int $cliente_id
     * @param string $tipo
     * @return int
     */
    public function countByTipo($cliente_id, $tipo)
    {
        if (!$this->db->table_exists('contatos_cliente')) {
            return 0;
        }

        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('tipo', $tipo);
        $this->db->where('ativo', 1);
        
        return $this->db->count_all_results('contatos_cliente');
    }
}

