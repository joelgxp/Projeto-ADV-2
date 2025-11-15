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

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('processos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->processos_model->count('processos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/processos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->processos_model->get('processos', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'processos/processos';

        return $this->layout();
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
        
        $this->load->model('mapos_model');
        $this->data['usuarios'] = $this->mapos_model->get('usuarios', '*', '', 0, 0, false);

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
            $this->db->where('numeroProcesso', $this->processos_model->normalizarNumeroProcesso($numeroProcesso));
            $existe = $this->db->get('processos')->row();
            
            if ($existe) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado no sistema.</p></div>';
            } else {
                $data = [
                    'numeroProcesso' => $numeroProcesso,
                    'classe' => $this->input->post('classe'),
                    'assunto' => $this->input->post('assunto'),
                    'tipo_processo' => $this->input->post('tipo_processo'),
                    'vara' => $this->input->post('vara'),
                    'comarca' => $this->input->post('comarca'),
                    'tribunal' => $this->input->post('tribunal'),
                    'segmento' => $this->input->post('segmento'),
                    'status' => $this->input->post('status') ?: 'em_andamento',
                    'valorCausa' => $this->input->post('valorCausa') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valorCausa'))) : null,
                    'dataDistribuicao' => $this->input->post('dataDistribuicao') ?: null,
                    'clientes_id' => $this->input->post('clientes_id') ?: null,
                    'usuarios_id' => $this->input->post('usuarios_id') ?: null,
                    'observacoes' => $this->input->post('observacoes'),
                ];

                if ($this->processos_model->add('processos', $data) == true) {
                    $this->session->set_flashdata('success', 'Processo adicionado com sucesso!');
                    log_info('Adicionou um processo.');
                    redirect(site_url('processos/'));
                } else {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
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
        
        $this->load->model('mapos_model');
        $this->data['usuarios'] = $this->mapos_model->get('usuarios', '*', '', 0, 0, false);

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
            $this->db->where('numeroProcesso', $this->processos_model->normalizarNumeroProcesso($numeroProcesso));
            $this->db->where('idProcessos !=', $idProcesso);
            $existe = $this->db->get('processos')->row();
            
            if ($existe) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este número de processo já está cadastrado em outro processo.</p></div>';
            } else {
                $data = [
                    'numeroProcesso' => $numeroProcesso,
                    'classe' => $this->input->post('classe'),
                    'assunto' => $this->input->post('assunto'),
                    'tipo_processo' => $this->input->post('tipo_processo'),
                    'vara' => $this->input->post('vara'),
                    'comarca' => $this->input->post('comarca'),
                    'tribunal' => $this->input->post('tribunal'),
                    'segmento' => $this->input->post('segmento'),
                    'status' => $this->input->post('status'),
                    'valorCausa' => $this->input->post('valorCausa') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valorCausa'))) : null,
                    'dataDistribuicao' => $this->input->post('dataDistribuicao') ?: null,
                    'clientes_id' => $this->input->post('clientes_id') ?: null,
                    'usuarios_id' => $this->input->post('usuarios_id') ?: null,
                    'observacoes' => $this->input->post('observacoes'),
                ];

                if ($this->processos_model->edit('processos', $data, 'idProcessos', $idProcesso) == true) {
                    $this->session->set_flashdata('success', 'Processo editado com sucesso!');
                    log_info('Alterou um processo. ID' . $idProcesso);
                    redirect(site_url('processos/editar/') . $idProcesso);
                } else {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
                }
            }
        }

        $this->data['result'] = $this->processos_model->getById($this->uri->segment(3));
        // Formatar número de processo para exibição
        if ($this->data['result'] && isset($this->data['result']->numeroProcesso)) {
            $this->data['result']->numeroProcessoFormatado = $this->processos_model->formatarNumeroProcesso($this->data['result']->numeroProcesso);
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

        // Validar formato e dígito verificador
        if (!$this->processos_model->validarNumeroProcesso($numero)) {
            $this->form_validation->set_message('validar_numero_processo', 'O {field} informado é inválido. Verifique o formato e o dígito verificador.');
            return false;
        }

        return true;
    }
}

