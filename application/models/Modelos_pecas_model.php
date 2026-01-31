<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Modelos_pecas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = [], $perpage = 0, $start = 0, $one = false)
    {
        if (!$this->db->table_exists('modelos_pecas')) {
            return $one ? null : [];
        }

        $this->db->select('modelos_pecas.*, usuarios.nome as nomeAutor');
        $this->db->from('modelos_pecas');
        $this->db->join('usuarios', 'usuarios.idUsuarios = modelos_pecas.usuarios_id', 'left');

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $this->db->where($key, $value);
                }
            } else {
                $this->db->where($where);
            }
        }

        $this->db->order_by('modelos_pecas.nome', 'ASC');

        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Erro na query Modelos_pecas_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return $one ? null : [];
        }

        return $one ? $query->row() : $query->result();
    }

    public function getById($id)
    {
        return $this->get(['modelos_pecas.id' => $id], 0, 0, true);
    }

    public function getByTipoArea($tipo_peca, $area = null)
    {
        $where = ['modelos_pecas.tipo_peca' => $tipo_peca, 'modelos_pecas.ativo' => 1];
        if ($area) {
            $where['modelos_pecas.area'] = $area;
        }
        return $this->get($where);
    }

    public function add($data)
    {
        if (!$this->db->table_exists('modelos_pecas')) {
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('modelos_pecas', $data);

        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($data, $id)
    {
        if (!$this->db->table_exists('modelos_pecas')) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update('modelos_pecas', $data);

        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        if (!$this->db->table_exists('modelos_pecas')) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->delete('modelos_pecas');

        return $this->db->affected_rows() == 1;
    }

    public function count($where = [])
    {
        if (!$this->db->table_exists('modelos_pecas')) {
            return 0;
        }

        $this->db->from('modelos_pecas');

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $this->db->where($key, $value);
                }
            } else {
                $this->db->where($where);
            }
        }

        return $this->db->count_all_results();
    }

    /**
     * Retorna lista de tipos de peça para dropdown
     */
    public function getTiposPeca()
    {
        return [
            'peticao_inicial' => 'Petição Inicial',
            'contestacao' => 'Contestação',
            'replica' => 'Réplica',
            'recurso' => 'Recurso (Apelação/Agravo)',
            'peticao_simples' => 'Petição Simples (manifestação, juntada, pedido de prazo)',
        ];
    }

    /**
     * Retorna lista de áreas para dropdown
     */
    public function getAreas()
    {
        return [
            'civel' => 'Cível',
            'trabalhista' => 'Trabalhista',
            'tributario' => 'Tributário',
            'criminal' => 'Criminal',
            'familia' => 'Família',
            'consumidor' => 'Consumidor',
            'outro' => 'Outro',
        ];
    }
}
