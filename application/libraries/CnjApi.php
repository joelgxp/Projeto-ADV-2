<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Library para integração com API CNJ/DataJud
 */
class CnjApi
{
    private $apiKey;
    private $baseUrl = 'https://api-publica.datajud.cnj.jus.br';
    private $timeout = 30;
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        // API Key pública do DataJud (pode ser alterada pelo CNJ)
        // Formato: Authorization: APIKey [Chave Pública]
        // O valor completo já inclui "APIKey " no início
        $this->apiKey = $_ENV['API_CNJ_KEY'] ?? 'APIKey cDZHYzlZa0JadVREZDJCendQbXY6SkJlTzNjLV9TRENyQk1RdnFKZGRQdw==';
        
        // Carrega helpers
        $this->CI->load->helper('tribunais_endpoints');
    }

    /**
     * Consulta processo na API CNJ/DataJud
     * 
     * @param string $numeroProcesso Número do processo (formatado ou limpo)
     * @param string|null $tribunal Código do tribunal (opcional, será detectado automaticamente)
     * @param int $size Quantidade de resultados (padrão: 1)
     * @param array|null $searchAfter Array com valores de sort para paginação
     * @return array|false Dados do processo ou false em caso de erro
     */
    public function consultarProcesso($numeroProcesso, $tribunal = null, $size = 1, $searchAfter = null)
    {
        // Normaliza número - remove TODOS os caracteres não numéricos
        $numeroLimpo = preg_replace('/[^0-9]/', '', $numeroProcesso);
        
        if (strlen($numeroLimpo) != 20) {
            log_message('error', 'CnjApi: Número de processo inválido (tamanho incorreto): ' . $numeroProcesso . ' (limpo: ' . $numeroLimpo . ', tamanho: ' . strlen($numeroLimpo) . ')');
            return false;
        }
        
        // Valida dígito verificador (mas não bloqueia se falhar - a API pode aceitar mesmo assim)
        $digitoValido = validar_digito_verificador_cnj($numeroLimpo);
        if (!$digitoValido) {
            log_message('info', 'CnjApi: Dígito verificador pode estar inválido: ' . $numeroProcesso . ' (continuando mesmo assim)');
        }
        
        // Detecta tribunal se não informado
        $dadosTribunal = detectar_tribunal_cnj($numeroLimpo);
        if (!$dadosTribunal) {
            log_message('error', 'CnjApi: Não foi possível detectar tribunal: ' . $numeroProcesso);
            return false;
        }
        
        $segmento = $dadosTribunal['segmento'];
        $tribunalCodigo = $tribunal ?: $dadosTribunal['tribunal'];
        
        // Obtém endpoint
        $endpoint = obter_endpoint_tribunal($segmento, $tribunalCodigo);
        if (!$endpoint) {
            log_message('error', 'CnjApi: Endpoint não encontrado para segmento ' . $segmento . ' e tribunal ' . $tribunalCodigo);
            return false;
        }
        
        // Faz requisição
        $url = $endpoint;
        $endpointInfo = [
            'url' => $url,
            'segmento' => $segmento,
            'tribunal' => $tribunalCodigo,
            'numero_limpo' => $numeroLimpo
        ];
        
        // Valida e ajusta size (mínimo 1, máximo 10000)
        $size = max(1, min(10000, intval($size)));
        
        // Formato básico conforme documentação da API CNJ
        $queryBase = [
            'query' => [
                'match' => [
                    'numeroProcesso' => $numeroLimpo
                ]
            ]
        ];
        
        // Adiciona paginação apenas se necessário
        if ($size > 1 || $searchAfter !== null) {
            $queryBase['size'] = $size;
            
            // Adiciona sort para paginação (search_after)
            if ($searchAfter !== null) {
                $queryBase['sort'] = [
                    [
                        '@timestamp' => [
                            'order' => 'asc'
                        ]
                    ]
                ];
                if (is_array($searchAfter) && !empty($searchAfter)) {
                    $queryBase['search_after'] = $searchAfter;
                }
            }
        }
        
        // Tenta diferentes formatos de query
        $queries = [
            // Formato 1: match simples (formato padrão da documentação)
            $queryBase,
            // Formato 2: term exato (busca exata)
            [
                'query' => [
                    'term' => [
                        'numeroProcesso' => $numeroLimpo
                    ]
                ]
            ],
            // Formato 3: match_phrase
            [
                'query' => [
                    'match_phrase' => [
                        'numeroProcesso' => $numeroLimpo
                    ]
                ]
            ]
        ];
        
        $response = false;
        $ultimoErro = null;
        $queryUsada = null;
        $responseCompleta = null;
        
        // Tenta cada formato até encontrar um que funcione
        foreach ($queries as $index => $query) {
            // Garante que o número na query está limpo (sem formatação)
            if (isset($query['query']['match']['numeroProcesso'])) {
                $query['query']['match']['numeroProcesso'] = preg_replace('/[^0-9]/', '', $query['query']['match']['numeroProcesso']);
            }
            if (isset($query['query']['term']['numeroProcesso'])) {
                $query['query']['term']['numeroProcesso'] = preg_replace('/[^0-9]/', '', $query['query']['term']['numeroProcesso']);
            }
            if (isset($query['query']['match_phrase']['numeroProcesso'])) {
                $query['query']['match_phrase']['numeroProcesso'] = preg_replace('/[^0-9]/', '', $query['query']['match_phrase']['numeroProcesso']);
            }
            if (isset($query['query']['query_string']['query'])) {
                $query['query']['query_string']['query'] = preg_replace('/[^0-9]/', '', $query['query']['query_string']['query']);
            }
            
            $queryJson = json_encode($query, JSON_PRETTY_PRINT);
            
            // Logs de debug apenas em desenvolvimento
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'CnjApi: Tentativa ' . ($index + 1) . ' - Consultando processo ' . $numeroLimpo . ' (limpo) no endpoint: ' . $url);
                log_message('debug', 'CnjApi: Query JSON: ' . $queryJson);
                
                // Salva query no arquivo de debug
                $debugLogFile = APPPATH . 'logs/cnj_api_debug_' . date('Y-m-d') . '.log';
                $debugContent = "=== CNJ API Query ===\n";
                $debugContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
                $debugContent .= "Tentativa: " . ($index + 1) . "\n";
                $debugContent .= "URL: " . $url . "\n";
                $debugContent .= "Query:\n" . $queryJson . "\n";
                $debugContent .= "=== End Query ===\n\n";
                file_put_contents($debugLogFile, $debugContent, FILE_APPEND);
            }
            
            $response = $this->fazerRequisicao($url, $query);
            
            if ($response !== false) {
                // Verifica se encontrou resultados
                if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', 'CnjApi: Processo encontrado com formato de query ' . ($index + 1));
                    }
                    // Armazena query e resposta para debug
                    $queryUsada = $query;
                    $responseCompleta = $response;
                    break;
                } else {
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', 'CnjApi: Processo não encontrado com formato de query ' . ($index + 1));
                    }
                    // Continua tentando outros formatos
                }
            } else {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'CnjApi: Erro na requisição com formato de query ' . ($index + 1));
                }
                // Continua tentando outros formatos
            }
        }
        
        if ($response === false) {
            log_message('error', 'CnjApi: Todas as tentativas de consulta falharam para: ' . $numeroLimpo);
            return false;
        }
        
        // Log detalhado da resposta antes de verificar
        log_message('info', 'CnjApi: Verificando resposta da API para: ' . $numeroLimpo);
        log_message('info', 'CnjApi: - Tem "hits"? ' . (isset($response['hits']) ? 'SIM' : 'NÃO'));
        if (isset($response['hits'])) {
            log_message('info', 'CnjApi: - Tem "hits.hits"? ' . (isset($response['hits']['hits']) ? 'SIM' : 'NÃO'));
            if (isset($response['hits']['hits'])) {
                log_message('info', 'CnjApi: - Quantidade de hits: ' . count($response['hits']['hits']));
                log_message('info', 'CnjApi: - hits está vazio? ' . (empty($response['hits']['hits']) ? 'SIM' : 'NÃO'));
            }
            if (isset($response['hits']['total'])) {
                $total = is_array($response['hits']['total']) 
                    ? ($response['hits']['total']['value'] ?? 0)
                    : $response['hits']['total'];
                log_message('info', 'CnjApi: - Total de resultados: ' . $total);
            }
        }
        
        // Verifica se encontrou resultados
        if (!isset($response['hits']['hits']) || empty($response['hits']['hits'])) {
            log_message('info', 'CnjApi: Processo não encontrado na API: ' . $numeroLimpo);
            
            // Verifica se há informações sobre o total de resultados
            $total = 0;
            if (isset($response['hits']['total'])) {
                $total = is_array($response['hits']['total']) 
                    ? ($response['hits']['total']['value'] ?? 0)
                    : $response['hits']['total'];
            }
            
            return [
                'numero' => null,
                'numero_limpo' => $numeroLimpo,
                'total_resultados' => $total,
                'endpoint_info' => $endpointInfo,
                'response_structure' => [
                    'has_hits' => isset($response['hits']),
                    'has_hits_hits' => isset($response['hits']['hits']),
                    'hits_count' => isset($response['hits']['hits']) ? count($response['hits']['hits']) : 0,
                    'total' => $total
                ]
            ];
        }
        
        // Processa resposta
        $resultado = $this->processarResposta($response, $dadosTribunal, $size);
        
        // Adiciona informações do endpoint ao resultado
        if (is_array($resultado)) {
            $resultado['endpoint_info'] = $endpointInfo;
            
            // Adiciona informações de paginação
            if ($size > 1 && isset($resultado['processos'])) {
                // Múltiplos resultados
                $resultado['paginacao'] = [
                    'size' => $size,
                    'search_after_atual' => $searchAfter,
                    'search_after_proximo' => $resultado['search_after'] ?? null,
                    'total' => $resultado['total'] ?? 0,
                    'has_more' => $resultado['has_more'] ?? false
                ];
            } else {
                // Resultado único (compatibilidade)
                $resultado['paginacao'] = [
                    'size' => $size,
                    'search_after' => $searchAfter,
                    'total' => $response['hits']['total']['value'] ?? ($response['hits']['total'] ?? 1)
                ];
            }
            
            // Adiciona debug se em desenvolvimento
            if (ENVIRONMENT === 'development' && isset($queryUsada) && isset($responseCompleta)) {
                $resultado['_debug'] = [
                    'query_enviada' => $queryUsada,
                    'url_completa' => $url,
                    'response_keys' => array_keys($responseCompleta),
                    'hits_count' => isset($responseCompleta['hits']['hits']) ? count($responseCompleta['hits']['hits']) : 0,
                    'total_resultados' => isset($responseCompleta['hits']['total']) 
                        ? (is_array($responseCompleta['hits']['total']) ? $responseCompleta['hits']['total']['value'] : $responseCompleta['hits']['total'])
                        : 0,
                    'response_completo' => $responseCompleta
                ];
            }
        }
        
        // Log do resultado processado para debug
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'CnjApi: Processed Result: ' . json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $debugLogFile = APPPATH . 'logs/cnj_api_debug_' . date('Y-m-d') . '.log';
            $debugContent = "=== CNJ API Processed Result ===\n";
            $debugContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $debugContent .= "Process Number: " . $numeroLimpo . "\n";
            $debugContent .= "Size: " . $size . "\n";
            $debugContent .= "Search After: " . ($searchAfter ? json_encode($searchAfter) : 'null') . "\n";
            $debugContent .= "Result:\n" . json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            $debugContent .= "=== End Result ===\n\n";
            file_put_contents($debugLogFile, $debugContent, FILE_APPEND);
        }
        
        // Adiciona informações do endpoint ao resultado
        if (is_array($resultado)) {
            $resultado['endpoint_info'] = $endpointInfo;
        }
        
        return $resultado;
    }

    /**
     * Consulta processos com filtros e paginação
     * 
     * @param array $filtros Filtros de busca (ex: ['classe.codigo' => 1116, 'orgaoJulgador.codigo' => 13597])
     * @param string|null $tribunal Código do tribunal
     * @param int $size Quantidade de resultados por página (padrão: 10, máximo: 10000)
     * @param array|null $searchAfter Array com valores de sort da última página (para paginação)
     * @return array|false Dados dos processos ou false em caso de erro
     */
    public function consultarProcessosComFiltros($filtros = [], $tribunal = null, $size = 10, $searchAfter = null)
    {
        if (empty($filtros)) {
            log_message('error', 'CnjApi: Filtros de busca não informados');
            return false;
        }
        
        // Detecta tribunal se necessário (pode ser inferido dos filtros)
        $endpoint = null;
        if ($tribunal) {
            // Tenta obter endpoint baseado no tribunal
            $this->load->helper('tribunais_endpoints');
            // Para busca com filtros, pode precisar de endpoint específico
            // Por enquanto, usa endpoint genérico ou detecta do primeiro filtro
        }
        
        // Se não tiver endpoint, retorna erro
        if (!$endpoint) {
            log_message('error', 'CnjApi: Endpoint não determinado para busca com filtros');
            return false;
        }
        
        // Valida e ajusta size
        $size = max(1, min(10000, intval($size)));
        
        // Monta query bool com must
        $must = [];
        foreach ($filtros as $campo => $valor) {
            $must[] = [
                'match' => [
                    $campo => $valor
                ]
            ];
        }
        
        $query = [
            'size' => $size,
            'query' => [
                'bool' => [
                    'must' => $must
                ]
            ],
            'sort' => [
                [
                    '@timestamp' => [
                        'order' => 'asc'
                    ]
                ]
            ]
        ];
        
        // Adiciona search_after se fornecido
        if ($searchAfter !== null && is_array($searchAfter) && !empty($searchAfter)) {
            $query['search_after'] = $searchAfter;
        }
        
        $response = $this->fazerRequisicao($endpoint, $query);
        
        if ($response === false) {
            return false;
        }
        
        // Processa múltiplos resultados
        $processos = [];
        if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                if (isset($hit['_source'])) {
                    $processos[] = $hit['_source'];
                }
            }
        }
        
        // Prepara resultado com informações de paginação
        $ultimoHit = !empty($response['hits']['hits']) ? end($response['hits']['hits']) : null;
        $resultado = [
            'processos' => $processos,
            'total' => $response['hits']['total']['value'] ?? ($response['hits']['total'] ?? 0),
            'size' => $size,
            'retornados' => count($processos),
            'paginacao' => null
        ];
        
        if ($ultimoHit && isset($ultimoHit['sort'])) {
            $resultado['paginacao'] = [
                'search_after' => $ultimoHit['sort'],
                'tem_proxima_pagina' => ($resultado['total'] > count($processos))
            ];
        }
        
        return $resultado;
    }

    /**
     * Sincroniza movimentações de um processo
     * 
     * @param int $processoId ID do processo no banco
     * @param string $numeroProcesso Número do processo
     * @return array|false Movimentações importadas ou false
     */
    public function sincronizarMovimentacoes($processoId, $numeroProcesso)
    {
        $dadosProcesso = $this->consultarProcesso($numeroProcesso);
        
        if (!$dadosProcesso || !isset($dadosProcesso['movimentos'])) {
            return false;
        }
        
        $movimentacoesImportadas = [];
        $this->CI->load->model('movimentacoes_processuais_model');
        
        foreach ($dadosProcesso['movimentos'] as $movimento) {
            // Prepara data de movimentação no formato correto para verificação
            $dataMovimentacaoFormatada = null;
            if (isset($movimento['dataHora']) && !empty($movimento['dataHora'])) {
                // Converte data da API para formato do banco (Y-m-d H:i:s)
                $dataMovimentacaoFormatada = date('Y-m-d H:i:s', strtotime($movimento['dataHora']));
            }
            
            // Verifica se já existe (RN 10.3)
            // Nota: $movimento['nome'] é passado como $tipo mas não é usado na verificação
            // A verificação usa apenas processo_id + data para evitar duplicatas
            $existe = $this->CI->movimentacoes_processuais_model->verificarMovimentacaoExistente(
                $processoId,
                $dataMovimentacaoFormatada,
                $movimento['nome'] ?? null
            );
            
            // Se verificação retornou false (pode ser erro ou não existe), continua normalmente
            // O modelo já faz log de erros internamente
            
            if (!$existe) {
                // Extrai tribunal e juiz se disponível (RN 10.3)
                $tribunal = null;
                $juiz = null;
                
                if (isset($movimento['orgaoJulgador'])) {
                    $orgao = $movimento['orgaoJulgador'];
                    $tribunal = $orgao['nome'] ?? null;
                }
                
                if (isset($movimento['juiz'])) {
                    $juiz = is_array($movimento['juiz']) ? ($movimento['juiz']['nome'] ?? null) : $movimento['juiz'];
                }
                
                // Importa movimentação (RN 10.3)
                $data = [
                    'processos_id' => $processoId,
                    'data' => isset($movimento['dataHora']) ? date('Y-m-d H:i:s', strtotime($movimento['dataHora'])) : date('Y-m-d H:i:s'),
                    'tipo' => $movimento['nome'] ?? 'Movimentação', // Nome da movimentação vai em 'tipo'
                    'descricao' => $movimento['descricao'] ?? '',
                    'origem' => 'api_cnj',
                    'dados_api' => json_encode($movimento, JSON_UNESCAPED_UNICODE),
                    'importado_api' => 1,
                    'data_atualizacao' => date('Y-m-d H:i:s'), // RN 10.3: marca data de atualização
                    'usuarios_id' => $this->CI->session->userdata('id_admin') ?? null
                ];
                
                // Adiciona tribunal e juiz se disponível (RN 10.3)
                if ($tribunal) {
                    $data['tribunal'] = $tribunal;
                }
                if ($juiz) {
                    $data['juiz'] = $juiz;
                }
                
                // Identifica se é movimentação importante (RN 10.3)
                $palavrasChave = ['sentença', 'decisão', 'acórdão', 'julgamento', 'despacho', 'intimação'];
                $tipoLower = strtolower($data['tipo']);
                $descricaoLower = strtolower($data['descricao']);
                $importante = false;
                
                foreach ($palavrasChave as $palavra) {
                    if (strpos($tipoLower, $palavra) !== false || strpos($descricaoLower, $palavra) !== false) {
                        $importante = true;
                        break;
                    }
                }
                
                if ($importante) {
                    $data['importante'] = 1; // Marca como importante se a coluna existir
                }
                
                if ($this->CI->movimentacoes_processuais_model->add('movimentacoes_processuais', $data)) {
                    $movimentacoesImportadas[] = $data;
                }
            }
        }
        
        return $movimentacoesImportadas;
    }

    /**
     * Faz requisição HTTP para API
     * 
     * @param string $url URL completa
     * @param array $data Dados para enviar
     * @return array|false Resposta JSON ou false
     */
    private function fazerRequisicao($url, $data = [])
    {
        if (empty($this->apiKey)) {
            log_message('error', 'CnjApi: API Key não configurada');
            return false;
        }
        
        // Usa cURL
        $ch = curl_init();
        
        // O valor já inclui "APIKey " se não estiver no .env
        // Se vier do .env sem "APIKey ", adiciona
        $authHeader = $this->apiKey;
        if (strpos($authHeader, 'APIKey ') !== 0) {
            $authHeader = 'APIKey ' . $authHeader;
        }
        
        // Converte para JSON (raw string) - equivalente ao "raw" JSON no Postman
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        // Log do que está sendo enviado (apenas em desenvolvimento)
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'CnjApi: URL completa: ' . $url);
            log_message('debug', 'CnjApi: Método: POST');
            log_message('debug', 'CnjApi: Body Type: raw JSON (text/plain)');
            log_message('debug', 'CnjApi: Headers: Authorization: ' . substr($authHeader, 0, 20) . '..., Content-Type: application/json');
            log_message('debug', 'CnjApi: Body (primeiros 500 chars): ' . substr($jsonData, 0, 500));
            log_message('debug', 'CnjApi: Body length: ' . strlen($jsonData) . ' bytes');
            log_message('debug', 'CnjApi: JSON completo enviado: ' . $jsonData);
        }
        
        // Configura cURL para enviar como raw JSON (equivalente ao Postman "raw" + "JSON")
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData, // String JSON raw (não form-data)
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $authHeader,
                'Content-Type: application/json', // Indica que é JSON raw
                'Accept: application/json',
                'Content-Length: ' . strlen($jsonData) // Tamanho exato do body
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_VERBOSE => ENVIRONMENT === 'development' // Ativa verbose em desenvolvimento
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        
        // Log detalhado da resposta apenas em desenvolvimento
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'CnjApi: HTTP Code: ' . $httpCode);
            log_message('debug', 'CnjApi: Response length: ' . strlen($response) . ' bytes');
            log_message('debug', 'CnjApi: Response RAW (primeiros 2000 chars): ' . substr($response, 0, 2000));
            
            // Salva resposta completa para comparação com Postman apenas em desenvolvimento
            $debugLogFile = APPPATH . 'logs/cnj_api_response_' . date('Y-m-d') . '.log';
            $debugContent = "=== CNJ API RAW RESPONSE (COMPARAÇÃO COM POSTMAN) ===\n";
            $debugContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $debugContent .= "URL: " . $url . "\n";
            $debugContent .= "HTTP Code: " . $httpCode . "\n";
            $debugContent .= "Response Length: " . strlen($response) . " bytes\n";
            $debugContent .= "=== RESPONSE RAW (igual ao Postman) ===\n";
            $debugContent .= $response . "\n";
            $debugContent .= "=== END RAW RESPONSE ===\n\n";
            file_put_contents($debugLogFile, $debugContent, FILE_APPEND);
            
            log_message('debug', 'CnjApi: Response completa salva em: ' . $debugLogFile);
            log_message('debug', 'CnjApi: cURL Info: ' . json_encode($curlInfo, JSON_PRETTY_PRINT));
        }
        
        curl_close($ch);
        
        if ($error) {
            log_message('error', 'CnjApi cURL Error: ' . $error);
            log_message('error', 'CnjApi cURL Info: ' . json_encode($curlInfo));
            return false;
        }
        
        // Log da resposta para debug apenas em desenvolvimento
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'CnjApi: HTTP Code: ' . $httpCode);
            log_message('debug', 'CnjApi: Response (first 500 chars): ' . substr($response, 0, 500));
            log_message('debug', 'CnjApi: Full Response Length: ' . strlen($response) . ' bytes');
            
            // Salva resposta completa em arquivo de log separado para facilitar debug
            $debugLogFile = APPPATH . 'logs/cnj_api_debug_' . date('Y-m-d') . '.log';
            $debugContent = "=== CNJ API Response ===\n";
            $debugContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $debugContent .= "URL: " . $url . "\n";
            $debugContent .= "HTTP Code: " . $httpCode . "\n";
            $debugContent .= "Response:\n" . $response . "\n";
            $debugContent .= "=== End Response ===\n\n";
            file_put_contents($debugLogFile, $debugContent, FILE_APPEND);
        }
        
        if ($httpCode != 200) {
            $errorDetails = substr($response, 0, 1000);
            log_message('error', 'CnjApi HTTP Error ' . $httpCode . ': ' . $errorDetails);
            log_message('error', 'CnjApi URL: ' . $url);
            log_message('error', 'CnjApi Query: ' . json_encode($data));
            
            $errorJson = json_decode($response, true);
            if ($errorJson) {
                if (isset($errorJson['error'])) {
                    log_message('error', 'CnjApi Error Details: ' . json_encode($errorJson['error']));
                }
                if (isset($errorJson['message'])) {
                    log_message('error', 'CnjApi Error Message: ' . $errorJson['message']);
                }
            }
            
            return false;
        }
        
        log_message('info', 'CnjApi: HTTP 200 - Response length: ' . strlen($response) . ' bytes');
        
        $json = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'CnjApi JSON Error: ' . json_last_error_msg());
            log_message('error', 'CnjApi Raw Response (primeiros 1000 chars): ' . substr($response, 0, 1000));
            
            // Salva erro de JSON também
            $errorLogFile = APPPATH . 'logs/cnj_api_json_error_' . date('Y-m-d') . '.log';
            file_put_contents($errorLogFile, "JSON Error: " . json_last_error_msg() . "\n\nResponse:\n" . $response, FILE_APPEND);
            
            return false;
        }
        
        // Log estrutura da resposta DECODIFICADA para comparar com Postman
        log_message('info', 'CnjApi: JSON decodificado com sucesso');
        log_message('info', 'CnjApi: Response keys (nível raiz): ' . implode(', ', array_keys($json)));
        
        if (isset($json['hits'])) {
            $hitsCount = isset($json['hits']['hits']) ? count($json['hits']['hits']) : 0;
            log_message('info', 'CnjApi: Hits encontrados: ' . $hitsCount);
            
            if ($hitsCount > 0 && isset($json['hits']['hits'][0])) {
                $firstHit = $json['hits']['hits'][0];
                log_message('info', 'CnjApi: Primeiro hit - keys: ' . implode(', ', array_keys($firstHit)));
                if (isset($firstHit['_source'])) {
                    log_message('info', 'CnjApi: _source keys: ' . implode(', ', array_keys($firstHit['_source'])));
                }
            }
        } else {
            log_message('warning', 'CnjApi: Resposta não contém "hits" - estrutura diferente do esperado');
            log_message('warning', 'CnjApi: Estrutura completa: ' . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        // Log da resposta JSON decodificada apenas em desenvolvimento
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'CnjApi: JSON Decoded Successfully');
            log_message('debug', 'CnjApi: Response Structure: ' . json_encode([
                'has_hits' => isset($json['hits']),
                'hits_count' => isset($json['hits']['hits']) ? count($json['hits']['hits']) : 0,
                'total' => $json['hits']['total']['value'] ?? ($json['hits']['total'] ?? 'N/A'),
                'keys' => array_keys($json)
            ], JSON_PRETTY_PRINT));
            
            // Salva JSON completo em arquivo de log apenas em desenvolvimento
            $debugLogFile = APPPATH . 'logs/cnj_api_debug_' . date('Y-m-d') . '.log';
            $debugContent = "=== CNJ API JSON Decoded ===\n";
            $debugContent .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
            $debugContent .= "JSON Structure:\n" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            $debugContent .= "=== End JSON ===\n\n";
            file_put_contents($debugLogFile, $debugContent, FILE_APPEND);
        }
        
        return $json;
    }

    /**
     * Processa resposta da API
     * 
     * @param array $response Resposta da API
     * @param array $dadosTribunal Dados do tribunal
     * @param int $size Quantidade de resultados solicitados
     * @return array Dados processados
     */
    private function processarResposta($response, $dadosTribunal, $size = 1)
    {
        // Processa múltiplos resultados se size > 1
        $processos = [];
        $lastSortValue = null;
        
        if (isset($response['hits']['hits']) && !empty($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                if (isset($hit['_source'])) {
                    $source = $hit['_source'];
                    
                    // Armazena último valor de sort para paginação (search_after)
                    if (isset($hit['sort']) && is_array($hit['sort'])) {
                        $lastSortValue = $hit['sort'];
                    }
                    
                    // Extrai assunto (pode ser array ou objeto)
                    $assunto = null;
                    if (isset($source['assuntos']) && is_array($source['assuntos']) && !empty($source['assuntos'])) {
                        // Retorna todos os assuntos (array)
                        $assunto = $source['assuntos'];
                    } elseif (isset($source['assunto'])) {
                        $assunto = $source['assunto'];
                    }
                    
                    // Extrai data de distribuição (pode estar em movimentos ou dataAjuizamento)
                    $dataDistribuicao = null;
                    if (isset($source['dataAjuizamento'])) {
                        // Formato pode ser YYYYMMDDHHMMSS ou ISO
                        $dataAjuizamento = $source['dataAjuizamento'];
                        if (strlen($dataAjuizamento) == 14) {
                            // Formato YYYYMMDDHHMMSS
                            $dataDistribuicao = substr($dataAjuizamento, 0, 4) . '-' . 
                                               substr($dataAjuizamento, 4, 2) . '-' . 
                                               substr($dataAjuizamento, 6, 2);
                        } else {
                            $dataDistribuicao = date('Y-m-d', strtotime($dataAjuizamento));
                        }
                    }
                    
                    // Extrai última movimentação
                    $dataUltimaMovimentacao = null;
                    if (isset($source['movimentos']) && is_array($source['movimentos']) && !empty($source['movimentos'])) {
                        // Os movimentos vêm ordenados do mais antigo para o mais recente
                        // A última movimentação é o último elemento do array
                        $ultimaMov = end($source['movimentos']);
                        if (isset($ultimaMov['dataHora'])) {
                            $dataUltimaMovimentacao = date('Y-m-d H:i:s', strtotime($ultimaMov['dataHora']));
                        }
                    } elseif (isset($source['dataHoraUltimaAtualizacao'])) {
                        $dataUltimaMovimentacao = date('Y-m-d H:i:s', strtotime($source['dataHoraUltimaAtualizacao']));
                    }
                    
                    // Extrai vara/comarca do órgão julgador
                    $vara = null;
                    $comarca = null;
                    if (isset($source['orgaoJulgador'])) {
                        $orgao = $source['orgaoJulgador'];
                        $vara = $orgao['nome'] ?? null;
                        // Comarca pode estar no nome ou em outro campo
                        if (isset($orgao['codigoMunicipioIBGE'])) {
                            $comarca = $orgao['nome'] ?? null;
                        }
                    }
                    
                    // Extrai número do processo - pode estar em diferentes campos
                    $numeroProcesso = null;
                    if (isset($source['numeroProcesso'])) {
                        $numeroProcesso = $source['numeroProcesso'];
                    } elseif (isset($source['numero'])) {
                        $numeroProcesso = $source['numero'];
                    } else {
                        $numeroProcesso = $dadosTribunal['numero_formatado'];
                    }
                    
                    $processoItem = [
                        'numero' => $numeroProcesso,
                        'numero_limpo' => $dadosTribunal['numero_limpo'],
                        'numero_formatado' => $dadosTribunal['numero_formatado'],
                        'classe' => $source['classe'] ?? null,
                        'assunto' => $assunto,
                        'situacao' => $source['situacao'] ?? null,
                        'status' => $source['status'] ?? ($source['grau'] ?? null),
                        'valor' => isset($source['valorCausa']) ? floatval($source['valorCausa']) : null,
                        'dataDistribuicao' => $dataDistribuicao,
                        'dataUltimaMovimentacao' => $dataUltimaMovimentacao,
                        'vara' => $vara,
                        'comarca' => $comarca,
                        'tribunal' => $dadosTribunal['tribunal'],
                        'segmento' => $dadosTribunal['segmento'],
                        'partes' => $source['partes'] ?? [],
                        'movimentos' => $source['movimentos'] ?? [],
                        'dados_completos' => $source,
                        'sort' => $hit['sort'] ?? null // Valor de sort para paginação
                    ];
                    
                    $processos[] = $processoItem;
                }
            }
        }
        
        // Se size = 1, retorna apenas o primeiro resultado (compatibilidade)
        if ($size == 1 && !empty($processos)) {
            $processo = $processos[0];
            // Remove sort do resultado principal quando size = 1
            unset($processo['sort']);
            return $processo;
        }
        
        // Retorna múltiplos resultados com informações de paginação
        return [
            'processos' => $processos,
            'total' => $response['hits']['total']['value'] ?? ($response['hits']['total'] ?? count($processos)),
            'search_after' => $lastSortValue, // Valor para próxima página
            'has_more' => !empty($lastSortValue) && count($processos) >= $size
        ];
    }

    /**
     * Detecta tribunal do número de processo
     * 
     * @param string $numeroProcesso Número do processo
     * @return array|false Dados do tribunal ou false
     */
    public function detectarTribunal($numeroProcesso)
    {
        return detectar_tribunal_cnj($numeroProcesso);
    }
}

