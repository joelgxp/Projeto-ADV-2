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
        $this->apiKey = $_ENV['API_CNJ_KEY'] ?? '';
        
        // Carrega helpers
        $this->CI->load->helper('tribunais_endpoints');
    }

    /**
     * Consulta processo na API CNJ/DataJud
     * 
     * @param string $numeroProcesso Número do processo (formatado ou limpo)
     * @param string|null $tribunal Código do tribunal (opcional, será detectado automaticamente)
     * @return array|false Dados do processo ou false em caso de erro
     */
    public function consultarProcesso($numeroProcesso, $tribunal = null)
    {
        // Normaliza número
        $numeroLimpo = normalizar_numero_processo($numeroProcesso);
        
        if (strlen($numeroLimpo) != 20) {
            log_message('error', 'CnjApi: Número de processo inválido: ' . $numeroProcesso);
            return false;
        }
        
        // Valida dígito verificador
        if (!validar_digito_verificador_cnj($numeroLimpo)) {
            log_message('error', 'CnjApi: Dígito verificador inválido: ' . $numeroProcesso);
            return false;
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
        $response = $this->fazerRequisicao($url, [
            'query' => [
                'match' => [
                    'numeroProcesso' => $numeroLimpo
                ]
            ]
        ]);
        
        if ($response === false) {
            return false;
        }
        
        // Processa resposta
        return $this->processarResposta($response, $dadosTribunal);
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
            // Verifica se já existe
            $existe = $this->CI->movimentacoes_processuais_model->verificarMovimentacaoExistente(
                $processoId,
                $movimento['dataHora'] ?? null,
                $movimento['nome'] ?? ''
            );
            
            if (!$existe) {
                // Importa movimentação
                $data = [
                    'processos_id' => $processoId,
                    'dataMovimentacao' => isset($movimento['dataHora']) ? date('Y-m-d H:i:s', strtotime($movimento['dataHora'])) : date('Y-m-d H:i:s'),
                    'titulo' => $movimento['nome'] ?? 'Movimentação',
                    'descricao' => $movimento['descricao'] ?? '',
                    'tipo' => $movimento['tipo'] ?? 'outros',
                    'origem' => 'api_cnj',
                    'dados_api' => json_encode($movimento),
                    'importado_api' => 1,
                    'usuarios_id' => $this->CI->session->userdata('id_admin') ?? null
                ];
                
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
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: APIKey ' . $this->apiKey,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            log_message('error', 'CnjApi cURL Error: ' . $error);
            return false;
        }
        
        if ($httpCode != 200) {
            log_message('error', 'CnjApi HTTP Error ' . $httpCode . ': ' . $response);
            return false;
        }
        
        $json = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'CnjApi JSON Error: ' . json_last_error_msg());
            return false;
        }
        
        return $json;
    }

    /**
     * Processa resposta da API
     * 
     * @param array $response Resposta da API
     * @param array $dadosTribunal Dados do tribunal
     * @return array Dados processados
     */
    private function processarResposta($response, $dadosTribunal)
    {
        // Extrai dados do processo da resposta
        $processo = [];
        
        if (isset($response['hits']['hits'][0]['_source'])) {
            $source = $response['hits']['hits'][0]['_source'];
            
            $processo = [
                'numero' => $source['numeroProcesso'] ?? $dadosTribunal['numero_formatado'],
                'numero_limpo' => $dadosTribunal['numero_limpo'],
                'numero_formatado' => $dadosTribunal['numero_formatado'],
                'classe' => $source['classe'] ?? null,
                'assunto' => $source['assunto'] ?? null,
                'situacao' => $source['situacao'] ?? null,
                'status' => $source['status'] ?? null,
                'valor' => isset($source['valorCausa']) ? floatval($source['valorCausa']) : null,
                'dataDistribuicao' => isset($source['dataDistribuicao']) ? date('Y-m-d', strtotime($source['dataDistribuicao'])) : null,
                'dataUltimaMovimentacao' => isset($source['dataUltimaMovimentacao']) ? date('Y-m-d H:i:s', strtotime($source['dataUltimaMovimentacao'])) : null,
                'vara' => $source['vara'] ?? null,
                'comarca' => $source['comarca'] ?? null,
                'tribunal' => $dadosTribunal['tribunal'],
                'segmento' => $dadosTribunal['segmento'],
                'partes' => $source['partes'] ?? [],
                'movimentos' => $source['movimentos'] ?? [],
                'dados_completos' => $source
            ];
        } else {
            // Se não encontrou na estrutura esperada, tenta estrutura alternativa
            $processo = [
                'numero' => $dadosTribunal['numero_formatado'],
                'numero_limpo' => $dadosTribunal['numero_limpo'],
                'numero_formatado' => $dadosTribunal['numero_formatado'],
                'classe' => null,
                'assunto' => null,
                'situacao' => null,
                'status' => null,
                'valor' => null,
                'partes' => [],
                'movimentos' => [],
                'dados_completos' => $response
            ];
        }
        
        return $processo;
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

