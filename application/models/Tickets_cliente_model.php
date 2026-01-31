<?php

class Tickets_cliente_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Buscar ticket por ID
     * 
     * @param int $id ID do ticket
     * @return object|null Ticket ou null se não encontrado
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return null;
        }

        $this->db->where('id', $id);
        $query = $this->db->get('tickets_cliente');

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }

    /**
     * Buscar tickets de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @param string $status Status do ticket (opcional)
     * @param int $limit Limite de resultados
     * @param int $offset Offset
     * @return array Lista de tickets
     */
    public function getByCliente($cliente_id, $status = null, $limit = 0, $offset = 0)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return [];
        }

        $this->db->where('clientes_id', $cliente_id);

        if ($status) {
            $this->db->where('status', $status);
        }

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('tickets_cliente.*, processos.numeroProcesso, processos.assunto as assuntoProcesso');
            $this->db->join('processos', 'processos.idProcessos = tickets_cliente.processos_id', 'left');
        }

        // Join com usuarios (advogado)
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('usuarios.nome as nomeAdvogado');
            $this->db->join('usuarios', 'usuarios.idUsuarios = tickets_cliente.usuarios_id', 'left');
        }

        $this->db->order_by('tickets_cliente.data_abertura', 'desc');

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get('tickets_cliente');

        if ($query === false) {
            return [];
        }

        return $query->result();
    }

    /**
     * Buscar tickets de um advogado
     * 
     * @param int $usuario_id ID do advogado
     * @param string $status Status do ticket (opcional)
     * @return array Lista de tickets
     */
    public function getByAdvogado($usuario_id, $status = null)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return [];
        }

        $this->db->where('usuarios_id', $usuario_id);

        if ($status) {
            $this->db->where('status', $status);
        }

        // Join com clientes
        if ($this->db->table_exists('clientes')) {
            $this->db->select('tickets_cliente.*, clientes.nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = tickets_cliente.clientes_id', 'left');
        }

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso');
            $this->db->join('processos', 'processos.idProcessos = tickets_cliente.processos_id', 'left');
        }

        $this->db->order_by('tickets_cliente.data_abertura', 'desc');
        $query = $this->db->get('tickets_cliente');

        if ($query === false) {
            return [];
        }

        return $query->result();
    }

    /**
     * Criar novo ticket
     * 
     * @param array $data Dados do ticket
     * @return int|false ID do ticket criado ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return false;
        }

        $data['data_abertura'] = date('Y-m-d H:i:s');
        $data['lido_cliente'] = 0;
        $data['lido_advogado'] = 0;

        if ($this->db->insert('tickets_cliente', $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Atualizar ticket
     * 
     * @param int $id ID do ticket
     * @param array $data Dados para atualizar
     * @return bool Sucesso ou falha
     */
    public function edit($id, $data)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return false;
        }

        $this->db->where('id', $id);
        return $this->db->update('tickets_cliente', $data);
    }

    /**
     * Marcar ticket como lido pelo cliente
     * 
     * @param int $id ID do ticket
     * @return bool Sucesso ou falha
     */
    public function marcarLidoCliente($id)
    {
        return $this->edit($id, ['lido_cliente' => 1]);
    }

    /**
     * Marcar ticket como lido pelo advogado
     * 
     * @param int $id ID do ticket
     * @return bool Sucesso ou falha
     */
    public function marcarLidoAdvogado($id)
    {
        return $this->edit($id, ['lido_advogado' => 1]);
    }

    /**
     * Fechar ticket
     * 
     * @param int $id ID do ticket
     * @return bool Sucesso ou falha
     */
    public function fechar($id)
    {
        return $this->edit($id, [
            'status' => 'fechado',
            'data_fechamento' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Contar tickets não lidos do cliente
     * 
     * @param int $cliente_id ID do cliente
     * @return int Número de tickets não lidos
     */
    public function countNaoLidosByCliente($cliente_id)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return 0;
        }

        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('lido_cliente', 0);
        $this->db->where_in('status', ['aberto', 'em_andamento', 'respondido']);

        return $this->db->count_all_results('tickets_cliente');
    }

    /**
     * Contar tickets não lidos do advogado
     * 
     * @param int $usuario_id ID do advogado
     * @return int Número de tickets não lidos
     */
    public function countNaoLidosByAdvogado($usuario_id)
    {
        if (!$this->db->table_exists('tickets_cliente')) {
            return 0;
        }

        $this->db->where('usuarios_id', $usuario_id);
        $this->db->where('lido_advogado', 0);
        $this->db->where_in('status', ['aberto', 'em_andamento']);

        return $this->db->count_all_results('tickets_cliente');
    }
}

