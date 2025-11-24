<?php

class Clientes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idClientes', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->like('nomeCliente', $where);
            $this->db->or_like('documento', $where);
            $this->db->or_like('email', $where);
            $this->db->or_like('telefone', $where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->select('*');
        $this->db->from('clientes');
        $this->db->where('idClientes', $id);
        $this->db->limit(1);

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return null;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id($table);
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

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function getOsByCliente($id)
    {
        // Verificar se a tabela os existe (não existe no sistema de advocacia)
        if (!$this->db->table_exists('os')) {
            return [];
        }

        $this->db->where('clientes_id', $id);
        $this->db->order_by('idOs', 'desc');
        $this->db->limit(10);

        $query = $this->db->get('os');
        
        if ($query === false) {
            log_message('error', 'Erro na query getOsByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Retorna todas as OS vinculados ao cliente
     *
     * @param  int  $id
     * @return array
     */
    public function getAllOsByClient($id)
    {
        // Verificar se a tabela os existe (não existe no sistema de advocacia)
        if (!$this->db->table_exists('os')) {
            return [];
        }

        $this->db->where('clientes_id', $id);

        $query = $this->db->get('os');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAllOsByClient: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Remover todas as OS por cliente
     *
     * @param  array  $os
     * @return bool
     */
    public function removeClientOs($os)
    {
        // Verificar se a tabela os existe (não existe no sistema de advocacia)
        if (!$this->db->table_exists('os')) {
            return true; // Retorna true pois não há nada para remover
        }

        try {
            foreach ($os as $o) {
                if ($this->db->table_exists('servicos_os')) {
                    $this->db->where('os_id', $o->idOs);
                    $this->db->delete('servicos_os');
                }

                if ($this->db->table_exists('produtos_os')) {
                    $this->db->where('os_id', $o->idOs);
                    $this->db->delete('produtos_os');
                }

                $this->db->where('idOs', $o->idOs);
                $this->db->delete('os');
            }
        } catch (Exception $e) {
            log_message('error', 'Erro ao remover OS do cliente: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Retorna todas as Vendas vinculados ao cliente
     *
     * @param  int  $id
     * @return array
     */
    public function getAllVendasByClient($id)
    {
        // Verificar se a tabela vendas existe (não existe no sistema de advocacia)
        if (!$this->db->table_exists('vendas')) {
            return [];
        }

        $this->db->where('clientes_id', $id);

        $query = $this->db->get('vendas');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAllVendasByClient: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Remover todas as Vendas por cliente
     *
     * @param  array  $vendas
     * @return bool
     */
    public function removeClientVendas($vendas)
    {
        // Verificar se a tabela vendas existe (não existe no sistema de advocacia)
        if (!$this->db->table_exists('vendas')) {
            return true; // Retorna true pois não há nada para remover
        }

        try {
            foreach ($vendas as $v) {
                if ($this->db->table_exists('itens_de_vendas')) {
                    $this->db->where('vendas_id', $v->idVendas);
                    $this->db->delete('itens_de_vendas');
                }

                $this->db->where('idVendas', $v->idVendas);
                $this->db->delete('vendas');
            }
        } catch (Exception $e) {
            log_message('error', 'Erro ao remover vendas do cliente: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Verifica se o e-mail já existe na tabela de clientes
     * 
     * Útil para validar unicidade de email antes de inserir/atualizar.
     * Em edição, permite excluir o próprio cliente da verificação.
     *
     * @param string $email Email a verificar
     * @param int|null $id ID do cliente a excluir da verificação (opcional, para edição)
     * @return bool True se email existe, False caso contrário
     */
    public function emailExists($email, $id = null)
    {
        if (empty($email)) {
            return false;
        }
        
        $this->db->where('email', $email);
        
        if ($id !== null) {
            $this->db->where('idClientes !=', $id);
        }
        
        $query = $this->db->get('clientes');
        
        return $query->num_rows() > 0;
    }

    /**
     * Verifica se o documento (CPF/CNPJ) já existe na tabela de clientes
     * 
     * Útil para validar unicidade de documento antes de inserir/atualizar.
     * Em edição, permite excluir o próprio cliente da verificação.
     *
     * @param string $documento Documento (CPF/CNPJ) a verificar (sem formatação)
     * @param int|null $id ID do cliente a excluir da verificação (opcional, para edição)
     * @return bool True se documento existe, False caso contrário
     */
    public function documentoExists($documento, $id = null)
    {
        if (empty($documento)) {
            return false;
        }
        
        // Limpar formatação do documento
        $documento_limpo = preg_replace('/[^a-zA-Z0-9]/', '', $documento);
        
        if (empty($documento_limpo)) {
            return false;
        }
        
        $this->db->where('documento', $documento_limpo);
        
        if ($id !== null) {
            $this->db->where('idClientes !=', $id);
        }
        
        $query = $this->db->get('clientes');
        
        return $query->num_rows() > 0;
    }
}
