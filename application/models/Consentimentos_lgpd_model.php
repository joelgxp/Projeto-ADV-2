<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Consentimentos_lgpd_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registra ou atualiza consentimento
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de consentimento (tratamento_dados, comunicacao, marketing)
     * @param bool $consentido Se consentiu ou não
     * @return bool|int ID do registro ou false
     */
    public function registrarConsentimento($cliente_id, $tipo, $consentido)
    {
        $ci = &get_instance();
        
        // Verificar se já existe consentimento deste tipo
        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('tipo_consentimento', $tipo);
        $existente = $this->db->get('consentimentos_lgpd')->row();
        
        $data = [
            'clientes_id' => $cliente_id,
            'tipo_consentimento' => $tipo,
            'consentido' => $consentido ? 1 : 0,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($consentido) {
            $data['data_consentimento'] = date('Y-m-d H:i:s');
            $data['data_revogacao'] = null;
        } else {
            $data['data_revogacao'] = date('Y-m-d H:i:s');
        }
        
        if ($existente) {
            // Atualizar existente
            $this->db->where('id', $existente->id);
            $this->db->update('consentimentos_lgpd', $data);
            return $existente->id;
        } else {
            // Criar novo
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('consentimentos_lgpd', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Obtém consentimentos de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @return array
     */
    public function getByCliente($cliente_id)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->order_by('tipo_consentimento', 'asc');
        return $this->db->get('consentimentos_lgpd')->result();
    }

    /**
     * Verifica se cliente consentiu com um tipo específico
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de consentimento
     * @return bool
     */
    public function temConsentimento($cliente_id, $tipo)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('tipo_consentimento', $tipo);
        $this->db->where('consentido', 1);
        $this->db->where('data_revogacao IS NULL');
        return $this->db->get('consentimentos_lgpd')->num_rows() > 0;
    }
}

