<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Planos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('planos_model');
        $this->data['menuPlanos'] = 'planos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar planos.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('planos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->planos_model->count('planos', $pesquisa);
        if ($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/planos") . "?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->planos_model->get('planos', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'planos/planos';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar planos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('planos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $valor_mensal = set_value('valor_mensal');
            $valor_mensal = str_replace(',', '.', str_replace('.', '', $valor_mensal));
            
            $data = [
                'nome' => set_value('nome'),
                'descricao' => set_value('descricao'),
                'valor_mensal' => $valor_mensal ? floatval($valor_mensal) : 0.00,
                'limite_processos' => set_value('limite_processos') ? intval(set_value('limite_processos')) : 0,
                'limite_prazos' => set_value('limite_prazos') ? intval(set_value('limite_prazos')) : 0,
                'limite_audiencias' => set_value('limite_audiencias') ? intval(set_value('limite_audiencias')) : 0,
                'limite_documentos' => set_value('limite_documentos') ? intval(set_value('limite_documentos')) : 0,
                'acesso_portal' => $this->input->post('acesso_portal') ? 1 : 0,
                'acesso_api' => $this->input->post('acesso_api') ? 1 : 0,
                'suporte_prioritario' => $this->input->post('suporte_prioritario') ? 1 : 0,
                'relatorios_avancados' => $this->input->post('relatorios_avancados') ? 1 : 0,
                'status' => $this->input->post('status') ? 1 : 0,
            ];

            if ($this->planos_model->add('planos', $data) == true) {
                $this->session->set_flashdata('success', 'Plano adicionado com sucesso!');
                redirect(site_url('planos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o plano.</p></div>';
            }
        }

        $this->data['view'] = 'planos/adicionarPlano';
        return $this->layout();
    }

    public function editar($id = null)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar planos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Plano não encontrado.');
            redirect(site_url('planos/gerenciar'));
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $this->data['result'] = $this->planos_model->getById($id);

        if ($this->form_validation->run('planos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $valor_mensal = set_value('valor_mensal');
            $valor_mensal = str_replace(',', '.', str_replace('.', '', $valor_mensal));
            
            $data = [
                'nome' => set_value('nome'),
                'descricao' => set_value('descricao'),
                'valor_mensal' => $valor_mensal ? floatval($valor_mensal) : 0.00,
                'limite_processos' => set_value('limite_processos') ? intval(set_value('limite_processos')) : 0,
                'limite_prazos' => set_value('limite_prazos') ? intval(set_value('limite_prazos')) : 0,
                'limite_audiencias' => set_value('limite_audiencias') ? intval(set_value('limite_audiencias')) : 0,
                'limite_documentos' => set_value('limite_documentos') ? intval(set_value('limite_documentos')) : 0,
                'acesso_portal' => $this->input->post('acesso_portal') ? 1 : 0,
                'acesso_api' => $this->input->post('acesso_api') ? 1 : 0,
                'suporte_prioritario' => $this->input->post('suporte_prioritario') ? 1 : 0,
                'relatorios_avancados' => $this->input->post('relatorios_avancados') ? 1 : 0,
                'status' => $this->input->post('status') ? 1 : 0,
            ];

            if ($this->planos_model->edit('planos', $data, 'idPlanos', $id) == true) {
                $this->session->set_flashdata('success', 'Plano editado com sucesso!');
                redirect(site_url('planos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o plano.</p></div>';
            }
        }

        $this->data['view'] = 'planos/editarPlano';
        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir planos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir plano.');
            redirect(site_url('planos/gerenciar'));
        }

        // Verificar se há clientes usando este plano
        $this->load->model('clientes_model');
        $this->db->where('planos_id', $id);
        $clientes = $this->db->get('clientes')->result();

        if (count($clientes) > 0) {
            $this->session->set_flashdata('error', 'Não é possível excluir este plano pois existem clientes vinculados a ele.');
            redirect(site_url('planos/gerenciar'));
        }

        if ($this->planos_model->delete('planos', 'idPlanos', $id) == true) {
            $this->session->set_flashdata('success', 'Plano excluído com sucesso!');
            redirect(site_url('planos/gerenciar'));
        } else {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir plano.');
            redirect(site_url('planos/gerenciar'));
        }
    }

    public function visualizar($id = null)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar planos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Plano não encontrado.');
            redirect(site_url('planos/gerenciar'));
        }

        $this->data['result'] = $this->planos_model->getById($id);
        
        // Buscar clientes com este plano
        $this->db->where('planos_id', $id);
        $this->data['clientes'] = $this->db->get('clientes')->result();

        $this->data['view'] = 'planos/visualizar';
        return $this->layout();
    }
}

