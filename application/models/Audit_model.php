<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Audit_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idLogs', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function add($data)
    {
        // Adicionar user_agent se não foi fornecido (RN 1.4)
        if (!isset($data['user_agent'])) {
            $ci = &get_instance();
            $data['user_agent'] = $ci->input->user_agent();
        }
        
        $this->db->insert('logs', $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all('logs');
    }

    public function clean()
    {
        $this->db->where('data <', date('Y-m-d', strtotime('- 30 days')));
        $this->db->delete('logs');

        if ($this->db->affected_rows()) {
            return true;
        }

        return false;
    }

    /**
     * Registra um acesso a uma entidade
     *
     * @param string $usuario Nome do usuário
     * @param string $entidade_tipo Tipo de entidade (cliente, processo, etc.)
     * @param int $entidade_id ID da entidade
     * @param string $acao Ação realizada (visualizar, editar, excluir)
     * @param bool $dados_sensiveis Se dados sensíveis foram acessados
     * @return bool
     */
    public function log_access($usuario, $entidade_tipo, $entidade_id, $acao, $dados_sensiveis = false)
    {
        $ci = &get_instance();
        $ip = $ci->input->ip_address();
        
        $data = [
            'usuario' => $usuario,
            'ip' => $ip,
            'tarefa' => ucfirst($acao) . ' ' . $entidade_tipo,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $entidade_tipo,
            'entidade_id' => $entidade_id,
            'acao' => $acao,
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $this->add($data);
    }

    /**
     * Registra uma edição em uma entidade
     *
     * @param string $usuario Nome do usuário
     * @param string $entidade_tipo Tipo de entidade
     * @param int $entidade_id ID da entidade
     * @param string $campo Campo alterado
     * @param mixed $valor_anterior Valor anterior
     * @param mixed $valor_novo Valor novo
     * @return bool
     */
    public function log_edit($usuario, $entidade_tipo, $entidade_id, $campo, $valor_anterior, $valor_novo)
    {
        // Verificar se o campo é sensível
        $campos_sensiveis = ['rg', 'cpf', 'cnpj', 'documento', 'filiacao', 'email', 'telefone', 'celular', 'razao_social', 'inscricao_estadual', 'inscricao_municipal', 'representantes_legais', 'socios'];
        $dados_sensiveis = in_array(strtolower($campo), $campos_sensiveis);

        $ci = &get_instance();
        $ip = $ci->input->ip_address();

        $data = [
            'usuario' => $usuario,
            'ip' => $ip,
            'tarefa' => 'Editar ' . $entidade_tipo . ' - Campo: ' . $campo,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $entidade_tipo,
            'entidade_id' => $entidade_id,
            'acao' => 'editar',
            'campo_alterado' => $campo,
            'valor_anterior' => is_array($valor_anterior) ? json_encode($valor_anterior) : (string)$valor_anterior,
            'valor_novo' => is_array($valor_novo) ? json_encode($valor_novo) : (string)$valor_novo,
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $this->add($data);
    }

    /**
     * Busca logs por entidade
     *
     * @param string $entidade_tipo Tipo de entidade
     * @param int $entidade_id ID da entidade
     * @param int $limit Limite de resultados
     * @return array
     */
    public function get_by_entity($entidade_tipo, $entidade_id, $limit = 50)
    {
        $this->db->where('entidade_tipo', $entidade_tipo);
        $this->db->where('entidade_id', $entidade_id);
        $this->db->order_by('idLogs', 'desc');
        $this->db->limit($limit);

        $query = $this->db->get('logs');

        if ($query === false) {
            log_message('error', 'Erro na query Audit_model::get_by_entity: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Busca acessos a dados sensíveis
     *
     * @param int $limit Limite de resultados
     * @return array
     */
    public function get_sensitive_access($limit = 50)
    {
        $this->db->where('dados_sensiveis', 1);
        $this->db->order_by('idLogs', 'desc');
        $this->db->limit($limit);

        $query = $this->db->get('logs');

        if ($query === false) {
            log_message('error', 'Erro na query Audit_model::get_sensitive_access: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }
}

/* End of file Log_model.php */
/* Location: ./application/models/Log_model.php */
