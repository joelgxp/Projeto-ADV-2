<?php

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Relatorios extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Relatorios_model');
        $this->load->model('Usuarios_model');
        $this->load->model('Mapos_model');

        $this->data['menuRelatorios'] = 'Relatórios';
    }

    public function index()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vRelatorio')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para acessar relatórios.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/index';
        return $this->layout();
    }

    public function clientes()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_clientes';

        return $this->layout();
    }

    public function processos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de processos.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_processos';

        return $this->layout();
    }

    public function clientesCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');

        $data['dataInicial'] = date('d/m/Y', strtotime($dataInicial));
        $data['dataFinal'] = date('d/m/Y', strtotime($dataFinal));

        $data['clientes'] = $this->Relatorios_model->clientesCustom($dataInicial, $dataFinal, $this->input->get('tipocliente'));
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Clientes Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirClientes', $data, true);
        pdf_create($html, 'relatorio_clientes' . date('d/m/y'), true);
    }

    public function clientesRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de clientes.');
            redirect(base_url());
        }

        $format = $this->input->get('format');

        if ($format == 'xls') {
            $clientes = $this->Relatorios_model->clientesRapid($array = true);
            $cabecalho = [
                'Código' => 'integer',
                'Nome' => 'string',
                'Sexo' => 'string',
                'Pessoa Física' => 'string',
                'Documento' => 'string',
                'Telefone' => 'string',
                'Celular' => 'string',
                'Contato' => 'string',
                'E-mail' => 'string',
                'Fornecedor' => 'string',
                'Data de Cadastro' => 'YYYY-MM-DD',
                'Rua' => 'string',
                'Número' => 'string',
                'Complemento' => 'string',
                'Bairro' => 'string',
                'Cidade' => 'string',
                'Estado' => 'string',
                'CEP' => 'string',
            ];

            $writer = new XLSXWriter();

            $writer->writeSheetHeader('Sheet1', $cabecalho);
            foreach ($clientes as $cliente) {
                if ($cliente['fornecedor']) {
                    $cliente['fornecedor'] = 'sim';
                } else {
                    $cliente['fornecedor'] = 'não';
                }
                if ($cliente['pessoa_fisica']) {
                    $cliente['pessoa_fisica'] = 'sim';
                } else {
                    $cliente['pessoa_fisica'] = 'não';
                }
                $writer->writeSheetRow('Sheet1', $cliente);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_clientes.xlsx', $arquivo);

            return;
        }

        $data['clientes'] = $this->Relatorios_model->clientesRapid();
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Clientes';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');

        $html = $this->load->view('relatorios/imprimir/imprimirClientes', $data, true);
        pdf_create($html, 'relatorio_clientes' . date('d/m/y'), true);
    }

    public function processosRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de processos.');
            redirect(base_url());
        }

        $this->load->model('Processos_model');
        $data['processos'] = $this->Processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Processos';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirProcessos', $data, true);
        pdf_create($html, 'relatorio_processos' . date('d/m/y'), true);
    }

    public function processosCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rProcesso')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de processos.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $status = $this->input->get('status');
        $tipo = $this->input->get('tipo_processo');

        $this->load->model('Processos_model');
        $where = [];
        if ($dataInicial) {
            $where[] = "dataDistribuicao >= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataInicial))) . "'";
        }
        if ($dataFinal) {
            $where[] = "dataDistribuicao <= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataFinal))) . "'";
        }
        if ($status) {
            $where[] = "status = '" . $status . "'";
        }
        if ($tipo) {
            $where[] = "tipo_processo = '" . $tipo . "'";
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        $data['processos'] = $this->Processos_model->get('processos', '*', $whereClause, 0, 0, false, 'array');
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Processos Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirProcessos', $data, true);
        pdf_create($html, 'relatorio_processos' . date('d/m/y'), true);
    }

    public function prazos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de prazos.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_prazos';

        return $this->layout();
    }

    public function prazosVencidos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de prazos.');
            redirect(base_url());
        }

        $this->load->model('Prazos_model');
        $data['prazos'] = $this->Prazos_model->getPrazosVencidos();
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Prazos Vencidos';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirPrazos', $data, true);
        pdf_create($html, 'relatorio_prazos_vencidos' . date('d/m/y'), true);
    }

    public function audiencias()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de audiências.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_audiencias';

        return $this->layout();
    }

    public function audienciasAgendadas()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de audiências.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');

        $this->load->model('Audiencias_model');
        $where = [];
        if ($dataInicial) {
            $where[] = "DATE(dataHora) >= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataInicial))) . "'";
        }
        if ($dataFinal) {
            $where[] = "DATE(dataHora) <= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataFinal))) . "'";
        }
        $where[] = "status = 'Agendada'";
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '';
        $data['audiencias'] = $this->Audiencias_model->get('audiencias', '*', $whereClause, 0, 0, false, 'array');
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Audiências Agendadas';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirAudiencias', $data, true);
        pdf_create($html, 'relatorio_audiencias' . date('d/m/y'), true);
    }

    public function honorarios()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_honorarios';

        return $this->layout();
    }

    public function honorariosRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $format = $this->input->get('format');

        $this->load->model('Financeiro_model');
        $lancamentos = $this->Financeiro_model->get('lancamentos', '*', "tipo = 'honorario'", 0, 0, false, 'array');

        if ($format == 'xls') {
            $cabecalho = [
                'ID' => 'integer',
                'Descrição' => 'string',
                'Valor' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Status' => 'string',
                'Cliente' => 'string',
            ];

            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $cabecalho);
            
            foreach ($lancamentos as $lancamento) {
                $writer->writeSheetRow('Sheet1', [
                    $lancamento['idLancamentos'],
                    $lancamento['descricao'],
                    $lancamento['valor'],
                    $lancamento['data_vencimento'],
                    $lancamento['data_pagamento'] ?? '',
                    $lancamento['baixado'] ? 'Pago' : 'Pendente',
                    $lancamento['cliente_fornecedor'] ?? '',
                ]);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_honorarios.xlsx', $arquivo);
            return;
        }

        $data['lancamentos'] = $lancamentos;
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Honorários';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirHonorarios', $data, true);
        pdf_create($html, 'relatorio_honorarios' . date('d/m/y'), true);
    }

    public function honorariosCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $situacao = $this->input->get('situacao');
        $format = $this->input->get('format');

        $this->load->model('Financeiro_model');
        $where = "tipo = 'honorario'";
        
        if ($dataInicial) {
            $where .= " AND data_vencimento >= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataInicial))) . "'";
        }
        if ($dataFinal) {
            $where .= " AND data_vencimento <= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataFinal))) . "'";
        }
        if ($situacao !== '') {
            $where .= " AND baixado = " . intval($situacao);
        }

        $lancamentos = $this->Financeiro_model->get('lancamentos', '*', $where, 0, 0, false, 'array');

        if ($format == 'xls') {
            $cabecalho = [
                'ID' => 'integer',
                'Descrição' => 'string',
                'Valor' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Status' => 'string',
                'Cliente' => 'string',
            ];

            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $cabecalho);
            
            foreach ($lancamentos as $lancamento) {
                $writer->writeSheetRow('Sheet1', [
                    $lancamento['idLancamentos'],
                    $lancamento['descricao'],
                    $lancamento['valor'],
                    $lancamento['data_vencimento'],
                    $lancamento['data_pagamento'] ?? '',
                    $lancamento['baixado'] ? 'Pago' : 'Pendente',
                    $lancamento['cliente_fornecedor'] ?? '',
                ]);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_honorarios_custom.xlsx', $arquivo);
            return;
        }

        $data['lancamentos'] = $lancamentos;
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Honorários Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirHonorarios', $data, true);
        pdf_create($html, 'relatorio_honorarios_custom' . date('d/m/y'), true);
    }

    public function custas()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_custas';

        return $this->layout();
    }

    public function custasRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $format = $this->input->get('format');

        $this->load->model('Financeiro_model');
        $lancamentos = $this->Financeiro_model->get('lancamentos', '*', "tipo = 'custa'", 0, 0, false, 'array');

        if ($format == 'xls') {
            $cabecalho = [
                'ID' => 'integer',
                'Descrição' => 'string',
                'Valor' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Status' => 'string',
                'Cliente' => 'string',
            ];

            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $cabecalho);
            
            foreach ($lancamentos as $lancamento) {
                $writer->writeSheetRow('Sheet1', [
                    $lancamento['idLancamentos'],
                    $lancamento['descricao'],
                    $lancamento['valor'],
                    $lancamento['data_vencimento'],
                    $lancamento['data_pagamento'] ?? '',
                    $lancamento['baixado'] ? 'Pago' : 'Pendente',
                    $lancamento['cliente_fornecedor'] ?? '',
                ]);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_custas.xlsx', $arquivo);
            return;
        }

        $data['lancamentos'] = $lancamentos;
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Custas';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirCustas', $data, true);
        pdf_create($html, 'relatorio_custas' . date('d/m/y'), true);
    }

    public function custasCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $situacao = $this->input->get('situacao');
        $format = $this->input->get('format');

        $this->load->model('Financeiro_model');
        $where = "tipo = 'custa'";
        
        if ($dataInicial) {
            $where .= " AND data_vencimento >= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataInicial))) . "'";
        }
        if ($dataFinal) {
            $where .= " AND data_vencimento <= '" . date('Y-m-d', strtotime(str_replace('/', '-', $dataFinal))) . "'";
        }
        if ($situacao !== '') {
            $where .= " AND baixado = " . intval($situacao);
        }

        $lancamentos = $this->Financeiro_model->get('lancamentos', '*', $where, 0, 0, false, 'array');

        if ($format == 'xls') {
            $cabecalho = [
                'ID' => 'integer',
                'Descrição' => 'string',
                'Valor' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Status' => 'string',
                'Cliente' => 'string',
            ];

            $writer = new XLSXWriter();
            $writer->writeSheetHeader('Sheet1', $cabecalho);
            
            foreach ($lancamentos as $lancamento) {
                $writer->writeSheetRow('Sheet1', [
                    $lancamento['idLancamentos'],
                    $lancamento['descricao'],
                    $lancamento['valor'],
                    $lancamento['data_vencimento'],
                    $lancamento['data_pagamento'] ?? '',
                    $lancamento['baixado'] ? 'Pago' : 'Pendente',
                    $lancamento['cliente_fornecedor'] ?? '',
                ]);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_custas_custom.xlsx', $arquivo);
            return;
        }

        $data['lancamentos'] = $lancamentos;
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Custas Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirCustas', $data, true);
        pdf_create($html, 'relatorio_custas_custom' . date('d/m/y'), true);
    }

    // Métodos antigos de produtos, os, vendas e sku foram removidos

    public function servicos()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }
        $this->data['view'] = 'relatorios/rel_servicos';

        return $this->layout();
    }

    public function servicosCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }

        $precoInicial = $this->input->get('precoInicial');
        $precoFinal = $this->input->get('precoFinal');

        $data['servicos'] = $this->Relatorios_model->servicosCustom($precoInicial, $precoFinal);
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Serviços Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirServicos', $data, true);
        pdf_create($html, 'relatorio_servicos' . date('d/m/y'), true);
    }

    public function servicosRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios de serviços.');
            redirect(base_url());
        }

        $data['servicos'] = $this->Relatorios_model->servicosRapid();
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório de Serviços';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirServicos', $data, true);
        pdf_create($html, 'relatorio_servicos' . date('d/m/y'), true);
    }

    // Métodos os(), osRapid() e osCustom() foram removidos - adaptados para processos

    public function financeiro()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $this->data['view'] = 'relatorios/rel_financeiro';

        return $this->layout();
    }

    public function financeiroRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $format = $this->input->get('format');

        if ($format == 'xls') {
            $lancamentos = $this->Relatorios_model->financeiroRapid(true);

            $lancamentosFormatados = array_map(function ($item) {
                return [
                    'idLancamentos' => $item['idLancamentos'],
                    'descricao' => $item['descricao'],
                    'valor' => $item['valor'],
                    'desconto' => $item['desconto'],
                    'valor_desconto' => $item['valor_desconto'],
                    'tipo_desconto' => $item['tipo_desconto'],
                    'data_vencimento' => $item['data_vencimento'],
                    'data_pagamento' => $item['data_pagamento'],
                    'baixado' => $item['baixado'],
                    'cliente_fornecedor' => $item['cliente_fornecedor'],
                    'forma_pgto' => $item['forma_pgto'],
                    'tipo' => $item['tipo'],
                ];
            }, $lancamentos);

            $cabecalho = [
                'ID Lançamentos' => 'integer',
                'Descricao' => 'string',
                'Valor' => 'price',
                'Desconto' => 'price',
                'Tipo Desconto' => 'string',
                'Valor Com Desc.' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Baixado' => 'integer',
                'Cliente/Fornecedor' => 'string',
                'Forma Pagamento' => 'string',
                'Tipo' => 'string',
            ];

            $writer = new XLSXWriter();

            $writer->writeSheetHeader('Sheet1', $cabecalho);
            foreach ($lancamentosFormatados as $lancamento) {
                $writer->writeSheetRow('Sheet1', $lancamento);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_financeiro.xlsx', $arquivo);

            return;
        }

        $data['lancamentos'] = $this->Relatorios_model->financeiroRapid();
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório Financeiro';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirFinanceiro', $data, true);
        pdf_create($html, 'relatorio_os' . date('d/m/y'), true);
    }

    public function financeiroCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');
        $tipo = $this->input->get('tipo');
        $situacao = $this->input->get('situacao');
        $format = $this->input->get('format');

        if ($format == 'xls') {
            $lancamentos = $this->Relatorios_model->financeiroCustom($dataInicial, $dataFinal, $tipo, $situacao, true);

            $lancamentosFormatados = array_map(function ($item) {
                return [
                    'idLancamentos' => $item['idLancamentos'],
                    'descricao' => $item['descricao'],
                    'valor' => $item['valor'],
                    'desconto' => $item['desconto'],
                    'valor_desconto' => $item['valor_desconto'],
                    'tipo_desconto' => $item['tipo_desconto'],
                    'data_vencimento' => $item['data_vencimento'],
                    'data_pagamento' => $item['data_pagamento'],
                    'baixado' => $item['baixado'],
                    'cliente_fornecedor' => $item['cliente_fornecedor'],
                    'forma_pgto' => $item['forma_pgto'],
                    'tipo' => $item['tipo'],
                ];
            }, $lancamentos);

            $cabecalho = [
                'ID Lançamentos' => 'integer',
                'Descricao' => 'string',
                'Valor' => 'price',
                'Desconto' => 'price',
                'Tipo Desconto' => 'string',
                'Valor Com Desc.' => 'price',
                'Data Vencimento' => 'YYYY-MM-DD',
                'Data Pagamento' => 'YYYY-MM-DD',
                'Baixado' => 'integer',
                'Cliente/Fornecedor' => 'string',
                'Forma Pagamento' => 'string',
                'Tipo' => 'string',
            ];

            $writer = new XLSXWriter();

            $writer->writeSheetHeader('Sheet1', $cabecalho);
            foreach ($lancamentosFormatados as $lancamento) {
                $writer->writeSheetRow('Sheet1', $lancamento);
            }

            $arquivo = $writer->writeToString();
            $this->load->helper('download');
            force_download('relatorio_financeiro_custom.xlsx', $arquivo);

            return;
        }

        $data['lancamentos'] = $this->Relatorios_model->financeiroCustom($dataInicial, $dataFinal, $tipo, $situacao);
        $data['emitente'] = $this->Mapos_model->getEmitente();
        $data['title'] = 'Relatório Financeiro Customizado';
        $data['topo'] = $this->load->view('relatorios/imprimir/imprimirTopo', $data, true);

        $this->load->helper('mpdf');
        $html = $this->load->view('relatorios/imprimir/imprimirFinanceiro', $data, true);
        pdf_create($html, 'relatorio_financeiro' . date('d/m/y'), true);
    }

    // Métodos vendas(), vendasRapid() e vendasCustom() foram removidos - não aplicáveis ao contexto jurídico

    public function receitasBrutasMei()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $this->data['view'] = 'relatorios/rel_receitas_brutas_mei';

        return $this->layout();
    }

    public function receitasBrutasRapid()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $this->load->helper('download');
        $this->load->helper('file');

        $format = $this->input->get('format') ?: 'docx';

        $templatePath = realpath(FCPATH . 'assets/relatorios/RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI.docx');
        if (! $templatePath) {
            $this->session->set_flashdata('error', 'Modelo de relatório não encontrado!');

            return redirect('/relatorios/receitasBrutasMei');
        }

        $tempFilePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'temp.docx';
        $generatedFilePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . "RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI_GERADO.$format";

        $templateProcessor = new TemplateProcessor($templatePath);
        $data = $this->Relatorios_model->receitasBrutasRapid();
        $templateProcessor->setValues($data);

        if ($format === 'docx') {
            $templateProcessor->saveAs($generatedFilePath);

            $fileContents = file_get_contents($generatedFilePath);
            unlink($generatedFilePath);

            return force_download("relatorio_receitas_brutas_mei_rapido.$format", $fileContents);
        } else {
            Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
            Settings::setPdfRendererPath('.');

            $templateProcessor->saveAs($tempFilePath);
            $template = IOFactory::load($tempFilePath);
            $pdfWriter = IOFactory::createWriter($template, 'PDF');
            $pdfWriter->save($generatedFilePath);

            $fileContents = file_get_contents($generatedFilePath);
            unlink($tempFilePath);
            unlink($generatedFilePath);

            return $this->output
                ->set_header('Content-disposition: inline;filename=' . "relatorio_receitas_brutas_mei_rapido.$format")
                ->set_content_type(get_mime_by_extension($generatedFilePath))
                ->set_status_header(200)
                ->set_output($fileContents)
                ->_display();
        }
    }

    public function receitasBrutasCustom()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar relatórios financeiros.');
            redirect(base_url());
        }

        $this->load->helper('download');
        $this->load->helper('file');

        $format = $this->input->get('format') ?: 'docx';
        $dataInicial = $this->input->get('dataInicial');
        $dataFinal = $this->input->get('dataFinal');

        $templatePath = realpath(FCPATH . 'assets/relatorios/RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI.docx');
        if (! $templatePath) {
            $this->session->set_flashdata('error', 'Modelo de relatório não encontrado!');

            return redirect('/relatorios/receitasBrutasMei');
        }

        $tempFilePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . 'temp.docx';
        $generatedFilePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR . "RELATORIO_MENSAL_DAS_RECEITAS_BRUTAS_MEI_GERADO.$format";

        $templateProcessor = new TemplateProcessor($templatePath);
        $data = $this->Relatorios_model->receitasBrutasCustom($dataInicial, $dataFinal);
        $templateProcessor->setValues($data);

        if ($format === 'docx') {
            $templateProcessor->saveAs($generatedFilePath);

            $fileContents = file_get_contents($generatedFilePath);
            unlink($generatedFilePath);

            return force_download(
                sprintf(
                    "relatorio_receitas_brutas_mei_custom_%s_até_%s.$format",
                    $dataInicial,
                    $dataFinal
                ),
                $fileContents
            );
        } else {
            Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
            Settings::setPdfRendererPath('.');

            $templateProcessor->saveAs($tempFilePath);
            $template = IOFactory::load($tempFilePath);
            $pdfWriter = IOFactory::createWriter($template, 'PDF');
            $pdfWriter->save($generatedFilePath);

            $fileContents = file_get_contents($generatedFilePath);
            unlink($tempFilePath);
            unlink($generatedFilePath);

            return $this->output
                ->set_header('Content-disposition: inline;filename=' . "relatorio_receitas_brutas_mei_custom_%s_até_%s.$format")
                ->set_content_type(get_mime_by_extension($generatedFilePath))
                ->set_status_header(200)
                ->set_output($fileContents)
                ->_display();
        }
    }
}
