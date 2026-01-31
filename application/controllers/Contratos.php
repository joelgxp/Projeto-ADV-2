<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Contratos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Contratos_model');
        $this->load->model('clientes_model');
        $this->data['menuContratos'] = 'financeiro';
    }

    public function index()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar contratos.');
            redirect(base_url());
        }

        $where = [];
        $cliente = $this->input->get('cliente');
        $tipo = $this->input->get('tipo');
        $ativo = $this->input->get('ativo');

        if ($cliente) {
            $where['clientes.nomeCliente LIKE'] = "%{$cliente}%";
        }
        if ($tipo) {
            $where['contratos.tipo'] = $tipo;
        }
        if ($ativo !== null && $ativo !== '') {
            $where['contratos.ativo'] = $ativo;
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url("contratos/?cliente={$cliente}&tipo={$tipo}&ativo={$ativo}");
        $this->data['configuration']['total_rows'] = $this->Contratos_model->count($where);
        $this->data['configuration']['page_query_string'] = true;

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->Contratos_model->get($where, $this->data['configuration']['per_page'], $this->input->get('per_page'));

        $this->data['view'] = 'contratos/contratos';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar contratos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('contratos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'clientes_id' => $this->input->post('clientes_id'),
                'tipo' => $this->input->post('tipo'),
                'data_inicio' => $this->input->post('data_inicio'),
                'data_fim' => $this->input->post('data_fim') ?: null,
                'valor_fixo' => $this->input->post('valor_fixo') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valor_fixo'))) : null,
                'percentual_sucumbencia' => $this->input->post('percentual_sucumbencia') ?: null,
                'percentual_exito' => $this->input->post('percentual_exito') ?: null,
                'ativo' => $this->input->post('ativo') ? 1 : 0,
                'observacoes' => $this->input->post('observacoes')
            ];

            // Log dos dados antes de enviar ao model
            log_message('debug', 'Contratos::adicionar - Dados preparados: ' . json_encode($data));
            
            $result = $this->Contratos_model->add($data);
            
            if ($result !== false && $result > 0) {
                // Auditoria: Registrar criação (RN 8.1)
                $this->load->helper('audit');
                log_create('contrato', $result, $data);
                
                $this->session->set_flashdata('success', 'Contrato adicionado com sucesso!');
                redirect(base_url() . 'index.php/contratos');
            } else {
                $error = $this->db->error();
                $error_message = 'Ocorreu um erro ao adicionar o contrato.';
                if ($error['code'] != 0) {
                    $error_message .= ' Erro: ' . $error['message'];
                    log_message('error', 'Contratos::adicionar - Erro SQL: ' . $error['message'] . ' (Código: ' . $error['code'] . ')');
                }
                $this->data['custom_error'] = '<div class="form_error"><p>' . $error_message . '</p></div>';
            }
        }

        $this->data['clientes'] = $this->clientes_model->get('clientes', 'idClientes, nomeCliente', '', 0, 0, false, 'array');
        $this->data['view'] = 'contratos/adicionarContrato';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar contratos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Contratos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Contrato não encontrado.');
            redirect(base_url() . 'contratos');
        }

        if ($this->form_validation->run('contratos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'clientes_id' => $this->input->post('clientes_id'),
                'tipo' => $this->input->post('tipo'),
                'data_inicio' => $this->input->post('data_inicio'),
                'data_fim' => $this->input->post('data_fim') ?: null,
                'valor_fixo' => $this->input->post('valor_fixo') ? str_replace(',', '.', str_replace('.', '', $this->input->post('valor_fixo'))) : null,
                'percentual_sucumbencia' => $this->input->post('percentual_sucumbencia') ?: null,
                'percentual_exito' => $this->input->post('percentual_exito') ?: null,
                'ativo' => $this->input->post('ativo') ? 1 : 0,
                'observacoes' => $this->input->post('observacoes')
            ];

            // Obter dados anteriores para auditoria
            $contrato_anterior = $this->Contratos_model->getById($id);
            $dados_anteriores = $contrato_anterior ? [
                'clientes_id' => $contrato_anterior->clientes_id,
                'tipo' => $contrato_anterior->tipo,
                'data_inicio' => $contrato_anterior->data_inicio,
                'data_fim' => $contrato_anterior->data_fim,
                'valor_fixo' => $contrato_anterior->valor_fixo,
                'ativo' => $contrato_anterior->ativo,
            ] : [];
            
            if ($this->Contratos_model->edit($id, $data) == true) {
                // Auditoria: Registrar atualização (RN 8.1)
                $this->load->helper('audit');
                log_update('contrato', $id, $dados_anteriores, $data);
                
                $this->session->set_flashdata('success', 'Contrato editado com sucesso!');
                redirect(base_url() . 'index.php/contratos');
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o contrato.</p></div>';
            }
        }

        $this->data['result'] = $this->Contratos_model->getById($id);
        $this->data['clientes'] = $this->clientes_model->get('clientes', 'idClientes, nomeCliente', '', 0, 0, false, 'array');
        $this->data['view'] = 'contratos/editarContrato';
        return $this->layout();
    }

    public function visualizar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar contratos.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Contratos_model->getById($id)) {
            $this->session->set_flashdata('error', 'Contrato não encontrado.');
            redirect(base_url() . 'contratos');
        }

        $this->data['result'] = $this->Contratos_model->getById($id);
        $this->load->model('Faturas_model');
        $this->data['faturas'] = $this->Faturas_model->getByCliente($this->data['result']->clientes_id);

        $this->data['view'] = 'contratos/visualizarContrato';
        return $this->layout();
    }

    public function ativar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para ativar contratos.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null) {
            $this->session->set_flashdata('error', 'Contrato não encontrado.');
            redirect(base_url() . 'contratos');
        }

        if ($this->Contratos_model->ativarContrato($id)) {
            $this->session->set_flashdata('success', 'Contrato ativado com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao ativar contrato.');
        }

        redirect(base_url() . 'contratos');
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dContrato')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir contratos.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null) {
            $this->session->set_flashdata('error', 'Contrato não encontrado.');
            redirect(base_url() . 'contratos');
        }

        // Obter dados antes de excluir para auditoria
        $contrato_excluido = $this->Contratos_model->getById($id);
        $dados_anteriores = $contrato_excluido ? [
            'id' => $contrato_excluido->id,
            'clientes_id' => $contrato_excluido->clientes_id,
            'tipo' => $contrato_excluido->tipo,
            'data_inicio' => $contrato_excluido->data_inicio,
            'valor_fixo' => $contrato_excluido->valor_fixo,
        ] : [];

        if ($this->Contratos_model->delete($id)) {
            // Auditoria: Registrar exclusão (RN 8.1)
            $this->load->helper('audit');
            log_delete('contrato', $id, $dados_anteriores);
            
            $this->session->set_flashdata('success', 'Contrato excluído com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Não é possível excluir este contrato pois existem faturas vinculadas.');
        }

        redirect(base_url() . 'contratos');
    }

    public function getByCliente($cliente_id)
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vContrato')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        $contratos = $this->Contratos_model->getByCliente($cliente_id);
        // Converter objetos para arrays se necessário
        $contratos_array = [];
        foreach ($contratos as $c) {
            $contratos_array[] = [
                'id' => $c->id,
                'tipo' => $c->tipo,
                'data_inicio' => $c->data_inicio,
                'ativo' => $c->ativo
            ];
        }
        echo json_encode(['success' => true, 'contratos' => $contratos_array]);
    }
}

