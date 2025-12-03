<?php

class Advogados_processo_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca todos os advogados ativos de um processo
     */
    public function getByProcesso($processos_id, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return [];
        }

        $this->db->select('advogados_processo.*, usuarios.nome as nome_usuario, usuarios.email as email_usuario');
        $this->db->from('advogados_processo');
        $this->db->join('usuarios', 'usuarios.idUsuarios = advogados_processo.usuarios_id', 'left');
        $this->db->where('advogados_processo.processos_id', $processos_id);
        
        if ($apenas_ativos) {
            $this->db->where('advogados_processo.ativo', 1);
        }
        
        $this->db->order_by('advogados_processo.papel', 'ASC');
        $this->db->order_by('advogados_processo.data_atribuicao', 'ASC');
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Advogados_processo_model::getByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Busca advogados por papel
     */
    public function getByPapel($processos_id, $papel, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return [];
        }

        $this->db->select('advogados_processo.*, usuarios.nome as nome_usuario, usuarios.email as email_usuario');
        $this->db->from('advogados_processo');
        $this->db->join('usuarios', 'usuarios.idUsuarios = advogados_processo.usuarios_id', 'left');
        $this->db->where('advogados_processo.processos_id', $processos_id);
        $this->db->where('advogados_processo.papel', $papel);
        
        if ($apenas_ativos) {
            $this->db->where('advogados_processo.ativo', 1);
        }
        
        $this->db->order_by('advogados_processo.data_atribuicao', 'ASC');
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Advogados_processo_model::getByPapel: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Busca advogado principal de um processo
     */
    public function getPrincipal($processos_id)
    {
        $result = $this->getByPapel($processos_id, 'principal', true);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Verifica se processo tem advogado principal
     */
    public function hasPrincipal($processos_id)
    {
        return $this->getPrincipal($processos_id) !== null;
    }

    /**
     * Busca um advogado específico
     */
    public function getById($id)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return false;
        }

        $this->db->select('advogados_processo.*, usuarios.nome as nome_usuario, usuarios.email as email_usuario');
        $this->db->from('advogados_processo');
        $this->db->join('usuarios', 'usuarios.idUsuarios = advogados_processo.usuarios_id', 'left');
        $this->db->where('advogados_processo.idAdvogadoProcesso', $id);
        $this->db->limit(1);
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Advogados_processo_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    /**
     * Adiciona um advogado a um processo
     */
    public function add($data)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            log_message('error', "Tabela 'advogados_processo' não existe em Advogados_processo_model::add");
            return false;
        }

        // Se não informou data_atribuicao, usar data atual
        if (!isset($data['data_atribuicao']) || empty($data['data_atribuicao'])) {
            $data['data_atribuicao'] = date('Y-m-d H:i:s');
        }

        // Validar papel
        $papeis_validos = ['principal', 'coadjuvante', 'estagiario'];
        if (isset($data['papel']) && !in_array(strtolower($data['papel']), $papeis_validos)) {
            log_message('error', "Papel inválido: {$data['papel']}. Papéis válidos: " . implode(', ', $papeis_validos));
            return false;
        }

        // Se está adicionando um principal, remover outros principais
        if (isset($data['papel']) && strtolower($data['papel']) === 'principal') {
            $this->removerPapel($data['processos_id'], 'principal');
        }

        $this->db->insert('advogados_processo', $data);
        
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Erro ao inserir advogado no processo: ' . $this->db->error()['message']);
            return false;
        }
        
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        log_message('error', 'Nenhuma linha afetada ao inserir advogado no processo. Dados: ' . json_encode($data));
        return false;
    }

    /**
     * Adiciona múltiplos advogados
     */
    public function addMultiple($processos_id, $advogados)
    {
        if (empty($advogados) || !is_array($advogados)) {
            return false;
        }

        $ids = [];
        foreach ($advogados as $advogado) {
            $advogado['processos_id'] = $processos_id;
            $id = $this->add($advogado);
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * Atualiza um advogado
     */
    public function edit($id, $data)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            log_message('error', "Tabela 'advogados_processo' não existe em Advogados_processo_model::edit");
            return false;
        }

        // Validar papel se fornecido
        if (isset($data['papel'])) {
            $papeis_validos = ['principal', 'coadjuvante', 'estagiario'];
            if (!in_array(strtolower($data['papel']), $papeis_validos)) {
                log_message('error', "Papel inválido: {$data['papel']}. Papéis válidos: " . implode(', ', $papeis_validos));
                return false;
            }

            // Se está mudando para principal, remover outros principais
            if (strtolower($data['papel']) === 'principal') {
                $advogado = $this->getById($id);
                if ($advogado) {
                    $this->removerPapel($advogado->processos_id, 'principal', $id);
                }
            }
        }

        $this->db->where('idAdvogadoProcesso', $id);
        $this->db->update('advogados_processo', $data);

        if ($this->db->error()['code'] != 0) {
            log_message('error', 'Erro ao atualizar advogado do processo: ' . $this->db->error()['message']);
            return false;
        }

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Remove um advogado (soft delete)
     */
    public function delete($id)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            log_message('error', "Tabela 'advogados_processo' não existe em Advogados_processo_model::delete");
            return false;
        }

        $this->db->where('idAdvogadoProcesso', $id);
        $this->db->update('advogados_processo', [
            'ativo' => 0,
            'data_remocao' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Remove todos os advogados de um processo
     */
    public function deleteByProcesso($processos_id)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return false;
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->where('ativo', 1);
        $this->db->update('advogados_processo', [
            'ativo' => 0,
            'data_remocao' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Remove todos os advogados com um papel específico (exceto um ID específico)
     */
    private function removerPapel($processos_id, $papel, $excluir_id = null)
    {
        $this->db->where('processos_id', $processos_id);
        $this->db->where('papel', $papel);
        $this->db->where('ativo', 1);
        
        if ($excluir_id !== null) {
            $this->db->where('idAdvogadoProcesso !=', $excluir_id);
        }
        
        $this->db->update('advogados_processo', [
            'papel' => 'coadjuvante',
            'data_remocao' => null // Não remover, apenas mudar papel
        ]);
    }

    /**
     * Marca advogado como notificado
     */
    public function marcarNotificado($id)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return false;
        }

        $this->db->where('idAdvogadoProcesso', $id);
        $this->db->update('advogados_processo', [
            'notificado' => 1,
            'data_notificacao' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Conta advogados ativos de um processo
     */
    public function countByProcesso($processos_id, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return 0;
        }

        $this->db->where('processos_id', $processos_id);
        
        if ($apenas_ativos) {
            $this->db->where('ativo', 1);
        }
        
        $this->db->from('advogados_processo');
        return $this->db->count_all_results();
    }

    /**
     * Busca processos por advogado
     */
    public function getProcessosByAdvogado($usuarios_id, $apenas_ativos = true)
    {
        if (!$this->db->table_exists('advogados_processo')) {
            return [];
        }

        $this->db->select('advogados_processo.*, processos.*');
        $this->db->from('advogados_processo');
        $this->db->join('processos', 'processos.idProcessos = advogados_processo.processos_id', 'inner');
        $this->db->where('advogados_processo.usuarios_id', $usuarios_id);
        
        if ($apenas_ativos) {
            $this->db->where('advogados_processo.ativo', 1);
        }
        
        $this->db->order_by('advogados_processo.data_atribuicao', 'DESC');
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Advogados_processo_model::getProcessosByAdvogado: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }
}

