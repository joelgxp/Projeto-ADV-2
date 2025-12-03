<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tentativas_login_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registra uma tentativa de login
     * 
     * @param string $email
     * @param string $ip_address
     * @param string|null $user_agent
     * @param bool $sucesso
     * @return int|false ID da tentativa ou false em caso de erro
     */
    public function registrar($email, $ip_address, $user_agent = null, $sucesso = false)
    {
        if (!$this->db->table_exists('tentativas_login')) {
            return false;
        }

        $data = [
            'email' => $email,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent ?: $_SERVER['HTTP_USER_AGENT'] ?? null,
            'sucesso' => $sucesso ? 1 : 0,
            'data_hora' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tentativas_login', $data);

        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Conta tentativas falhadas recentes (últimas 15 minutos)
     * 
     * @param string $email
     * @param string|null $ip_address Se informado, conta apenas deste IP
     * @return int
     */
    public function contarFalhas($email, $ip_address = null)
    {
        if (!$this->db->table_exists('tentativas_login')) {
            return 0;
        }

        $this->db->where('email', $email);
        $this->db->where('sucesso', 0);
        $this->db->where('data_hora >=', date('Y-m-d H:i:s', strtotime('-15 minutes')));

        if ($ip_address) {
            $this->db->where('ip_address', $ip_address);
        }

        return $this->db->count_all_results('tentativas_login');
    }

    /**
     * Limpa tentativas antigas (mais de 24 horas)
     * 
     * @return int Quantidade de registros removidos
     */
    public function limparAntigas()
    {
        if (!$this->db->table_exists('tentativas_login')) {
            return 0;
        }

        $this->db->where('data_hora <', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $this->db->delete('tentativas_login');

        return $this->db->affected_rows();
    }
}

