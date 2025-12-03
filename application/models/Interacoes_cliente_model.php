<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Interacoes_cliente_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca interações de um cliente
     * 
     * @param int $cliente_id
     * @param string|null $tipo Filtro por tipo
     * @param int $limit
     * @return array
     */
    public function getByCliente($cliente_id, $tipo = null, $limit = null)
    {
        if (!$this->db->table_exists('interacoes_cliente')) {
            return [];
        }

        $this->db->where('clientes_id', $cliente_id);
        
        if ($tipo) {
            $this->db->where('tipo', $tipo);
        }
        
        $this->db->order_by('data_hora', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        $query = $this->db->get('interacoes_cliente');
        
        if ($query === false) {
            log_message('error', 'Erro na query Interacoes_cliente_model::getByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Adiciona uma interação
     * 
     * @param array $data Dados da interação
     * @return int|false ID da interação ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('interacoes_cliente')) {
            log_message('error', 'Tabela interacoes_cliente não existe');
            return false;
        }

        // Garantir que data_hora está definida (usar campo do helper ou padrão)
        if (!isset($data['data_hora']) || empty($data['data_hora'])) {
            $data['data_hora'] = date('Y-m-d H:i:s');
        }
        
        // Se dataCadastro não estiver definida, usar data_hora
        if (!isset($data['dataCadastro']) || empty($data['dataCadastro'])) {
            $data['dataCadastro'] = $data['data_hora'];
        }
        
        $this->db->insert('interacoes_cliente', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        $error = $this->db->error();
        if (!empty($error['message'])) {
            log_message('error', 'Erro ao adicionar interação: ' . json_encode($error));
        }
        
        return false;
    }

    /**
     * Busca interação por ID
     * 
     * @param int $id
     * @return object|null
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('interacoes_cliente')) {
            return null;
        }

        $this->db->where('idInteracao', $id);
        $this->db->limit(1);
        
        // Join com usuário se possível
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('interacoes_cliente.*, usuarios.nome as nome_usuario');
            $this->db->join('usuarios', 'usuarios.idUsuarios = interacoes_cliente.usuarios_id', 'left');
        }
        
        $query = $this->db->get('interacoes_cliente');
        
        if ($query === false || $query->num_rows() === 0) {
            return null;
        }
        
        return $query->row();
    }

    /**
     * Remove interação (permanente - para auditoria, interações não devem ser deletadas)
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Por padrão, interações não são deletadas para manter histórico
        // Se necessário, pode ser implementado soft delete
        return false;
    }
}

