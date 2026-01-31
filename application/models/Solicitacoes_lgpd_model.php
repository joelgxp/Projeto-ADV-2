<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Solicitacoes_lgpd_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria nova solicitação LGPD
     * 
     * @param array $data Dados da solicitação
     * @return int|false ID da solicitação ou false
     */
    public function add($data)
    {
        $ci = &get_instance();
        
        $data['ip'] = $ci->input->ip_address();
        $data['data_solicitacao'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pendente';
        
        $this->db->insert('solicitacoes_lgpd', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Atualiza status da solicitação
     * 
     * @param int $id ID da solicitação
     * @param string $status Novo status
     * @param string $observacoes Observações
     * @return bool
     */
    public function atualizarStatus($id, $status, $observacoes = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($status == 'concluida') {
            $data['data_conclusao'] = date('Y-m-d H:i:s');
        }
        
        if ($observacoes !== null) {
            $data['observacoes'] = $observacoes;
        }
        
        $this->db->where('id', $id);
        $this->db->update('solicitacoes_lgpd', $data);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Obtém solicitações de um cliente
     * 
     * @param int $cliente_id ID do cliente
     * @return array
     */
    public function getByCliente($cliente_id)
    {
        $this->db->where('clientes_id', $cliente_id);
        $this->db->order_by('data_solicitacao', 'desc');
        return $this->db->get('solicitacoes_lgpd')->result();
    }

    /**
     * Obtém todas as solicitações com filtros
     * 
     * @param array $filtros Filtros (status, tipo_solicitacao)
     * @param int $perpage Registros por página
     * @param int $start Offset
     * @return array
     */
    public function getWithFilters($filtros = [], $perpage = 0, $start = 0)
    {
        $this->db->select('s.*, c.nomeCliente');
        $this->db->from('solicitacoes_lgpd s');
        $this->db->join('clientes c', 'c.idClientes = s.clientes_id', 'left');
        
        if (!empty($filtros['status'])) {
            $this->db->where('s.status', $filtros['status']);
        }
        
        if (!empty($filtros['tipo_solicitacao'])) {
            $this->db->where('s.tipo_solicitacao', $filtros['tipo_solicitacao']);
        }
        
        $this->db->order_by('s.data_solicitacao', 'desc');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Solicitacoes_lgpd_model::getWithFilters: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Conta solicitações com filtros
     * 
     * @param array $filtros Filtros
     * @return int
     */
    public function countWithFilters($filtros = [])
    {
        $this->db->from('solicitacoes_lgpd');
        
        if (!empty($filtros['status'])) {
            $this->db->where('status', $filtros['status']);
        }
        
        if (!empty($filtros['tipo_solicitacao'])) {
            $this->db->where('tipo_solicitacao', $filtros['tipo_solicitacao']);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Executa direito ao esquecimento (anonimiza dados)
     * 
     * @param int $cliente_id ID do cliente
     * @return bool
     */
    public function executarEsquecimento($cliente_id)
    {
        $this->load->model('clientes_model');
        
        // Anonimizar dados do cliente
        $data_anonimo = [
            'nomeCliente' => 'Cliente Anonimizado',
            'documento' => null,
            'email' => null,
            'telefone' => null,
            'celular' => null,
            'endereco' => null,
            'numero' => null,
            'complemento' => null,
            'bairro' => null,
            'cidade' => null,
            'estado' => null,
            'cep' => null,
        ];
        
        $this->db->where('idClientes', $cliente_id);
        $this->db->update('clientes', $data_anonimo);
        
        // Registrar na auditoria
        $this->load->helper('audit');
        log_info("Direito ao esquecimento executado para cliente ID: {$cliente_id}");
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Gera arquivo de portabilidade de dados
     * 
     * @param int $cliente_id ID do cliente
     * @return array Dados do cliente em formato JSON
     */
    public function gerarPortabilidade($cliente_id)
    {
        $this->load->model('clientes_model');
        $this->load->model('processos_model');
        $this->load->model('Contratos_model');
        $this->load->model('Faturas_model');
        
        $cliente = $this->clientes_model->getById($cliente_id);
        
        if (!$cliente) {
            return false;
        }
        
        // Coletar todos os dados do cliente
        $dados = [
            'cliente' => (array)$cliente,
            'processos' => $this->processos_model->get('processos', '*', ['clientes_id' => $cliente_id], 0, 0, false, 'array'),
            'contratos' => $this->Contratos_model->getByCliente($cliente_id),
            'faturas' => $this->Faturas_model->getByCliente($cliente_id),
        ];
        
        return $dados;
    }
}

