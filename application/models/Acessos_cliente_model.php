<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Acessos_cliente_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Buscar acesso por ID
     */
    public function getById($id)
    {
        $this->db->where('id', $id);
        $this->db->limit(1);
        return $this->db->get('acessos_cliente')->row();
    }

    /**
     * Buscar acesso por token
     */
    public function getByToken($token)
    {
        $this->db->where('token_acesso', $token);
        $this->db->where('ativo', 1);
        $this->db->limit(1);
        return $this->db->get('acessos_cliente')->row();
    }

    /**
     * Buscar acesso ativo por cliente
     */
    public function getAcessoAtivoByCliente($cliente_id)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('ativo', 1);
        $this->db->where('data_expiracao >=', date('Y-m-d H:i:s'));
        $this->db->order_by('data_criacao', 'desc');
        $this->db->limit(1);
        return $this->db->get('acessos_cliente')->row();
    }

    /**
     * Buscar todos os acessos de um cliente
     */
    public function getByCliente($cliente_id)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->order_by('data_criacao', 'desc');
        return $this->db->get('acessos_cliente')->result();
    }

    /**
     * Criar novo acesso
     */
    public function add($data)
    {
        $this->db->insert('acessos_cliente', $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Atualizar acesso
     */
    public function edit($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('acessos_cliente', $data);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Desativar todos os acessos de um cliente (antes de criar novo)
     */
    public function desativarAcessosByCliente($cliente_id)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->update('acessos_cliente', ['ativo' => 0]);
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Verificar se token é válido
     */
    public function isTokenValido($token)
    {
        $acesso = $this->getByToken($token);
        
        if (!$acesso) {
            return false;
        }

        // Verificar se não expirou
        $dataExpiracao = new DateTime($acesso->data_expiracao);
        $dataAtual = new DateTime();
        
        if ($dataExpiracao < $dataAtual) {
            return false;
        }

        return true;
    }

    /**
     * Renovar acesso (prorrogar por mais 365 dias)
     */
    public function renovarAcesso($id)
    {
        $acesso = $this->getById($id);
        
        if (!$acesso) {
            return false;
        }

        $novaDataExpiracao = date('Y-m-d H:i:s', strtotime('+365 days'));
        
        $data = [
            'data_renovacao' => date('Y-m-d H:i:s'),
            'data_expiracao' => $novaDataExpiracao,
            'ativo' => 1
        ];

        return $this->edit($id, $data);
    }

    /**
     * Atualizar último acesso
     */
    public function atualizarUltimoAcesso($token)
    {
        $acesso = $this->getByToken($token);
        
        if (!$acesso) {
            return false;
        }

        $data = [
            'ultimo_acesso' => date('Y-m-d H:i:s')
        ];

        return $this->edit($acesso->id, $data);
    }

    /**
     * Gerar token único
     */
    public function gerarTokenUnico()
    {
        $this->load->helper('string');
        
        do {
            $token = random_string('alnum', 64);
            $existe = $this->getByToken($token);
        } while ($existe);
        
        return $token;
    }

    /**
     * Criar novo acesso para cliente (365 dias)
     */
    public function criarAcesso($cliente_id, $ip_criacao = null)
    {
        // Desativar acessos anteriores
        $this->desativarAcessosByCliente($cliente_id);
        
        // Gerar token único
        $token = $this->gerarTokenUnico();
        
        // Data de expiração: 365 dias
        $data_criacao = date('Y-m-d H:i:s');
        $data_expiracao = date('Y-m-d H:i:s', strtotime('+365 days'));
        
        $data = [
            'clientes_id' => $cliente_id,
            'token_acesso' => $token,
            'data_criacao' => $data_criacao,
            'data_expiracao' => $data_expiracao,
            'data_renovacao' => null,
            'ativo' => 1,
            'ip_criacao' => $ip_criacao ?: $_SERVER['REMOTE_ADDR'] ?? null,
            'ultimo_acesso' => null
        ];
        
        $id = $this->add($data);
        
        if ($id) {
            return [
                'id' => $id,
                'token' => $token,
                'data_expiracao' => $data_expiracao
            ];
        }
        
        return false;
    }
}

/* End of file Acessos_cliente_model.php */
/* Location: ./application/models/Acessos_cliente_model.php */

