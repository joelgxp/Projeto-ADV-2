<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists($table)) {
            log_message('error', 'Tabela ' . $table . ' não existe em Financeiro_model::get');
            return $one ? null : [];
        }
        
        // Detectar estrutura da tabela principal
        $table_columns = $this->db->list_fields($table);
        
        // Detectar coluna de data de vencimento
        $data_vencimento_col = in_array('data_vencimento', $table_columns) ? 'data_vencimento' : 
                              (in_array('dataVencimento', $table_columns) ? 'dataVencimento' : null);
        
        // Detectar coluna de usuário na tabela principal
        $usuarios_id_col = in_array('usuarios_id', $table_columns) ? 'usuarios_id' : 
                          (in_array('usuario_id', $table_columns) ? 'usuario_id' : null);
        
        $this->db->select($fields);
        
        // JOIN com usuarios apenas se a tabela existir e houver coluna de relacionamento
        if ($this->db->table_exists('usuarios') && $usuarios_id_col) {
            $usuarios_columns = $this->db->list_fields('usuarios');
            $usuarios_pk = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : 
                          (in_array('id', $usuarios_columns) ? 'id' : null);
            
            if ($usuarios_pk) {
                $this->db->select('usuarios.*');
                $this->db->join('usuarios', 'usuarios.' . $usuarios_pk . ' = ' . $table . '.' . $usuarios_id_col, 'left');
            }
        }
        
        $this->db->from($table);
        
        // Ordenar por data de vencimento se a coluna existir
        if ($data_vencimento_col) {
            $this->db->order_by($data_vencimento_col, 'asc');
        }
        
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Financeiro_model::get: ' . ($error['message'] ?? 'Erro desconhecido'));
            return $one ? null : [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getTotals($where = '')
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('lancamentos')) {
            log_message('error', 'Tabela lancamentos não existe em Financeiro_model::getTotals');
            return ['despesas' => 0, 'receitas' => 0];
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('lancamentos');
        
        // Detectar colunas
        $tipo_col = in_array('tipo', $columns) ? 'tipo' : null;
        $valor_col = in_array('valor', $columns) ? 'valor' : null;
        $desconto_col = in_array('desconto', $columns) ? 'desconto' : null;
        $valor_desconto_col = in_array('valor_desconto', $columns) ? 'valor_desconto' : null;
        
        if (!$tipo_col || !$valor_col) {
            log_message('error', 'Colunas necessárias não encontradas em lancamentos');
            return ['despesas' => 0, 'receitas' => 0];
        }
        
        // Construir SELECT adaptado
        $select = "SUM(case when " . $tipo_col . " = 'despesa' then " . $valor_col;
        if ($desconto_col) {
            $select .= " - " . $desconto_col;
        }
        $select .= " end) as despesas, ";
        
        $select .= "SUM(case when " . $tipo_col . " = 'receita' then (IF(";
        if ($valor_desconto_col) {
            $select .= $valor_desconto_col . " = 0, " . $valor_col . ", " . $valor_desconto_col;
        } else {
            $select .= $valor_col;
        }
        $select .= ")) end) as receitas";
        
        $this->db->select($select);
        $this->db->from('lancamentos');

        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Financeiro_model::getTotals: ' . ($error['message'] ?? 'Erro desconhecido'));
            return ['despesas' => 0, 'receitas' => 0];
        }

        return (array) $query->row();
    }

    public function getEstatisticasFinanceiro2()
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('lancamentos')) {
            log_message('error', 'Tabela lancamentos não existe em Financeiro_model::getEstatisticasFinanceiro2');
            return (object) [
                'total_receita' => 0,
                'total_despesa' => 0,
                'total_valor_desconto' => 0,
                'total_valor_desconto_pendente' => 0,
                'total_receita_sem_desconto' => 0,
                'total_despesa_sem_desconto' => 0,
                'total_receita_pendente' => 0,
                'total_despesa_pendente' => 0
            ];
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('lancamentos');
        
        // Detectar colunas
        $tipo_col = in_array('tipo', $columns) ? 'tipo' : null;
        $valor_col = in_array('valor', $columns) ? 'valor' : null;
        $desconto_col = in_array('desconto', $columns) ? 'desconto' : null;
        $valor_desconto_col = in_array('valor_desconto', $columns) ? 'valor_desconto' : null;
        $baixado_col = in_array('baixado', $columns) ? 'baixado' : null;
        
        if (!$tipo_col || !$valor_col) {
            log_message('error', 'Colunas necessárias não encontradas em lancamentos para getEstatisticasFinanceiro2');
            return (object) [
                'total_receita' => 0,
                'total_despesa' => 0,
                'total_valor_desconto' => 0,
                'total_valor_desconto_pendente' => 0,
                'total_receita_sem_desconto' => 0,
                'total_despesa_sem_desconto' => 0,
                'total_receita_pendente' => 0,
                'total_despesa_pendente' => 0
            ];
        }
        
        // Construir SQL adaptado
        $sql = "SELECT ";
        
        // total_receita
        $sql .= "SUM(CASE WHEN ";
        if ($baixado_col) {
            $sql .= $baixado_col . " = 1 AND ";
        }
        $sql .= $tipo_col . " = 'receita' THEN IF(";
        if ($valor_desconto_col) {
            $sql .= $valor_desconto_col . " = 0, " . $valor_col . ", " . $valor_desconto_col;
        } else {
            $sql .= $valor_col;
        }
        $sql .= ") END) as total_receita, ";
        
        // total_despesa
        $sql .= "SUM(CASE WHEN ";
        if ($baixado_col) {
            $sql .= $baixado_col . " = 1 AND ";
        }
        $sql .= $tipo_col . " = 'despesa' THEN " . $valor_col;
        if ($desconto_col) {
            $sql .= " - " . $desconto_col;
        }
        $sql .= " END) as total_despesa, ";
        
        // total_valor_desconto
        if ($desconto_col && $baixado_col) {
            $sql .= "SUM(CASE WHEN " . $baixado_col . " = 1 THEN " . $desconto_col . " END) as total_valor_desconto, ";
        } else {
            $sql .= "0 as total_valor_desconto, ";
        }
        
        // total_valor_desconto_pendente
        if ($baixado_col) {
            $sql .= "SUM(CASE WHEN " . $baixado_col . " = 0 THEN " . $valor_col;
            if ($valor_desconto_col) {
                $sql .= " - " . $valor_desconto_col;
            }
            $sql .= " END) as total_valor_desconto_pendente, ";
        } else {
            $sql .= "0 as total_valor_desconto_pendente, ";
        }
        
        // total_receita_sem_desconto
        $sql .= "SUM(CASE WHEN " . $tipo_col . " = 'receita' THEN " . $valor_col . " END) as total_receita_sem_desconto, ";
        
        // total_despesa_sem_desconto
        $sql .= "SUM(CASE WHEN " . $tipo_col . " = 'despesa' THEN " . $valor_col . " END) as total_despesa_sem_desconto, ";
        
        // total_receita_pendente
        if ($baixado_col && $valor_desconto_col) {
            $sql .= "SUM(CASE WHEN " . $baixado_col . " = 0 AND " . $tipo_col . " = 'receita' THEN " . $valor_desconto_col . " END) as total_receita_pendente, ";
        } else {
            $sql .= "0 as total_receita_pendente, ";
        }
        
        // total_despesa_pendente
        if ($baixado_col && $valor_desconto_col) {
            $sql .= "SUM(CASE WHEN " . $baixado_col . " = 0 AND " . $tipo_col . " = 'despesa' THEN " . $valor_desconto_col . " END) as total_despesa_pendente ";
        } else {
            $sql .= "0 as total_despesa_pendente ";
        }
        
        $sql .= "FROM lancamentos";

        $query = $this->db->query($sql);
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Financeiro_model::getEstatisticasFinanceiro2: ' . ($error['message'] ?? 'Erro desconhecido'));
            return (object) [
                'total_receita' => 0,
                'total_despesa' => 0,
                'total_valor_desconto' => 0,
                'total_valor_desconto_pendente' => 0,
                'total_receita_sem_desconto' => 0,
                'total_despesa_sem_desconto' => 0,
                'total_receita_pendente' => 0,
                'total_despesa_pendente' => 0
            ];
        }

        return $query->row();
    }

    public function getById($id)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('clientes')) {
            log_message('error', 'Tabela clientes não existe em Financeiro_model::getById');
            return null;
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('clientes');
        $id_col = in_array('idClientes', $columns) ? 'idClientes' : 
                  (in_array('id', $columns) ? 'id' : null);
        
        if (!$id_col) {
            log_message('error', 'Coluna de ID não encontrada na tabela clientes');
            return null;
        }
        
        $this->db->where($id_col, $id);
        $this->db->limit(1);

        $query = $this->db->get('clientes');
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Financeiro_model::getById: ' . ($error['message'] ?? 'Erro desconhecido'));
            return null;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function add1($table, $data1)
    {
        $this->db->insert($table, $data1);
        if ($this->db->affected_rows() == '1') {
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

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table, $where)
    {
        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }

        return $this->db->count_all_results();
    }

    public function autoCompleteClienteFornecedor($q)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('lancamentos')) {
            echo json_encode([]);
            return;
        }
        
        // Verificar se a coluna existe
        $columns = $this->db->list_fields('lancamentos');
        if (!in_array('cliente_fornecedor', $columns)) {
            echo json_encode([]);
            return;
        }
        
        $this->db->select('DISTINCT(cliente_fornecedor) as cliente_fornecedor');
        $this->db->limit(5);
        $this->db->like('cliente_fornecedor', $q);
        $query = $this->db->get('lancamentos');
        
        if ($query === false) {
            echo json_encode([]);
            return;
        }
        
        if ($query->num_rows() > 0) {
            $row_set = [];
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label' => $row['cliente_fornecedor'], 'id' => $row['cliente_fornecedor']];
            }
            echo json_encode($row_set);
        } else {
            echo json_encode([]);
        }
    }

    public function autoCompleteClienteReceita($q)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('clientes')) {
            echo json_encode([]);
            return;
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('clientes');
        $id_col = in_array('idClientes', $columns) ? 'idClientes' : 
                  (in_array('id', $columns) ? 'id' : null);
        $nome_col = in_array('nomeCliente', $columns) ? 'nomeCliente' : 
                    (in_array('nome', $columns) ? 'nome' : null);
        
        if (!$id_col || !$nome_col) {
            echo json_encode([]);
            return;
        }
        
        $this->db->select($id_col . ', ' . $nome_col);
        $this->db->limit(5);
        $this->db->like($nome_col, $q);
        $query = $this->db->get('clientes');
        
        if ($query === false) {
            echo json_encode([]);
            return;
        }
        
        if ($query->num_rows() > 0) {
            $row_set = [];
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label' => $row[$nome_col], 'id' => $row[$id_col]];
            }
            echo json_encode($row_set);
        } else {
            echo json_encode([]);
        }
    }
}
