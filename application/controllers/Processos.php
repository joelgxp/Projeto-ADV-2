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
        $this->form_validation->set_rules('clientes_id', 'Cliente', 'trim');
        $this->form_validation->set_rules('usuarios_id', 'Advogado Responsável', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $numeroProcesso = $this->input->post('numeroProcesso');
            
            // Verificar se número já existe
            if ($this->processos_model->numeroProcessoExists($numeroProcesso)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado no sistema.</p></div>';
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
                        
                        $this->session->set_flashdata('success', 'Processo adicionado com sucesso!');
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
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exceção ao adicionar processo: ' . $e->getMessage());
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro inesperado: ' . $e->getMessage() . '</p></div>';
                }
            }
        }

        $this->data['view'] = 'processos/adicionarProcesso';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->processos_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Processo não encontrado ou parâmetro inválido.');
            redirect('processos/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar processos.');
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
        $this->form_validation->set_rules('clientes_id', 'Cliente', 'trim');
        $this->form_validation->set_rules('usuarios_id', 'Advogado Responsável', 'trim');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $numeroProcesso = $this->input->post('numeroProcesso');
            $idProcesso = $this->input->post('idProcessos');
            
            // Verificar se número já existe em outro processo
            if ($this->processos_model->numeroProcessoExists($numeroProcesso, $idProcesso)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado em outro processo.</p></div>';
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
                        
                        $this->session->set_flashdata('success', 'Processo editado com sucesso!');
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
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exceção ao editar processo: ' . $e->getMessage());
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro inesperado: ' . $e->getMessage() . '</p></div>';
                }
            }
        }

        $this->data['result'] = $this->processos_model->getById($this->uri->segment(3));
        // Formatar número de processo para exibição
        if ($this->data['result'] && isset($this->data['result']->numeroProcesso)) {
            $this->data['result']->numeroProcessoFormatado = $this->processos_model->formatarNumeroProcesso($this->data['result']->numeroProcesso);
        }
        
        // Carregar partes do processo
        $this->load->model('partes_processo_model');
        $this->data['partes'] = $this->partes_processo_model->getByProcesso($this->uri->segment(3));
        $this->data['partes_ativo'] = $this->partes_processo_model->getByPolo($this->uri->segment(3), 'ativo');
        $this->data['partes_passivo'] = $this->partes_processo_model->getByPolo($this->uri->segment(3), 'passivo');
        
        // Carregar documentos
        if ($this->db->table_exists('documentos_processuais')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataUpload', 'desc');
            $this->data['documentos'] = $this->db->get('documentos_processuais')->result();
        } else {
            $this->data['documentos'] = [];
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
            $this->data['movimentacoes'] = $this->db->get('movimentacoes_processuais')->result();
        } else {
            $this->data['movimentacoes'] = [];
        }

        // Carregar prazos
        if ($this->db->table_exists('prazos')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataVencimento', 'asc');
            $this->data['prazos'] = $this->db->get('prazos')->result();
        } else {
            $this->data['prazos'] = [];
        }

        // Carregar audiências
        if ($this->db->table_exists('audiencias')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataHora', 'asc');
            $this->data['audiencias'] = $this->db->get('audiencias')->result();
        } else {
            $this->data['audiencias'] = [];
        }

        // Carregar documentos
        if ($this->db->table_exists('documentos_processuais')) {
            $this->db->where('processos_id', $this->uri->segment(3));
            $this->db->order_by('dataUpload', 'desc');
            $this->data['documentos'] = $this->db->get('documentos_processuais')->result();
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
        $termo = $this->input->get('termo');
        
        if (empty($termo) || strlen($termo) < 2) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $this->load->model('clientes_model');
        
        // Buscar clientes por nome, CPF/CNPJ ou email
        $this->db->group_start();
        $this->db->like('nomeCliente', $termo);
        $this->db->or_like('documento', $termo);
        $this->db->or_like('email', $termo);
        $this->db->group_end();
        $this->db->limit(20);
        
        $query = $this->db->get('clientes');
        $clientes = $query->result();

        $resultado = [];
        foreach ($clientes as $cliente) {
            $resultado[] = [
                'id' => $cliente->idClientes,
                'nome' => $cliente->nomeCliente,
                'documento' => isset($cliente->documento) ? $cliente->documento : '',
                'email' => isset($cliente->email) ? $cliente->email : '',
                'telefone' => isset($cliente->telefone) ? $cliente->telefone : '',
                'celular' => isset($cliente->celular) ? $cliente->celular : '',
                'tipo_pessoa' => isset($cliente->pessoa_fisica) && $cliente->pessoa_fisica == 1 ? 'fisica' : 'juridica',
            ];
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

