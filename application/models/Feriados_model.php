<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Feriados_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os feriados
     * 
     * @param int|null $municipio_id ID do município para filtrar feriados municipais
     * @param int|null $ano Ano para filtrar (opcional, se não informado busca todos)
     * @return array Lista de feriados
     */
    public function getAll($municipio_id = null, $ano = null)
    {
        if (!$this->db->table_exists('feriados')) {
            return [];
        }

        if ($ano !== null) {
            $this->db->where("YEAR(data)", $ano);
        }

        // Feriados nacionais ou do município específico
        $this->db->group_start();
        $this->db->where('tipo', 'nacional');
        if ($municipio_id !== null) {
            $this->db->or_group_start();
            $this->db->where('tipo', 'municipal');
            $this->db->where('municipio_id', $municipio_id);
            $this->db->group_end();
        }
        $this->db->group_end();

        $this->db->or_where('recorrente', 1); // Feriados recorrentes (ex: Natal sempre 25/12)

        $this->db->order_by('data', 'ASC');
        $query = $this->db->get('feriados');

        return $query->result();
    }

    /**
     * Busca feriado por ID
     * 
     * @param int $id ID do feriado
     * @return object|false Objeto do feriado ou false se não encontrado
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('feriados')) {
            return false;
        }

        $this->db->where('idFeriados', $id);
        $query = $this->db->get('feriados');

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    /**
     * Verifica se uma data é feriado
     * 
     * @param string $data Data no formato Y-m-d
     * @param int|null $municipio_id ID do município para verificar feriados municipais
     * @return bool True se é feriado, False caso contrário
     */
    public function isFeriado($data, $municipio_id = null)
    {
        if (!$this->db->table_exists('feriados')) {
            return false;
        }

        $this->db->where('data', $data);

        // Verificar feriados nacionais ou municipais
        $this->db->group_start();
        $this->db->where('tipo', 'nacional');
        if ($municipio_id !== null) {
            $this->db->or_group_start();
            $this->db->where('tipo', 'municipal');
            $this->db->where('municipio_id', $municipio_id);
            $this->db->group_end();
        }
        $this->db->group_end();

        // Verificar também feriados recorrentes (ex: Natal sempre em 25/12)
        $this->db->or_group_start();
        $this->db->where('recorrente', 1);
        $this->db->where("DATE_FORMAT(data, '%m-%d')", date('m-d', strtotime($data)));
        $this->db->group_end();

        $query = $this->db->get('feriados');

        return $query->num_rows() > 0;
    }

    /**
     * Adiciona novo feriado
     * 
     * @param array $data Dados do feriado
     * @return int|false ID do feriado inserido ou false em caso de erro
     */
    public function add($data)
    {
        if (!$this->db->table_exists('feriados')) {
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('feriados', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Atualiza feriado
     * 
     * @param int $id ID do feriado
     * @param array $data Dados atualizados
     * @return bool True se atualizado com sucesso
     */
    public function edit($id, $data)
    {
        if (!$this->db->table_exists('feriados')) {
            return false;
        }

        $this->db->where('idFeriados', $id);
        $this->db->update('feriados', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Remove feriado
     * 
     * @param int $id ID do feriado
     * @return bool True se removido com sucesso
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('feriados')) {
            return false;
        }

        $this->db->where('idFeriados', $id);
        $this->db->delete('feriados');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Busca feriados por tipo
     * 
     * @param string $tipo Tipo do feriado (nacional, municipal)
     * @param int|null $ano Ano para filtrar (opcional)
     * @return array Lista de feriados
     */
    public function getByTipo($tipo, $ano = null)
    {
        if (!$this->db->table_exists('feriados')) {
            return [];
        }

        $this->db->where('tipo', $tipo);

        if ($ano !== null) {
            $this->db->where("YEAR(data)", $ano);
        }

        $this->db->order_by('data', 'ASC');
        $query = $this->db->get('feriados');

        return $query->result();
    }

    /**
     * Busca feriados recorrentes
     * 
     * @return array Lista de feriados recorrentes
     */
    public function getRecorrentes()
    {
        if (!$this->db->table_exists('feriados')) {
            return [];
        }

        $this->db->where('recorrente', 1);
        $this->db->order_by("DATE_FORMAT(data, '%m-%d')", 'ASC');
        $query = $this->db->get('feriados');

        return $query->result();
    }
}

