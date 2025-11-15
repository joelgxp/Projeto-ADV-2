<?php

class Processos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        $this->db->select($fields . ', processos.*');
        $this->db->from($table);

        // Join com clientes se a tabela existir
        if ($this->db->table_exists('clientes')) {
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
            $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : (in_array('nome', $clientes_columns) ? 'nome' : null);
            
            if ($clientes_id_col && $clientes_nome_col) {
                $this->db->select("clientes.{$clientes_nome_col} as nomeCliente");
                $this->db->join('clientes', "clientes.{$clientes_id_col} = processos.clientes_id", 'left');
            }
        }

        // Join com usuarios (advogado responsável)
        if ($this->db->table_exists('usuarios')) {
            $usuarios_columns = $this->db->list_fields('usuarios');
            $usuarios_id_col = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : (in_array('id', $usuarios_columns) ? 'id' : null);
            $usuarios_nome_col = in_array('nome', $usuarios_columns) ? 'nome' : null;
            
            if ($usuarios_id_col && $usuarios_nome_col) {
                $this->db->select("usuarios.{$usuarios_nome_col} as nomeAdvogado");
                $this->db->join('usuarios', "usuarios.{$usuarios_id_col} = processos.usuarios_id", 'left');
            }
        }

        $this->db->order_by('processos.dataCadastro', 'desc');
        $this->db->limit($perpage, $start);

        if ($where) {
            $this->db->group_start();
            $this->db->like('processos.numeroProcesso', $where);
            $this->db->or_like('processos.classe', $where);
            $this->db->or_like('processos.assunto', $where);
            $this->db->or_like('processos.comarca', $where);
            $this->db->or_like('processos.tribunal', $where);
            if ($this->db->table_exists('clientes')) {
                $clientes_columns = $this->db->list_fields('clientes');
                $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : (in_array('nome', $clientes_columns) ? 'nome' : null);
                if ($clientes_nome_col) {
                    $this->db->or_like("clientes.{$clientes_nome_col}", $where);
                }
            }
            $this->db->group_end();
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Processos_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        if (!$this->db->table_exists('processos')) {
            return false;
        }

        $this->db->where('idProcessos', $id);
        $this->db->limit(1);

        // Join com clientes
        if ($this->db->table_exists('clientes')) {
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
            $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : (in_array('nome', $clientes_columns) ? 'nome' : null);
            
            if ($clientes_id_col && $clientes_nome_col) {
                $this->db->select("clientes.*");
                $this->db->join('clientes', "clientes.{$clientes_id_col} = processos.clientes_id", 'left');
            }
        }

        // Join com usuarios
        if ($this->db->table_exists('usuarios')) {
            $usuarios_columns = $this->db->list_fields('usuarios');
            $usuarios_id_col = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : (in_array('id', $usuarios_columns) ? 'id' : null);
            
            if ($usuarios_id_col) {
                $this->db->select("usuarios.nome as nomeAdvogado, usuarios.email as emailAdvogado");
                $this->db->join('usuarios', "usuarios.{$usuarios_id_col} = processos.usuarios_id", 'left');
            }
        }

        $query = $this->db->get('processos');
        
        if ($query === false) {
            log_message('error', 'Erro na query Processos_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Processos_model::add");
            return false;
        }

        // Normalizar numeroProcesso (remover formatação)
        if (isset($data['numeroProcesso'])) {
            $data['numeroProcesso'] = $this->normalizarNumeroProcesso($data['numeroProcesso']);
        }

        // Extrair segmento e tribunal do número se não fornecidos
        if (isset($data['numeroProcesso']) && empty($data['segmento'])) {
            $info = $this->extrairInfoNumeroProcesso($data['numeroProcesso']);
            if ($info) {
                $data['segmento'] = $info['segmento'] ?? null;
                $data['tribunal'] = $info['tribunal'] ?? null;
            }
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Processos_model::edit");
            return false;
        }

        // Normalizar numeroProcesso se fornecido
        if (isset($data['numeroProcesso'])) {
            $data['numeroProcesso'] = $this->normalizarNumeroProcesso($data['numeroProcesso']);
            
            // Atualizar segmento e tribunal se número mudou
            $info = $this->extrairInfoNumeroProcesso($data['numeroProcesso']);
            if ($info) {
                $data['segmento'] = $info['segmento'] ?? $data['segmento'] ?? null;
                $data['tribunal'] = $info['tribunal'] ?? $data['tribunal'] ?? null;
            }
        }

        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Processos_model::delete");
            return false;
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        if (!$this->db->table_exists($table)) {
            return 0;
        }
        return $this->db->count_all($table);
    }

    /**
     * Normaliza o número de processo removendo formatação
     * Aceita: 0000123-45.2023.8.13.0139 ou 00001234520238130139
     * Retorna: 00001234520238130139 (limpo)
     */
    public function normalizarNumeroProcesso($numero)
    {
        // Remove todos os caracteres não numéricos
        return preg_replace('/[^0-9]/', '', $numero);
    }

    /**
     * Extrai informações do número de processo CNJ
     * Formato CNJ: NNNNNNN-DD.AAAA.J.TR.OOOO
     * Onde: N=sequencial, D=dígito, A=ano, J=segmento, TR=tribunal, O=órgão
     */
    public function extrairInfoNumeroProcesso($numero)
    {
        $numero_limpo = $this->normalizarNumeroProcesso($numero);
        
        // Número CNJ tem 20 dígitos
        if (strlen($numero_limpo) != 20) {
            return null;
        }

        $segmento = substr($numero_limpo, 13, 1); // Posição 14 (0-indexed: 13)
        $tribunal = substr($numero_limpo, 14, 2); // Posições 15-16

        $segmentos = [
            '1' => 'estadual',
            '2' => 'federal',
            '3' => 'trabalho',
            '4' => 'eleitoral',
            '5' => 'militar',
            '6' => 'justica_federal',
            '7' => 'justica_militar',
            '8' => 'justica_eleitoral',
        ];

        return [
            'segmento' => $segmentos[$segmento] ?? null,
            'tribunal' => $tribunal,
        ];
    }

    /**
     * Valida número de processo CNJ
     */
    public function validarNumeroProcesso($numero)
    {
        $numero_limpo = $this->normalizarNumeroProcesso($numero);
        
        // Número CNJ deve ter 20 dígitos
        if (strlen($numero_limpo) != 20) {
            return false;
        }

        // Validar dígito verificador (algoritmo CNJ)
        $sequencial = substr($numero_limpo, 0, 7);
        $digito = substr($numero_limpo, 7, 2);
        $ano = substr($numero_limpo, 9, 4);
        $segmento = substr($numero_limpo, 13, 1);
        $tribunal = substr($numero_limpo, 14, 2);
        $orgao = substr($numero_limpo, 16, 4);

        // Calcular dígito verificador
        $numero_calculo = $sequencial . $ano . $segmento . $tribunal . $orgao;
        $soma = 0;
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        
        for ($i = 0; $i < strlen($numero_calculo); $i++) {
            $soma += intval($numero_calculo[$i]) * $pesos[$i % 8];
        }
        
        $resto = $soma % 97;
        $digito_calculado = 98 - $resto;
        
        return intval($digito) == $digito_calculado;
    }

    /**
     * Formata número de processo para exibição
     * 00001234520238130139 -> 0000123-45.2023.8.13.0139
     */
    public function formatarNumeroProcesso($numero)
    {
        $numero_limpo = $this->normalizarNumeroProcesso($numero);
        
        if (strlen($numero_limpo) != 20) {
            return $numero; // Retorna como está se não tiver 20 dígitos
        }

        return substr($numero_limpo, 0, 7) . '-' . 
               substr($numero_limpo, 7, 2) . '.' . 
               substr($numero_limpo, 9, 4) . '.' . 
               substr($numero_limpo, 13, 1) . '.' . 
               substr($numero_limpo, 14, 2) . '.' . 
               substr($numero_limpo, 16, 4);
    }

    /**
     * Busca processos por cliente
     */
    public function getProcessosByCliente($cliente_id, $limit = 0, $offset = 0)
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de clientes
        if ($this->db->table_exists('clientes')) {
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
            
            if ($clientes_id_col) {
                $this->db->where('processos.clientes_id', $cliente_id);
                $this->db->order_by('processos.dataCadastro', 'desc');
                
                if ($limit > 0) {
                    $this->db->limit($limit, $offset);
                }
                
                $query = $this->db->get('processos');
                
                if ($query === false) {
                    log_message('error', 'Erro na query getProcessosByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
                    return [];
                }
                
                return $query->result();
            }
        }

        return [];
    }

    /**
     * Busca processos por cliente com filtros avançados
     *
     * @param int $cliente_id ID do cliente
     * @param array $filters Filtros: tipo_processo, status, comarca, usuarios_id (advogado)
     * @param int $perpage Limite de resultados por página
     * @param int $start Offset
     * @return array
     */
    public function getByClienteWithFilters($cliente_id, $filters = [], $perpage = 0, $start = 0)
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de clientes
        if (!$this->db->table_exists('clientes')) {
            return [];
        }

        $clientes_columns = $this->db->list_fields('clientes');
        $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
        
        if (!$clientes_id_col) {
            return [];
        }

        $this->db->select('processos.*');
        $this->db->from('processos');
        
        // Join com usuarios (advogado)
        if ($this->db->table_exists('usuarios')) {
            $usuarios_columns = $this->db->list_fields('usuarios');
            $usuarios_id_col = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : (in_array('id', $usuarios_columns) ? 'id' : null);
            $usuarios_nome_col = in_array('nome', $usuarios_columns) ? 'nome' : null;
            
            if ($usuarios_id_col && $usuarios_nome_col) {
                $this->db->select("usuarios.{$usuarios_nome_col} as nomeAdvogado");
                $this->db->join('usuarios', "usuarios.{$usuarios_id_col} = processos.usuarios_id", 'left');
            }
        }

        // Filtro por cliente
        $this->db->where('processos.clientes_id', $cliente_id);

        // Aplicar filtros
        if (isset($filters['tipo_processo']) && !empty($filters['tipo_processo'])) {
            $this->db->where('processos.tipo_processo', $filters['tipo_processo']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('processos.status', $filters['status']);
        }

        if (isset($filters['comarca']) && !empty($filters['comarca'])) {
            $this->db->like('processos.comarca', $filters['comarca']);
        }

        if (isset($filters['usuarios_id']) && !empty($filters['usuarios_id'])) {
            $this->db->where('processos.usuarios_id', $filters['usuarios_id']);
        }

        // Ordenação
        $this->db->order_by('processos.dataCadastro', 'desc');

        // Paginação
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getByClienteWithFilters: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Conta processos por cliente
     */
    public function countProcessosByCliente($cliente_id)
    {
        if (!$this->db->table_exists('processos')) {
            return 0;
        }

        // Detectar coluna de ID de clientes
        if ($this->db->table_exists('clientes')) {
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
            
            if ($clientes_id_col) {
                $this->db->where('processos.clientes_id', $cliente_id);
                $this->db->from('processos');
                return $this->db->count_all_results();
            }
        }

        return 0;
    }

    /**
     * Busca processos por advogado responsável
     */
    public function getProcessosByAdvogado($usuario_id)
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de usuarios
        if ($this->db->table_exists('usuarios')) {
            $usuarios_columns = $this->db->list_fields('usuarios');
            $usuarios_id_col = in_array('idUsuarios', $usuarios_columns) ? 'idUsuarios' : (in_array('id', $usuarios_columns) ? 'id' : null);
            
            if ($usuarios_id_col) {
                $this->db->where('processos.usuarios_id', $usuario_id);
                $this->db->order_by('processos.dataCadastro', 'desc');
                $query = $this->db->get('processos');
                
                if ($query === false) {
                    log_message('error', 'Erro na query getProcessosByAdvogado: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
                    return [];
                }
                
                return $query->result();
            }
        }

        return [];
    }

    /**
     * Busca processos por status
     */
    public function getProcessosByStatus($status)
    {
        if (!$this->db->table_exists('processos')) {
            return [];
        }

        $this->db->where('processos.status', $status);
        $this->db->order_by('processos.dataCadastro', 'desc');
        $query = $this->db->get('processos');
        
        if ($query === false) {
            log_message('error', 'Erro na query getProcessosByStatus: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }


    /**
     * Conta processos por cliente com filtros
     *
     * @param int $cliente_id ID do cliente
     * @param array $filters Filtros: tipo_processo, status, comarca, usuarios_id
     * @return int
     */
    public function countByClienteWithFilters($cliente_id, $filters = [])
    {
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return 0;
        }

        $clientes_columns = $this->db->list_fields('clientes');
        $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
        
        if (!$clientes_id_col) {
            return 0;
        }

        $this->db->where('processos.clientes_id', $cliente_id);

        // Aplicar filtros
        if (isset($filters['tipo_processo']) && !empty($filters['tipo_processo'])) {
            $this->db->where('processos.tipo_processo', $filters['tipo_processo']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('processos.status', $filters['status']);
        }

        if (isset($filters['comarca']) && !empty($filters['comarca'])) {
            $this->db->like('processos.comarca', $filters['comarca']);
        }

        if (isset($filters['usuarios_id']) && !empty($filters['usuarios_id'])) {
            $this->db->where('processos.usuarios_id', $filters['usuarios_id']);
        }

        $this->db->from('processos');
        return $this->db->count_all_results();
    }
}

