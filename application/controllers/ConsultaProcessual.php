<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class ConsultaProcessual extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('cnjapi');
        $this->load->model('processos_model');
        $this->load->model('movimentacoes_processuais_model');
        $this->data['menuConsultaProcessual'] = 'consulta-processual';
    }

    public function index()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para consultar processos na API.');
            redirect(base_url());
        }

        $this->data['view'] = 'consultaProcessual/index';
        return $this->layout();
    }

    public function consultar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Você não tem permissão para consultar processos.']));
            return;
        }

        $numeroProcesso = $this->input->post('numero_processo') ?: $this->input->get('numero_processo');
        $tribunal = $this->input->post('tribunal') ?: $this->input->get('tribunal');

        if (empty($numeroProcesso)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Número do processo é obrigatório.']));
            return;
        }

        // Consulta na API
        $resultado = $this->cnjapi->consultarProcesso($numeroProcesso, $tribunal);

        if ($resultado === false) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Erro ao consultar processo. Verifique o número e tente novamente.'
                ]));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $resultado
            ]));
    }

    public function sincronizar($processoId = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'sProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para sincronizar processos.');
            redirect(base_url());
        }

        if ($processoId == null) {
            $processoId = $this->input->post('processo_id');
        }

        if ($processoId == null) {
            $this->session->set_flashdata('error', 'ID do processo não informado.');
            redirect(site_url('processos/gerenciar'));
        }

        // Busca processo
        $processo = $this->processos_model->getById($processoId);
        if (!$processo) {
            $this->session->set_flashdata('error', 'Processo não encontrado.');
            redirect(site_url('processos/gerenciar'));
        }

        // Sincroniza movimentações
        $movimentacoes = $this->cnjapi->sincronizarMovimentacoes($processoId, $processo->numeroProcesso);

        if ($movimentacoes === false) {
            $this->session->set_flashdata('error', 'Erro ao sincronizar movimentações.');
        } else {
            $total = count($movimentacoes);
            if ($total > 0) {
                $this->session->set_flashdata('success', "Sincronização concluída. {$total} movimentação(ões) importada(s).");
                
                // Atualiza última consulta
                $this->processos_model->edit('processos', [
                    'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
                    'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 day'))
                ], 'idProcessos', $processoId);
            } else {
                $this->session->set_flashdata('info', 'Nenhuma movimentação nova encontrada.');
            }
        }

        redirect(site_url('processos/visualizar/' . $processoId));
    }
}

