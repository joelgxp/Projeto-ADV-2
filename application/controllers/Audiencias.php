<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Audiencias extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('audiencias_model');
        $this->load->model('processos_model');
        $this->data['menuAudiencias'] = 'audiencias';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar audiências.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $data_inicio = $this->input->get('data_inicio');
        $data_fim = $this->input->get('data_fim');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('audiencias/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->audiencias_model->count('audiencias');
        
        $suffix_parts = [];
        if($pesquisa) $suffix_parts[] = "pesquisa={$pesquisa}";
        if($status) $suffix_parts[] = "status={$status}";
        if($data_inicio) $suffix_parts[] = "data_inicio={$data_inicio}";
        if($data_fim) $suffix_parts[] = "data_fim={$data_fim}";
        
        if (!empty($suffix_parts)) {
            $this->data['configuration']['suffix'] = "?" . implode('&', $suffix_parts);
            $this->data['configuration']['first_url'] = base_url("index.php/audiencias") . "?" . implode('&', $suffix_parts);
        }

        $this->pagination->initialize($this->data['configuration']);

        $where = '';
        if ($pesquisa) {
            $where = $pesquisa;
        }
        if ($status) {
            $where .= ($where ? ' AND ' : '') . "status = '{$status}'";
        }
        if ($data_inicio) {
            $where .= ($where ? ' AND ' : '') . "DATE(dataHora) >= '{$data_inicio}'";
        }
        if ($data_fim) {
            $where .= ($where ? ' AND ' : '') . "DATE(dataHora) <= '{$data_fim}'";
        }

        $this->data['results'] = $this->audiencias_model->get('audiencias', '*', $where, $this->data['configuration']['per_page'], $this->uri->segment(3));
        $this->data['status'] = $status;
        $this->data['data_inicio'] = $data_inicio;
        $this->data['data_fim'] = $data_fim;

        $this->data['view'] = 'audiencias/audiencias';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar audiências.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('dataHora', 'Data e Hora', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'dataHora' => $this->input->post('dataHora') . ' ' . ($this->input->post('hora') ?: '09:00:00'),
                'local' => $this->input->post('local'),
                'observacoes' => $this->input->post('observacoes'),
                'status' => $this->input->post('status') ?: 'Agendada',
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if ($this->audiencias_model->add('audiencias', $data) == true) {
                $this->session->set_flashdata('success', 'Audiência adicionada com sucesso!');
                log_info('Adicionou uma audiência.');
                redirect(site_url('audiencias/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar a audiência.</p></div>';
            }
        }

        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'audiencias/adicionarAudiencia';

        return $this->layout();
    }

    public function editar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar audiências.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar editar audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('dataHora', 'Data e Hora', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'dataHora' => $this->input->post('dataHora') . ' ' . ($this->input->post('hora') ?: '09:00:00'),
                'local' => $this->input->post('local'),
                'observacoes' => $this->input->post('observacoes'),
                'status' => $this->input->post('status'),
            ];

            if ($this->audiencias_model->edit('audiencias', $data, 'idAudiencias', $id) == true) {
                $this->session->set_flashdata('success', 'Audiência editada com sucesso!');
                log_info('Alterou uma audiência. ID: ' . $id);
                redirect(site_url('audiencias/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar a audiência.</p></div>';
            }
        }

        $this->data['result'] = $this->audiencias_model->getById($id);
        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'audiencias/editarAudiencia';

        return $this->layout();
    }

    public function visualizar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar audiências.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar visualizar audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        $this->data['result'] = $this->audiencias_model->getById($id);
        $this->data['view'] = 'audiencias/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir audiências.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        if ($this->audiencias_model->delete('audiencias', 'idAudiencias', $id) == true) {
            $this->session->set_flashdata('success', 'Audiência excluída com sucesso!');
            log_info('Removeu uma audiência. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir a audiência.');
        }

        redirect(site_url('audiencias/gerenciar'));
    }
}

