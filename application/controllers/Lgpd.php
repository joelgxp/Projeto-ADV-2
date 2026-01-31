<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lgpd extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para acessar esta área.');
            redirect(base_url());
        }
        
        $this->load->model('Consentimentos_lgpd_model');
        $this->load->model('Solicitacoes_lgpd_model');
        $this->load->helper('lgpd');
        $this->data['menuConfiguracoes'] = 'LGPD';
    }

    /**
     * Lista solicitações LGPD
     */
    public function index()
    {
        $this->load->library('pagination');
        
        $filtros = [
            'status' => $this->input->get('status'),
            'tipo_solicitacao' => $this->input->get('tipo_solicitacao'),
        ];
        
        $filtros = array_filter($filtros, function($value) {
            return $value !== '' && $value !== null;
        });
        
        $this->data['configuration']['base_url'] = site_url('lgpd/index/');
        $this->data['configuration']['total_rows'] = $this->Solicitacoes_lgpd_model->countWithFilters($filtros);
        
        $query_string = http_build_query($filtros);
        if ($query_string) {
            $this->data['configuration']['base_url'] .= '?' . $query_string . '&';
        }
        
        $this->pagination->initialize($this->data['configuration']);
        
        $this->data['solicitacoes'] = $this->Solicitacoes_lgpd_model->getWithFilters($filtros, $this->data['configuration']['per_page'], $this->uri->segment(3));
        $this->data['filtros'] = $filtros;
        
        $this->data['view'] = 'lgpd/index';
        return $this->layout();
    }

    /**
     * Visualiza detalhes de uma solicitação
     */
    public function visualizar()
    {
        $id = $this->uri->segment(3);
        if (!$id || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Solicitação não encontrada.');
            redirect(site_url('lgpd'));
        }
        
        $this->db->select('s.*, c.nomeCliente, c.email');
        $this->db->from('solicitacoes_lgpd s');
        $this->db->join('clientes c', 'c.idClientes = s.clientes_id', 'left');
        $this->db->where('s.id', $id);
        $this->data['solicitacao'] = $this->db->get()->row();
        
        if (!$this->data['solicitacao']) {
            $this->session->set_flashdata('error', 'Solicitação não encontrada.');
            redirect(site_url('lgpd'));
        }
        
        $this->data['view'] = 'lgpd/visualizar';
        return $this->layout();
    }

    /**
     * Processa solicitação de direito ao esquecimento
     */
    public function processar_esquecimento()
    {
        $id = $this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('error', 'Solicitação inválida.');
            redirect(site_url('lgpd'));
        }
        
        $this->db->where('id', $id);
        $solicitacao = $this->db->get('solicitacoes_lgpd')->row();
        
        if (!$solicitacao || $solicitacao->tipo_solicitacao != 'esquecimento') {
            $this->session->set_flashdata('error', 'Solicitação inválida.');
            redirect(site_url('lgpd'));
        }
        
        // Executar anonimização
        if ($this->Solicitacoes_lgpd_model->executarEsquecimento($solicitacao->clientes_id)) {
            $this->Solicitacoes_lgpd_model->atualizarStatus($id, 'concluida', 'Dados anonimizados com sucesso.');
            $this->session->set_flashdata('success', 'Direito ao esquecimento executado com sucesso.');
        } else {
            $this->session->set_flashdata('error', 'Erro ao executar direito ao esquecimento.');
        }
        
        redirect(site_url('lgpd/visualizar/' . $id));
    }

    /**
     * Gera arquivo de portabilidade
     */
    public function gerar_portabilidade()
    {
        $id = $this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('error', 'Solicitação inválida.');
            redirect(site_url('lgpd'));
        }
        
        $this->db->where('id', $id);
        $solicitacao = $this->db->get('solicitacoes_lgpd')->row();
        
        if (!$solicitacao || $solicitacao->tipo_solicitacao != 'portabilidade') {
            $this->session->set_flashdata('error', 'Solicitação inválida.');
            redirect(site_url('lgpd'));
        }
        
        $dados = $this->Solicitacoes_lgpd_model->gerarPortabilidade($solicitacao->clientes_id);
        
        if ($dados) {
            // Salvar dados na solicitação
            $this->db->where('id', $id);
            $this->db->update('solicitacoes_lgpd', [
                'dados_solicitados' => json_encode($dados, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            // Gerar arquivo JSON para download
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="portabilidade_cliente_' . $solicitacao->clientes_id . '_' . date('Y-m-d_His') . '.json"');
            echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $this->session->set_flashdata('error', 'Erro ao gerar arquivo de portabilidade.');
            redirect(site_url('lgpd/visualizar/' . $id));
        }
    }

    /**
     * Atualiza status da solicitação
     */
    public function atualizar_status()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $observacoes = $this->input->post('observacoes');
        
        if (!$id || !$status) {
            $this->session->set_flashdata('error', 'Dados inválidos.');
            redirect(site_url('lgpd'));
        }
        
        if ($this->Solicitacoes_lgpd_model->atualizarStatus($id, $status, $observacoes)) {
            $this->session->set_flashdata('success', 'Status atualizado com sucesso.');
        } else {
            $this->session->set_flashdata('error', 'Erro ao atualizar status.');
        }
        
        redirect(site_url('lgpd/visualizar/' . $id));
    }
}

