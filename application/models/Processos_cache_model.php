<?php

class Processos_cache_model extends CI_Model
{
    private $table = 'processos_cache';

    public function __construct()
    {
        parent::__construct();
    }

    public function getByNumero($numeroLimpo)
    {
        if (! $this->db->table_exists($this->table)) {
            return null;
        }

        $this->db->where('numeroProcesso', $numeroLimpo);
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        return $query ? $query->row() : null;
    }

    public function saveCache($numeroLimpo, $payloadJson, $hash)
    {
        if (! $this->db->table_exists($this->table)) {
            return false;
        }

        $existing = $this->getByNumero($numeroLimpo);
        $data = [
            'numeroProcesso' => $numeroLimpo,
            'payload' => $payloadJson,
            'hash_payload' => $hash,
            'ultimo_fetch' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            if ($existing->hash_payload !== $hash) {
                $data['ultima_atualizacao'] = date('Y-m-d H:i:s');
            } else {
                // mantém ultima_atualizacao existente se hash igual
                unset($data['payload']);
                unset($data['hash_payload']);
            }

            $this->db->where('id', $existing->id);
            return $this->db->update($this->table, $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['ultima_atualizacao'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
}

