<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model para gerenciar alertas de prazos
 * 
 * Conforme RN 4.4 - Sistema de Alertas Automáticos
 */
class Alertas_prazos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adiciona alerta na fila
     * 
     * @param array $data Dados do alerta
     * @return int|false ID do alerta ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('alertas_prazos', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Busca alertas pendentes
     * 
     * @param string|null $tipo_alerta Tipo do alerta (7d, 2d, 1d, hoje)
     * @return array Lista de alertas pendentes
     */
    public function getPendentes($tipo_alerta = null)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return [];
        }

        $this->db->where('status', 'pendente');

        if ($tipo_alerta !== null) {
            $this->db->where('tipo_alerta', $tipo_alerta);
        }

        $this->db->order_by('data_envio_previsto', 'ASC');
        $query = $this->db->get('alertas_prazos');

        return $query->result();
    }

    /**
     * Busca alertas que precisam ser enviados agora
     * 
     * @return array Lista de alertas a serem enviados
     */
    public function getParaEnviar()
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return [];
        }

        $this->db->where('status', 'pendente');
        $this->db->where('data_envio_previsto <=', date('Y-m-d H:i:s'));
        $this->db->order_by('data_envio_previsto', 'ASC');
        $query = $this->db->get('alertas_prazos');

        return $query->result();
    }

    /**
     * Marca alerta como enviado
     * 
     * @param int $id ID do alerta
     * @return bool True se atualizado com sucesso
     */
    public function marcarEnviado($id)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return false;
        }

        $this->db->where('idAlertasPrazos', $id);
        $this->db->update('alertas_prazos', [
            'status' => 'enviado',
            'data_envio' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Marca alerta como falhou
     * 
     * @param int $id ID do alerta
     * @param string $erro Mensagem de erro
     * @return bool True se atualizado com sucesso
     */
    public function marcarFalhou($id, $erro = null)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return false;
        }

        $data = [
            'status' => 'falhou',
            'data_envio' => date('Y-m-d H:i:s')
        ];

        if ($erro !== null) {
            $data['observacoes'] = $erro;
        }

        $this->db->where('idAlertasPrazos', $id);
        $this->db->update('alertas_prazos', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Verifica se alerta já foi enviado para um prazo específico
     * 
     * @param int $prazos_id ID do prazo
     * @param string $tipo_alerta Tipo do alerta
     * @return bool True se já foi enviado
     */
    public function jaEnviado($prazos_id, $tipo_alerta)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return false;
        }

        $this->db->where('prazos_id', $prazos_id);
        $this->db->where('tipo_alerta', $tipo_alerta);
        $this->db->where('status', 'enviado');
        $query = $this->db->get('alertas_prazos');

        return $query->num_rows() > 0;
    }

    /**
     * Busca alertas por prazo
     * 
     * @param int $prazos_id ID do prazo
     * @return array Lista de alertas
     */
    public function getByPrazo($prazos_id)
    {
        if (!$this->db->table_exists('alertas_prazos')) {
            return [];
        }

        $this->db->where('prazos_id', $prazos_id);
        $this->db->order_by('data_envio_previsto', 'DESC');
        $query = $this->db->get('alertas_prazos');

        return $query->result();
    }
}

