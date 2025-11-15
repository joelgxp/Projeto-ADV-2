<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Garantias_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists($table)) {
            log_message('error', 'Tabela ' . $table . ' não existe em Garantias_model::get');
            return $one ? null : [];
        }
        
        // Detectar estrutura das tabelas
        $garantias_columns = $this->db->list_fields('garantias');
        $usuarios_columns = $this->db->table_exists('usuarios') ? $this->db->list_fields('usuarios') : [];
        
        // Detectar colunas
        $garantias_id_col = in_array('idGarantias', $garantias_columns) ? 'idGarantias' : 
                           (in_array('id', $garantias_columns) ? 'id' : null);
        $garantias_usuarios_id_col = in_array('usuarios_id', $garantias_columns) ? 'usuarios_id' : null;
        $usuarios_pk = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : 
                      (in_array('id', $usuarios_columns) ? 'id' : null);
        $usuarios_nome_col = in_array('nome', $usuarios_columns) ? 'nome' : null;
        
        $this->db->select($fields);
        
        // JOIN com usuarios apenas se as tabelas e colunas existirem
        if ($this->db->table_exists('usuarios') && $garantias_usuarios_id_col && $usuarios_pk && $usuarios_nome_col) {
            $this->db->select('usuarios.' . $usuarios_nome_col . ' as nome, usuarios.' . $usuarios_pk . ' as idUsuarios');
            $this->db->join('usuarios', 'usuarios.' . $usuarios_pk . ' = garantias.' . $garantias_usuarios_id_col);
        }
        
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        
        // Ordenar por ID se a coluna existir
        if ($garantias_id_col) {
            $this->db->order_by($garantias_id_col, 'asc');
        }
        
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Garantias_model::get: ' . ($error['message'] ?? 'Erro desconhecido'));
            return $one ? null : [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('garantias')) {
            log_message('error', 'Tabela garantias não existe em Garantias_model::getById');
            return null;
        }
        
        // Detectar estrutura das tabelas
        $garantias_columns = $this->db->list_fields('garantias');
        $usuarios_columns = $this->db->table_exists('usuarios') ? $this->db->list_fields('usuarios') : [];
        
        // Detectar colunas
        $garantias_id_col = in_array('idGarantias', $garantias_columns) ? 'idGarantias' : 
                           (in_array('id', $garantias_columns) ? 'id' : null);
        $garantias_usuarios_id_col = in_array('usuarios_id', $garantias_columns) ? 'usuarios_id' : null;
        $usuarios_pk = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : 
                      (in_array('id', $usuarios_columns) ? 'id' : null);
        
        if (!$garantias_id_col) {
            log_message('error', 'Coluna de ID não encontrada na tabela garantias');
            return null;
        }
        
        $this->db->select('garantias.*');
        
        // JOIN com usuarios apenas se as tabelas e colunas existirem
        if ($this->db->table_exists('usuarios') && $garantias_usuarios_id_col && $usuarios_pk) {
            $select_fields = [];
            if (in_array('telefone', $usuarios_columns)) {
                $select_fields[] = 'usuarios.telefone';
            }
            if (in_array('email', $usuarios_columns)) {
                $select_fields[] = 'usuarios.email';
            } elseif (in_array('usuario', $usuarios_columns)) {
                $select_fields[] = 'usuarios.usuario as email';
            }
            if (in_array('nome', $usuarios_columns)) {
                $select_fields[] = 'usuarios.nome';
            }
            
            if (!empty($select_fields)) {
                $this->db->select(implode(', ', $select_fields));
                $this->db->join('usuarios', 'usuarios.' . $usuarios_pk . ' = garantias.' . $garantias_usuarios_id_col);
            }
        }
        
        $this->db->from('garantias');
        $this->db->where('garantias.' . $garantias_id_col, $id);
        $this->db->limit(1);

        $query = $this->db->get();
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Garantias_model::getById: ' . ($error['message'] ?? 'Erro desconhecido'));
            return null;
        }

        return $query->row();
    }

    public function getByIdOsGarantia($id)
    {
        // Verificar se as tabelas existem
        if (!$this->db->table_exists('garantias')) {
            log_message('error', 'Tabela garantias não existe em Garantias_model::getByIdOsGarantia');
            return null;
        }
        
        // Detectar estrutura das tabelas
        $garantias_columns = $this->db->list_fields('garantias');
        $os_columns = $this->db->table_exists('os') ? $this->db->list_fields('os') : [];
        $clientes_columns = $this->db->table_exists('clientes') ? $this->db->list_fields('clientes') : [];
        $usuarios_columns = $this->db->table_exists('usuarios') ? $this->db->list_fields('usuarios') : [];
        
        // Detectar colunas
        $garantias_id_col = in_array('idGarantias', $garantias_columns) ? 'idGarantias' : 
                           (in_array('id', $garantias_columns) ? 'id' : null);
        $os_garantias_id_col = in_array('garantias_id', $os_columns) ? 'garantias_id' : null;
        $os_id_col = in_array('idOs', $os_columns) ? 'idOs' : 
                    (in_array('idOS', $os_columns) ? 'idOS' : (in_array('id', $os_columns) ? 'id' : null));
        $os_data_final_col = in_array('dataFinal', $os_columns) ? 'dataFinal' : 
                            (in_array('data_final', $os_columns) ? 'data_final' : null);
        $os_clientes_id_col = in_array('clientes_id', $os_columns) ? 'clientes_id' : null;
        $os_usuarios_id_col = in_array('usuarios_id', $os_columns) ? 'usuarios_id' : null;
        $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : 
                          (in_array('id', $clientes_columns) ? 'id' : null);
        $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : 
                            (in_array('nome', $clientes_columns) ? 'nome' : null);
        $usuarios_pk = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : 
                      (in_array('id', $usuarios_columns) ? 'id' : null);
        
        if (!$garantias_id_col) {
            log_message('error', 'Coluna de ID não encontrada na tabela garantias');
            return null;
        }
        
        $this->db->select('garantias.*');
        
        // JOIN com os
        if ($this->db->table_exists('os') && $os_garantias_id_col) {
            $os_select = [];
            if ($os_id_col) {
                $os_select[] = 'os.' . $os_id_col . ' as idOs';
            }
            if ($os_data_final_col) {
                $os_select[] = 'os.' . $os_data_final_col . ' as osDataFinal';
            }
            if (!empty($os_select)) {
                $this->db->select(implode(', ', $os_select));
            }
            $this->db->join('os', 'os.' . $os_garantias_id_col . ' = garantias.' . $garantias_id_col);
        }
        
        // JOIN com clientes
        if ($this->db->table_exists('clientes') && $os_clientes_id_col && $clientes_id_col && $clientes_nome_col) {
            $this->db->select('clientes.' . $clientes_nome_col . ' as nomeCliente');
            $this->db->join('clientes', 'clientes.' . $clientes_id_col . ' = os.' . $os_clientes_id_col);
        }
        
        // JOIN com usuarios
        if ($this->db->table_exists('usuarios') && $os_usuarios_id_col && $usuarios_pk) {
            $usuarios_select = [];
            if (in_array('telefone', $usuarios_columns)) {
                $usuarios_select[] = 'usuarios.telefone as tecnicoTelefone';
            }
            if (in_array('email', $usuarios_columns)) {
                $usuarios_select[] = 'usuarios.email as tecnicoEmail';
            } elseif (in_array('usuario', $usuarios_columns)) {
                $usuarios_select[] = 'usuarios.usuario as tecnicoEmail';
            }
            if (in_array('nome', $usuarios_columns)) {
                $usuarios_select[] = 'usuarios.nome as tecnicoName';
            }
            if (!empty($usuarios_select)) {
                $this->db->select(implode(', ', $usuarios_select));
                $this->db->join('usuarios', 'usuarios.' . $usuarios_pk . ' = os.' . $os_usuarios_id_col);
            }
        }
        
        $this->db->from('garantias');
        $this->db->where('garantias.' . $garantias_id_col, $id);
        $this->db->limit(1);

        $query = $this->db->get();
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query Garantias_model::getByIdOsGarantia: ' . ($error['message'] ?? 'Erro desconhecido'));
            return null;
        }

        return $query->row();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }

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

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function autoCompleteProduto($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('descricao', $q);
        $query = $this->db->get('produtos');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label' => $row['descricao'] . ' | Preço: R$ ' . $row['precoVenda'] . ' | Estoque: ' . $row['estoque'], 'estoque' => $row['estoque'], 'id' => $row['idProdutos'], 'preco' => $row['precoVenda']];
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCliente($q)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('clientes')) {
            echo json_encode([]);
            return;
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('clientes');
        $nome_col = in_array('nomeCliente', $columns) ? 'nomeCliente' : 
                   (in_array('nome', $columns) ? 'nome' : null);
        $telefone_col = in_array('telefone', $columns) ? 'telefone' : null;
        $id_col = in_array('idClientes', $columns) ? 'idClientes' : 
                 (in_array('id', $columns) ? 'id' : null);
        
        if (!$nome_col || !$id_col) {
            echo json_encode([]);
            return;
        }
        
        $select_fields = [$id_col, $nome_col];
        if ($telefone_col) {
            $select_fields[] = $telefone_col;
        }
        
        $this->db->select(implode(', ', $select_fields));
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
                $label = $row[$nome_col];
                if ($telefone_col && isset($row[$telefone_col])) {
                    $label .= ' | Telefone: ' . $row[$telefone_col];
                }
                $row_set[] = ['label' => $label, 'id' => $row[$id_col]];
            }
            echo json_encode($row_set);
        } else {
            echo json_encode([]);
        }
    }

    public function autoCompleteUsuario($q)
    {
        // Verificar se a tabela existe
        if (!$this->db->table_exists('usuarios')) {
            echo json_encode([]);
            return;
        }
        
        // Detectar estrutura da tabela
        $columns = $this->db->list_fields('usuarios');
        $nome_col = in_array('nome', $columns) ? 'nome' : null;
        $telefone_col = in_array('telefone', $columns) ? 'telefone' : null;
        $id_col = in_array('idUsuarios', $columns) ? 'idUsuarios' : 
                 (in_array('id', $columns) ? 'id' : null);
        $situacao_col = in_array('situacao', $columns) ? 'situacao' : 
                       (in_array('ativo', $columns) ? 'ativo' : null);
        
        if (!$nome_col || !$id_col) {
            echo json_encode([]);
            return;
        }
        
        $select_fields = [$id_col, $nome_col];
        if ($telefone_col) {
            $select_fields[] = $telefone_col;
        }
        
        $this->db->select(implode(', ', $select_fields));
        $this->db->limit(5);
        $this->db->like($nome_col, $q);
        
        // Filtrar por situação se a coluna existir
        if ($situacao_col) {
            $this->db->where($situacao_col, 1);
        }
        
        $query = $this->db->get('usuarios');
        
        if ($query === false) {
            echo json_encode([]);
            return;
        }
        
        if ($query->num_rows() > 0) {
            $row_set = [];
            foreach ($query->result_array() as $row) {
                $label = $row[$nome_col];
                if ($telefone_col && isset($row[$telefone_col])) {
                    $label .= ' | Telefone: ' . $row[$telefone_col];
                }
                $row_set[] = ['label' => $label, 'id' => $row[$id_col]];
            }
            echo json_encode($row_set);
        } else {
            echo json_encode([]);
        }
    }
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
