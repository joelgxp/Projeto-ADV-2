<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Faturas_model extends CI_Model
{
    private $id_column = null; // Cache para coluna de ID
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Detecta e retorna o nome correto da coluna de ID
     */
    private function getIdColumn()
    {
        if ($this->id_column === null) {
            $columns = $this->db->list_fields('faturas');
            $this->id_column = in_array('idFaturas', $columns) ? 'idFaturas' : 'id';
        }
        return $this->id_column;
    }

    public function get($where = '', $perpage = 0, $start = 0, $one = false)
    {
        // Detectar nome correto da coluna de número
        $columns = $this->db->list_fields('faturas');
        $numero_col = in_array('numero_fatura', $columns) ? 'numero_fatura' : 'numero';
        
        // Usar método getIdColumn() para detectar coluna de ID correta
        $id_col = $this->getIdColumn();
        
        // Selecionar campos explicitamente para garantir que id está presente
        // Usar a coluna correta de ID e criar alias 'id' para compatibilidade
        // IMPORTANTE: Selecionar id_col primeiro, depois faturas.* para evitar conflito
        $this->db->select('faturas.' . $id_col . ' as id');
        $this->db->select('faturas.*');
        $this->db->select('clientes.nomeCliente, clientes.documento');
        // Adicionar alias para compatibilidade com views
        $this->db->select('faturas.' . $numero_col . ' as numero');
        $this->db->from('faturas');
        $this->db->join('clientes', 'clientes.idClientes = faturas.clientes_id', 'left');
        
        // Aplicar filtros WHERE apenas se houver
        if ($where !== null && $where !== '') {
            if (is_array($where)) {
                // Se array vazio, não aplicar WHERE (retorna todas as faturas)
                if (count($where) > 0) {
                    foreach ($where as $key => $value) {
                        // Ignorar valores vazios ou null
                        if ($value === '' || $value === null) {
                            continue;
                        }
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
                }
            } else {
                $this->db->where($where);
            }
        }
        
        $this->db->order_by('faturas.data_emissao', 'DESC');
        $this->db->order_by('faturas.' . $numero_col, 'DESC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        // Verificar erros SQL
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Erro na query Faturas_model::get: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('debug', 'Query SQL: ' . $this->db->last_query());
            return $one ? null : [];
        }
        
        if ($query === false) {
            log_message('error', 'Query retornou false em Faturas_model::get');
            return $one ? null : [];
        }
        
        if ($one) {
            $result = $query->row();
            // Garantir que propriedade 'id' existe
            if ($result && !isset($result->id) && isset($result->idFaturas)) {
                $result->id = $result->idFaturas;
            }
            return $result;
        }
        
        $results = $query->result();
        // Garantir que propriedade 'id' existe em todos os resultados
        foreach ($results as $r) {
            if (!isset($r->id) && isset($r->idFaturas)) {
                $r->id = $r->idFaturas;
            }
        }
        return $results;
    }

    public function getById($id)
    {
        // Usar método getIdColumn() para detectar coluna de ID correta
        $id_col = $this->getIdColumn();
        return $this->get(['faturas.' . $id_col => $id], 0, 0, true);
    }

    public function getByCliente($cliente_id, $status = null)
    {
        $where = ['faturas.clientes_id' => $cliente_id];
        if ($status) {
            $where['faturas.status'] = $status;
        }
        return $this->get($where);
    }

    public function gerarNumero()
    {
        $ano = date('Y');
        
        // Verificar qual coluna existe: 'numero' ou 'numero_fatura'
        $columns = $this->db->list_fields('faturas');
        $numero_col = in_array('numero_fatura', $columns) ? 'numero_fatura' : 'numero';
        
        $this->db->select($numero_col);
        $this->db->from('faturas');
        $this->db->like($numero_col, 'FAT-' . $ano . '-', 'after');
        $this->db->order_by($numero_col, 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        
        // Validar se a query foi bem-sucedida antes de chamar row()
        if ($query === false) {
            log_message('error', 'Erro na query Faturas_model::gerarNumero: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            // Retornar número padrão se a query falhar
            return 'FAT-' . $ano . '-001';
        }
        
        $ultimo = $query->row();
        
        if ($ultimo) {
            // Extrair número sequencial (usar propriedade dinâmica)
            $numero_valor = $numero_col === 'numero_fatura' ? $ultimo->numero_fatura : $ultimo->numero;
            $parts = explode('-', $numero_valor);
            $sequencial = intval($parts[2]) + 1;
        } else {
            $sequencial = 1;
        }
        
        return 'FAT-' . $ano . '-' . str_pad($sequencial, 3, '0', STR_PAD_LEFT);
    }

    public function add($data)
    {
        // Verificar qual coluna existe: 'numero' ou 'numero_fatura'
        $columns = $this->db->list_fields('faturas');
        $numero_col = in_array('numero_fatura', $columns) ? 'numero_fatura' : 'numero';
        
        if (!isset($data[$numero_col])) {
            $data[$numero_col] = $this->gerarNumero();
        }
        
        if (!isset($data['saldo_restante'])) {
            $data['saldo_restante'] = $data['valor_total'];
        }
        
        $data['created_by'] = $this->session->userdata('id_admin');
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Remover campos que não existem na tabela antes de inserir
        $table_fields = $this->db->list_fields('faturas');
        $data_clean = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $table_fields)) {
                $data_clean[$key] = $value;
            }
        }
        
        $this->db->insert('faturas', $data_clean);
        
        // Verificar erros SQL
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Erro ao inserir fatura: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('debug', 'Dados tentados: ' . json_encode($data_clean));
            log_message('debug', 'Colunas da tabela: ' . json_encode($table_fields));
            return false;
        }
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        log_message('error', 'Nenhuma linha afetada ao inserir fatura. Dados: ' . json_encode($data_clean));
        return false;
    }

    public function edit($id, $data)
    {
        $data['updated_by'] = $this->session->userdata('id_admin');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Usar método getIdColumn() para detectar coluna correta
        $this->db->where($this->getIdColumn(), $id);
        $this->db->update('faturas', $data);
        
        // Recalcular saldo se valor_total mudou
        if (isset($data['valor_total'])) {
            $this->calcularSaldo($id);
        }
        
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        // Verificar se há pagamentos
        $this->db->where('faturas_id', $id);
        $count = $this->db->count_all_results('pagamentos');
        
        if ($count > 0) {
            return false; // Não pode deletar se há pagamentos
        }
        
        // Deletar itens primeiro
        $this->db->where('faturas_id', $id);
        $this->db->delete('faturas_itens');
        
        // Deletar fatura
        // Usar método getIdColumn() para detectar coluna correta
        $this->db->where($this->getIdColumn(), $id);
        $this->db->delete('faturas');
        
        return $this->db->affected_rows() == 1;
    }

    public function calcularSaldo($fatura_id)
    {
        // Calcular total de pagamentos
        $this->db->select_sum('valor');
        $this->db->where('faturas_id', $fatura_id);
        $query = $this->db->get('pagamentos');
        $result = $query->row();
        $valor_pago = $result->valor ?: 0;
        
        // Buscar valor total da fatura
        $fatura = $this->getById($fatura_id);
        if (!$fatura) {
            return false;
        }
        
        $saldo_restante = $fatura->valor_total - $valor_pago;
        
        // Atualizar status
        $status = 'emitida';
        if ($saldo_restante <= 0) {
            $status = 'paga';
        } elseif ($valor_pago > 0) {
            $status = 'parcialmente_paga';
        }
        
        // Verificar se está atrasada
        if ($status != 'paga' && $status != 'cancelada') {
            if (strtotime($fatura->data_vencimento) < strtotime(date('Y-m-d'))) {
                $status = 'atrasada';
            }
        }
        
        // Atualizar fatura
        $this->db->where($this->getIdColumn(), $fatura_id);
        $this->db->update('faturas', [
            'valor_pago' => $valor_pago,
            'saldo_restante' => $saldo_restante,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }

    public function atualizarStatus()
    {
        // Atualizar status de faturas atrasadas
        $this->db->where('data_vencimento <', date('Y-m-d'));
        $this->db->where('status !=', 'paga');
        $this->db->where('status !=', 'cancelada');
        $this->db->update('faturas', [
            'status' => 'atrasada',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->db->affected_rows();
    }

    public function getItens($fatura_id)
    {
        $this->db->select('faturas_itens.*, processos.numeroProcesso');
        $this->db->from('faturas_itens');
        $this->db->join('processos', 'processos.idProcessos = faturas_itens.processos_id', 'left');
        $this->db->where('faturas_itens.faturas_id', $fatura_id);
        $this->db->order_by('faturas_itens.id', 'ASC');
        
        return $this->db->get()->result();
    }

    public function addItem($data)
    {
        // Calcular valor total se não informado
        if (!isset($data['valor_total'])) {
            $valor_base = $data['valor_unitario'] * $data['quantidade'];
            $valor_ipi = ($valor_base * $data['ipi']) / 100;
            $valor_iss = ($valor_base * $data['iss']) / 100;
            $data['valor_total'] = $valor_base + $valor_ipi + $valor_iss;
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('faturas_itens', $data);
        
        if ($this->db->affected_rows() == 1) {
            // Recalcular valor total da fatura
            $this->recalcularValorTotal($data['faturas_id']);
            return $this->db->insert_id();
        }
        
        return false;
    }

    public function editItem($id, $data)
    {
        // Calcular valor total se necessário
        if (isset($data['valor_unitario']) || isset($data['quantidade']) || isset($data['ipi']) || isset($data['iss'])) {
            $item = $this->getItemById($id);
            if ($item) {
                $valor_unitario = isset($data['valor_unitario']) ? $data['valor_unitario'] : $item->valor_unitario;
                $quantidade = isset($data['quantidade']) ? $data['quantidade'] : $item->quantidade;
                $ipi = isset($data['ipi']) ? $data['ipi'] : $item->ipi;
                $iss = isset($data['iss']) ? $data['iss'] : $item->iss;
                
                $valor_base = $valor_unitario * $quantidade;
                $valor_ipi = ($valor_base * $ipi) / 100;
                $valor_iss = ($valor_base * $iss) / 100;
                $data['valor_total'] = $valor_base + $valor_ipi + $valor_iss;
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update('faturas_itens', $data);
        
        if ($this->db->affected_rows() >= 0) {
            // Recalcular valor total da fatura
            $item = $this->getItemById($id);
            if ($item) {
                $this->recalcularValorTotal($item->faturas_id);
            }
            return true;
        }
        
        return false;
    }

    public function getItemById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('faturas_itens')->row();
    }

    public function deleteItem($id)
    {
        $item = $this->getItemById($id);
        if (!$item) {
            return false;
        }
        
        $fatura_id = $item->faturas_id;
        
        $this->db->where('id', $id);
        $this->db->delete('faturas_itens');
        
        if ($this->db->affected_rows() == 1) {
            // Recalcular valor total da fatura
            $this->recalcularValorTotal($fatura_id);
            return true;
        }
        
        return false;
    }

    public function recalcularValorTotal($fatura_id)
    {
        $this->db->select_sum('valor_total');
        $this->db->where('faturas_id', $fatura_id);
        $query = $this->db->get('faturas_itens');
        $result = $query->row();
        $valor_total = $result->valor_total ?: 0;
        
        // Atualizar fatura
        $this->db->where($this->getIdColumn(), $fatura_id);
        $this->db->update('faturas', [
            'valor_total' => $valor_total,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Recalcular saldo
        $this->calcularSaldo($fatura_id);
        
        return true;
    }

    public function count($where = '')
    {
        $this->db->from('faturas');
        $this->db->join('clientes', 'clientes.idClientes = faturas.clientes_id', 'left');
        
        if ($where) {
            if (is_array($where)) {
                // Se array vazio, não aplicar WHERE
                if (count($where) > 0) {
                    foreach ($where as $key => $value) {
                        // Ignorar valores vazios ou null
                        if ($value === '' || $value === null) {
                            continue;
                        }
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
                }
            } else {
                $this->db->where($where);
            }
        }
        
        $result = $this->db->count_all_results();
        
        // Verificar erros
        $error = $this->db->error();
        if ($error['code'] != 0) {
            log_message('error', 'Erro na query Faturas_model::count: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
            log_message('debug', 'Query SQL: ' . $this->db->last_query());
            return 0;
        }
        
        return $result;
    }

    public function getEstatisticas($where = '')
    {
        $this->db->select('
            COUNT(*) as total_faturas,
            SUM(valor_total) as valor_total,
            SUM(valor_pago) as valor_pago,
            SUM(saldo_restante) as saldo_restante,
            SUM(CASE WHEN status = "paga" THEN 1 ELSE 0 END) as total_pagas,
            SUM(CASE WHEN status = "atrasada" THEN 1 ELSE 0 END) as total_atrasadas,
            SUM(CASE WHEN status = "emitida" THEN 1 ELSE 0 END) as total_emitidas
        ');
        $this->db->from('faturas');
        $this->db->join('clientes', 'clientes.idClientes = faturas.clientes_id', 'left');
        
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
        
        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query Faturas_model::getEstatisticas: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return (object)[
                'total_faturas' => 0,
                'valor_total' => 0,
                'valor_pago' => 0,
                'saldo_restante' => 0,
                'total_pagas' => 0,
                'total_atrasadas' => 0,
                'total_emitidas' => 0
            ];
        }
        
        return $query->row();
    }
}

