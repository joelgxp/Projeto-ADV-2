<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Historico_alteracoes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registra alteração em um registro
     * 
     * @param string $tabela Nome da tabela
     * @param int $registro_id ID do registro
     * @param string $acao Ação (create, update, delete)
     * @param array $dados_anteriores Dados anteriores (para update/delete)
     * @param array $dados_novos Dados novos (para create/update)
     * @param string $campo Campo específico (opcional, para alterações de campo único)
     * @return int|false ID do registro ou false
     */
    public function registrar($tabela, $registro_id, $acao, $dados_anteriores = [], $dados_novos = [], $campo = null)
    {
        $ci = &get_instance();
        
        $usuario_id = $ci->session->userdata('id_admin');
        $usuario_nome = $ci->session->userdata('nome_admin') ?: 'Sistema';
        
        $data = [
            'tabela' => $tabela,
            'registro_id' => $registro_id,
            'campo' => $campo,
            'acao' => $acao,
            'usuario_id' => $usuario_id,
            'usuario_nome' => $usuario_nome,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'data_alteracao' => date('Y-m-d H:i:s'),
        ];
        
        // Converter arrays para JSON
        if (!empty($dados_anteriores)) {
            $data['valor_anterior'] = is_array($dados_anteriores) ? json_encode($dados_anteriores, JSON_UNESCAPED_UNICODE) : $dados_anteriores;
        }
        
        if (!empty($dados_novos)) {
            $data['valor_novo'] = is_array($dados_novos) ? json_encode($dados_novos, JSON_UNESCAPED_UNICODE) : $dados_novos;
        }
        
        $this->db->insert('historico_alteracoes', $data);
        
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Obtém histórico de alterações de um registro
     * 
     * @param string $tabela Nome da tabela
     * @param int $registro_id ID do registro
     * @param int $limit Limite de resultados
     * @return array
     */
    public function getByRegistro($tabela, $registro_id, $limit = 50)
    {
        $this->db->where('tabela', $tabela);
        $this->db->where('registro_id', $registro_id);
        $this->db->order_by('data_alteracao', 'desc');
        $this->db->limit($limit);
        
        $query = $this->db->get('historico_alteracoes');
        
        if ($query === false) {
            log_message('error', 'Erro na query Historico_alteracoes_model::getByRegistro: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        $result = $query->result();
        
        // Decodificar JSON
        foreach ($result as $item) {
            if (!empty($item->valor_anterior)) {
                $decoded = json_decode($item->valor_anterior, true);
                if ($decoded !== null) {
                    $item->valor_anterior = $decoded;
                }
            }
            if (!empty($item->valor_novo)) {
                $decoded = json_decode($item->valor_novo, true);
                if ($decoded !== null) {
                    $item->valor_novo = $decoded;
                }
            }
        }
        
        return $result;
    }

    /**
     * Compara dois estados de um registro
     * 
     * @param string $tabela Nome da tabela
     * @param int $registro_id ID do registro
     * @param int $historico_id_antes ID do histórico antes
     * @param int $historico_id_depois ID do histórico depois
     * @return array Comparação
     */
    public function comparar($tabela, $registro_id, $historico_id_antes, $historico_id_depois)
    {
        $this->db->where('id', $historico_id_antes);
        $antes = $this->db->get('historico_alteracoes')->row();
        
        $this->db->where('id', $historico_id_depois);
        $depois = $this->db->get('historico_alteracoes')->row();
        
        if (!$antes || !$depois) {
            return false;
        }
        
        $antes_data = json_decode($antes->valor_anterior, true) ?: [];
        $depois_data = json_decode($depois->valor_novo, true) ?: [];
        
        $comparacao = [
            'antes' => $antes_data,
            'depois' => $depois_data,
            'alteracoes' => [],
        ];
        
        // Identificar campos alterados
        $todos_campos = array_unique(array_merge(array_keys($antes_data), array_keys($depois_data)));
        
        foreach ($todos_campos as $campo) {
            $valor_antes = $antes_data[$campo] ?? null;
            $valor_depois = $depois_data[$campo] ?? null;
            
            if ($valor_antes != $valor_depois) {
                $comparacao['alteracoes'][$campo] = [
                    'antes' => $valor_antes,
                    'depois' => $valor_depois,
                ];
            }
        }
        
        return $comparacao;
    }
}

