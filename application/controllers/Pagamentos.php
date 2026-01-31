<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pagamentos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pagamentos_model');
        $this->load->model('Faturas_model');
        $this->data['menuPagamentos'] = 'financeiro';
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar pagamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $fatura_id = $this->input->post('faturas_id');
        if (!$fatura_id) {
            $fatura_id = $this->uri->segment(3);
        }

        if (!$fatura_id || !$this->Faturas_model->getById($fatura_id)) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
            redirect(base_url() . 'faturas');
        }

        $this->form_validation->set_rules('faturas_id', 'Fatura', 'required');
        $this->form_validation->set_rules('data_pagamento', 'Data de Pagamento', 'required');
        $this->form_validation->set_rules('valor', 'Valor', 'required');
        $this->form_validation->set_rules('metodo_pagamento', 'Método de Pagamento', 'required');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'faturas_id' => $this->input->post('faturas_id'),
                'data_pagamento' => $this->input->post('data_pagamento'),
                'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('valor'))),
                'metodo_pagamento' => $this->input->post('metodo_pagamento'),
                'observacoes' => $this->input->post('observacoes')
            ];

            if ($this->Pagamentos_model->add($data)) {
                $this->session->set_flashdata('success', 'Pagamento adicionado com sucesso!');
                redirect(base_url() . 'faturas/visualizar/' . $fatura_id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o pagamento.</p></div>';
            }
        }

        $this->data['fatura'] = $this->Faturas_model->getById($fatura_id);
        $this->data['view'] = 'pagamentos/adicionarPagamento';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'ePagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar pagamentos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Pagamentos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Pagamento não encontrado.');
            redirect(base_url() . 'faturas');
        }

        $this->form_validation->set_rules('data_pagamento', 'Data de Pagamento', 'required');
        $this->form_validation->set_rules('valor', 'Valor', 'required');
        $this->form_validation->set_rules('metodo_pagamento', 'Método de Pagamento', 'required');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'data_pagamento' => $this->input->post('data_pagamento'),
                'valor' => str_replace(',', '.', str_replace('.', '', $this->input->post('valor'))),
                'metodo_pagamento' => $this->input->post('metodo_pagamento'),
                'observacoes' => $this->input->post('observacoes')
            ];

            if ($this->Pagamentos_model->edit($id, $data)) {
                $pagamento = $this->Pagamentos_model->getById($id);
                $this->session->set_flashdata('success', 'Pagamento editado com sucesso!');
                redirect(base_url() . 'faturas/visualizar/' . $pagamento->faturas_id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o pagamento.</p></div>';
            }
        }

        $this->data['result'] = $this->Pagamentos_model->getById($id);
        $this->data['fatura'] = $this->Faturas_model->getById($this->data['result']->faturas_id);
        $this->data['view'] = 'pagamentos/editarPagamento';
        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir pagamentos.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Pagamentos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Pagamento não encontrado.');
            redirect(base_url() . 'faturas');
        }

        $pagamento = $this->Pagamentos_model->getById($id);
        $fatura_id = $pagamento->faturas_id;

        if ($this->Pagamentos_model->delete($id)) {
            $this->session->set_flashdata('success', 'Pagamento excluído com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao excluir pagamento.');
        }

        redirect(base_url() . 'faturas/visualizar/' . $fatura_id);
    }

    public function gerarRecibo()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vPagamento')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar recibos.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Pagamentos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Pagamento não encontrado.');
            redirect(base_url() . 'faturas');
        }

        $this->load->helper('mpdf');
        $this->load->model('Sistema_model');
        
        $this->data['pagamento'] = $this->Pagamentos_model->getById($id);
        $this->data['fatura'] = $this->Faturas_model->getById($this->data['pagamento']->faturas_id);
        $this->data['emitente'] = $this->Sistema_model->getEmitente();

        $html = $this->load->view('pagamentos/recibo_pdf', $this->data, true);
        pdf_create($html, 'Recibo_' . $this->data['pagamento']->id, true);
    }
}

