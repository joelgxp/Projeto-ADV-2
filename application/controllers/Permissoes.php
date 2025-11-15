<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Permissoes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar as permissões no sistema.');
            redirect(base_url());
        }

        $this->load->helper(['form', 'codegen_helper']);
        $this->load->model('permissoes_model');
        $this->data['menuConfiguracoes'] = 'Permissões';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('permissoes/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->permissoes_model->count('permissoes');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->permissoes_model->get('permissoes', 'idPermissao,nome,data,situacao', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'permissoes/permissoes';

        return $this->layout();
    }

    public function adicionar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $nomePermissao = $this->input->post('nome');
            $cadastro = date('Y-m-d');
            $situacao = 1;

            $permissoes = [

                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),
                
                // Permissões RBAC de clientes
                'vClienteDadosSensiveis' => $this->input->post('vClienteDadosSensiveis'),
                'eClienteDadosSensiveis' => $this->input->post('eClienteDadosSensiveis'),
                'vClienteProcessos' => $this->input->post('vClienteProcessos'),
                'vClienteDocumentos' => $this->input->post('vClienteDocumentos'),
                'vClienteFinanceiro' => $this->input->post('vClienteFinanceiro'),

                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),

                // Novas permissões jurídicas
                'aProcesso' => $this->input->post('aProcesso'),
                'eProcesso' => $this->input->post('eProcesso'),
                'dProcesso' => $this->input->post('dProcesso'),
                'vProcesso' => $this->input->post('vProcesso'),
                'sProcesso' => $this->input->post('sProcesso'), // Sincronizar processo

                'aPrazo' => $this->input->post('aPrazo'),
                'ePrazo' => $this->input->post('ePrazo'),
                'dPrazo' => $this->input->post('dPrazo'),
                'vPrazo' => $this->input->post('vPrazo'),

                'aAudiencia' => $this->input->post('aAudiencia'),
                'eAudiencia' => $this->input->post('eAudiencia'),
                'dAudiencia' => $this->input->post('dAudiencia'),
                'vAudiencia' => $this->input->post('vAudiencia'),

                'cConsultaProcessual' => $this->input->post('cConsultaProcessual'), // Consulta processual na API

                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),

                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),

                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),
                'cAuditoria' => $this->input->post('cAuditoria'),
                'cEmail' => $this->input->post('cEmail'),
                'cSistema' => $this->input->post('cSistema'),

                'rCliente' => $this->input->post('rCliente'),
                'rServico' => $this->input->post('rServico'),
                'rProcesso' => $this->input->post('rProcesso'),
                'rPrazo' => $this->input->post('rPrazo'),
                'rAudiencia' => $this->input->post('rAudiencia'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),

                'aCobranca' => $this->input->post('aCobranca'),
                'eCobranca' => $this->input->post('eCobranca'),
                'dCobranca' => $this->input->post('dCobranca'),
                'vCobranca' => $this->input->post('vCobranca'),
            ];
            $permissoes = serialize($permissoes);

            $data = [
                'nome' => $nomePermissao,
                'data' => $cadastro,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            ];

            if ($this->permissoes_model->add('permissoes', $data) == true) {
                $this->session->set_flashdata('success', 'Permissão adicionada com sucesso!');
                log_info('Adicionou uma permissão');
                redirect(site_url('permissoes/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'permissoes/adicionarPermissao';

        return $this->layout();
    }

    public function editar()
    {
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $nomePermissao = $this->input->post('nome');
            $situacao = $this->input->post('situacao');
            $permissoes = [

                'aCliente' => $this->input->post('aCliente'),
                'eCliente' => $this->input->post('eCliente'),
                'dCliente' => $this->input->post('dCliente'),
                'vCliente' => $this->input->post('vCliente'),
                
                // Permissões RBAC de clientes
                'vClienteDadosSensiveis' => $this->input->post('vClienteDadosSensiveis'),
                'eClienteDadosSensiveis' => $this->input->post('eClienteDadosSensiveis'),
                'vClienteProcessos' => $this->input->post('vClienteProcessos'),
                'vClienteDocumentos' => $this->input->post('vClienteDocumentos'),
                'vClienteFinanceiro' => $this->input->post('vClienteFinanceiro'),

                'aServico' => $this->input->post('aServico'),
                'eServico' => $this->input->post('eServico'),
                'dServico' => $this->input->post('dServico'),
                'vServico' => $this->input->post('vServico'),

                // Novas permissões jurídicas
                'aProcesso' => $this->input->post('aProcesso'),
                'eProcesso' => $this->input->post('eProcesso'),
                'dProcesso' => $this->input->post('dProcesso'),
                'vProcesso' => $this->input->post('vProcesso'),
                'sProcesso' => $this->input->post('sProcesso'), // Sincronizar processo

                'aPrazo' => $this->input->post('aPrazo'),
                'ePrazo' => $this->input->post('ePrazo'),
                'dPrazo' => $this->input->post('dPrazo'),
                'vPrazo' => $this->input->post('vPrazo'),

                'aAudiencia' => $this->input->post('aAudiencia'),
                'eAudiencia' => $this->input->post('eAudiencia'),
                'dAudiencia' => $this->input->post('dAudiencia'),
                'vAudiencia' => $this->input->post('vAudiencia'),

                'cConsultaProcessual' => $this->input->post('cConsultaProcessual'), // Consulta processual na API

                'aArquivo' => $this->input->post('aArquivo'),
                'eArquivo' => $this->input->post('eArquivo'),
                'dArquivo' => $this->input->post('dArquivo'),
                'vArquivo' => $this->input->post('vArquivo'),

                'aLancamento' => $this->input->post('aLancamento'),
                'eLancamento' => $this->input->post('eLancamento'),
                'dLancamento' => $this->input->post('dLancamento'),
                'vLancamento' => $this->input->post('vLancamento'),

                'cUsuario' => $this->input->post('cUsuario'),
                'cEmitente' => $this->input->post('cEmitente'),
                'cPermissao' => $this->input->post('cPermissao'),
                'cBackup' => $this->input->post('cBackup'),
                'cAuditoria' => $this->input->post('cAuditoria'),
                'cEmail' => $this->input->post('cEmail'),
                'cSistema' => $this->input->post('cSistema'),

                'rCliente' => $this->input->post('rCliente'),
                'rServico' => $this->input->post('rServico'),
                'rProcesso' => $this->input->post('rProcesso'),
                'rPrazo' => $this->input->post('rPrazo'),
                'rAudiencia' => $this->input->post('rAudiencia'),
                'rFinanceiro' => $this->input->post('rFinanceiro'),

                'aCobranca' => $this->input->post('aCobranca'),
                'eCobranca' => $this->input->post('eCobranca'),
                'dCobranca' => $this->input->post('dCobranca'),
                'vCobranca' => $this->input->post('vCobranca'),

            ];
            $permissoes = serialize($permissoes);

            $data = [
                'nome' => $nomePermissao,
                'permissoes' => $permissoes,
                'situacao' => $situacao,
            ];

            if ($this->permissoes_model->edit('permissoes', $data, 'idPermissao', $this->input->post('idPermissao')) == true) {
                $this->session->set_flashdata('success', 'Permissão editada com sucesso!');
                log_info('Alterou uma permissão. ID: ' . $this->input->post('idPermissao'));
                redirect(site_url('permissoes/editar/') . $this->input->post('idPermissao'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->permissoes_model->getById($this->uri->segment(3));

        $this->data['view'] = 'permissoes/editarPermissao';

        return $this->layout();
    }

    public function desativar()
    {
        $id = $this->input->post('id');
        if (! $id) {
            $this->session->set_flashdata('error', 'Erro ao tentar desativar permissão.');
            redirect(site_url('permissoes/gerenciar/'));
        }
        $data = [
            'situacao' => false,
        ];
        if ($this->permissoes_model->edit('permissoes', $data, 'idPermissao', $id)) {
            log_info('Desativou uma permissão. ID: ' . $id);
            $this->session->set_flashdata('success', 'Permissão desativada com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao desativar permissão!');
        }

        redirect(site_url('permissoes/gerenciar/'));
    }
}

/* End of file permissoes.php */
/* Location: ./application/controllers/permissoes.php */
