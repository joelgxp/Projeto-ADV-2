<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Processos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('processos_model');
        $this->data['menuProcessos'] = 'processos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar processos.');
            redirect(base_url());
        }

        // Se for requisição AJAX do DataTables, retornar JSON
        // Verificar se é requisição do DataTables (tem parâmetros específicos)
        $hasDatatablesParams = (
            $this->input->get('sEcho') !== false || 
            $this->input->get('draw') !== false ||
            $this->input->get('iDisplayStart') !== false ||
            $this->input->get('start') !== false ||
            $this->input->get('iDisplayLength') !== false ||
            $this->input->get('length') !== false
        );
        
        if ($this->input->is_ajax_request() && $hasDatatablesParams) {
            $this->datatables_ajax();
            return;
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('processos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->processos_model->count('processos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/processos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->processos_model->get('processos', '*', $pesquisa, $this->data['configuration']['per_page'], 0);

        $this->data['view'] = 'processos/processos';

        return $this->layout();
    }

    /**
     * Retorna dados para DataTables (server-side processing)
     */
    private function datatables_ajax()
    {
        // DataTables 1.9.4 usa parâmetros diferentes
        // sEcho deve ser retornado exatamente como foi enviado
        $sEcho = $this->input->get('sEcho');
        if ($sEcho === false || $sEcho === null) {
            $sEcho = $this->input->get('draw') ?: 1;
        }
        $draw = intval($sEcho);
        
        $start = intval($this->input->get('iDisplayStart') ?: $this->input->get('start') ?: 0);
        $length = intval($this->input->get('iDisplayLength') ?: $this->input->get('length') ?: 20);
        
        // Buscar termo de pesquisa
        $search = '';
        $search_param = $this->input->get('sSearch');
        if ($search_param !== false && $search_param !== null && $search_param !== '') {
            $search = $search_param;
        } else {
            $search_array = $this->input->get('search');
            if (is_array($search_array) && isset($search_array['value'])) {
                $search = $search_array['value'];
            }
        }
        
        // Total de registros sem filtro
        $recordsTotal = $this->processos_model->count('processos');
        
        // Buscar registros com paginação e filtro
        $results = $this->processos_model->get('processos', '*', $search, $length, $start);
        
        // Total de registros com filtro aplicado (usar mesma lógica do get)
        $recordsFiltered = $recordsTotal;
        if ($search) {
            // Resetar query builder
            $this->db->reset_query();
            
            $this->db->from('processos');
            
            // Join com clientes se necessário
            if ($this->db->table_exists('clientes')) {
                $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
            }
            
            $this->db->group_start();
            $this->db->like('processos.numeroProcesso', $search);
            $this->db->or_like('processos.classe', $search);
            $this->db->or_like('processos.assunto', $search);
            $this->db->or_like('processos.comarca', $search);
            $this->db->or_like('processos.tribunal', $search);
            if ($this->db->table_exists('clientes')) {
                $this->db->or_like('clientes.nomeCliente', $search);
            }
            $this->db->group_end();
            
            $recordsFiltered = $this->db->count_all_results();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                $numeroFormatado = $this->processos_model->formatarNumeroProcesso($r->numeroProcesso);
                
                // Status com cores
                $status_labels = [
                    'em_andamento' => ['label' => 'Em Andamento', 'class' => 'label-info'],
                    'suspenso' => ['label' => 'Suspenso', 'class' => 'label-warning'],
                    'arquivado' => ['label' => 'Arquivado', 'class' => 'label-default'],
                    'finalizado' => ['label' => 'Finalizado', 'class' => 'label-success'],
                ];
                $status = $r->status ?? 'em_andamento';
                $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'class' => 'label-default'];
                
                // Ações
                $acoes = '';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/processos/visualizar/' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProcesso')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/processos/editar/' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Processo"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dProcesso')) {
                    $acoes .= '<a href="#modal-excluir" role="button" data-toggle="modal" processo="' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Processo"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                
                $data[] = [
                    $r->idProcessos,
                    '<a href="' . base_url() . 'index.php/processos/visualizar/' . $r->idProcessos . '">' . $numeroFormatado . '</a>',
                    $r->classe ?? '-',
                    $r->assunto ?? '-',
                    isset($r->nomeCliente) ? $r->nomeCliente : '-',
                    isset($r->nomeAdvogado) ? $r->nomeAdvogado : '-',
                    '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>',
                    $r->comarca ?? '-',
                    $acoes
                ];
            }
        }
        
        // DataTables 1.9.4 usa formato diferente
        // sEcho deve ser retornado exatamente como foi enviado (pode ser string)
        $response = [
            'sEcho' => $sEcho, // Manter original, não converter para int
            'iTotalRecords' => intval($recordsTotal),
            'iTotalDisplayRecords' => intval($recordsFiltered),
            'aaData' => $data
        ];
        
        // Log para debug (remover em produção)
        // log_message('debug', 'DataTables Response: ' . json_encode($response));
        
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar processos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        // Carregar clientes e usuários para selects
        $this->load->model('clientes_model');
        $this->data['clientes'] = $this->clientes_model->get('clientes', '*', '', 0, 0, false);
        
        $this->load->model('sistema_model');
        $this->data['usuarios'] = $this->sistema_model->get('usuarios', '*', '', 0, 0, false);

        // Regras de validação
        $this->form_validation->set_rules('numeroProcesso', 'Número de Processo', 'required|trim|callback_validar_numero_processo');
        $this->form_validation->set_rules('classe', 'Classe Processual', 'trim');
        $this->form_validation->set_rules('assunto', 'Assunto', 'trim');
        $this->form_validation->set_rules('tipo_processo', 'Tipo de Processo', 'trim');
        $this->form_validation->set_rules('vara', 'Vara', 'trim');
        $this->form_validation->set_rules('comarca', 'Comarca', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|trim');
        // Cliente não é obrigatório, mas se for fornecido, deve ser válido
        $this->form_validation->set_rules('clientes_id', 'Cliente', 'trim|numeric');
        $this->form_validation->set_rules('usuarios_id', 'Advogado Responsável', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
            // Preservar partes do POST em caso de erro de validação
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
        } else {
            $numeroProcesso = $this->input->post('numeroProcesso');
            
            // Validar obrigatoriedade de advogado responsável (RN 3.3)
            $usuarios_id = $this->input->post('usuarios_id');
            if (empty($usuarios_id) || $usuarios_id == '' || $usuarios_id == '0') {
                $this->data['custom_error'] = '<div class="form_error"><p>O campo Advogado Responsável é obrigatório. Todo processo deve ter pelo menos 1 advogado responsável.</p></div>';
                // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
            }
            // Validar obrigatoriedade de polos (RN 3.2)
            elseif (!$this->validarPolosProcesso()) {
                // Erro já foi definido no método validarPolosProcesso
                if (empty($this->data['custom_error'])) {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro na validação dos polos do processo.</p></div>';
                }
                // Preservar partes do POST
                $partes_ativo_raw = $this->input->post('partes_ativo');
                $partes_passivo_raw = $this->input->post('partes_passivo');
                
                $partes_ativo = [];
                if (is_array($partes_ativo_raw)) {
                    foreach ($partes_ativo_raw as $key => $parte) {
                        if (is_array($parte) && (!empty($parte['nome']) || !empty($parte['clientes_id']))) {
                            $partes_ativo[] = [
                                'nome' => $parte['nome'] ?? '',
                                'clientes_id' => $parte['clientes_id'] ?? '',
                                'cpf_cnpj' => $parte['cpf_cnpj'] ?? '',
                                'email' => $parte['email'] ?? '',
                                'telefone' => $parte['telefone'] ?? '',
                                'tipo_polo' => $parte['tipo_polo'] ?? 'ativo'
                            ];
                        }
                    }
                }
                
                $partes_passivo = [];
                if (is_array($partes_passivo_raw)) {
                    foreach ($partes_passivo_raw as $key => $parte) {
                        if (is_array($parte) && (!empty($parte['nome']) || !empty($parte['clientes_id']))) {
                            $partes_passivo[] = [
                                'nome' => $parte['nome'] ?? '',
                                'clientes_id' => $parte['clientes_id'] ?? '',
                                'cpf_cnpj' => $parte['cpf_cnpj'] ?? '',
                                'email' => $parte['email'] ?? '',
                                'telefone' => $parte['telefone'] ?? '',
                                'tipo_polo' => $parte['tipo_polo'] ?? 'passivo'
                            ];
                        }
                    }
                }
                
                $this->data['partes_ativo_post'] = $partes_ativo;
                $this->data['partes_passivo_post'] = $partes_passivo;
            }
            // Verificar se número já existe
            elseif ($this->processos_model->numeroProcessoExists($numeroProcesso)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado no sistema.</p></div>';
                // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
            } else {
                // O model já normaliza o número do processo no método add()
                $data = [
                    'numeroProcesso' => $numeroProcesso,
                    'classe' => $this->input->post('classe') ?: null,
                    'assunto' => $this->input->post('assunto') ?: null,
                    'tipo_processo' => $this->input->post('tipo_processo') ?: null,
                    'vara' => $this->input->post('vara') ?: null,
                    'comarca' => $this->input->post('comarca') ?: null,
                    'tribunal' => $this->input->post('tribunal') ?: null,
                    'segmento' => $this->input->post('segmento') ?: null,
                    'status' => $this->input->post('status') ?: 'em_andamento',
                    'valorCausa' => $this->input->post('valorCausa') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valorCausa'))) : null,
                    'dataDistribuicao' => $this->input->post('dataDistribuicao') ?: null,
                    'clientes_id' => $this->input->post('clientes_id') && $this->input->post('clientes_id') != '' ? intval($this->input->post('clientes_id')) : null,
                    'usuarios_id' => $this->input->post('usuarios_id') && $this->input->post('usuarios_id') != '' ? intval($this->input->post('usuarios_id')) : null,
                    'observacoes' => $this->input->post('observacoes') ?: null,
                ];
                
                // Remover campos vazios (strings vazias) para salvar como NULL
                foreach ($data as $key => $value) {
                    if ($value === '') {
                        $data[$key] = null;
                    }
                }

                try {
                    $id_processo = $this->processos_model->add('processos', $data);
                    
                    if ($id_processo) {
                        // Salvar partes do processo
                        try {
                            $this->salvarPartesProcesso($id_processo);
                        } catch (Exception $e) {
                            log_message('error', 'Erro ao salvar partes do processo: ' . $e->getMessage());
                        }
                        
                        // Upload de documentos
                        try {
                            $this->uploadDocumentos($id_processo);
                        } catch (Exception $e) {
                            log_message('error', 'Erro ao fazer upload de documentos: ' . $e->getMessage());
                        }
                        
                        log_info('Adicionou um processo.');
                        redirect(site_url('processos/'));
                    } else {
                        $db_error = $this->db->error();
                        $error_msg = 'Ocorreu um erro ao salvar o processo.';
                        if (!empty($db_error['message'])) {
                            $error_msg .= ' ' . $db_error['message'];
                            log_message('error', 'Erro ao adicionar processo: ' . $db_error['message']);
                        }
                        $this->data['custom_error'] = '<div class="form_error"><p>' . $error_msg . '</p></div>';
                        // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exceção ao adicionar processo: ' . $e->getMessage());
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro inesperado: ' . $e->getMessage() . '</p></div>';
                    // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
                }
            }
        }

        $this->data['view'] = 'processos/adicionarProcesso';

        return $this->layout();
    }

    public function editar()
    {
        $id_processo = $this->uri->segment(3);
        
        if (! $id_processo || ! is_numeric($id_processo) || ! $this->processos_model->getById($id_processo)) {
            $this->session->set_flashdata('error', 'Processo não encontrado ou parâmetro inválido.');
            redirect('processos/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar processos.');
            redirect(base_url());
        }

        // Verificar se processo pode ser editado (não está encerrado)
        if (!$this->processos_model->podeEditar($id_processo)) {
            $this->session->set_flashdata('error', 'Processos encerrados não podem ser editados.');
            redirect('processos/visualizar/' . $id_processo);
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        // Carregar clientes e usuários para selects
        $this->load->model('clientes_model');
        $this->data['clientes'] = $this->clientes_model->get('clientes', '*', '', 0, 0, false);
        
        $this->load->model('sistema_model');
        $this->data['usuarios'] = $this->sistema_model->get('usuarios', '*', '', 0, 0, false);

        // Regras de validação
        $this->form_validation->set_rules('numeroProcesso', 'Número de Processo', 'required|trim|callback_validar_numero_processo');
        $this->form_validation->set_rules('classe', 'Classe Processual', 'trim');
        $this->form_validation->set_rules('assunto', 'Assunto', 'trim');
        $this->form_validation->set_rules('tipo_processo', 'Tipo de Processo', 'trim');
        $this->form_validation->set_rules('vara', 'Vara', 'trim');
        $this->form_validation->set_rules('comarca', 'Comarca', 'trim');
        $this->form_validation->set_rules('status', 'Status', 'required|trim');
        // Cliente não é obrigatório, mas se for fornecido, deve ser válido
        $this->form_validation->set_rules('clientes_id', 'Cliente', 'trim|numeric');
        $this->form_validation->set_rules('usuarios_id', 'Advogado Responsável', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
            // Preservar partes do POST em caso de erro de validação
            $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
            $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
        } else {
            $idProcesso = $this->input->post('idProcessos');
            
            // Verificar novamente se processo pode ser editado (dupla validação)
            if (!$this->processos_model->podeEditar($idProcesso)) {
                $this->session->set_flashdata('error', 'Processos encerrados não podem ser editados.');
                redirect('processos/visualizar/' . $idProcesso);
            }
            
            // Validar obrigatoriedade de advogado responsável (RN 3.3)
            $usuarios_id = $this->input->post('usuarios_id');
            if (empty($usuarios_id) || $usuarios_id == '' || $usuarios_id == '0') {
                $this->data['custom_error'] = '<div class="form_error"><p>O campo Advogado Responsável é obrigatório. Todo processo deve ter pelo menos 1 advogado responsável.</p></div>';
                // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
            }
            // Validar obrigatoriedade de polos (RN 3.2) - apenas se processo não estiver encerrado
            elseif (!$this->validarPolosProcesso()) {
                // Erro já foi definido no método validarPolosProcesso
                if (empty($this->data['custom_error'])) {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro na validação dos polos do processo.</p></div>';
                }
                // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
            }
            
            // Se houver erro de validação, recarregar dados do processo
            if (!empty($this->data['custom_error'])) {
                $this->data['result'] = $this->processos_model->getById($idProcesso);
                if ($this->data['result'] && isset($this->data['result']->numeroProcesso)) {
                    $this->data['result']->numeroProcessoFormatado = $this->processos_model->formatarNumeroProcesso($this->data['result']->numeroProcesso);
                }
                $this->load->model('partes_processo_model');
                $this->data['partes'] = $this->partes_processo_model->getByProcesso($idProcesso);
                $this->data['partes_ativo'] = $this->partes_processo_model->getByPolo($idProcesso, 'ativo');
                $this->data['partes_passivo'] = $this->partes_processo_model->getByPolo($idProcesso, 'passivo');
                if ($this->db->table_exists('documentos_processuais')) {
                    $this->db->where('processos_id', $idProcesso);
                    $this->db->order_by('dataUpload', 'desc');
                    $query = $this->db->get('documentos_processuais');
                    $this->data['documentos'] = ($query !== false) ? $query->result() : [];
                } else {
                    $this->data['documentos'] = [];
                }
                $this->data['pode_editar'] = true;
                $this->data['pode_editar_partes'] = true;
                $this->data['pode_anexar_documento'] = true;
                if (isset($this->data['result']->status)) {
                    $this->data['transicoes_permitidas'] = $this->processos_model->obterTransicoesPermitidas($this->data['result']->status);
                } else {
                    $this->data['transicoes_permitidas'] = [];
                }
                $this->data['view'] = 'processos/editarProcesso';
                return $this->layout();
            }
            
            $numeroProcesso = $this->input->post('numeroProcesso');
            
            // Validar transição de status se status foi alterado
            $processo_atual = $this->processos_model->getById($idProcesso);
            $novo_status = $this->input->post('status') ?: 'em_andamento';
            
            if (isset($processo_atual->status) && strtolower($processo_atual->status) != strtolower($novo_status)) {
                // Validar se transição é permitida
                if (!$this->processos_model->validarTransicao($processo_atual->status, $novo_status)) {
                    $transicoes = $this->processos_model->obterTransicoesPermitidas($processo_atual->status);
                    $transicoes_labels = [
                        'em_andamento' => 'Ativo',
                        'suspenso' => 'Suspenso',
                        'arquivado' => 'Arquivado',
                        'recurso' => 'Recurso',
                        'finalizado' => 'Encerrado'
                    ];
                    
                    $transicoes_texto = implode(', ', array_map(function($t) use ($transicoes_labels) {
                        return $transicoes_labels[$t] ?? ucfirst($t);
                    }, $transicoes));
                    
                    $this->data['custom_error'] = '<div class="form_error"><p>Transição de status não permitida. A partir de "' . ($transicoes_labels[$processo_atual->status] ?? ucfirst($processo_atual->status)) . '", apenas as seguintes transições são permitidas: ' . $transicoes_texto . '</p></div>';
                    
                    // Recarregar dados do processo para exibir no formulário
                    $this->data['result'] = $processo_atual;
                    $this->data['pode_editar'] = true;
                    
                    // Carregar dados necessários para a view
                    $this->load->model('clientes_model');
                    $this->data['clientes'] = $this->clientes_model->get('clientes', '*', '', 0, 0, false);
                    $this->load->model('sistema_model');
                    $this->data['usuarios'] = $this->sistema_model->get('usuarios', '*', '', 0, 0, false);
                    $this->load->model('partes_processo_model');
                    $this->data['partes'] = $this->partes_processo_model->getByProcesso($idProcesso);
                    $this->data['view'] = 'processos/editarProcesso';
                    return $this->layout();
                }
                
                // Se transição é válida, usar método de alteração de status que registra histórico
                $motivo = $this->input->post('motivo_mudanca_status') ?: '';
                $resultado_alteracao = $this->processos_model->alterarStatus($idProcesso, $novo_status, $motivo);
                
                if (!$resultado_alteracao['sucesso']) {
                    $this->data['custom_error'] = '<div class="form_error"><p>' . $resultado_alteracao['mensagem'] . '</p></div>';
                    $this->data['result'] = $processo_atual;
                    $this->data['pode_editar'] = true;
                    $this->load->model('clientes_model');
                    $this->data['clientes'] = $this->clientes_model->get('clientes', '*', '', 0, 0, false);
                    $this->load->model('sistema_model');
                    $this->data['usuarios'] = $this->sistema_model->get('usuarios', '*', '', 0, 0, false);
                    $this->load->model('partes_processo_model');
                    $this->data['partes'] = $this->partes_processo_model->getByProcesso($idProcesso);
                    $this->data['view'] = 'processos/editarProcesso';
                    return $this->layout();
                }
            }
            
            // Verificar se número já existe em outro processo
            if ($this->processos_model->numeroProcessoExists($numeroProcesso, $idProcesso)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado em outro processo.</p></div>';
                // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
            } else {
                // Normalizar número do processo antes de salvar
                $numeroProcessoNormalizado = $this->processos_model->normalizarNumeroProcesso($numeroProcesso);
                
                $data = [
                    'numeroProcesso' => $numeroProcessoNormalizado,
                    'classe' => $this->input->post('classe') ?: null,
                    'assunto' => $this->input->post('assunto') ?: null,
                    'tipo_processo' => $this->input->post('tipo_processo') ?: null,
                    'vara' => $this->input->post('vara') ?: null,
                    'comarca' => $this->input->post('comarca') ?: null,
                    'tribunal' => $this->input->post('tribunal') ?: null,
                    'segmento' => $this->input->post('segmento') ?: null,
                    'status' => $this->input->post('status') ?: 'em_andamento',
                    'valorCausa' => $this->input->post('valorCausa') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valorCausa'))) : null,
                    'dataDistribuicao' => $this->input->post('dataDistribuicao') ?: null,
                    'clientes_id' => $this->input->post('clientes_id') && $this->input->post('clientes_id') != '' ? intval($this->input->post('clientes_id')) : null,
                    'usuarios_id' => $this->input->post('usuarios_id') && $this->input->post('usuarios_id') != '' ? intval($this->input->post('usuarios_id')) : null,
                    'observacoes' => $this->input->post('observacoes') ?: null,
                ];
                
                // Remover campos vazios (strings vazias) para salvar como NULL
                foreach ($data as $key => $value) {
                    if ($value === '') {
                        $data[$key] = null;
                    }
                }

                try {
                    if ($this->processos_model->edit('processos', $data, 'idProcessos', $idProcesso) == true) {
                        // Salvar advogados responsáveis (múltiplos)
                        try {
                            $this->salvarAdvogadosProcesso($idProcesso);
                        } catch (Exception $e) {
                            log_message('error', 'Erro ao salvar advogados do processo: ' . $e->getMessage());
                        }
                        
                        // Salvar partes do processo
                        try {
                            $this->salvarPartesProcesso($idProcesso);
                        } catch (Exception $e) {
                            log_message('error', 'Erro ao salvar partes do processo: ' . $e->getMessage());
                        }
                        
                        // Upload de documentos
                        try {
                            $this->uploadDocumentos($idProcesso);
                        } catch (Exception $e) {
                            log_message('error', 'Erro ao fazer upload de documentos: ' . $e->getMessage());
                        }
                        
                        log_info('Alterou um processo. ID' . $idProcesso);
                        redirect(site_url('processos/editar/') . $idProcesso);
                    } else {
                        $db_error = $this->db->error();
                        $error_msg = 'Ocorreu um erro ao salvar o processo.';
                        if (!empty($db_error['message'])) {
                            $error_msg .= ' ' . $db_error['message'];
                            log_message('error', 'Erro ao editar processo: ' . $db_error['message']);
                        }
                        $this->data['custom_error'] = '<div class="form_error"><p>' . $error_msg . '</p></div>';
                        // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exceção ao editar processo: ' . $e->getMessage());
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro inesperado: ' . $e->getMessage() . '</p></div>';
                    // Preservar partes do POST
                $this->data['partes_ativo_post'] = $this->processarPartesPost('ativo');
                $this->data['partes_passivo_post'] = $this->processarPartesPost('passivo');
                }
            }
        }

        $this->data['result'] = $this->processos_model->getById($id_processo);
        // Formatar número de processo para exibição
        if ($this->data['result'] && isset($this->data['result']->numeroProcesso)) {
            $this->data['result']->numeroProcessoFormatado = $this->processos_model->formatarNumeroProcesso($this->data['result']->numeroProcesso);
        }
        
        // Carregar partes do processo
        $this->load->model('partes_processo_model');
        $this->data['partes'] = $this->partes_processo_model->getByProcesso($id_processo);
        $this->data['partes_ativo'] = $this->partes_processo_model->getByPolo($id_processo, 'ativo');
        $this->data['partes_passivo'] = $this->partes_processo_model->getByPolo($id_processo, 'passivo');
        
        // Carregar advogados do processo
        $this->load->model('advogados_processo_model');
        $this->data['advogados'] = $this->advogados_processo_model->getByProcesso($id_processo, true);
        
        // Preservar advogados do POST em caso de erro
        if (isset($this->data['advogados_post'])) {
            $this->data['advogados'] = [];
            foreach ($this->data['advogados_post'] as $adv) {
                if (is_array($adv) && !empty($adv['usuarios_id'])) {
                    $usuario = $this->sistema_model->get('usuarios', '*', "idUsuarios = " . intval($adv['usuarios_id']), 1, 0, true);
                    if ($usuario) {
                        $this->data['advogados'][] = (object)[
                            'id' => null,
                            'processos_id' => $id_processo,
                            'usuarios_id' => $adv['usuarios_id'],
                            'papel' => $adv['papel'] ?? 'coadjuvante',
                            'nome_usuario' => $usuario->nome ?? '',
                            'email_usuario' => $usuario->email ?? ''
                        ];
                    }
                }
            }
        }
        
        // Carregar documentos
        if ($this->db->table_exists('documentos_processuais')) {
            $this->db->where('processos_id', $id_processo);
            $this->db->order_by('dataUpload', 'desc');
            $query = $this->db->get('documentos_processuais');
            $this->data['documentos'] = ($query !== false) ? $query->result() : [];
        } else {
            $this->data['documentos'] = [];
        }
        
        // Informações para a view
        $this->data['pode_editar'] = $this->processos_model->podeEditar($id_processo);
        $this->data['pode_editar_partes'] = $this->processos_model->podeEditarPartes($id_processo);
        $this->data['pode_anexar_documento'] = $this->processos_model->podeAnexarDocumento($id_processo);
        
        // Transições permitidas para o status atual
        if (isset($this->data['result']->status)) {
            $this->data['transicoes_permitidas'] = $this->processos_model->obterTransicoesPermitidas($this->data['result']->status);
        } else {
            $this->data['transicoes_permitidas'] = [];
        }
        
        $this->data['view'] = 'processos/editarProcesso';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('adv');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar processos.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->data['result'] = $this->processos_model->getById($this->uri->segment(3));
        
        if (!$this->data['result']) {
            $this->session->set_flashdata('error', 'Processo não encontrado.');
            redirect('processos/gerenciar');
        }

        // Formatar número de processo para exibição
        if (isset($this->data['result']->numeroProcesso)) {
            $this->data['result']->numeroProcessoFormatado = $this->processos_model->formatarNumeroProcesso($this->data['result']->numeroProcesso);
        }

        // Carregar movimentações
        if ($this->db->table_exists('movimentacoes_processuais')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataMovimentacao', 'desc');
            $query = $this->db->get('movimentacoes_processuais');
            $this->data['movimentacoes'] = ($query !== false) ? $query->result() : [];
        } else {
            $this->data['movimentacoes'] = [];
        }

        // Carregar prazos
        if ($this->db->table_exists('prazos')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataVencimento', 'asc');
            $query = $this->db->get('prazos');
            $this->data['prazos'] = ($query !== false) ? $query->result() : [];
        } else {
            $this->data['prazos'] = [];
        }

        // Carregar audiências
        if ($this->db->table_exists('audiencias')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataHora', 'asc');
            $query = $this->db->get('audiencias');
            $this->data['audiencias'] = ($query !== false) ? $query->result() : [];
        } else {
            $this->data['audiencias'] = [];
        }

        // Carregar documentos
        if ($this->db->table_exists('documentos_processuais')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataUpload', 'desc');
            $query = $this->db->get('documentos_processuais');
            $this->data['documentos'] = ($query !== false) ? $query->result() : [];
        } else {
            $this->data['documentos'] = [];
        }

        // Carregar partes do processo
        $this->load->model('partes_processo_model');
        $this->data['partes'] = $this->partes_processo_model->getByProcesso($this->uri->segment(3));
        $this->data['partes_ativo'] = $this->partes_processo_model->getByPolo($this->uri->segment(3), 'ativo');
        $this->data['partes_passivo'] = $this->partes_processo_model->getByPolo($this->uri->segment(3), 'passivo');

        $this->data['view'] = 'processos/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir processos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir processo.');
            redirect(site_url('processos/gerenciar/'));
        }

        // Verificar se processo pode ser excluído (não está encerrado)
        if (!$this->processos_model->podeExcluir($id)) {
            $this->session->set_flashdata('error', 'Processos encerrados não podem ser excluídos.');
            redirect('processos/visualizar/' . $id);
        }

        $this->processos_model->delete('processos', 'idProcessos', $id);
        log_info('Removeu um processo. ID' . $id);

        $this->session->set_flashdata('success', 'Processo excluído com sucesso!');
        redirect(site_url('processos/gerenciar/'));
    }

    /**
     * Callback para validação de número de processo
     */
    public function validar_numero_processo($numero)
    {
        if (empty($numero)) {
            $this->form_validation->set_message('validar_numero_processo', 'O campo {field} é obrigatório.');
            return false;
        }

        // Normalizar número (remover formatação)
        $numero_limpo = preg_replace('/[^0-9]/', '', $numero);
        
        // Verificar se tem pelo menos alguns dígitos (mínimo 10 para ser um número válido)
        if (strlen($numero_limpo) < 10) {
            $this->form_validation->set_message('validar_numero_processo', 'O número do processo deve conter pelo menos 10 dígitos.');
            return false;
        }

        // Verificar unicidade
        $id_processo = $this->uri->segment(3); // ID do processo (null se for adicionar)
        if ($this->processos_model->numeroProcessoExists($numero, $id_processo)) {
            $this->form_validation->set_message('validar_numero_processo', 'Este número de processo já está cadastrado no sistema.');
            return false;
        }

        // Validar usando nova estrutura de validação (mas não bloquear se falhar)
        $validacao = $this->processos_model->validarNumeroProcesso($numero);
        
        // Se a validação CNJ falhar, apenas avisar mas permitir cadastro
        // (alguns processos podem não seguir o padrão CNJ rigoroso)
        if (!$validacao['valido'] && strlen($numero_limpo) != 20) {
            // Se não tem 20 dígitos e não passou na validação, avisar mas permitir
            // (pode ser um número antigo ou de outro formato)
        }

        return true;
    }

    /**
     * Valida número CNJ via AJAX
     * Retorna JSON com resultado da validação e dados extraídos
     */
    public function validar_cnj()
    {
        $numero = $this->input->post('numero');
        
        if (empty($numero)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'valido' => false,
                    'erros' => ['Número do processo não informado.'],
                    'dados' => []
                ]));
            return;
        }

        $validacao = $this->processos_model->validarNumeroProcesso($numero);
        
        $response = [
            'valido' => $validacao['valido'],
            'erros' => $validacao['erros'],
            'dados' => []
        ];

        if ($validacao['valido']) {
            // Extrair dados do CNJ
            $dados_cnj = $this->processos_model->extrairDadosCNJ($numero);
            
            if ($dados_cnj) {
                $response['dados'] = $dados_cnj;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Busca clientes via AJAX para preenchimento de partes
     */
    public function buscar_cliente()
    {
        // Permitir requisições AJAX sem autenticação de CSRF para GET
        header('Content-Type: application/json');
        
        // Select2 envia 'term', mas mantemos compatibilidade com 'termo'
        $termo = $this->input->get('term') ?: $this->input->get('termo');
        
        if (empty($termo) || strlen($termo) < 2) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $this->load->model('clientes_model');
        
        $resultado = [];
        
        try {
            // Buscar clientes APENAS por nome (sem CPF/CNPJ)
            $this->db->like('nomeCliente', $termo);
            $this->db->limit(20);
            
            $query = $this->db->get('clientes');
            
            if ($query !== false) {
                $clientes = $query->result();
                
                foreach ($clientes as $cliente) {
                    $resultado[] = [
                        'id' => $cliente->idClientes,
                        'text' => $cliente->nomeCliente, // Select2 usa 'text' para exibir
                        'nome' => $cliente->nomeCliente,
                        'documento' => isset($cliente->documento) ? $cliente->documento : '',
                        'email' => isset($cliente->email) ? $cliente->email : '',
                        'telefone' => isset($cliente->telefone) ? $cliente->telefone : '',
                        'celular' => isset($cliente->celular) ? $cliente->celular : '',
                        'tipo_pessoa' => isset($cliente->pessoa_fisica) && $cliente->pessoa_fisica == 1 ? 'fisica' : 'juridica',
                    ];
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Erro ao buscar clientes: ' . $e->getMessage());
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($resultado));
    }

    /**
     * Cadastra parte rápida (sem salvar como cliente completo)
     * Retorna JSON com dados da parte cadastrada
     */
    public function cadastrar_parte_rapida()
    {
        $this->load->model('partes_processo_model');
        
        $nome = $this->input->post('nome');
        $cpf_cnpj = $this->input->post('cpf_cnpj');
        $tipo_pessoa = $this->input->post('tipo_pessoa');
        $email = $this->input->post('email');
        $telefone = $this->input->post('telefone');
        $tipo_polo = $this->input->post('tipo_polo');
        $processos_id = $this->input->post('processos_id');

        if (empty($nome) || empty($tipo_polo)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'sucesso' => false,
                    'erro' => 'Nome e tipo de polo são obrigatórios.'
                ]));
            return;
        }

        // Determinar tipo de pessoa se não informado
        if (empty($tipo_pessoa) && !empty($cpf_cnpj)) {
            $cpf_cnpj_limpo = preg_replace('/[^0-9]/', '', $cpf_cnpj);
            $tipo_pessoa = strlen($cpf_cnpj_limpo) == 11 ? 'fisica' : 'juridica';
        }

        $data = [
            'processos_id' => $processos_id ?: null,
            'clientes_id' => null, // Parte rápida não vincula a cliente
            'tipo_polo' => $tipo_polo,
            'nome' => $nome,
            'cpf_cnpj' => $cpf_cnpj,
            'tipo_pessoa' => $tipo_pessoa ?: 'fisica',
            'email' => $email,
            'telefone' => $telefone,
            'observacoes' => $this->input->post('observacoes'),
        ];

        $id_parte = $this->partes_processo_model->add($data);

        if ($id_parte) {
            $parte = $this->partes_processo_model->getById($id_parte);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'sucesso' => true,
                    'parte' => $parte
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'sucesso' => false,
                    'erro' => 'Erro ao cadastrar parte.'
                ]));
        }
    }

    /**
     * Processa e retorna as partes do POST de forma padronizada
     * 
     * @param string $tipo_polo 'ativo' ou 'passivo'
     * @return array Array com as partes processadas
     */
    private function processarPartesPost($tipo_polo)
    {
        $campo = $tipo_polo === 'ativo' ? 'partes_ativo' : 'partes_passivo';
        $partes_raw = $this->input->post($campo);
        $partes = [];
        
        if (is_array($partes_raw) && !empty($partes_raw)) {
            foreach ($partes_raw as $key => $parte) {
                if (is_array($parte) && (!empty($parte['nome']) || !empty($parte['clientes_id']))) {
                    $partes[] = [
                        'nome' => $parte['nome'] ?? '',
                        'clientes_id' => $parte['clientes_id'] ?? '',
                        'cpf_cnpj' => $parte['cpf_cnpj'] ?? '',
                        'email' => $parte['email'] ?? '',
                        'telefone' => $parte['telefone'] ?? '',
                        'tipo_polo' => $parte['tipo_polo'] ?? $tipo_polo
                    ];
                }
            }
        }
        
        return $partes;
    }
    
    /**
     * Valida obrigatoriedade dos polos do processo (RN 3.2)
     * Retorna true se válido, false caso contrário
     * Define mensagem de erro em $this->data['custom_error'] se inválido
     * 
     * @return bool
     */
    private function validarPolosProcesso()
    {
        // Verificar se há partes no POST como array direto
        $partes_ativo = $this->input->post('partes_ativo');
        $partes_passivo = $this->input->post('partes_passivo');
        
        $count_ativo = 0;
        $count_passivo = 0;
        
        // Contar partes válidas do polo ativo
        if (is_array($partes_ativo) && !empty($partes_ativo)) {
            foreach ($partes_ativo as $parte) {
                if ((!empty($parte['nome']) || !empty($parte['clientes_id']))) {
                    $count_ativo++;
                }
            }
        }
        
        // Contar partes válidas do polo passivo
        if (is_array($partes_passivo) && !empty($partes_passivo)) {
            foreach ($partes_passivo as $parte) {
                if ((!empty($parte['nome']) || !empty($parte['clientes_id']))) {
                    $count_passivo++;
                }
            }
        }
        
        // Se não encontrou partes como array, tentar buscar nos campos individuais do POST
        if ($count_ativo == 0) {
            // Buscar campos como: nome_parte_ativo_0, cliente_id_ativo_0, etc.
            $partes_encontradas = [];
            foreach ($_POST as $key => $value) {
                if (preg_match('/_ativo_(\d+)$/', $key, $matches) && !empty($value)) {
                    $index = $matches[1];
                    if (!isset($partes_encontradas[$index])) {
                        $partes_encontradas[$index] = false;
                    }
                    // Verificar se tem nome ou cliente_id válido
                    if (preg_match('/nome/', $key) || preg_match('/cliente/', $key)) {
                        $partes_encontradas[$index] = true;
                    }
                }
            }
            $count_ativo = count(array_filter($partes_encontradas));
        }
        
        if ($count_passivo == 0) {
            $partes_encontradas = [];
            foreach ($_POST as $key => $value) {
                if (preg_match('/_passivo_(\d+)$/', $key, $matches) && !empty($value)) {
                    $index = $matches[1];
                    if (!isset($partes_encontradas[$index])) {
                        $partes_encontradas[$index] = false;
                    }
                    if (preg_match('/nome/', $key) || preg_match('/cliente/', $key)) {
                        $partes_encontradas[$index] = true;
                    }
                }
            }
            $count_passivo = count(array_filter($partes_encontradas));
        }
        
        // Validar polo ativo (RN 3.2 - obrigatório)
        if ($count_ativo < 1) {
            $this->data['custom_error'] = '<div class="form_error"><p>Processo deve ter pelo menos 1 parte no polo ativo.</p></div>';
            return false;
        }
        
        // Validar polo passivo (RN 3.2 - obrigatório)
        if ($count_passivo < 1) {
            $this->data['custom_error'] = '<div class="form_error"><p>Processo deve ter pelo menos 1 parte no polo passivo.</p></div>';
            return false;
        }
        
        return true;
    }

    /**
     * Salva os advogados responsáveis do processo (múltiplos com papéis)
     */
    private function salvarAdvogadosProcesso($processos_id)
    {
        $this->load->model('advogados_processo_model');
        
        // Verificar se processo pode ser editado (não está encerrado)
        if (!$this->processos_model->podeEditar($processos_id)) {
            log_message('info', "Tentativa de modificar advogados de processo encerrado ID: $processos_id");
            return false;
        }
        
        // Obter advogados do POST
        $advogados_post = $this->input->post('advogados');
        $usuarios_id_antigo = $this->input->post('usuarios_id'); // Formato antigo (compatibilidade)
        
        // Se não há advogados no novo formato, mas há usuarios_id (formato antigo), migrar
        if ((!is_array($advogados_post) || empty($advogados_post)) && !empty($usuarios_id_antigo) && $usuarios_id_antigo != '' && $usuarios_id_antigo != '0') {
            // Migrar formato antigo para novo
            $advogados_post = [
                [
                    'usuarios_id' => $usuarios_id_antigo,
                    'papel' => 'principal',
                    'observacoes' => 'Migrado do campo usuarios_id'
                ]
            ];
        }
        
        if (!is_array($advogados_post) || empty($advogados_post)) {
            log_message('warning', "Nenhum advogado fornecido para processo ID: $processos_id");
            return false;
        }
        
        // Validar que há pelo menos 1 advogado principal
        $tem_principal = false;
        foreach ($advogados_post as $adv) {
            if (is_array($adv) && !empty($adv['usuarios_id']) && isset($adv['papel']) && strtolower($adv['papel']) === 'principal') {
                $tem_principal = true;
                break;
            }
        }
        
        if (!$tem_principal) {
            throw new Exception('Processo deve ter pelo menos 1 advogado com papel Principal.');
        }
        
        // Remover advogados antigos (soft delete)
        $this->advogados_processo_model->deleteByProcesso($processos_id);
        
        // Adicionar novos advogados
        $advogados_para_salvar = [];
        foreach ($advogados_post as $adv) {
            if (is_array($adv) && !empty($adv['usuarios_id']) && $adv['usuarios_id'] != '' && $adv['usuarios_id'] != '0') {
                $papel = isset($adv['papel']) ? strtolower($adv['papel']) : 'coadjuvante';
                $papeis_validos = ['principal', 'coadjuvante', 'estagiario'];
                
                if (!in_array($papel, $papeis_validos)) {
                    $papel = 'coadjuvante';
                }
                
                $advogados_para_salvar[] = [
                    'processos_id' => $processos_id,
                    'usuarios_id' => intval($adv['usuarios_id']),
                    'papel' => $papel,
                    'data_atribuicao' => date('Y-m-d H:i:s'),
                    'ativo' => 1,
                    'notificado' => 0,
                    'observacoes' => isset($adv['observacoes']) ? $adv['observacoes'] : null
                ];
            }
        }
        
        if (!empty($advogados_para_salvar)) {
            // Garantir que só há 1 principal
            $principal_encontrado = false;
            foreach ($advogados_para_salvar as &$adv) {
                if ($adv['papel'] === 'principal') {
                    if ($principal_encontrado) {
                        // Já tem um principal, mudar este para coadjuvante
                        $adv['papel'] = 'coadjuvante';
                    } else {
                        $principal_encontrado = true;
                    }
                }
            }
            
            $ids = $this->advogados_processo_model->addMultiple($processos_id, $advogados_para_salvar);
            
            // Enviar notificações por email para novos advogados
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    try {
                        $this->notificarAdvogadoAtribuido($id);
                    } catch (Exception $e) {
                        log_message('error', 'Erro ao notificar advogado: ' . $e->getMessage());
                    }
                }
            }
            
            return $ids;
        }
        
        return false;
    }
    
    /**
     * Envia notificação por email ao advogado quando é atribuído a um processo
     */
    private function notificarAdvogadoAtribuido($advogado_processo_id)
    {
        $this->load->model('advogados_processo_model');
        $advogado = $this->advogados_processo_model->getById($advogado_processo_id);
        
        if (!$advogado || $advogado->notificado == 1) {
            return false; // Já foi notificado
        }
        
        // Carregar dados do processo
        $processo = $this->processos_model->getById($advogado->processos_id);
        if (!$processo) {
            return false;
        }
        
        // Carregar dados do emitente
        $this->load->model('sistema_model');
        $emitente = $this->sistema_model->getEmitente();
        
        // Configurar email
        $this->load->library('email');
        $this->load->config('email');
        
        $this->email->from($this->config->item('smtp_user'), $this->config->item('app_name'));
        $this->email->to($advogado->email_usuario);
        $this->email->subject('Você foi atribuído como advogado responsável - Processo ' . ($processo->numeroProcesso ?? 'N/A'));
        
        // Preparar dados para o template
        $dados_email = [
            'advogado' => $advogado,
            'processo' => $processo,
            'emitente' => $emitente,
            'papel_label' => [
                'principal' => 'Responsável Principal',
                'coadjuvante' => 'Coadjuvante',
                'estagiario' => 'Estagiário'
            ][$advogado->papel] ?? 'Advogado'
        ];
        
        // Tentar carregar template específico
        $email_body = $this->load->view('emails/advogado_atribuido', $dados_email, true);
        
        // Se não encontrou template, usar genérico
        if (empty($email_body) || strpos($email_body, 'Unable to load') !== false) {
            $email_body = "
                <html>
                <body>
                    <h2>Você foi atribuído como advogado responsável</h2>
                    <p>Olá {$advogado->nome_usuario},</p>
                    <p>Você foi atribuído como <strong>{$dados_email['papel_label']}</strong> no processo:</p>
                    <ul>
                        <li><strong>Número:</strong> " . ($processo->numeroProcesso ?? 'N/A') . "</li>
                        <li><strong>Classe:</strong> " . ($processo->classe ?? 'N/A') . "</li>
                        <li><strong>Assunto:</strong> " . ($processo->assunto ?? 'N/A') . "</li>
                    </ul>
                    <p>Acesse o sistema para mais detalhes.</p>
                </body>
                </html>
            ";
        }
        
        $this->email->message($email_body);
        
        if ($this->email->send(true)) {
            // Marcar como notificado
            $this->advogados_processo_model->marcarNotificado($advogado_processo_id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Salva partes do processo (ativo e passivo)
     * Chamado após salvar o processo
     */
    private function salvarPartesProcesso($processos_id)
    {
        $this->load->model('partes_processo_model');
        
        // Remover partes existentes
        $this->partes_processo_model->deleteByProcesso($processos_id);
        
        // Salvar partes do polo ativo
        $partes_ativo = $this->input->post('partes_ativo');
        if (is_array($partes_ativo)) {
            foreach ($partes_ativo as $parte) {
                if (!empty($parte['nome']) || !empty($parte['clientes_id'])) {
                    $data_parte = [
                        'processos_id' => $processos_id,
                        'tipo_polo' => 'ativo',
                        'clientes_id' => !empty($parte['clientes_id']) ? $parte['clientes_id'] : null,
                        'nome' => $parte['nome'] ?? null,
                        'cpf_cnpj' => $parte['cpf_cnpj'] ?? null,
                        'tipo_pessoa' => $parte['tipo_pessoa'] ?? 'fisica',
                        'email' => $parte['email'] ?? null,
                        'telefone' => $parte['telefone'] ?? null,
                        'observacoes' => $parte['observacoes'] ?? null,
                    ];
                    $this->partes_processo_model->add($data_parte);
                }
            }
        }
        
        // Salvar partes do polo passivo
        $partes_passivo = $this->input->post('partes_passivo');
        if (is_array($partes_passivo)) {
            foreach ($partes_passivo as $parte) {
                if (!empty($parte['nome']) || !empty($parte['clientes_id'])) {
                    $data_parte = [
                        'processos_id' => $processos_id,
                        'tipo_polo' => 'passivo',
                        'clientes_id' => !empty($parte['clientes_id']) ? $parte['clientes_id'] : null,
                        'nome' => $parte['nome'] ?? null,
                        'cpf_cnpj' => $parte['cpf_cnpj'] ?? null,
                        'tipo_pessoa' => $parte['tipo_pessoa'] ?? 'fisica',
                        'email' => $parte['email'] ?? null,
                        'telefone' => $parte['telefone'] ?? null,
                        'observacoes' => $parte['observacoes'] ?? null,
                    ];
                    $this->partes_processo_model->add($data_parte);
                }
            }
        }
    }

    /**
     * Faz upload de documentos para o processo
     */
    private function uploadDocumentos($processos_id)
    {
        if (!$this->db->table_exists('documentos_processuais')) {
            return;
        }

        if (!isset($_FILES['documentos']) || empty($_FILES['documentos']['name'][0])) {
            return;
        }

        $date = date('Y-m');
        $upload_path = './assets/documentos_processuais/' . $date . '/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png|txt|rtf';
        $config['max_size'] = 10240; // 10MB
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        $files = $_FILES['documentos'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (!empty($files['name'][$i])) {
                $_FILES['documento']['name'] = $files['name'][$i];
                $_FILES['documento']['type'] = $files['type'][$i];
                $_FILES['documento']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['documento']['error'] = $files['error'][$i];
                $_FILES['documento']['size'] = $files['size'][$i];

                $this->upload->initialize($config);

                if ($this->upload->do_upload('documento')) {
                    $upload_data = $this->upload->data();
                    
                    $usuario_id = $this->session->userdata('idUsuarios');
                    
                    $documento_data = [
                        'processos_id' => $processos_id,
                        'titulo' => $upload_data['orig_name'],
                        'descricao' => null,
                        'arquivo' => $upload_data['file_name'],
                        'tipo_documento' => $this->input->post('tipo_documento') ?: 'documento',
                        'dataUpload' => date('Y-m-d H:i:s'),
                        'usuarios_id' => $usuario_id,
                        'tamanho' => $upload_data['file_size'],
                        'mime_type' => $upload_data['file_type'],
                    ];

                    $this->db->insert('documentos_processuais', $documento_data);
                }
            }
        }
    }
}

