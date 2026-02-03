<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Confirmacoes_email_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria token de confirmação de e-mail
     * 
     * @param int $usuarios_id
     * @return array ['token' => string, 'id' => int] ou false em caso de erro
     */
    public function criarToken($usuarios_id)
    {
        // Gerar token único
        $token = bin2hex(random_bytes(32));

        $data = [
            'usuarios_id' => $usuarios_id,
            'token' => $token,
            'data_expiracao' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'data_cadastro' => date('Y-m-d H:i:s'),
            'token_utilizado' => 0
        ];

        $this->db->insert('confirmacoes_email', $data);
        
        $error = $this->db->error();
        if (!empty($error['message'])) {
            log_message('error', 'Erro ao criar token de confirmação: ' . json_encode($error));
            log_message('error', 'Dados tentados: ' . json_encode($data));
            return false;
        }

        if ($this->db->affected_rows() == 1) {
            return [
                'token' => $token,
                'id' => $this->db->insert_id()
            ];
        }

        return false;
    }

    /**
     * Valida e utiliza um token de confirmação
     * 
     * @param string $token
     * @return object|null Dados do usuário se válido, null caso contrário
     */
    public function validarToken($token)
    {

        $this->db->where('token', $token);
        $this->db->where('token_utilizado', 0);
        $this->db->where('data_expiracao >=', date('Y-m-d H:i:s'));
        $this->db->limit(1);

        $query = $this->db->get('confirmacoes_email');

        if (!$query || $query->num_rows() == 0) {
            return null;
        }

        $confirmacao = $query->row();

        // Marcar token como utilizado
        $this->db->where('id', $confirmacao->id);
        $this->db->update('confirmacoes_email', ['token_utilizado' => 1]);

        // Buscar dados do usuário
        $this->load->model('usuarios_model');
        $usuario = $this->usuarios_model->getById($confirmacao->usuarios_id);

        return $usuario;
    }

    /**
     * Valida token sem marcar como utilizado (para exibir formulário)
     *
     * @param string $token
     * @return object|null Confirmacao se válido, null caso contrário
     */
    public function getTokenValidoSemMarcar($token)
    {
        $this->db->where('token', $token);
        $this->db->where('token_utilizado', 0);
        $this->db->where('data_expiracao >=', date('Y-m-d H:i:s'));
        $this->db->limit(1);
        $query = $this->db->get('confirmacoes_email');
        return ($query && $query->num_rows() > 0) ? $query->row() : null;
    }

    /**
     * Invalida todos os tokens pendentes de um usuário (ao resetar senha)
     *
     * @param int $usuarios_id
     * @return int Quantidade invalidada
     */
    public function invalidarTokensUsuario($usuarios_id)
    {
        $this->db->where('usuarios_id', $usuarios_id);
        $this->db->where('token_utilizado', 0);
        $this->db->update('confirmacoes_email', ['token_utilizado' => 1]);
        return $this->db->affected_rows();
    }

    /**
     * Verifica se o usuário já confirmou o e-mail
     * 
     * @param int $usuarios_id
     * @return bool
     */
    public function estaConfirmado($usuarios_id)
    {

        $this->db->select('email_confirmado');
        $this->db->where('idUsuarios', $usuarios_id);
        $this->db->limit(1);

        $query = $this->db->get('usuarios');

        if ($query && $query->num_rows() > 0) {
            $usuario = $query->row();
            return isset($usuario->email_confirmado) && $usuario->email_confirmado == 1;
        }

        return false;
    }

    /**
     * Remove tokens expirados
     * 
     * @return int Quantidade removida
     */
    public function limparExpirados()
    {

        $this->db->where('data_expiracao <', date('Y-m-d H:i:s'));
        $this->db->delete('confirmacoes_email');

        return $this->db->affected_rows();
    }
}

