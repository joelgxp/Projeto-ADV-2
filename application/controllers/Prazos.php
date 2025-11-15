<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Prazos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('prazos_model');
        $this->load->model('processos_model');
        $this->data['menuPrazos'] = 'prazos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar prazos.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('prazos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->prazos_model->count('prazos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/prazos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $where = '';
        if ($pesquisa) {
            $where = $pesquisa;
        }
        if ($status) {
            $where .= ($where ? ' AND ' : '') . "status = '{$status}'";
        }

        $this->data['results'] = $this->prazos_model->get('prazos', '*', $where, $this->data['configuration']['per_page'], $this->uri->segment(3));
        $this->data['status'] = $status;

        $this->data['view'] = 'prazos/prazos';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar prazos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required|trim');
        $this->form_validation->set_rules('dataVencimento', 'Data de Vencimento', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'descricao' => $this->input->post('descricao'),
                'dataPrazo' => $this->input->post('dataPrazo'),
                'dataVencimento' => $this->input->post('dataVencimento'),
                'status' => $this->input->post('status') ?: 'Pendente',
                'prioridade' => $this->input->post('prioridade') ?: 'Normal',
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if ($this->prazos_model->add('prazos', $data) == true) {
                $this->session->set_flashdata('success', 'Prazo adicionado com sucesso!');
                log_info('Adicionou um prazo processual.');
                redirect(site_url('prazos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o prazo.</p></div>';
            }
        }

        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'prazos/adicionarPrazo';

        return $this->layout();
    }

    public function editar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar prazos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar editar prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required|trim');
        $this->form_validation->set_rules('dataVencimento', 'Data de Vencimento', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'descricao' => $this->input->post('descricao'),
                'dataPrazo' => $this->input->post('dataPrazo'),
                'dataVencimento' => $this->input->post('dataVencimento'),
                'status' => $this->input->post('status'),
                'prioridade' => $this->input->post('prioridade'),
            ];

            if ($this->prazos_model->edit('prazos', $data, 'idPrazos', $id) == true) {
                $this->session->set_flashdata('success', 'Prazo editado com sucesso!');
                log_info('Alterou um prazo processual. ID: ' . $id);
                redirect(site_url('prazos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o prazo.</p></div>';
            }
        }

        $this->data['result'] = $this->prazos_model->getById($id);
        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'prazos/editarPrazo';

        return $this->layout();
    }

    public function visualizar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar prazos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar visualizar prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        $this->data['result'] = $this->prazos_model->getById($id);
        $this->data['view'] = 'prazos/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir prazos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        if ($this->prazos_model->delete('prazos', 'idPrazos', $id) == true) {
            $this->session->set_flashdata('success', 'Prazo excluído com sucesso!');
            log_info('Removeu um prazo processual. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o prazo.');
        }

        redirect(site_url('prazos/gerenciar'));
    }
}

