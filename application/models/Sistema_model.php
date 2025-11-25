<?php

/**
 * Sistema_model
 * 
 * Model principal do sistema jurídico
 * Contém métodos genéricos e específicos para o sistema de advocacia
 */
class Sistema_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca registros de uma tabela
     * 
     * @param string $table Nome da tabela
     * @param string $fields Campos a selecionar
     * @param string|array $where Condição WHERE (string simples ou array associativo)
     * @param int $perpage Limite de registros
     * @param int $start Offset
     * @param bool $one Retornar apenas um registro
     * @param string $array Tipo de retorno
     * @return mixed Resultado da query
     */
    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        // Validar nome da tabela para prevenir SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            log_message('error', 'Sistema_model::get: Nome de tabela inválido: ' . $table);
            return false;
        }

        $this->db->select($fields);
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        
        if ($where) {
            // Se $where for array, usar where() com array (mais seguro)
            if (is_array($where)) {
                $this->db->where($where);
            } else {
                // Se for string, validar que não contém SQL perigoso
                $where_clean = trim($where);
                $dangerous_keywords = ['DROP', 'DELETE', 'TRUNCATE', 'INSERT', 'UPDATE', 'ALTER', 'CREATE', 'EXEC', 'EXECUTE', '--', ';', '/*', '*/', 'UNION', 'SELECT'];
                $where_upper = strtoupper($where_clean);
                foreach ($dangerous_keywords as $keyword) {
                    if (strpos($where_upper, $keyword) !== false) {
                        log_message('error', 'Sistema_model::get: Tentativa de SQL injection detectada: ' . $where);
                        return false;
                    }
                }
                $this->db->where($where_clean);
            }
        }

        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Sistema_model::get: Erro na query: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Busca usuário por ID
     */
    public function getById($id)
    {
        $this->db->from('usuarios');
        $this->db->select('usuarios.*');
        
        // Join com permissoes
        if ($this->db->table_exists('permissoes')) {
            $this->db->select('permissoes.nome as permissao');
            $this->db->join('permissoes', 'permissoes.idPermissao = usuarios.permissoes_id', 'left');
        }
        
        $this->db->where('usuarios.idUsuarios', $id);
        $this->db->limit(1);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    /**
     * Altera senha do usuário logado
     */
    public function alterarSenha($senha)
    {
        $this->db->set('senha', password_hash($senha, PASSWORD_DEFAULT));
        $this->db->where('idUsuarios', $this->session->userdata('id_admin'));
        $this->db->update('usuarios');

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    /**
     * Pesquisa global no sistema
     */
    public function pesquisar($termo)
    {
        $data = [];
        
        // Buscando clientes
        if ($this->db->table_exists('clientes')) {
            $this->db->like('nomeCliente', $termo);
            $this->db->or_like('telefone', $termo);
            $this->db->or_like('celular', $termo);
            $this->db->or_like('documento', $termo);
            $this->db->limit(15);
            $query = $this->db->get('clientes');
            $data['clientes'] = ($query !== false) ? $query->result() : [];
        } else {
            $data['clientes'] = [];
        }

        // Buscando processos
        if ($this->db->table_exists('processos')) {
            $this->db->like('numeroProcesso', $termo);
            $this->db->or_like('classe', $termo);
            $this->db->or_like('assunto', $termo);
            $this->db->limit(15);
            $query = $this->db->get('processos');
            $data['processos'] = ($query !== false) ? $query->result() : [];
        } else {
            $data['processos'] = [];
        }

        // Buscando prazos
        if ($this->db->table_exists('prazos')) {
            $this->db->like('descricao', $termo);
            $this->db->or_like('tipo', $termo);
            $this->db->limit(15);
            $query = $this->db->get('prazos');
            $data['prazos'] = ($query !== false) ? $query->result() : [];
        } else {
            $data['prazos'] = [];
        }

        // Buscando audiências
        if ($this->db->table_exists('audiencias')) {
            $this->db->like('tipo', $termo);
            $this->db->or_like('local', $termo);
            $this->db->or_like('observacoes', $termo);
            $this->db->limit(15);
            $query = $this->db->get('audiencias');
            $data['audiencias'] = ($query !== false) ? $query->result() : [];
        } else {
            $data['audiencias'] = [];
        }

        // Buscando serviços jurídicos
        $tableName = $this->db->table_exists('servicos_juridicos') ? 'servicos_juridicos' : ($this->db->table_exists('servicos') ? 'servicos' : null);
        if ($tableName) {
            $this->db->like('nome', $termo);
            $this->db->or_like('descricao', $termo);
            $this->db->limit(15);
            $query = $this->db->get($tableName);
            $data['servicos'] = ($query !== false) ? $query->result() : [];
        } else {
            $data['servicos'] = [];
        }

        return $data;
    }

    /**
     * Métodos CRUD genéricos
     */
    public function add($table, $data)
    {
        $this->db->insert($table, $data);
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

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    /**
     * Métodos Financeiros
     */
    public function getLancamentos()
    {
        if (!$this->db->table_exists('lancamentos')) {
            log_message('error', 'Tabela lancamentos não existe');
            return [];
        }
        
        $this->db->select('idLancamentos, tipo, cliente_fornecedor, descricao, data_vencimento, forma_pgto, baixado');
        $this->db->from('lancamentos');
        $this->db->where('baixado', 0);
        $this->db->order_by('idLancamentos', 'DESC');
        
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query getLancamentos: ' . ($error['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    public function getEstatisticasFinanceiro()
    {
        $sql = "SELECT SUM(CASE WHEN baixado = 1 AND tipo = 'receita' THEN valor END) as total_receita,
                       SUM(CASE WHEN baixado = 1 AND tipo = 'despesa' THEN valor END) as total_despesa,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'receita' THEN valor END) as total_receita_pendente,
                       SUM(CASE WHEN baixado = 0 AND tipo = 'despesa' THEN valor END) as total_despesa_pendente FROM lancamentos";
        
        $query = $this->db->query($sql);
        if ($query === false) {
            log_message('error', 'Erro na query getEstatisticasFinanceiro: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    public function getEstatisticasFinanceiroMes($year)
    {
        $numbersOnly = preg_replace('/[^0-9]/', '', $year);

        if (! $numbersOnly) {
            $numbersOnly = date('Y');
        }

        $sql = "
            SELECT
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 1) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_JAN_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 1) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_JAN_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 2) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_FEV_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 2) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_FEV_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 3) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_MAR_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 3) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_MAR_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 4) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_ABR_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 4) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_ABR_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 5) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_MAI_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 5) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_MAI_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 6) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_JUN_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 6) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_JUN_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 7) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_JUL_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 7) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_JUL_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 8) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_AGO_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 8) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_AGO_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 9) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_SET_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 9) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_SET_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 10) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_OUT_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 10) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_OUT_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 11) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_NOV_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 11) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_NOV_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 12) AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_DEZ_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 12) AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_DEZ_DES
            FROM lancamentos
            WHERE EXTRACT(YEAR FROM data_pagamento) = ?
        ";
        
        $query = $this->db->query($sql, [intval($numbersOnly)]);
        if ($query === false) {
            log_message('error', 'Erro na query getEstatisticasFinanceiroMes: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    public function getEstatisticasFinanceiroDia($year)
    {
        $numbersOnly = preg_replace('/[^0-9]/', '', $year);
        if (! $numbersOnly) {
            $numbersOnly = date('Y');
        }
        $sql = '
            SELECT
                SUM(CASE WHEN (EXTRACT(DAY FROM data_pagamento) = ' . date('d') . ') AND EXTRACT(MONTH FROM data_pagamento) = ' . date('m') . " AND baixado = 1 AND tipo = 'receita' THEN valor END) AS VALOR_" . date('m') . '_REC,
                SUM(CASE WHEN (EXTRACT(DAY FROM data_pagamento) = ' . date('d') . ') AND EXTRACT(MONTH FROM data_pagamento) = ' . date('m') . " AND baixado = 1 AND tipo = 'despesa' THEN valor END) AS VALOR_" . date('m') . '_DES
            FROM lancamentos
            WHERE EXTRACT(YEAR FROM data_pagamento) = ?
        ';
        
        $query = $this->db->query($sql, [intval($numbersOnly)]);
        if ($query === false) {
            log_message('error', 'Erro na query getEstatisticasFinanceiroDia: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    public function getEstatisticasFinanceiroMesInadimplencia($year)
    {
        $numbersOnly = preg_replace('/[^0-9]/', '', $year);

        if (! $numbersOnly) {
            $numbersOnly = date('Y');
        }

        $sql = "
            SELECT
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 1) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_JAN_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 1) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_JAN_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 2) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_FEV_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 2) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_FEV_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 3) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_MAR_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 3) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_MAR_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 4) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_ABR_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 4) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_ABR_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 5) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_MAI_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 5) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_MAI_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 6) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_JUN_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 6) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_JUN_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 7) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_JUL_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 7) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_JUL_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 8) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_AGO_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 8) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_AGO_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 9) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_SET_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 9) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_SET_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 10) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_OUT_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 10) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_OUT_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 11) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_NOV_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 11) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_NOV_DES,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 12) AND baixado = 0 AND tipo = 'receita' THEN valor END) AS VALOR_DEZ_REC,
                SUM(CASE WHEN (EXTRACT(MONTH FROM data_pagamento) = 12) AND baixado = 0 AND tipo = 'despesa' THEN valor END) AS VALOR_DEZ_DES
            FROM lancamentos
            WHERE EXTRACT(YEAR FROM data_pagamento) = ?
        ";
        
        $query = $this->db->query($sql, [intval($numbersOnly)]);
        if ($query === false) {
            log_message('error', 'Erro na query getEstatisticasFinanceiroMesInadimplencia: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    /**
     * Métodos de Calendário
     */
    public function calendario($start, $end, $status = null)
    {
        try {
            if (!$this->db->table_exists('audiencias')) {
                log_message('error', 'Tabela audiencias não existe para calendario');
                return [];
            }
            
            $this->db->select('audiencias.*');
            
            // Join com processos
            if ($this->db->table_exists('processos')) {
                $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
                $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
                
                // Join com clientes através de processos
                if ($this->db->table_exists('clientes')) {
                    $this->db->select('clientes.nomeCliente as nomeCliente');
                    $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
                }
            }
            
            $this->db->from('audiencias');
            
            // Filtros de data
            if ($start) {
                // Remover timezone se presente (ex: 2025-10-26T00:00:00-03:00)
                $start_clean = preg_replace('/T.*$/', '', $start);
                $this->db->where('DATE(audiencias.dataHora) >=', $start_clean);
            }
            if ($end) {
                // Remover timezone se presente
                $end_clean = preg_replace('/T.*$/', '', $end);
                $this->db->where('DATE(audiencias.dataHora) <=', $end_clean);
            }
            
            $this->db->order_by('audiencias.dataHora', 'ASC');

            // Filtro de status
            if (!empty($status)) {
                $this->db->where('audiencias.status', $status);
            }

            $query = $this->db->get();
            if ($query === false) {
                $error = $this->db->error();
                log_message('error', 'Erro na query calendario: ' . ($error['message'] ?? 'Erro desconhecido'));
                log_message('error', 'Query SQL: ' . $this->db->last_query());
                return [];
            }
            
            $result = $query->result();
            log_message('debug', 'Calendario retornou ' . count($result) . ' audiências');
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exceção no método calendario: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [];
        } catch (Error $e) {
            log_message('error', 'Erro fatal no método calendario: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Métodos de Emitente
     */
    public function getEmitente()
    {
        $query = $this->db->get('emitente');
        if ($query === false) {
            log_message('error', 'Erro na query getEmitente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->row();
    }

    public function addEmitente($nome, $cnpj, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $email, $site, $logo)
    {
        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('cep', $cep);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('celular', $celular);
        $this->db->set('email', $email);
        $this->db->set('site', $site);
        $this->db->set('url_logo', $logo);

        return $this->db->insert('emitente');
    }

    public function editEmitente($id, $nome, $cnpj, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $email, $site)
    {
        $this->db->set('nome', $nome);
        $this->db->set('cnpj', $cnpj);
        $this->db->set('cep', $cep);
        $this->db->set('rua', $logradouro);
        $this->db->set('numero', $numero);
        $this->db->set('bairro', $bairro);
        $this->db->set('cidade', $cidade);
        $this->db->set('uf', $uf);
        $this->db->set('telefone', $telefone);
        $this->db->set('celular', $celular);
        $this->db->set('email', $email);
        $this->db->set('site', $site);
        $this->db->where('id', $id);

        return $this->db->update('emitente');
    }

    public function editLogo($id, $logo)
    {
        $this->db->set('url_logo', $logo);
        $this->db->where('id', $id);

        return $this->db->update('emitente');
    }

    public function editImageUser($id, $imageUserPath)
    {
        $this->db->set('url_image_user', $imageUserPath);
        $this->db->where('idUsuarios', $id);

        return $this->db->update('usuarios');
    }

    /**
     * Verifica credenciais de login
     */
    public function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->where('situacao', 1);
        $this->db->limit(1);

        $query = $this->db->get('usuarios');
        
        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Erro na query check_credentials: ' . ($error['message'] ?? 'Erro desconhecido'));
            return false;
        }
        
        return $query->row();
    }

    /**
     * Métodos de Processos Jurídicos
     */
    public function getProcessosByStatus($status)
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        $this->db->select('processos.*');
        
        // Join com clientes através de processos
        if ($this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('processos');
        $this->db->where('processos.status', $status);
        $this->db->order_by('processos.dataCadastro', 'DESC');
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getProcessosByStatus: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    /**
     * Métodos de Prazos
     */
    public function getPrazosVencidos()
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->select('prazos.*');
        
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        }
        
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('prazos');
        $this->db->where('prazos.status', 'pendente');
        $this->db->where('prazos.dataVencimento <', date('Y-m-d'));
        $this->db->order_by('prazos.dataVencimento', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosVencidos: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    public function getPrazosProximos()
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->select('prazos.*');
        
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        }
        
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('prazos');
        $this->db->where('prazos.status', 'pendente');
        $this->db->where('prazos.dataVencimento >=', date('Y-m-d'));
        $this->db->where('prazos.dataVencimento <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->order_by('prazos.dataVencimento', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosProximos: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    /**
     * Métodos de Audiências
     */
    public function getAudienciasHoje()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->select('audiencias.*');
        
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }
        
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('audiencias');
        $this->db->where('DATE(audiencias.dataHora)', date('Y-m-d'));
        $this->db->where('audiencias.status', 'agendada');
        $this->db->order_by('audiencias.dataHora', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasHoje: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    public function getAudienciasProximas()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->select('audiencias.*');
        
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }
        
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('audiencias');
        $this->db->where('DATE(audiencias.dataHora) >', date('Y-m-d'));
        $this->db->where('DATE(audiencias.dataHora) <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->where('audiencias.status', 'agendada');
        $this->db->order_by('audiencias.dataHora', 'ASC');
        $this->db->limit(10);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasProximas: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    public function getAudienciasAgendadas()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->select('audiencias.*');
        
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }
        
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }
        
        $this->db->from('audiencias');
        $this->db->where('audiencias.dataHora >=', date('Y-m-d H:i:s'));
        $this->db->where('audiencias.status', 'agendada');
        $this->db->order_by('audiencias.dataHora', 'ASC');
        $this->db->limit(5);

        $query = $this->db->get();
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasAgendadas: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        return $query->result();
    }

    /**
     * Estatísticas
     */
    public function getEstatisticasProcessos()
    {
        if (!$this->db->table_exists('processos')) {
            return false;
        }

        $sql = 'SELECT status, COUNT(status) as total FROM processos GROUP BY status ORDER BY status';

        $query = $this->db->query($sql);
        if ($query === false) {
            log_message('error', 'Erro na query getEstatisticasProcessos: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }
        return $query->result();
    }

    /**
     * Salvar configurações do sistema
     */
    public function saveConfiguracao($data)
    {
        try {
            foreach ($data as $key => $valor) {
                // Verificar se a configuração já existe
                $this->db->where('config', $key);
                $exists = $this->db->get('configuracoes')->row();
                
                if ($exists) {
                    // Se existe, atualizar
                    $this->db->set('valor', $valor);
                    $this->db->where('config', $key);
                    $result = $this->db->update('configuracoes');
                    
                    if ($this->db->error()['code'] != 0) {
                        log_message('error', 'Erro ao atualizar configuração ' . $key . ': ' . $this->db->error()['message']);
                        return false;
                    }
                } else {
                    // Se não existe, inserir
                    $result = $this->db->insert('configuracoes', [
                        'config' => $key,
                        'valor' => $valor
                    ]);
                    
                    if ($this->db->error()['code'] != 0) {
                        log_message('error', 'Erro ao inserir configuração ' . $key . ': ' . $this->db->error()['message']);
                        return false;
                    }
                }
                
                log_message('info', 'Configuração ' . $key . ' salva com valor: ' . $valor);
            }
        } catch (Exception $e) {
            log_message('error', 'Erro ao salvar configuração: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}

