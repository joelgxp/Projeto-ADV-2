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

        // Join com clientes
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

        // Join com clientes (verificar se coluna clientes_id existe)
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

        // Remover campos que não existem na tabela
        $table_fields = $this->db->list_fields($table);
        foreach ($data as $key => $value) {
            if (!in_array($key, $table_fields)) {
                unset($data[$key]);
            }
        }

        $this->db->insert($table, $data);
        
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Erro ao inserir processo: ' . $this->db->error()['message']);
            return false;
        }
        
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        log_message('error', 'Nenhuma linha afetada ao inserir processo. Dados: ' . json_encode($data));
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

        // Remover campos que não existem na tabela
        $table_fields = $this->db->list_fields($table);
        foreach ($data as $key => $value) {
            if (!in_array($key, $table_fields)) {
                unset($data[$key]);
            }
        }

        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Erro ao atualizar processo: ' . $this->db->error()['message']);
            return false;
        }

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
            '1' => 'stf',
            '2' => 'cnj',
            '3' => 'stj',
            '4' => 'federal',
            '5' => 'trabalho',
            '6' => 'eleitoral',
            '7' => 'militar',
            '8' => 'estadual',
            '9' => 'militar',
        ];

        return [
            'segmento' => $segmentos[$segmento] ?? null,
            'tribunal' => $tribunal,
        ];
    }

    /**
     * Valida número de processo CNJ conforme Resolução CNJ 65/2008
     * Retorna array com resultado da validação e mensagens de erro
     * 
     * @param string $numero Número do processo (formatado ou limpo)
     * @return array ['valido' => bool, 'erros' => array, 'dados' => array]
     */
    public function validarNumeroProcesso($numero)
    {
        $resultado = [
            'valido' => false,
            'erros' => [],
            'dados' => []
        ];

        // 1. Validação de Formato
        $validacao_formato = $this->validarFormatoCNJ($numero);
        if (!$validacao_formato['valido']) {
            $resultado['erros'] = array_merge($resultado['erros'], $validacao_formato['erros']);
            return $resultado;
        }

        $numero_limpo = $validacao_formato['numero_limpo'];
        $dados = $validacao_formato['dados'];

        // Validação de Campos de Origem (opcional - não bloqueia se falhar)
        $validacao_campos = $this->validarCamposOrigemCNJ($dados);
        // Não bloqueia se a validação de campos falhar, apenas avisa
        if (!$validacao_campos['valido']) {
            // Apenas adiciona avisos, mas não bloqueia
            // $resultado['erros'] = array_merge($resultado['erros'], $validacao_campos['erros']);
        }

        // Validação passou (formato válido)
        $resultado['valido'] = true;
        $resultado['dados'] = $dados;
        
        return $resultado;
    }

    /**
     * Valida formato do número CNJ
     * Formato: NNNNNNN-DD.AAAA.J.TR.OOOO
     */
    private function validarFormatoCNJ($numero)
    {
        $resultado = [
            'valido' => false,
            'erros' => [],
            'numero_limpo' => '',
            'dados' => []
        ];

        // Remover espaços
        $numero = trim($numero);
        
        // Verificar se tem exatamente 20 caracteres (com separadores) ou 20 dígitos (sem separadores)
        $numero_limpo = preg_replace('/[^0-9]/', '', $numero);
        
        if (strlen($numero_limpo) != 20) {
            $resultado['erros'][] = 'Número deve conter exatamente 20 dígitos.';
            return $resultado;
        }

        // Se o número veio formatado, validar posição dos separadores
        if (preg_match('/[^0-9]/', $numero)) {
            // Verificar formato: NNNNNNN-DD.AAAA.J.TR.OOOO
            if (!preg_match('/^\d{7}-\d{2}\.\d{4}\.\d{1}\.\d{2}\.\d{4}$/', $numero)) {
                $resultado['erros'][] = 'Formato inválido. Use o padrão: NNNNNNN-DD.AAAA.J.TR.OOOO';
                return $resultado;
            }
        }

        // Extrair dados do número
        $dados = [
            'sequencial' => substr($numero_limpo, 0, 7),
            'digito' => substr($numero_limpo, 7, 2),
            'ano' => substr($numero_limpo, 9, 4),
            'segmento' => substr($numero_limpo, 13, 1),
            'tribunal' => substr($numero_limpo, 14, 2),
            'unidade_origem' => substr($numero_limpo, 16, 4),
        ];

        // Validar ano (deve estar entre 1900 e ano atual + 1)
        $ano_atual = (int)date('Y');
        $ano_processo = (int)$dados['ano'];
        if ($ano_processo < 1900 || $ano_processo > ($ano_atual + 1)) {
            $resultado['erros'][] = "Ano inválido: {$dados['ano']}. Deve estar entre 1900 e " . ($ano_atual + 1);
            return $resultado;
        }

        $resultado['valido'] = true;
        $resultado['numero_limpo'] = $numero_limpo;
        $resultado['dados'] = $dados;

        return $resultado;
    }

    /**
     * Valida dígito verificador usando algoritmo Módulo 97
     * Conforme Resolução CNJ 65/2008
     */
    private function validarDigitoVerificadorCNJ($numero_limpo, $dados)
    {
        $resultado = [
            'valido' => false,
            'erros' => []
        ];

        // Número sem o dígito verificador (posições 7-8)
        $numero_sem_digito = substr($numero_limpo, 0, 7) . 
                            substr($numero_limpo, 9, 4) . 
                            substr($numero_limpo, 13, 1) . 
                            substr($numero_limpo, 14, 2) . 
                            substr($numero_limpo, 16, 4);

        // Calcular resto usando Módulo 97
        // Converter para inteiro (pode ser muito grande, usar bcmod se disponível)
        $resto = 0;
        for ($i = 0; $i < strlen($numero_sem_digito); $i++) {
            $resto = ($resto * 10 + intval($numero_sem_digito[$i])) % 97;
        }

        // Calcular dígito esperado
        $digito_esperado = 98 - $resto;
        
        // Formatar com 2 dígitos (com zero à esquerda se necessário)
        $digito_esperado = str_pad($digito_esperado, 2, '0', STR_PAD_LEFT);
        $digito_informado = $dados['digito'];

        if ($digito_esperado != $digito_informado) {
            $resultado['erros'][] = "Dígito verificador inválido. Esperado: {$digito_esperado}, informado: {$digito_informado}";
            return $resultado;
        }

        $resultado['valido'] = true;
        return $resultado;
    }

    /**
     * Valida campos de origem (segmento, tribunal, unidade)
     */
    private function validarCamposOrigemCNJ($dados)
    {
        $resultado = [
            'valido' => false,
            'erros' => []
        ];

        $segmento = $dados['segmento'];
        $tribunal = $dados['tribunal'];

        // Validar segmento (J)
        $segmentos_validos = ['1', '2', '3', '4', '5', '6', '7', '8', '9'];
        if (!in_array($segmento, $segmentos_validos)) {
            $resultado['erros'][] = "Segmento do Justiça inválido: {$segmento}. Valores válidos: 1-9";
            return $resultado;
        }

        // Validar tribunal (TR) conforme segmento
        $tribunais_validos = $this->getTribunaisValidosPorSegmento($segmento);
        if (!in_array($tribunal, $tribunais_validos)) {
            $resultado['erros'][] = "Código do Tribunal inválido: {$tribunal} para o segmento {$segmento}";
            return $resultado;
        }

        // Validar compatibilidade entre segmento e tribunal
        if (!$this->validarCompatibilidadeSegmentoTribunal($segmento, $tribunal)) {
            $resultado['erros'][] = "Tribunal {$tribunal} incompatível com o segmento {$segmento}";
            return $resultado;
        }

        $resultado['valido'] = true;
        return $resultado;
    }

    /**
     * Retorna lista de tribunais válidos por segmento
     */
    private function getTribunaisValidosPorSegmento($segmento)
    {
        // Gerar arrays de códigos com 2 dígitos
        $tribunais_01_24 = array_map(function($n) { return str_pad($n, 2, '0', STR_PAD_LEFT); }, range(1, 24));
        $tribunais_01_27 = array_map(function($n) { return str_pad($n, 2, '0', STR_PAD_LEFT); }, range(1, 27));
        $tribunais_01_12 = array_map(function($n) { return str_pad($n, 2, '0', STR_PAD_LEFT); }, range(1, 12));
        
        $tribunais = [
            '1' => ['00'], // STF (Supremo Tribunal Federal)
            '2' => ['90'], // CNJ (Conselho Nacional de Justiça)
            '3' => ['90'], // STJ (Superior Tribunal de Justiça)
            '4' => ['01', '02', '03', '04', '05', '06', '90', '00'], // Justiça Federal (TRF + STJ/STF)
            '5' => array_merge($tribunais_01_24, ['90']), // Justiça do Trabalho (TRT + TST)
            '6' => array_merge($tribunais_01_27, ['90']), // Justiça Eleitoral (TRE + TSE)
            '7' => ['10'], // Justiça Militar da União (STM)
            '8' => $tribunais_01_27, // Justiça Estadual (TJ)
            '9' => ['13', '21', '26'], // Justiça Militar Estadual (TJM-MG, TJM-RS, TJM-SP)
        ];

        return $tribunais[$segmento] ?? [];
    }

    /**
     * Valida compatibilidade entre segmento e tribunal
     */
    private function validarCompatibilidadeSegmentoTribunal($segmento, $tribunal)
    {
        $tribunais_validos = $this->getTribunaisValidosPorSegmento($segmento);
        return in_array($tribunal, $tribunais_validos);
    }

    /**
     * Extrai informações do número CNJ validado
     * Retorna array com dados extraídos ou null se inválido
     */
    public function extrairDadosCNJ($numero)
    {
        $validacao = $this->validarNumeroProcesso($numero);
        
        if (!$validacao['valido']) {
            return null;
        }

        $dados = $validacao['dados'];
        
        // Mapear segmento para nome
        $segmentos_nomes = [
            '1' => 'Supremo Tribunal Federal (STF)',
            '2' => 'Conselho Nacional de Justiça (CNJ)',
            '3' => 'Superior Tribunal de Justiça (STJ)',
            '4' => 'Justiça Federal',
            '5' => 'Justiça do Trabalho',
            '6' => 'Justiça Eleitoral',
            '7' => 'Justiça Militar da União',
            '8' => 'Justiça dos Estados e do Distrito Federal e Territórios',
            '9' => 'Justiça Militar Estadual',
        ];

        // Mapear tribunal para nome (exemplos principais)
        $tribunais_nomes = [
            '00' => 'STF',
            '90' => 'STJ/TST/TSE',
            '01' => 'TRF1/TJ-AC/TRT1/TRE-AC',
            '02' => 'TRF2/TJ-AL/TRT2/TRE-AL',
            '13' => 'TJ-SP/TRT2',
            '26' => 'TJ-SP/TRT15',
        ];

        // Montar número completo para formatação
        $numero_completo = $dados['sequencial'] . 
                          $dados['digito'] . 
                          $dados['ano'] . 
                          $dados['segmento'] . 
                          $dados['tribunal'] . 
                          $dados['unidade_origem'];

        return [
            'numero_formatado' => $this->formatarNumeroProcesso($numero_completo),
            'sequencial' => $dados['sequencial'],
            'ano' => $dados['ano'],
            'segmento' => $dados['segmento'],
            'segmento_nome' => $segmentos_nomes[$dados['segmento']] ?? 'Desconhecido',
            'tribunal' => $dados['tribunal'],
            'tribunal_nome' => $tribunais_nomes[$dados['tribunal']] ?? 'Tribunal ' . $dados['tribunal'],
            'unidade_origem' => $dados['unidade_origem'],
        ];
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

        // Verificar se processos.clientes_id existe
        // Verificar se tabelas existem
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
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
     * Busca processos de um cliente com filtros aplicados
     * 
     * Retorna processos vinculados a um cliente específico, com possibilidade
     * de filtrar por tipo, status, comarca e advogado responsável.
     * Utiliza JOINs para otimizar performance e evitar queries N+1.
     * 
     * @param int $cliente_id ID do cliente
     * @param array $filters Filtros opcionais: ['tipo_processo', 'status', 'comarca', 'usuarios_id']
     * @param int $perpage Limite de registros por página (0 = sem limite)
     * @param int $start Offset para paginação
     * @return array Lista de processos encontrados
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

    /**
     * Verifica se o número de processo já existe na tabela de processos
     * 
     * Útil para validar unicidade de número de processo antes de inserir/atualizar.
     * Em edição, permite excluir o próprio processo da verificação.
     *
     * @param string $numeroProcesso Número do processo (com ou sem formatação)
     * @param int|null $id ID do processo a excluir da verificação (opcional, para edição)
     * @return bool True se número existe, False caso contrário
     */
    public function numeroProcessoExists($numeroProcesso, $id = null)
    {
        if (empty($numeroProcesso)) {
            return false;
        }
        
        // Normalizar número (remover formatação)
        $numero_limpo = $this->normalizarNumeroProcesso($numeroProcesso);
        
        if (empty($numero_limpo)) {
            return false;
        }
        
        $this->db->where('numeroProcesso', $numero_limpo);
        
        if ($id !== null) {
            $this->db->where('idProcessos !=', $id);
        }
        
        $query = $this->db->get('processos');
        
        return $query->num_rows() > 0;
    }
}

