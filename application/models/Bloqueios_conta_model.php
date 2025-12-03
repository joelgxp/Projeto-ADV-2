<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Bloqueios_conta_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verifica se o email está bloqueado
     * 
     * @param string $email
     * @return object|null Retorna o bloqueio se estiver ativo, null caso contrário
     */
    public function verificarBloqueio($email)
    {
        if (!$this->db->table_exists('bloqueios_conta')) {
            return null;
        }

        $this->db->where('email', $email);
        $this->db->where('desbloqueado', 0);
        $this->db->where('bloqueado_ate >=', date('Y-m-d H:i:s'));
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get('bloqueios_conta');

        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }

    /**
     * Cria ou atualiza bloqueio de conta
     * 
     * @param string $email
     * @param string|null $ip_address
     * @param int $tentativas_falhadas
     * @return int|false ID do bloqueio ou false
     */
    public function bloquear($email, $ip_address = null, $tentativas_falhadas = 0)
    {
        if (!$this->db->table_exists('bloqueios_conta')) {
            return false;
        }

        // Verificar se já existe bloqueio ativo
        $bloqueio_existente = $this->verificarBloqueio($email);

        $bloqueado_ate = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if ($bloqueio_existente) {
            // Atualizar bloqueio existente
            $this->db->where('id', $bloqueio_existente->id);
            $this->db->update('bloqueios_conta', [
                'tentativas_falhadas' => $tentativas_falhadas,
                'bloqueado_ate' => $bloqueado_ate,
                'data_bloqueio' => date('Y-m-d H:i:s')
            ]);

            return $bloqueio_existente->id;
        } else {
            // Criar novo bloqueio
            $data = [
                'email' => $email,
                'ip_address' => $ip_address,
                'tentativas_falhadas' => $tentativas_falhadas,
                'bloqueado_ate' => $bloqueado_ate,
                'data_bloqueio' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('bloqueios_conta', $data);

            if ($this->db->affected_rows() == 1) {
                return $this->db->insert_id();
            }
        }

        return false;
    }

    /**
     * Desbloqueia uma conta
     * 
     * @param string $email
     * @param int|null $admin_id ID do admin que desbloqueou
     * @return bool
     */
    public function desbloquear($email, $admin_id = null)
    {
        if (!$this->db->table_exists('bloqueios_conta')) {
            return false;
        }

        $this->db->where('email', $email);
        $this->db->where('desbloqueado', 0);
        $this->db->update('bloqueios_conta', [
            'desbloqueado' => 1,
            'desbloqueado_por' => $admin_id,
            'data_desbloqueio' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Remove bloqueios expirados automaticamente
     * 
     * @return int Quantidade removida
     */
    public function removerExpirados()
    {
        if (!$this->db->table_exists('bloqueios_conta')) {
            return 0;
        }

        $this->db->where('bloqueado_ate <', date('Y-m-d H:i:s'));
        $this->db->where('desbloqueado', 0);
        $this->db->update('bloqueios_conta', ['desbloqueado' => 1]);

        return $this->db->affected_rows();
    }
}

