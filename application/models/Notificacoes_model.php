<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Notificacoes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adiciona notificação
     * 
     * @param array $data Dados da notificação
     * @return int|false ID da notificação ou false
     */
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['lida'] = $data['lida'] ?? 0;
        $data['enviada'] = $data['enviada'] ?? 0;
        
        $this->db->insert('notificacoes', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Atualiza notificação
     * 
     * @param int $id ID da notificação
     * @param array $data Dados para atualizar
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('notificacoes', $data);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Marca notificação como lida
     * 
     * @param int $id ID da notificação
     * @return bool
     */
    public function marcar_lida($id)
    {
        $this->db->where('id', $id);
        $this->db->update('notificacoes', [
            'lida' => 1,
            'data_leitura' => date('Y-m-d H:i:s'),
        ]);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Obtém notificações de um usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param int $limit Limite
     * @param bool $apenas_nao_lidas Apenas não lidas
     * @return array
     */
    public function getByUsuario($usuario_id, $limit = 50, $apenas_nao_lidas = false)
    {
        $this->db->where('usuario_id', $usuario_id);
        
        if ($apenas_nao_lidas) {
            $this->db->where('lida', 0);
        }
        
        $this->db->order_by('created_at', 'desc');
        $this->db->limit($limit);
        
        $query = $this->db->get('notificacoes');
        
        if ($query === false) {
            log_message('error', 'Erro na query Notificacoes_model::getByUsuario: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Obtém notificações de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @param int $limit Limite
     * @param bool $apenas_nao_lidas Apenas não lidas
     * @return array
     */
    public function getByCliente($cliente_id, $limit = 50, $apenas_nao_lidas = false)
    {
        $this->db->where('cliente_id', $cliente_id);
        
        if ($apenas_nao_lidas) {
            $this->db->where('lida', 0);
        }
        
        $this->db->order_by('created_at', 'desc');
        $this->db->limit($limit);
        
        $query = $this->db->get('notificacoes');
        
        if ($query === false) {
            log_message('error', 'Erro na query Notificacoes_model::getByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Conta notificações não lidas
     * 
     * @param int|null $usuario_id ID do usuário
     * @param int|null $cliente_id ID do cliente
     * @return int
     */
    public function countNaoLidas($usuario_id = null, $cliente_id = null)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('notificacoes')) {
            log_message('debug', 'Tabela notificacoes não existe. Retornando 0.');
            return 0;
        }
        
        $this->db->where('lida', 0);
        
        if ($usuario_id) {
            $this->db->where('usuario_id', $usuario_id);
        }
        
        if ($cliente_id) {
            $this->db->where('cliente_id', $cliente_id);
        }
        
        $result = $this->db->count_all_results('notificacoes');
        
        // Verificar erros
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Erro na query Notificacoes_model::countNaoLidas: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('debug', 'Query SQL: ' . $this->db->last_query());
            return 0;
        }
        
        return $result;
    }
}

