<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Contratos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = '', $perpage = 0, $start = 0, $one = false)
    {
        $this->db->select('contratos.*, clientes.nomeCliente, clientes.documento');
        $this->db->from('contratos');
        $this->db->join('clientes', 'clientes.idClientes = contratos.clientes_id', 'left');
        
        if ($where) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    if (strpos($key, ' LIKE') !== false) {
                        $field = str_replace(' LIKE', '', $key);
                        $this->db->like($field, $value);
                    } elseif (strpos($key, ' >=') !== false) {
                        $field = str_replace(' >=', '', $key);
                        $this->db->where($field . ' >=', $value);
                    } elseif (strpos($key, ' <=') !== false) {
                        $field = str_replace(' <=', '', $key);
                        $this->db->where($field . ' <=', $value);
                    } else {
                        $this->db->where($key, $value);
                    }
                }
            } else {
                $this->db->where($where);
            }
        }
        
        $this->db->order_by('contratos.data_inicio', 'DESC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        // Verificar erros SQL
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Erro na query Contratos_model::get: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('debug', 'Query SQL: ' . $this->db->last_query());
            return $one ? null : [];
        }
        
        if ($query === false) {
            log_message('error', 'Query retornou false em Contratos_model::get');
            return $one ? null : [];
        }
        
        if ($one) {
            $result = $query->row();
            // Garantir que propriedade 'id' existe
            if ($result && !isset($result->id) && isset($result->idContratos)) {
                $result->id = $result->idContratos;
            }
            return $result;
        }
        
        $results = $query->result();
        // Garantir que propriedade 'id' existe em todos os resultados
        foreach ($results as $r) {
            if (!isset($r->id) && isset($r->idContratos)) {
                $r->id = $r->idContratos;
            }
        }
        return $results;
    }

    public function getById($id)
    {
        return $this->get(['contratos.id' => $id], 0, 0, true);
    }

    public function getByCliente($cliente_id)
    {
        return $this->get(['contratos.clientes_id' => $cliente_id]);
    }

    public function getContratoAtivo($cliente_id)
    {
        return $this->get(['contratos.clientes_id' => $cliente_id, 'contratos.ativo' => 1], 0, 0, true);
    }

    public function add($data)
    {
        // Log dos dados recebidos para debug
        log_message('debug', 'Contratos_model::add - Dados recebidos: ' . json_encode($data));
        
        // Validar dados obrigatórios
        if (empty($data['clientes_id'])) {
            log_message('error', 'Contratos_model::add - clientes_id está vazio');
            return false;
        }
        
        if (empty($data['tipo'])) {
            log_message('error', 'Contratos_model::add - tipo está vazio');
            return false;
        }
        
        if (empty($data['data_inicio'])) {
            log_message('error', 'Contratos_model::add - data_inicio está vazio');
            return false;
        }
        
        // Se está ativando um contrato, desativa os outros
        if (isset($data['ativo']) && $data['ativo'] == 1 && isset($data['clientes_id'])) {
            $this->desativarOutros($data['clientes_id']);
        }
        
        $data['created_by'] = $this->session->userdata('id_admin');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Limpar campos vazios (null)
        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                unset($data[$key]);
            }
        }
        
        $this->db->insert('contratos', $data);
        
        // Verificar erros SQL
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Contratos_model::add - Erro SQL: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('error', 'Contratos_model::add - Query: ' . $this->db->last_query());
            return false;
        }
        
        if ($this->db->affected_rows() == 1) {
            $insert_id = $this->db->insert_id();
            log_message('info', 'Contratos_model::add - Contrato criado com sucesso. ID: ' . $insert_id);
            return $insert_id;
        }
        
        log_message('error', 'Contratos_model::add - Nenhuma linha afetada. affected_rows: ' . $this->db->affected_rows());
        return false;
    }

    public function edit($id, $data)
    {
        // Se está ativando um contrato, desativa os outros
        if (isset($data['ativo']) && $data['ativo'] == 1) {
            $contrato = $this->getById($id);
            if ($contrato) {
                $this->desativarOutros($contrato->clientes_id, $id);
            }
        }
        
        $data['updated_by'] = $this->session->userdata('id_admin');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update('contratos', $data);
        
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        // Verificar se há faturas vinculadas
        $this->db->where('contratos_id', $id);
        $count = $this->db->count_all_results('faturas');
        
        if ($count > 0) {
            return false; // Não pode deletar se há faturas
        }
        
        $this->db->where('id', $id);
        $this->db->delete('contratos');
        
        return $this->db->affected_rows() == 1;
    }

    public function ativarContrato($id)
    {
        $contrato = $this->getById($id);
        if (!$contrato) {
            return false;
        }
        
        // Desativa outros contratos do mesmo cliente
        $this->desativarOutros($contrato->clientes_id, $id);
        
        // Ativa este contrato
        return $this->edit($id, ['ativo' => 1]);
    }

    public function desativarOutros($cliente_id, $excluir_id = null)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->where('ativo', 1);
        
        if ($excluir_id) {
            $this->db->where('id !=', $excluir_id);
        }
        
        $this->db->update('contratos', [
            'ativo' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }

    public function count($where = '')
    {
        $this->db->from('contratos');
        $this->db->join('clientes', 'clientes.idClientes = contratos.clientes_id', 'left');
        
        if ($where) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    if (strpos($key, ' LIKE') !== false) {
                        $field = str_replace(' LIKE', '', $key);
                        $this->db->like($field, $value);
                    } elseif (strpos($key, ' >=') !== false) {
                        $field = str_replace(' >=', '', $key);
                        $this->db->where($field . ' >=', $value);
                    } elseif (strpos($key, ' <=') !== false) {
                        $field = str_replace(' <=', '', $key);
                        $this->db->where($field . ' <=', $value);
                    } else {
                        $this->db->where($key, $value);
                    }
                }
            } else {
                $this->db->where($where);
            }
        }
        return $this->db->count_all_results();
    }
}

