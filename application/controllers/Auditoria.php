<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auditoria extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
            redirect(base_url());
        }
        $this->load->model('Audit_model');
        $this->data['menuConfiguracoes'] = 'Auditoria';
    }

    public function index()
    {
        $this->load->library('pagination');

        // Coletar filtros
        $filtros = [
            'usuario' => $this->input->get('usuario'),
            'acao' => $this->input->get('acao'),
            'modulo' => $this->input->get('modulo'),
            'data_inicio' => $this->input->get('data_inicio'),
            'data_fim' => $this->input->get('data_fim'),
            'dados_sensiveis' => $this->input->get('dados_sensiveis'),
            'pesquisa' => $this->input->get('pesquisa'),
        ];
        
        // Remover filtros vazios
        $filtros = array_filter($filtros, function($value) {
            return $value !== '' && $value !== null;
        });

        // Configurar paginação
        $this->data['configuration']['base_url'] = site_url('auditoria/index/');
        $this->data['configuration']['total_rows'] = $this->Audit_model->countWithFilters($filtros);
        
        // Preservar filtros na URL
        $query_string = http_build_query($filtros);
        if ($query_string) {
            $this->data['configuration']['base_url'] .= '?' . $query_string . '&';
        }

        $this->pagination->initialize($this->data['configuration']);

        // Buscar logs com filtros
        $this->data['results'] = $this->Audit_model->getWithFilters($filtros, $this->data['configuration']['per_page'], $this->uri->segment(3));
        
        // Passar filtros para a view
        $this->data['filtros'] = $filtros;
        
        // Obter lista de usuários únicos para filtro
        $this->db->select('DISTINCT usuario');
        $this->db->from('logs');
        $this->db->order_by('usuario', 'asc');
        $this->data['usuarios'] = $this->db->get()->result();
        
        // Obter lista de ações únicas
        $this->db->select('DISTINCT acao');
        $this->db->from('logs');
        $this->db->where('acao IS NOT NULL');
        $this->db->where('acao !=', '');
        $this->db->order_by('acao', 'asc');
        $this->data['acoes'] = $this->db->get()->result();
        
        // Obter lista de módulos únicos
        $this->db->select('DISTINCT entidade_tipo as modulo');
        $this->db->from('logs');
        $this->db->where('entidade_tipo IS NOT NULL');
        $this->db->where('entidade_tipo !=', '');
        $this->db->order_by('entidade_tipo', 'asc');
        $this->data['modulos'] = $this->db->get()->result();

        $this->data['view'] = 'auditoria/logs';
        return $this->layout();
    }
    
    /**
     * Visualiza detalhes de um log específico
     */
    public function visualizar()
    {
        $id = $this->uri->segment(3);
        if (!$id || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Log não encontrado.');
            redirect(site_url('auditoria'));
        }
        
        $this->data['log'] = $this->Audit_model->get('logs', '*', ['idLogs' => $id], 0, 0, true);
        
        if (!$this->data['log']) {
            $this->session->set_flashdata('error', 'Log não encontrado.');
            redirect(site_url('auditoria'));
        }
        
        // Decodificar JSON se existir
        if (!empty($this->data['log']->dados_anteriores)) {
            $this->data['dados_anteriores'] = json_decode($this->data['log']->dados_anteriores, true);
        }
        if (!empty($this->data['log']->dados_novos)) {
            $this->data['dados_novos'] = json_decode($this->data['log']->dados_novos, true);
        }
        
        $this->data['view'] = 'auditoria/visualizar';
        return $this->layout();
    }
    
    /**
     * Exporta logs em CSV
     */
    public function exportar()
    {
        // Coletar filtros (mesmos do index)
        $filtros = [
            'usuario' => $this->input->get('usuario'),
            'acao' => $this->input->get('acao'),
            'modulo' => $this->input->get('modulo'),
            'data_inicio' => $this->input->get('data_inicio'),
            'data_fim' => $this->input->get('data_fim'),
            'dados_sensiveis' => $this->input->get('dados_sensiveis'),
            'pesquisa' => $this->input->get('pesquisa'),
        ];
        
        $filtros = array_filter($filtros, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Buscar todos os logs (sem paginação)
        $logs = $this->Audit_model->getWithFilters($filtros, 0, 0);
        
        // Configurar headers para download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="auditoria_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, [
            'ID',
            'Usuário',
            'Data',
            'Hora',
            'IP',
            'Ação',
            'Módulo',
            'Registro ID',
            'Tarefa',
            'Dados Sensíveis',
            'User Agent'
        ], ';');
        
        // Dados
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->idLogs,
                $log->usuario,
                $log->data,
                $log->hora,
                $log->ip,
                $log->acao ?? '',
                $log->entidade_tipo ?? '',
                $log->entidade_id ?? '',
                $log->tarefa,
                $log->dados_sensiveis ? 'Sim' : 'Não',
                $log->user_agent ?? ''
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    public function clean()
    {
        if ($this->Audit_model->clean()) {
            log_info('Efetuou limpeza de logs');
            $this->session->set_flashdata('success', 'Limpeza de logs realizada com sucesso.');
        } else {
            $this->session->set_flashdata('error', 'Nenhum log com mais de 30 dias encontrado.');
        }
        redirect(site_url('auditoria'));
    }

    /**
     * Visualiza logs de auditoria de um cliente específico
     */
    public function cliente($cliente_id = null)
    {
        if (!$cliente_id || !is_numeric($cliente_id)) {
            $this->session->set_flashdata('error', 'Cliente inválido.');
            redirect(site_url('auditoria'));
        }

        $this->load->model('clientes_model');
        $cliente = $this->clientes_model->getById($cliente_id);
        
        if (!$cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado.');
            redirect(site_url('auditoria'));
        }

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('auditoria/cliente/' . $cliente_id . '/');
        $this->data['configuration']['total_rows'] = count($this->Audit_model->get_by_entity('cliente', $cliente_id, 1000));

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->Audit_model->get_by_entity('cliente', $cliente_id, $this->data['configuration']['per_page'], $this->uri->segment(4));
        $this->data['cliente'] = $cliente;
        $this->data['view'] = 'auditoria/cliente';

        return $this->layout();
    }
}

/* End of file Auditoria.php */
