<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ResetSenhas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca token de reset por email
     * 
     * @param string $email
     * @return object|null
     */
    public function getById($email)
    {
        $this->db->where('email', $email);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        return $this->db->get('resets_de_senha')->row();
    }

    /**
     * Busca token por valor do token
     * RN 1.3: Token válido por 1 hora
     * 
     * @param string $token
     * @return object|null Token válido ou null se inválido/expirado
     */
    public function getByToken($token)
    {
        if (!$this->db->table_exists('resets_de_senha')) {
            return null;
        }

        $this->db->where('token', $token);
        $this->db->where('token_utilizado', 0); // Token não utilizado ainda
        $this->db->where('data_expiracao >=', date('Y-m-d H:i:s')); // Não expirado
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        return $this->db->get('resets_de_senha')->row();
    }

    /**
     * Valida se o token está válido (não expirou e não foi usado)
     * RN 1.3: Token válido por 1 hora
     * 
     * @param string $token
     * @return bool
     */
    public function validarToken($token)
    {
        $token_data = $this->getByToken($token);
        return $token_data !== null;
    }

    /**
     * Marca token como utilizado (RN 1.3: Token fica inválido após uso)
     * 
     * @param string $token
     * @return bool
     */
    public function marcarTokenComoUtilizado($token)
    {
        if (!$this->db->table_exists('resets_de_senha')) {
            return false;
        }

        $this->db->where('token', $token);
        $this->db->update('resets_de_senha', [
            'token_utilizado' => 1,
            'data_utilizacao' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Remove tokens antigos/expirados (limpeza)
     * 
     * @return int Quantidade removida
     */
    public function limparTokensExpirados()
    {
        if (!$this->db->table_exists('resets_de_senha')) {
            return 0;
        }

        // Remove tokens expirados há mais de 7 dias
        $this->db->where('data_expiracao <', date('Y-m-d H:i:s', strtotime('-7 days')));
        $this->db->delete('resets_de_senha');

        return $this->db->affected_rows();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }

            return true;
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }
}
