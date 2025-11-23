<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ConsultaProcessual extends MY_Controller
{
    /**
     * Tempo (em segundos) para reutilizar uma consulta antes de buscar na API novamente.
     */
    private $cacheTtlSeconds = 3600; // 1 hora

    public function __construct()
    {
        parent::__construct();
        $this->load->library('cnjapi');
        $this->load->model('processos_model');
        $this->load->model('movimentacoes_processuais_model');
        $this->load->model('clientes_model');
        $this->load->model('processos_cache_model');
        $this->data['menuConsultaProcessual'] = 'consulta-processual';
    }

    public function index()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para consultar processos na API.');
            redirect(base_url());
        }

        $this->data['view'] = 'consultaProcessual/index';
        return $this->layout();
    }

    public function consultar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Você não tem permissão para consultar processos.']));
            return;
        }

        $numeroProcesso = $this->input->post('numero_processo') ?: $this->input->get('numero_processo');
        $tribunal = $this->input->post('tribunal') ?: $this->input->get('tribunal');
        $segmento = $this->input->post('segmento') ?: $this->input->get('segmento');
        $size = (int)($this->input->post('size') ?: $this->input->get('size') ?: 1);
        $searchAfter = $this->input->post('search_after') ?: $this->input->get('search_after');
        
        // Converte search_after de string JSON para array se necessário
        if ($searchAfter && is_string($searchAfter)) {
            $searchAfter = json_decode($searchAfter, true);
        }

        if (empty($numeroProcesso)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Número do processo é obrigatório.']));
            return;
        }
        
        // Limita size entre 1 e 10000
        $size = max(1, min(10000, $size));

        // Detecta endpoint antes de consultar (para exibir mesmo em caso de erro)
        $this->load->helper('tribunais_endpoints');
        $numeroLimpo = normalizar_numero_processo($numeroProcesso);
        $dadosTribunal = false;
        $endpointInfo = null;
        $cachePayload = null;
        $cacheRow = $this->processos_cache_model->getByNumero($numeroLimpo);
        if ($cacheRow && !empty($cacheRow->payload)) {
            $cachePayload = json_decode($cacheRow->payload, true);
            $ultimoFetch = $cacheRow->ultimo_fetch ? strtotime($cacheRow->ultimo_fetch) : 0;
            if ($cachePayload && $ultimoFetch && (time() - $ultimoFetch) < $this->cacheTtlSeconds) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'data' => $cachePayload,
                        'from_cache' => true,
                        'cache_timestamp' => $cacheRow->ultimo_fetch,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return;
            }
        }
        
        if (strlen($numeroLimpo) == 20) {
            $dadosTribunal = detectar_tribunal_cnj($numeroLimpo);
            if ($dadosTribunal) {
                $segmento = $dadosTribunal['segmento'];
                $tribunalCodigo = $tribunal ?: $dadosTribunal['tribunal'];
                $endpoint = obter_endpoint_tribunal($segmento, $tribunalCodigo);
                
                if ($endpoint) {
                    $endpointInfo = [
                        'url' => $endpoint,
                        'segmento' => $segmento,
                        'tribunal' => $tribunalCodigo,
                        'numero_limpo' => $numeroLimpo
                    ];
                }
            }
        }
        
        // Consulta na API com paginação
        $resultado = $this->cnjapi->consultarProcesso($numeroProcesso, $tribunal, $size, $searchAfter);

        if ($resultado === false) {
            if ($cachePayload) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'data' => $cachePayload,
                        'from_cache' => true,
                        'cache_timestamp' => $cacheRow->ultimo_fetch ?? null,
                        'cache_stale' => true,
                        'message' => 'Exibindo dados armazenados. Não foi possível atualizar a consulta agora.'
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return;
            }

            $response = [
                'success' => false,
                'message' => 'Erro ao consultar processo',
                'numero' => $numeroProcesso
            ];
            
            if ($endpointInfo) {
                $response['endpoint_info'] = $endpointInfo;
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }
        
        // Verifica se encontrou resultados
        // Quando size > 1, o resultado vem com 'processos', quando size = 1, vem com 'numero'
        $temResultado = false;
        if (isset($resultado['processos']) && !empty($resultado['processos'])) {
            // Múltiplos resultados
            $temResultado = true;
        } elseif (!empty($resultado['numero'])) {
            // Resultado único
            $temResultado = true;
        }
        
        if (!$temResultado) {
            if ($cachePayload) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => true,
                        'data' => $cachePayload,
                        'from_cache' => true,
                        'cache_timestamp' => $cacheRow->ultimo_fetch ?? null,
                        'cache_stale' => true,
                        'message' => 'Exibindo dados armazenados. Consulta atual não retornou resultados.'
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                return;
            }

            $response = [
                'success' => false,
                'message' => 'Processo não encontrado na API CNJ. Verifique se o número está correto e se o processo existe no tribunal informado.',
                'numero' => $numeroProcesso,
                'numero_limpo' => $numeroLimpo
            ];
            
            if ($endpointInfo) {
                $response['endpoint_info'] = $endpointInfo;
            }
            
            // Adiciona informações da estrutura da resposta se disponível
            if (isset($resultado['response_structure'])) {
                $response['response_structure'] = $resultado['response_structure'];
            }
            
            // Adiciona total de resultados se disponível
            if (isset($resultado['total_resultados'])) {
                $response['total_resultados'] = $resultado['total_resultados'];
            }
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return;
        }

        if (!isset($resultado['endpoint_info']) && $endpointInfo) {
            $resultado['endpoint_info'] = $endpointInfo;
        }
        
        $response = [
            'success' => true,
            'data' => $resultado,
            'from_cache' => false
        ];
        
        // Adiciona debug apenas em desenvolvimento
        if (ENVIRONMENT === 'development' && $this->input->get('debug')) {
            $response['debug'] = [
                'endpoint_info' => $endpointInfo,
                'numero_consultado' => $numeroProcesso,
                'numero_limpo' => $numeroLimpo,
                'tribunal_detectado' => $tribunalCodigo ?? null,
                'segmento_detectado' => $segmento ?? null,
                'api_debug' => $resultado['_debug'] ?? null
            ];
        }
        
        $payloadJson = json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $hashPayload = hash('sha256', $payloadJson);
        $this->processos_cache_model->saveCache($numeroLimpo, $payloadJson, $hashPayload);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function sincronizar($processoId = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'sProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para sincronizar processos.');
            redirect(base_url());
        }

        if ($processoId == null) {
            $processoId = $this->input->post('processo_id');
        }

        if ($processoId == null) {
            $this->session->set_flashdata('error', 'ID do processo não informado.');
            redirect(site_url('processos/gerenciar'));
        }

        // Busca processo
        $processo = $this->processos_model->getById($processoId);
        if (!$processo) {
            $this->session->set_flashdata('error', 'Processo não encontrado.');
            redirect(site_url('processos/gerenciar'));
        }

        // Sincroniza movimentações
        $movimentacoes = $this->cnjapi->sincronizarMovimentacoes($processoId, $processo->numeroProcesso);

        if ($movimentacoes === false) {
            $this->session->set_flashdata('error', 'Erro ao sincronizar movimentações.');
        } else {
            $total = count($movimentacoes);
            if ($total > 0) {
                $this->session->set_flashdata('success', "Sincronização concluída. {$total} movimentação(ões) importada(s).");
                
                // Atualiza última consulta
                $this->processos_model->edit('processos', [
                    'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
                    'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 day'))
                ], 'idProcessos', $processoId);
            } else {
                $this->session->set_flashdata('info', 'Nenhuma movimentação nova encontrada.');
            }
        }

        redirect(site_url('processos/visualizar/' . $processoId));
    }

    /**
     * Busca clientes para vinculação a processo
     * 
     * Retorna lista de clientes filtrados por termo de busca (nome, documento, email).
     * Se termo vazio ou menor que 2 caracteres, retorna todos os clientes (limitado a 1000).
     * 
     * @return void Retorna JSON com lista de clientes
     */
    public function buscar_cliente()
    {
        $permissaoUsuario = $this->session->userdata('permissao');
        $nivelUsuario = $this->session->userdata('nivel_admin');
        $isAdmin = is_string($permissaoUsuario) && strtolower($permissaoUsuario) === 'admin';
        $isAdmin = $isAdmin || (is_string($nivelUsuario) && strtolower($nivelUsuario) === 'admin');

        if (! $isAdmin && ! $this->permission->checkPermission($permissaoUsuario, 'aProcesso')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Você não tem permissão.']));
            return;
        }

        $termo = $this->input->get('name');
        if ($termo === null) {
        $termo = $this->input->get('termo');
        }
        
        // Se termo vazio, retorna todos os clientes (limitado a 1000 para performance)
        $buscarTodos = empty($termo) || strlen($termo) < 2;

        $this->load->model('clientes_model');
        
        // Buscar clientes por nome, CPF/CNPJ ou email
        $clientes_columns = $this->db->list_fields('clientes');
        $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
        $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : (in_array('nome', $clientes_columns) ? 'nome' : null);
        
        if (!$clientes_id_col || !$clientes_nome_col) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        if (!$buscarTodos) {
            $this->db->group_start();
            $this->db->like($clientes_nome_col, $termo);
            $this->db->or_like('documento', $termo);
            $this->db->or_like('email', $termo);
            $this->db->group_end();
        }
        $this->db->limit($buscarTodos ? 1000 : 20);
        $this->db->order_by($clientes_nome_col, 'ASC');
        
        $query = $this->db->get('clientes');
        $clientes = $query->result();

        $resultado = [];
        foreach ($clientes as $cliente) {
            $resultado[] = [
                'id' => $cliente->$clientes_id_col,
                'nome' => $cliente->$clientes_nome_col,
                'documento' => isset($cliente->documento) ? $cliente->documento : '',
                'email' => isset($cliente->email) ? $cliente->email : '',
                'telefone' => isset($cliente->telefone) ? $cliente->telefone : '',
                'celular' => isset($cliente->celular) ? $cliente->celular : '',
                'tipo_pessoa' => (isset($cliente->pessoa_fisica) && $cliente->pessoa_fisica == 1) ? 'fisica' : 'juridica',
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($resultado));
    }

    /**
     * Cadastra um cliente rapidamente durante vinculação a processo
     * 
     * Permite cadastro rápido de cliente com dados mínimos (nome obrigatório).
     * Valida duplicidade de documento e email antes de inserir.
     * Gera senha aleatória segura se não fornecida.
     * 
     * @return void Retorna JSON com resultado do cadastro
     */
    public function cadastrar_cliente_rapido()
    {
        $permissaoUsuario = $this->session->userdata('permissao');
        $nivelUsuario = $this->session->userdata('nivel_admin');
        $isAdmin = is_string($permissaoUsuario) && strtolower($permissaoUsuario) === 'admin';
        $isAdmin = $isAdmin || (is_string($nivelUsuario) && strtolower($nivelUsuario) === 'admin');

        if (! $isAdmin && ! $this->permission->checkPermission($permissaoUsuario, 'aCliente')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Você não tem permissão para cadastrar clientes.']));
            return;
        }

        $this->load->model('clientes_model');
        $this->load->library('form_validation');

        $nome = $this->input->post('nome');
        $documento = preg_replace('/[^0-9]/', '', $this->input->post('documento'));
        $email = $this->input->post('email');
        $telefone = $this->input->post('telefone');
        $celular = $this->input->post('celular');

        if (empty($nome)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Nome é obrigatório.']));
            return;
        }

        // Determina tipo de pessoa pelo documento
        $tipo_pessoa = strlen($documento) == 11 ? 'fisica' : (strlen($documento) == 14 ? 'juridica' : 'fisica');
        $pessoa_fisica = $tipo_pessoa == 'fisica' ? 1 : 0;

        // Verifica se documento já existe
        if (!empty($documento)) {
            $this->db->where('documento', $documento);
            $existe = $this->db->get('clientes')->row();
            if ($existe) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false, 
                        'message' => 'Cliente já existe com este documento.',
                        'cliente_existente' => [
                            'id' => $existe->idClientes ?? $existe->id,
                            'nome' => $existe->nomeCliente ?? $existe->nome,
                            'documento' => $existe->documento,
                            'email' => $existe->email ?? '',
                        ]
                    ]));
                return;
            }
        }

        // Verifica email se informado
        if (!empty($email) && $this->clientes_model->emailExists($email)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Este e-mail já está sendo utilizado.']));
            return;
        }

        // Dados básicos para cadastro rápido
        $data = [
            'nomeCliente' => $nome,
            'pessoa_fisica' => $pessoa_fisica,
            'tipo_cliente' => $tipo_pessoa,
            'documento' => $documento ?: null,
            'telefone' => $telefone ?: null,
            'celular' => $celular ?: null,
            'email' => $email ?: null,
            'senha' => password_hash($documento ?: '123456', PASSWORD_DEFAULT),
            'dataCadastro' => date('Y-m-d'),
        ];

        $id_cliente = $this->clientes_model->add('clientes', $data);

        if ($id_cliente) {
            $cliente = $this->clientes_model->getById($id_cliente);
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : 'id';
            $clientes_nome_col = in_array('nomeCliente', $clientes_columns) ? 'nomeCliente' : 'nome';

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Cliente cadastrado com sucesso!',
                    'cliente' => [
                        'id' => $cliente->$clientes_id_col,
                        'nome' => $cliente->$clientes_nome_col,
                        'documento' => $cliente->documento ?? '',
                        'email' => $cliente->email ?? '',
                        'telefone' => $cliente->telefone ?? '',
                        'celular' => $cliente->celular ?? '',
                        'tipo_pessoa' => $tipo_pessoa,
                    ]
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente.']));
        }
    }

    /**
     * Salva processo consultado com cliente vinculado
     * 
     * Salva processo retornado da API CNJ/DataJud no banco de dados,
     * vinculando-o a um cliente. Verifica duplicidade antes de inserir.
     * Sincroniza movimentações se disponíveis na resposta da API.
     * 
     * @return void Retorna JSON com resultado da operação
     */
    public function salvar_processo()
    {
        $permissaoUsuario = $this->session->userdata('permissao');
        $nivelUsuario = $this->session->userdata('nivel_admin');
        $isAdmin = is_string($permissaoUsuario) && strtolower($permissaoUsuario) === 'admin';
        $isAdmin = $isAdmin || (is_string($nivelUsuario) && strtolower($nivelUsuario) === 'admin');

        if (! $isAdmin && ! $this->permission->checkPermission($permissaoUsuario, 'aProcesso')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Você não tem permissão para adicionar processos.']));
            return;
        }

        $numeroProcesso = $this->input->post('numero_processo');
        $clienteId = (int) $this->input->post('cliente_id');
        $dadosProcesso = $this->input->post('dados_processo'); // JSON com dados do processo da API

        if (empty($numeroProcesso)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Número do processo é obrigatório.']));
            return;
        }

        if (empty($clienteId)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Selecione um cliente para vincular ao processo.']));
            return;
        }

        $clienteExistente = $this->clientes_model->getById($clienteId);
        if (!$clienteExistente) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Cliente informado não foi encontrado.']));
            return;
        }

        // Decodifica dados do processo se vier como JSON
        if (is_string($dadosProcesso)) {
            $dadosProcesso = json_decode($dadosProcesso, true);
        }

        // Normaliza número do processo
        $numeroLimpo = $this->processos_model->normalizarNumeroProcesso($numeroProcesso);

        // Verifica se processo já existe
        $this->db->where('numeroProcesso', $numeroLimpo);
        $existe = $this->db->get('processos')->row();
        
        if ($existe) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'Este processo já está cadastrado.',
                    'processo_id' => $existe->idProcessos ?? $existe->id
                ]));
            return;
        }

        // Prepara dados do processo
        $data = [
            'numeroProcesso' => $numeroLimpo,
            'classe' => isset($dadosProcesso['classe']) ? (is_array($dadosProcesso['classe']) ? $dadosProcesso['classe']['nome'] : $dadosProcesso['classe']) : null,
            'assunto' => isset($dadosProcesso['assunto']) ? (is_array($dadosProcesso['assunto']) ? (is_array($dadosProcesso['assunto'][0]) ? $dadosProcesso['assunto'][0]['nome'] : $dadosProcesso['assunto'][0]) : $dadosProcesso['assunto']) : null,
            'vara' => $dadosProcesso['vara'] ?? null,
            'comarca' => $dadosProcesso['comarca'] ?? null,
            'tribunal' => $dadosProcesso['tribunal'] ?? null,
            'segmento' => $dadosProcesso['segmento'] ?? null,
            'status' => 'em_andamento',
            'valorCausa' => isset($dadosProcesso['valor']) ? floatval($dadosProcesso['valor']) : null,
            'dataDistribuicao' => isset($dadosProcesso['dataDistribuicao']) ? date('Y-m-d', strtotime($dadosProcesso['dataDistribuicao'])) : null,
            'clientes_id' => $clienteId ?: null,
            'usuarios_id' => $this->session->userdata('id_admin') ?? null,
            'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
            'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];

        $id_processo = $this->processos_model->add('processos', $data);

        if ($id_processo) {
            // Sincroniza movimentações se houver
            if (isset($dadosProcesso['movimentos']) && !empty($dadosProcesso['movimentos'])) {
                $this->cnjapi->sincronizarMovimentacoes($id_processo, $numeroLimpo);
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Processo salvo com sucesso!',
                    'processo_id' => $id_processo
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Erro ao salvar processo.']));
        }
    }
}

