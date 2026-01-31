<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Faturas extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Faturas_model');
        $this->load->model('Pagamentos_model');
        $this->load->model('clientes_model');
        $this->load->model('Contratos_model');
        $this->load->model('processos_model');
        $this->data['menuFaturas'] = 'financeiro';
    }

    public function index()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar faturas.');
            redirect(base_url());
        }

        $where = [];
        $cliente = $this->input->get('cliente');
        $status = $this->input->get('status');
        $vencimento_de = $this->input->get('vencimento_de');
        $vencimento_ate = $this->input->get('vencimento_ate');

        if ($cliente && $cliente !== '') {
            $where['clientes.nomeCliente LIKE'] = "%{$cliente}%";
        }
        if ($status && $status !== '') {
            $where['faturas.status'] = $status;
        }
        if ($vencimento_de && $vencimento_de !== '') {
            $date = DateTime::createFromFormat('d/m/Y', $vencimento_de);
            if ($date) {
                $where['faturas.data_vencimento >='] = $date->format('Y-m-d');
            }
        }
        if ($vencimento_ate && $vencimento_ate !== '') {
            $date = DateTime::createFromFormat('d/m/Y', $vencimento_ate);
            if ($date) {
                $where['faturas.data_vencimento <='] = $date->format('Y-m-d');
            }
        }
        
        // Se não houver filtros, passar null em vez de array vazio
        if (empty($where)) {
            $where = null;
        }

        // Atualizar status de faturas atrasadas
        $this->Faturas_model->atualizarStatus();

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url("faturas/?cliente={$cliente}&status={$status}&vencimento_de={$vencimento_de}&vencimento_ate={$vencimento_ate}");
        $this->data['configuration']['total_rows'] = $this->Faturas_model->count($where);
        $this->data['configuration']['page_query_string'] = true;

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->Faturas_model->get($where, $this->data['configuration']['per_page'], $this->input->get('per_page'));
        $this->data['estatisticas'] = $this->Faturas_model->getEstatisticas();

        $this->data['view'] = 'faturas/faturas';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar faturas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('clientes_id', 'Cliente', 'required');
        $this->form_validation->set_rules('data_emissao', 'Data de Emissão', 'required');
        $this->form_validation->set_rules('data_vencimento', 'Data de Vencimento', 'required');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'clientes_id' => $this->input->post('clientes_id'),
                'contratos_id' => $this->input->post('contratos_id') ?: null,
                'data_emissao' => $this->input->post('data_emissao'),
                'data_vencimento' => $this->input->post('data_vencimento'),
                'valor_total' => 0, // Será calculado pelos itens
                'valor_pago' => 0,
                'saldo_restante' => 0,
                'status' => $this->input->post('status') ?: 'rascunho',
                'observacoes' => $this->input->post('observacoes')
            ];

            $fatura_id = $this->Faturas_model->add($data);
            if ($fatura_id) {
                // Adicionar itens se houver
                $itens = $this->input->post('itens');
                if ($itens && is_array($itens)) {
                    foreach ($itens as $item) {
                        if (!empty($item['descricao'])) {
                            $this->Faturas_model->addItem([
                                'faturas_id' => $fatura_id,
                                'processos_id' => !empty($item['processos_id']) ? $item['processos_id'] : null,
                                'tipo_item' => $item['tipo_item'],
                                'descricao' => $item['descricao'],
                                'valor_unitario' => str_replace(',', '.', str_replace('.', '', $item['valor_unitario'])),
                                'quantidade' => str_replace(',', '.', str_replace('.', '', $item['quantidade'])),
                                'ipi' => !empty($item['ipi']) ? $item['ipi'] : 0,
                                'iss' => !empty($item['iss']) ? $item['iss'] : 0
                            ]);
                        }
                    }
                }

                // Auditoria: Registrar criação (RN 8.1)
                $this->load->helper('audit');
                log_create('fatura', $fatura_id, $data);
                
                // Notificação: Enviar e-mail de fatura emitida (RN 9.1)
                $this->load->helper('notificacoes');
                notificar_fatura_emitida($fatura_id);
                
                $this->session->set_flashdata('success', 'Fatura adicionada com sucesso!');
                redirect(base_url() . 'index.php/faturas/editar/' . $fatura_id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar a fatura.</p></div>';
            }
        }

        $this->data['clientes'] = $this->clientes_model->get('clientes', 'idClientes, nomeCliente', '', 0, 0, false, 'array');
        $this->data['contratos'] = []; // Inicialmente vazio, será carregado via AJAX quando cliente for selecionado
        $this->data['view'] = 'faturas/adicionarFatura';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar faturas.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Faturas_model->getById($id)) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
                redirect(base_url() . 'index.php/faturas');
        }

        $this->form_validation->set_rules('data_emissao', 'Data de Emissão', 'required');
        $this->form_validation->set_rules('data_vencimento', 'Data de Vencimento', 'required');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'contratos_id' => $this->input->post('contratos_id') ?: null,
                'data_emissao' => $this->input->post('data_emissao'),
                'data_vencimento' => $this->input->post('data_vencimento'),
                'status' => $this->input->post('status'),
                'observacoes' => $this->input->post('observacoes')
            ];

            // Obter dados anteriores para auditoria
            $fatura_anterior = $this->Faturas_model->getById($id);
            $dados_anteriores = $fatura_anterior ? [
                'contratos_id' => $fatura_anterior->contratos_id,
                'data_emissao' => $fatura_anterior->data_emissao,
                'data_vencimento' => $fatura_anterior->data_vencimento,
                'status' => $fatura_anterior->status,
            ] : [];

            if ($this->Faturas_model->edit($id, $data)) {
                // Auditoria: Registrar atualização (RN 8.1)
                $this->load->helper('audit');
                log_update('fatura', $id, $dados_anteriores, $data);
                
                $this->session->set_flashdata('success', 'Fatura editada com sucesso!');
                redirect(base_url() . 'index.php/faturas/editar/' . $id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar a fatura.</p></div>';
            }
        }

        $this->data['result'] = $this->Faturas_model->getById($id);
        $this->data['itens'] = $this->Faturas_model->getItens($id);
        $this->data['pagamentos'] = $this->Pagamentos_model->getByFatura($id);
        $this->data['clientes'] = $this->clientes_model->get('clientes', 'idClientes, nomeCliente', '', 0, 0, false, 'array');
        $this->data['contratos'] = $this->Contratos_model->getByCliente($this->data['result']->clientes_id);
        $this->data['processos'] = $this->processos_model->get('idProcessos, numeroProcesso', ['clientes_id' => $this->data['result']->clientes_id], 0, 0, false, 'array');

        $this->data['view'] = 'faturas/editarFatura';
        return $this->layout();
    }

    public function visualizar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar faturas.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Faturas_model->getById($id)) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
                redirect(base_url() . 'index.php/faturas');
        }

        $this->data['result'] = $this->Faturas_model->getById($id);
        $this->data['itens'] = $this->Faturas_model->getItens($id);
        $this->data['pagamentos'] = $this->Pagamentos_model->getByFatura($id);

        $this->data['view'] = 'faturas/visualizarFatura';
        return $this->layout();
    }

    public function adicionarItem()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        $fatura_id = $this->input->post('faturas_id');
        
        // Buscar processos do cliente da fatura
        $fatura = $this->Faturas_model->getById($fatura_id);
        if (!$fatura) {
            echo json_encode(['success' => false, 'message' => 'Fatura não encontrada']);
            return;
        }

        $processos = $this->processos_model->get('idProcessos, numeroProcesso', ['clientes_id' => $fatura->clientes_id], 0, 0, false, 'array');

        $data = [
            'faturas_id' => $fatura_id,
            'processos_id' => $this->input->post('processos_id') ?: null,
            'tipo_item' => $this->input->post('tipo_item'),
            'descricao' => $this->input->post('descricao'),
            'valor_unitario' => str_replace(',', '.', str_replace('.', '', $this->input->post('valor_unitario'))),
            'quantidade' => str_replace(',', '.', str_replace('.', '', $this->input->post('quantidade'))),
            'ipi' => $this->input->post('ipi') ?: 0,
            'iss' => $this->input->post('iss') ?: 0
        ];

        if ($this->Faturas_model->addItem($data)) {
            echo json_encode(['success' => true, 'message' => 'Item adicionado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar item']);
        }
    }

    public function removerItem()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        $id = $this->input->post('item_id');
        if ($this->Faturas_model->deleteItem($id)) {
            echo json_encode(['success' => true, 'message' => 'Item removido com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover item']);
        }
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir faturas.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
                redirect(base_url() . 'index.php/faturas');
        }

        // Obter dados antes de excluir para auditoria
        $fatura_excluida = $this->Faturas_model->getById($id);
        $dados_anteriores = $fatura_excluida ? [
            'id' => $fatura_excluida->id,
            'numero' => $fatura_excluida->numero,
            'clientes_id' => $fatura_excluida->clientes_id,
            'valor_total' => $fatura_excluida->valor_total,
            'status' => $fatura_excluida->status,
        ] : [];

        if ($this->Faturas_model->delete($id)) {
            // Auditoria: Registrar exclusão (RN 8.1)
            $this->load->helper('audit');
            log_delete('fatura', $id, $dados_anteriores);
            $this->session->set_flashdata('success', 'Fatura excluída com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Não é possível excluir esta fatura pois existem pagamentos vinculados.');
        }

                redirect(base_url() . 'index.php/faturas');
    }

    public function cancelar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para cancelar faturas.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
                redirect(base_url() . 'index.php/faturas');
        }

        if ($this->Faturas_model->edit($id, ['status' => 'cancelada'])) {
            $this->session->set_flashdata('success', 'Fatura cancelada com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao cancelar fatura.');
        }

                redirect(base_url() . 'index.php/faturas');
    }

    public function gerarPdf()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vFatura')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar PDFs de faturas.');
            redirect(base_url());
        }

        $id = $this->uri->segment(3);
        if ($id == null || !$this->Faturas_model->getById($id)) {
            $this->session->set_flashdata('error', 'Fatura não encontrada.');
                redirect(base_url() . 'index.php/faturas');
        }

        $this->load->helper('mpdf');
        $this->load->model('Sistema_model');
        
        $this->data['fatura'] = $this->Faturas_model->getById($id);
        $this->data['itens'] = $this->Faturas_model->getItens($id);
        $this->data['pagamentos'] = $this->Pagamentos_model->getByFatura($id);
        $this->data['emitente'] = $this->Sistema_model->getEmitente();
        
        $html = $this->load->view('faturas/pdf_fatura', $this->data, true);
        pdf_create($html, 'Fatura_' . $this->data['fatura']->numero, true);
    }
}

