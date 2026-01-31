<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PecasGeradas extends MY_Controller
{
    private const LIMITE_GERACOES_DIA = 50;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('pecas_geradas_model');
        $this->load->model('modelos_pecas_model');
        $this->data['menuPecasGeradas'] = 'pecas-geradas';
    }

    /**
     * Verifica permissão gPeticaoIA
     */
    private function verificarPermissaoGerar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'gPeticaoIA')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar petições com IA.');
            redirect(base_url());
        }
    }

    /**
     * Verifica se usuário pode aprovar (advogado ou admin)
     */
    private function podeAprovar(): bool
    {
        $permissoes_id = $this->session->userdata('permissoes_id');
        return in_array($permissoes_id, [1, 2], true);
    }

    public function index()
    {
        $this->listar();
    }

    public function listar()
    {
        $this->verificarPermissaoGerar();

        $this->load->library('pagination');

        $where = [];
        $tipo = $this->input->get('tipo');
        $status = $this->input->get('status');
        $processo = $this->input->get('processo');

        if ($tipo) {
            $where['pecas_geradas.tipo_peca'] = $tipo;
        }
        if ($status) {
            $where['pecas_geradas.status'] = $status;
        }
        if ($processo) {
            $where['pecas_geradas.processos_id'] = $processo;
        }

        $this->data['configuration']['base_url'] = site_url('pecasGeradas/listar/');
        $this->data['configuration']['total_rows'] = $this->pecas_geradas_model->count($where);
        $this->data['configuration']['page_query_string'] = true;

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->pecas_geradas_model->get(
            $where,
            $this->data['configuration']['per_page'],
            $this->input->get('per_page') ?: 0
        );

        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['filtros'] = ['tipo' => $tipo, 'status' => $status, 'processo' => $processo];
        $this->data['view'] = 'pecas_geradas/listar';

        return $this->layout();
    }

    /**
     * Formulário de geração
     */
    public function gerar()
    {
        $this->verificarPermissaoGerar();

        $processos_id = $this->input->get('processos_id') ?: $this->uri->segment(3);
        $prazos_id = $this->input->get('prazos_id');
        $contratos_id = $this->input->get('contratos_id');

        $this->load->model('processos_model');
        $this->load->model('clientes_model');
        $this->load->model('prazos_model');
        $this->load->model('Contratos_model');

        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false);
        $this->data['clientes'] = $this->clientes_model->get('clientes', '*', '', 0, 0, false);
        $this->data['modelos'] = $this->modelos_pecas_model->get(['modelos_pecas.ativo' => 1]);
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['areas'] = $this->modelos_pecas_model->getAreas();

        $this->data['processos_id'] = $processos_id;
        $this->data['prazos_id'] = $prazos_id;
        $this->data['contratos_id'] = $contratos_id;

        $processo = null;
        $documentos = [];
        $prazo = null;
        $contrato = null;

        if ($processos_id) {
            $processo = $this->processos_model->getById($processos_id);
            if ($processo && $this->db->table_exists('documentos_processuais')) {
                $this->db->where('processos_id', $processos_id);
                $documentos = $this->db->get('documentos_processuais')->result();
            }
        }
        if ($prazos_id) {
            $prazo = $this->prazos_model->getById($prazos_id);
        }
        if ($contratos_id) {
            $contrato = $this->Contratos_model->getById($contratos_id);
        }

        $this->data['processo'] = $processo;
        $this->data['documentos'] = $documentos;
        $this->data['prazo'] = $prazo;
        $this->data['contrato'] = $contrato;
        $this->data['custom_error'] = '';
        $this->data['view'] = 'pecas_geradas/gerador';

        return $this->layout();
    }

    /**
     * Escreve em arquivo de debug (funciona mesmo quando log do CI não grava)
     */
    private function _pecasDebugLog(string $msg): void
    {
        $path = FCPATH . 'application/logs/pecas_debug.log';
        $line = date('Y-m-d H:i:s') . ' | ' . $msg . "\n";
        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Executa a geração (POST)
     */
    public function executar_geracao()
    {
        $this->verificarPermissaoGerar();

        // Captura fatal errors e grava em arquivo próprio
        register_shutdown_function(function () {
            $err = error_get_last();
            if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $path = FCPATH . 'application/logs/pecas_debug.log';
                $msg = date('Y-m-d H:i:s') . ' | FATAL: ' . ($err['message'] ?? '') . ' em ' . ($err['file'] ?? '') . ':' . ($err['line'] ?? '') . "\n";
                @file_put_contents($path, $msg, FILE_APPEND | LOCK_EX);
            }
        });

        $this->_pecasDebugLog('INICIO executar_geracao');

        // API de IA pode demorar 30-90s; evita timeout em produção
        set_time_limit(120);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '256M');
        }

        if ($this->input->method() !== 'post') {
            redirect('pecasGeradas/gerar');
        }

        $tese_principal = $this->input->post('tese_principal');
        if (empty(trim($tese_principal))) {
            $this->session->set_flashdata('error', 'O campo Tese principal é obrigatório.');
            redirect($this->input->post('redirect_url') ?: 'pecasGeradas/gerar');
        }

        $processos_id = $this->input->post('processos_id') ?: null;
        $prazos_id = $this->input->post('prazos_id') ?: null;
        $contratos_id = $this->input->post('contratos_id') ?: null;
        $clientes_id = $this->input->post('clientes_id') ?: null;
        $contexto_manual = $this->input->post('contexto_manual') ?: null;

        if (!$processos_id && !$prazos_id && !$contratos_id && !$clientes_id && empty(trim($contexto_manual ?? ''))) {
            $this->session->set_flashdata('error', 'Informe pelo menos: processo, prazo, contrato, cliente ou contexto textual.');
            redirect($this->input->post('redirect_url') ?: 'pecasGeradas/gerar');
        }

        $user_id = $this->session->userdata('id_admin');
        if ($this->db->table_exists('logs_geracao_pecas')) {
            $this->db->where('usuarios_id', $user_id);
            $this->db->where('DATE(dataCadastro)', date('Y-m-d'));
            $count = $this->db->count_all_results('logs_geracao_pecas');
            if ($count >= self::LIMITE_GERACOES_DIA) {
                $this->session->set_flashdata('error', 'Limite de ' . self::LIMITE_GERACOES_DIA . ' gerações por dia atingido. Tente novamente amanhã.');
                redirect('pecasGeradas/listar');
            }
        }

        try {
            $this->load->library('PeticaoGenerator');
            $this->_pecasDebugLog('PeticaoGenerator carregado OK');
        } catch (Throwable $e) {
            $this->_pecasDebugLog('ERRO carregar PeticaoGenerator: ' . $e->getMessage());
            log_message('error', 'PecasGeradas executar_geracao: Erro ao carregar PeticaoGenerator - ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            $this->session->set_flashdata('error', 'Erro técnico: ' . $e->getMessage() . '. Verifique application/logs/pecas_debug.log e composer install.');
            redirect($this->input->post('redirect_url') ?: 'pecasGeradas/gerar');
        }

        $params = [
            'tipo_peca' => $this->input->post('tipo_peca') ?: 'peticao_simples',
            'tese_principal' => $tese_principal,
            'pontos_enfatizar' => $this->input->post('pontos_enfatizar'),
            'tom' => $this->input->post('tom') ?: 'tecnico',
            'modelos_pecas_id' => $this->input->post('modelos_pecas_id') ?: null,
            'processos_id' => $processos_id,
            'prazos_id' => $prazos_id,
            'contratos_id' => $contratos_id,
            'clientes_id' => $clientes_id,
            'contexto_manual' => $contexto_manual,
            'incluir_movimentacoes' => (bool) $this->input->post('incluir_movimentacoes'),
            'anexos_ids' => $this->input->post('anexos_ids') ?: [],
        ];

        $this->_pecasDebugLog('Chamando peticaogenerator->gerar...');
        try {
            $resultado = $this->peticaogenerator->gerar($params);
            $this->_pecasDebugLog('gerar retornou: sucesso=' . ($resultado['sucesso'] ? '1' : '0') . ' erro=' . ($resultado['erro'] ?? ''));
        } catch (Throwable $e) {
            $this->_pecasDebugLog('ERRO gerar: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', 'PecasGeradas executar_geracao: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
            $this->session->set_flashdata('error', 'Erro na geração: ' . $e->getMessage() . '. Consulte application/logs/pecas_debug.log');
            redirect($this->input->post('redirect_url') ?: 'pecasGeradas/gerar');
        }

        if (!$resultado['sucesso']) {
            $this->_pecasDebugLog('Falha: ' . ($resultado['erro'] ?? ''));
            $this->session->set_flashdata('error', $resultado['erro']);
            redirect($this->input->post('redirect_url') ?: 'pecasGeradas/gerar');
        }

        // Sanitizar: converter strings vazias em null para campos INT (evita erro MySQL)
        $pecaData = [
            'processos_id' => $processos_id ? (int) $processos_id : null,
            'prazos_id' => $prazos_id ? (int) $prazos_id : null,
            'contratos_id' => $contratos_id ? (int) $contratos_id : null,
            'tipo_peca' => $params['tipo_peca'],
            'status' => 'rascunho_ia',
            'modelos_pecas_id' => $params['modelos_pecas_id'] ? (int) $params['modelos_pecas_id'] : null,
            'usuarios_gerador_id' => $user_id ? (int) $user_id : null,
            'tese_principal' => $tese_principal ?: null,
            'pontos_enfatizar' => $params['pontos_enfatizar'] ?: null,
            'tom' => $params['tom'] ?: null,
            'clientes_id' => $clientes_id ? (int) $clientes_id : null,
            'contexto_manual' => $contexto_manual ?: null,
        ];

        $peca_id = $this->pecas_geradas_model->add($pecaData);
        if (!$peca_id) {
            $this->_pecasDebugLog('FALHA add peca: ' . ($this->pecas_geradas_model->getLastError() ?? 'erro desconhecido'));
            $this->session->set_flashdata('error', 'Erro ao salvar a peça gerada. Verifique application/logs/pecas_debug.log para detalhes.');
            redirect('pecasGeradas/listar');
        }

        $versao_id = $this->pecas_geradas_model->addVersao($peca_id, $resultado['conteudo'], 'ia', $user_id);

        $this->pecas_geradas_model->addLogGeracao(
            $peca_id,
            $versao_id,
            ($resultado['prompt_system'] ?? '') . "\n\n" . ($resultado['prompt_user'] ?? ''),
            $params,
            $resultado['conteudo'],
            $this->peticaogenerator->getModelo(),
            $this->peticaogenerator->isChamadaLocal() ? 1 : 0,
            $user_id
        );

        $this->_pecasDebugLog('SUCESSO peca_id=' . $peca_id);
        log_info('Gerou petição com IA (ID: ' . $peca_id . ')');
        $this->session->set_flashdata('success', 'Petição gerada com sucesso. Revise e aprove antes de usar.');
        redirect('pecasGeradas/visualizar/' . $peca_id);
    }

    /**
     * Visualizar/editar peça
     */
    public function visualizar($id)
    {
        $this->verificarPermissaoGerar();

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca) {
            $this->session->set_flashdata('error', 'Peça não encontrada.');
            redirect('pecasGeradas/listar');
        }

        $ultimaVersao = $this->pecas_geradas_model->getUltimaVersao($id);
        $versaoIA = $this->pecas_geradas_model->getVersaoIA($id);
        $versaoAprovada = $this->pecas_geradas_model->getVersaoAprovada($id);

        $checklist = $this->pecas_geradas_model->getChecklist($id);
        $itensChecklist = $this->pecas_geradas_model->getChecklistItens();
        $marcados = [];
        foreach ($checklist as $c) {
            $marcados[$c->item] = (bool) $c->marcado;
        }

        $this->load->model('sistema_model');
        $usuario = $this->sistema_model->get('usuarios', '*', ['idUsuarios' => $this->session->userdata('id_admin')], 0, 0, true);

        $this->data['peca'] = $peca;
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['ultima_versao'] = $ultimaVersao;
        $this->data['versao_ia'] = $versaoIA;
        $this->data['versao_aprovada'] = $versaoAprovada;
        $this->data['conteudo_atual'] = $ultimaVersao ? $ultimaVersao->conteudo : '';
        $this->data['itens_checklist'] = $itensChecklist;
        $this->data['checklist_marcados'] = $marcados;
        $this->data['checklist_completo'] = $this->pecas_geradas_model->checklistCompleto($id);
        $this->data['pode_aprovar'] = $this->podeAprovar();
        $this->data['usuario'] = $usuario;
        $this->data['custom_error'] = '';
        $this->data['view'] = 'pecas_geradas/visualizar';

        return $this->layout();
    }

    /**
     * Salvar edição manual
     */
    public function salvar_edicao($id)
    {
        $this->verificarPermissaoGerar();

        if ($this->input->method() !== 'post') {
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca) {
            $this->session->set_flashdata('error', 'Peça não encontrada.');
            redirect('pecasGeradas/listar');
        }

        if ($peca->status === 'aprovado') {
            $this->session->set_flashdata('error', 'Peça já aprovada. Alterações via IA criam nova versão.');
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $conteudo = $this->input->post('conteudo');
        $user_id = $this->session->userdata('id_admin');

        $ultimaVersao = $this->pecas_geradas_model->getUltimaVersao($id);
        $diff = $ultimaVersao ? $this->calcularDiff($ultimaVersao->conteudo, $conteudo) : null;

        $this->pecas_geradas_model->addVersao($id, $conteudo, 'editado_manual', $user_id, $diff);
        $this->pecas_geradas_model->edit(['status' => 'em_revisao'], $id);

        log_info('Editou petição gerada por IA (ID: ' . $id . ')');
        $this->session->set_flashdata('success', 'Edição salva com sucesso.');
        redirect('pecasGeradas/visualizar/' . $id);
    }

    /**
     * Refinar com IA (AJAX)
     */
    public function refinar()
    {
        $this->verificarPermissaoGerar();

        if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
            show_404();
        }

        $id = (int) $this->input->post('id');
        $instrucao = $this->input->post('instrucao');
        $conteudo = $this->input->post('conteudo');

        if (!$id || !$instrucao || !$conteudo) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'sucesso' => false,
                'erro' => 'Parâmetros inválidos.',
            ]));
            return;
        }

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca || $peca->status === 'aprovado') {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'sucesso' => false,
                'erro' => 'Peça não encontrada ou já aprovada.',
            ]));
            return;
        }

        $this->load->library('PeticaoGenerator');
        $resultado = $this->peticaogenerator->refinar($conteudo, $instrucao);

        if (!$resultado['sucesso']) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'sucesso' => false,
                'erro' => $resultado['erro'],
            ]));
            return;
        }

        $user_id = $this->session->userdata('id_admin');
        $this->pecas_geradas_model->addVersao($id, $resultado['conteudo'], 'editado_manual', $user_id);
        $this->pecas_geradas_model->edit(['status' => 'em_revisao'], $id);

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'sucesso' => true,
            'conteudo' => $resultado['conteudo'],
        ]));
    }

    /**
     * Salvar checklist
     */
    public function salvar_checklist($id)
    {
        $this->verificarPermissaoGerar();

        if ($this->input->method() !== 'post') {
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca) {
            $this->session->set_flashdata('error', 'Peça não encontrada.');
            redirect('pecasGeradas/listar');
        }

        $itens = $this->input->post('checklist') ?: [];
        $user_id = $this->session->userdata('id_admin');

        $this->pecas_geradas_model->saveChecklist($id, $itens, $user_id);

        $this->session->set_flashdata('success', 'Checklist salvo.');
        redirect('pecasGeradas/visualizar/' . $id);
    }

    /**
     * Aprovar peça
     */
    public function aprovar($id)
    {
        $this->verificarPermissaoGerar();

        if (!$this->podeAprovar()) {
            $this->session->set_flashdata('error', 'Apenas advogados podem aprovar peças.');
            redirect('pecasGeradas/visualizar/' . $id);
        }

        if ($this->input->method() !== 'post') {
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca) {
            $this->session->set_flashdata('error', 'Peça não encontrada.');
            redirect('pecasGeradas/listar');
        }

        if (!$this->pecas_geradas_model->checklistCompleto($id)) {
            $this->session->set_flashdata('error', 'É obrigatório marcar todos os itens do checklist antes de aprovar.');
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $ultimaVersao = $this->pecas_geradas_model->getUltimaVersao($id);
        $conteudo = $ultimaVersao ? $ultimaVersao->conteudo : '';

        $user_id = $this->session->userdata('id_admin');
        $this->pecas_geradas_model->addVersao($id, $conteudo, 'aprovado', $user_id);
        $this->pecas_geradas_model->edit([
            'status' => 'aprovado',
            'usuarios_aprovador_id' => $user_id,
            'data_aprovacao' => date('Y-m-d H:i:s'),
        ], $id);

        log_info('Aprovou petição gerada por IA (ID: ' . $id . ')');
        $this->session->set_flashdata('success', 'Peça aprovada com sucesso. Pode ser vinculada a protocolo ou exportada.');
        redirect('pecasGeradas/visualizar/' . $id);
    }

    /**
     * Exportar para DOC/PDF (apenas aprovadas)
     */
    public function exportar($id)
    {
        $this->verificarPermissaoGerar();

        $peca = $this->pecas_geradas_model->getById($id);
        if (!$peca) {
            $this->session->set_flashdata('error', 'Peça não encontrada.');
            redirect('pecasGeradas/listar');
        }

        if ($peca->status !== 'aprovado') {
            $this->session->set_flashdata('error', 'É obrigatória a revisão e aprovação por advogado antes de exportar.');
            redirect('pecasGeradas/visualizar/' . $id);
        }

        $versaoAprovada = $this->pecas_geradas_model->getVersaoAprovada($id);
        $conteudo = $versaoAprovada ? $versaoAprovada->conteudo : '';

        $formato = $this->input->get('formato') ?: 'txt';
        if ($formato === 'txt') {
            $this->output->set_content_type('text/plain; charset=utf-8');
            $this->output->set_header('Content-Disposition: attachment; filename="peca_' . $id . '.txt"');
            $this->output->set_output($conteudo);
            return;
        }

        $this->session->set_flashdata('error', 'Formato não suportado.');
        redirect('pecasGeradas/visualizar/' . $id);
    }

    /**
     * Dashboard de métricas
     */
    public function dashboard()
    {
        $this->verificarPermissaoGerar();

        $periodo_inicio = $this->input->get('inicio') ?: date('Y-m-01');
        $periodo_fim = $this->input->get('fim') ?: date('Y-m-t');
        $tipo = $this->input->get('tipo');
        $advogado_id = $this->input->get('advogado');

        $where = [];
        $where['pecas_geradas.dataCadastro >='] = $periodo_inicio . ' 00:00:00';
        $where['pecas_geradas.dataCadastro <='] = $periodo_fim . ' 23:59:59';
        if ($tipo) {
            $where['pecas_geradas.tipo_peca'] = $tipo;
        }
        if ($advogado_id) {
            $where['pecas_geradas.usuarios_gerador_id'] = $advogado_id;
        }

        $total_geradas = $this->pecas_geradas_model->count($where);

        $where_aprovadas = array_merge($where, ['pecas_geradas.status' => 'aprovado']);
        $total_aprovadas = $this->pecas_geradas_model->count($where_aprovadas);

        $this->db->select('pecas_geradas.id, pecas_geradas.dataCadastro, pecas_geradas.data_aprovacao');
        $this->db->from('pecas_geradas');
        $this->db->where('pecas_geradas.status', 'aprovado');
        $this->db->where('pecas_geradas.dataCadastro >=', $periodo_inicio . ' 00:00:00');
        $this->db->where('pecas_geradas.dataCadastro <=', $periodo_fim . ' 23:59:59');
        if ($tipo) {
            $this->db->where('pecas_geradas.tipo_peca', $tipo);
        }
        if ($advogado_id) {
            $this->db->where('pecas_geradas.usuarios_gerador_id', $advogado_id);
        }
        $aprovadas = $this->db->get()->result();

        $tempos = [];
        foreach ($aprovadas as $a) {
            if ($a->data_aprovacao && $a->dataCadastro) {
                $tempos[] = strtotime($a->data_aprovacao) - strtotime($a->dataCadastro);
            }
        }
        $tempo_medio_horas = !empty($tempos) ? array_sum($tempos) / count($tempos) / 3600 : 0;

        $percentual_aprovadas = $total_geradas > 0 ? round(100 * $total_aprovadas / $total_geradas, 1) : 0;

        $this->load->model('sistema_model');
        $usuarios = $this->sistema_model->get('usuarios', 'idUsuarios, nome', '', 0, 0, false, false);

        $this->data['total_geradas'] = $total_geradas;
        $this->data['total_aprovadas'] = $total_aprovadas;
        $this->data['percentual_aprovadas'] = $percentual_aprovadas;
        $this->data['tempo_medio_horas'] = round($tempo_medio_horas, 1);
        $this->data['periodo_inicio'] = $periodo_inicio;
        $this->data['periodo_fim'] = $periodo_fim;
        $this->data['tipo'] = $tipo;
        $this->data['advogado_id'] = $advogado_id;
        $this->data['usuarios'] = $usuarios;
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['view'] = 'pecas_geradas/dashboard';

        return $this->layout();
    }

    /**
     * Jurisprudência (base RAG)
     */
    public function jurisprudencia()
    {
        $this->verificarPermissaoGerar();

        $this->load->model('jurisprudencia_model');

        $this->data['results'] = $this->jurisprudencia_model->get([], 50, 0);
        $this->data['view'] = 'pecas_geradas/jurisprudencia';

        return $this->layout();
    }

    public function adicionar_jurisprudencia()
    {
        $this->verificarPermissaoGerar();

        $this->load->model('jurisprudencia_model');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('tribunal', 'Tribunal', 'trim');
        $this->form_validation->set_rules('numero_processo', 'Número do processo', 'trim');
        $this->form_validation->set_rules('trecho', 'Trecho', 'required');

        if ($this->form_validation->run() === false) {
            $this->data['view'] = 'pecas_geradas/form_jurisprudencia';
            return $this->layout();
        }

        $data = [
            'tribunal' => $this->input->post('tribunal'),
            'numero_processo' => $this->input->post('numero_processo'),
            'data' => $this->input->post('data') ?: null,
            'trecho' => $this->input->post('trecho'),
            'link' => $this->input->post('link'),
            'area' => $this->input->post('area'),
            'assunto' => $this->input->post('assunto'),
        ];

        if ($this->jurisprudencia_model->add($data)) {
            $this->session->set_flashdata('success', 'Jurisprudência adicionada.');
            redirect('pecasGeradas/jurisprudencia');
        }

        $this->data['custom_error'] = 'Erro ao salvar.';
        $this->data['view'] = 'pecas_geradas/form_jurisprudencia';
        return $this->layout();
    }

    /**
     * CRUD Modelos
     */
    public function modelos()
    {
        $this->verificarPermissaoGerar();

        $this->data['results'] = $this->modelos_pecas_model->get([], 0, 0);
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['areas'] = $this->modelos_pecas_model->getAreas();
        $this->data['view'] = 'pecas_geradas/modelos';

        return $this->layout();
    }

    public function adicionar_modelo()
    {
        $this->verificarPermissaoGerar();

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Nome', 'required|trim');
        $this->form_validation->set_rules('tipo_peca', 'Tipo de peça', 'required');

        if ($this->form_validation->run() === false) {
            $this->data['modelo'] = null;
            $this->data['custom_error'] = validation_errors();
            $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
            $this->data['areas'] = $this->modelos_pecas_model->getAreas();
            $this->data['view'] = 'pecas_geradas/form_modelo';
            return $this->layout();
        }

        $data = [
            'nome' => $this->input->post('nome'),
            'area' => $this->input->post('area') ?: null,
            'tipo_peca' => $this->input->post('tipo_peca'),
            'corpo' => $this->input->post('corpo') ?: null,
            'usuarios_id' => $this->session->userdata('id_admin'),
        ];

        if ($this->modelos_pecas_model->add($data)) {
            $this->session->set_flashdata('success', 'Modelo adicionado com sucesso.');
            redirect('pecasGeradas/modelos');
        }

        $this->data['modelo'] = null;
        $this->data['custom_error'] = 'Erro ao salvar modelo.';
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['areas'] = $this->modelos_pecas_model->getAreas();
        $this->data['view'] = 'pecas_geradas/form_modelo';
        return $this->layout();
    }

    public function editar_modelo($id)
    {
        $this->verificarPermissaoGerar();

        $modelo = $this->modelos_pecas_model->getById($id);
        if (!$modelo) {
            $this->session->set_flashdata('error', 'Modelo não encontrado.');
            redirect('pecasGeradas/modelos');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Nome', 'required|trim');
        $this->form_validation->set_rules('tipo_peca', 'Tipo de peça', 'required');

        if ($this->form_validation->run() === false) {
            $this->data['modelo'] = $modelo;
            $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
            $this->data['areas'] = $this->modelos_pecas_model->getAreas();
            $this->data['view'] = 'pecas_geradas/form_modelo';
            return $this->layout();
        }

        $data = [
            'nome' => $this->input->post('nome'),
            'area' => $this->input->post('area') ?: null,
            'tipo_peca' => $this->input->post('tipo_peca'),
            'corpo' => $this->input->post('corpo') ?: null,
        ];

        if ($this->modelos_pecas_model->edit($data, $id)) {
            $this->session->set_flashdata('success', 'Modelo atualizado com sucesso.');
            redirect('pecasGeradas/modelos');
        }

        $this->data['modelo'] = $modelo;
        $this->data['custom_error'] = 'Erro ao atualizar modelo.';
        $this->data['tipos_peca'] = $this->modelos_pecas_model->getTiposPeca();
        $this->data['areas'] = $this->modelos_pecas_model->getAreas();
        $this->data['view'] = 'pecas_geradas/form_modelo';
        return $this->layout();
    }

    /**
     * Diagnóstico do ambiente (PHP, extensões, paths) - compare local vs produção
     */
    public function diagnostico()
    {
        $this->verificarPermissaoGerar();

        $info = [
            'PHP_VERSION' => PHP_VERSION,
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'log_errors' => ini_get('log_errors'),
            'error_log' => ini_get('error_log') ?: '(vazio - usa padrão)',
            'display_errors' => ini_get('display_errors'),
            'FCPATH' => FCPATH,
            'logs_path' => FCPATH . 'application/logs/',
            'logs_writable' => is_writable(FCPATH . 'application/logs/'),
            'pecas_debug_exists' => file_exists(FCPATH . 'application/logs/pecas_debug.log'),
            'vendor_autoload' => file_exists(FCPATH . 'application/vendor/autoload.php'),
            'OpenAI_class' => (class_exists('OpenAI\Client') || class_exists('OpenAI\Factory')) ? 'SIM' : 'NAO',
            'OPENROUTER_API_KEY' => !empty($_ENV['OPENROUTER_API_KEY']) ? '(configurado)' : '(vazio)',
            'PETICAO_IA_MODELO' => $_ENV['PETICAO_IA_MODELO'] ?? '(não definido)',
        ];

        $this->data['info'] = $info;
        $this->data['view'] = 'pecas_geradas/diagnostico';
        return $this->layout();
    }

    private function calcularDiff($antigo, $novo): string
    {
        return json_encode(['antigo' => mb_substr($antigo, 0, 5000), 'novo' => mb_substr($novo, 0, 5000)]);
    }
}
