<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Preferencias_notificacao_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtém preferência de um usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param string $tipo_notificacao Tipo (email, push, sms)
     * @param string $categoria Categoria (movimentacao, prazo, fatura, etc)
     * @return object|null
     */
    public function getPreferencia($usuario_id, $tipo_notificacao, $categoria)
    {
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('tipo_notificacao', $tipo_notificacao);
        $this->db->where('categoria', $categoria);
        
        $query = $this->db->get('preferencias_notificacao');
        
        if ($query === false) {
            log_message('error', 'Erro na query Preferencias_notificacao_model::getPreferencia: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return null;
        }
        
        return $query->row();
    }

    /**
     * Obtém preferência de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo_notificacao Tipo (email, push, sms)
     * @param string $categoria Categoria
     * @return object|null
     */
    public function getPreferenciaCliente($cliente_id, $tipo_notificacao, $categoria)
    {
        $this->db->where('cliente_id', $cliente_id);
        $this->db->where('tipo_notificacao', $tipo_notificacao);
        $this->db->where('categoria', $categoria);
        
        $query = $this->db->get('preferencias_notificacao');
        
        if ($query === false) {
            log_message('error', 'Erro na query Preferencias_notificacao_model::getPreferenciaCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return null;
        }
        
        return $query->row();
    }

    /**
     * Salva ou atualiza preferência
     * 
     * @param int|null $usuario_id ID do usuário
     * @param int|null $cliente_id ID do cliente
     * @param string $tipo_notificacao Tipo
     * @param string $categoria Categoria
     * @param bool $habilitado Se habilitado
     * @param int|null $dias_antes_prazo Dias antes do prazo (para categoria prazo)
     * @return bool
     */
    public function salvar($usuario_id, $cliente_id, $tipo_notificacao, $categoria, $habilitado, $dias_antes_prazo = null)
    {
        // Verificar se já existe
        $this->db->where('tipo_notificacao', $tipo_notificacao);
        $this->db->where('categoria', $categoria);
        
        if ($usuario_id) {
            $this->db->where('usuario_id', $usuario_id);
            $this->db->where('cliente_id IS NULL');
        } else {
            $this->db->where('cliente_id', $cliente_id);
            $this->db->where('usuario_id IS NULL');
        }
        
        $existente = $this->db->get('preferencias_notificacao')->row();
        
        $data = [
            'usuario_id' => $usuario_id,
            'cliente_id' => $cliente_id,
            'tipo_notificacao' => $tipo_notificacao,
            'categoria' => $categoria,
            'habilitado' => $habilitado ? 1 : 0,
            'dias_antes_prazo' => $dias_antes_prazo,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($existente) {
            // Atualizar
            $this->db->where('id', $existente->id);
            $this->db->update('preferencias_notificacao', $data);
            return $this->db->affected_rows() > 0;
        } else {
            // Criar
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('preferencias_notificacao', $data);
            return $this->db->affected_rows() == 1;
        }
    }

    /**
     * Obtém todas as preferências de um usuário
     * 
     * @param int $usuario_id ID do usuário
     * @return array
     */
    public function getTodasPreferenciasUsuario($usuario_id)
    {
        $this->db->where('usuario_id', $usuario_id);
        $this->db->order_by('tipo_notificacao', 'asc');
        $this->db->order_by('categoria', 'asc');
        
        $query = $this->db->get('preferencias_notificacao');
        
        if ($query === false) {
            log_message('error', 'Erro na query Preferencias_notificacao_model::getTodasPreferenciasUsuario: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Obtém todas as preferências de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @return array
     */
    public function getTodasPreferenciasCliente($cliente_id)
    {
        $this->db->where('cliente_id', $cliente_id);
        $this->db->order_by('tipo_notificacao', 'asc');
        $this->db->order_by('categoria', 'asc');
        
        $query = $this->db->get('preferencias_notificacao');
        
        if ($query === false) {
            log_message('error', 'Erro na query Preferencias_notificacao_model::getTodasPreferenciasCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }
}

