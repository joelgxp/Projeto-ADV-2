<?php

class Tickets_respostas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Buscar resposta por ID
     * 
     * @param int $id ID da resposta
     * @return object|null Resposta ou null se não encontrado
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('tickets_respostas')) {
            return null;
        }

        $this->db->where('id', $id);
        $query = $this->db->get('tickets_respostas');

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }

    /**
     * Buscar respostas de um ticket
     * 
     * @param int $ticket_id ID do ticket
     * @return array Lista de respostas
     */
    public function getByTicket($ticket_id)
    {
        if (!$this->db->table_exists('tickets_respostas')) {
            return [];
        }

        $this->db->where('tickets_id', $ticket_id);

        // Join com usuarios (se for resposta do advogado)
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('tickets_respostas.*, usuarios.nome as nomeUsuario');
            $this->db->join('usuarios', 'usuarios.idUsuarios = tickets_respostas.usuarios_id', 'left');
        }

        // Join com clientes (se for resposta do cliente)
        if ($this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = tickets_respostas.clientes_id', 'left');
        }

        $this->db->order_by('tickets_respostas.data_resposta', 'asc');
        $query = $this->db->get('tickets_respostas');

        if ($query === false) {
            return [];
        }

        return $query->result();
    }

    /**
     * Adicionar resposta a um ticket
     * 
     * @param array $data Dados da resposta
     * @return int|false ID da resposta criada ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('tickets_respostas')) {
            return false;
        }

        $data['data_resposta'] = date('Y-m-d H:i:s');

        if ($this->db->insert('tickets_respostas', $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Atualizar resposta
     * 
     * @param int $id ID da resposta
     * @param array $data Dados para atualizar
     * @return bool Sucesso ou falha
     */
    public function edit($id, $data)
    {
        if (!$this->db->table_exists('tickets_respostas')) {
            return false;
        }

        $this->db->where('id', $id);
        return $this->db->update('tickets_respostas', $data);
    }

    /**
     * Deletar resposta
     * 
     * @param int $id ID da resposta
     * @return bool Sucesso ou falha
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('tickets_respostas')) {
            return false;
        }

        $this->db->where('id', $id);
        return $this->db->delete('tickets_respostas');
    }
}

