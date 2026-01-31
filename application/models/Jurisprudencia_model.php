<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Jurisprudencia_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = [], $perpage = 0, $start = 0, $one = false)
    {
        if (!$this->db->table_exists('jurisprudencia_base')) {
            return $one ? null : [];
        }

        $this->db->from('jurisprudencia_base');

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    if (strpos($key, ' LIKE') !== false) {
                        $field = str_replace(' LIKE', '', $key);
                        $this->db->like($field, $value);
                    } else {
                        $this->db->where($key, $value);
                    }
                }
            } else {
                $this->db->where($where);
            }
        }

        $this->db->order_by('dataCadastro', 'DESC');

        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Erro na query Jurisprudencia_model::get');
            return $one ? null : [];
        }

        return $one ? $query->row() : $query->result();
    }

    public function getById($id)
    {
        return $this->get(['id' => $id], 0, 0, true);
    }

    public function add($data)
    {
        if (!$this->db->table_exists('jurisprudencia_base')) {
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('jurisprudencia_base', $data);

        return $this->db->affected_rows() == 1 ? $this->db->insert_id() : false;
    }

    public function edit($data, $id)
    {
        if (!$this->db->table_exists('jurisprudencia_base')) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update('jurisprudencia_base', $data);

        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        if (!$this->db->table_exists('jurisprudencia_base')) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->delete('jurisprudencia_base');

        return $this->db->affected_rows() == 1;
    }

    public function count($where = [])
    {
        if (!$this->db->table_exists('jurisprudencia_base')) {
            return 0;
        }

        $this->db->from('jurisprudencia_base');

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
}
