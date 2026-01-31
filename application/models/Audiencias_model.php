<?php

class Audiencias_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        // Montar lista de campos para select
        $select_fields = [];
        
        // Campos base de audiencias
        $select_fields[] = 'audiencias.*';
        
        // Campos extras se fields não for '*'
        if ($fields && $fields != '*' && trim($fields) != '') {
            $select_fields[] = $fields;
        }
        
        // Campos dos joins
        if ($this->db->table_exists('processos')) {
            $select_fields[] = 'processos.numeroProcesso, processos.classe, processos.assunto, processos.status as statusProcesso';
        }
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $select_fields[] = 'clientes.nomeCliente as nomeCliente';
        }
        if ($this->db->table_exists('usuarios')) {
            $select_fields[] = 'usuarios.nome as nomeResponsavel, usuarios.idUsuarios';
        }
        if ($this->db->table_exists('prazos')) {
            $select_fields[] = 'prazos.tipo as tipoPrazo, prazos.descricao as descricaoPrazo, prazos.dataVencimento as dataVencimentoPrazo, prazos.idPrazos';
        }
        
        // Fazer um único select com todos os campos
        $this->db->select(implode(', ', $select_fields), false);
        $this->db->from($table);

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }

        // Join com clientes através de processos
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios (responsável)
        if ($this->db->table_exists('usuarios')) {
            $this->db->join('usuarios', 'usuarios.idUsuarios = audiencias.usuarios_id', 'left');
        }

        // Join com prazos (para compromissos tipo prazo)
        if ($this->db->table_exists('prazos')) {
            $this->db->join('prazos', 'prazos.idPrazos = audiencias.prazos_id', 'left');
        }

        // Aplicar WHERE antes de ORDER BY e LIMIT
        if ($where) {
            if (is_string($where)) {
                // Se for string, usar where direto
                if (strpos($where, 'AND') !== false || strpos($where, 'OR') !== false) {
                    $this->db->where($where, null, false);
                } else {
                    $this->db->group_start();
                    $this->db->like('audiencias.tipo', $where);
                    $this->db->or_like('audiencias.local', $where);
                    $this->db->or_like('audiencias.observacoes', $where);
                    if ($this->db->table_exists('processos')) {
                        $this->db->or_like('processos.numeroProcesso', $where);
                    }
                    $this->db->group_end();
                }
            } else {
                $this->db->where($where);
            }
        }
        
        $this->db->order_by('audiencias.dataHora', 'ASC');
        
        // Aplicar LIMIT apenas se perpage > 0 (quando 0, retornar todos os registros)
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Audiencias_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        if (!$this->db->table_exists('audiencias')) {
            return false;
        }

        // Montar lista de campos para select
        $select_fields = [];
        
        // Campos base de audiencias
        $select_fields = ['audiencias.*'];
        
        // Campos dos joins
        if ($this->db->table_exists('processos')) {
            $select_fields[] = 'processos.numeroProcesso, processos.classe, processos.assunto, processos.status as statusProcesso, processos.idProcessos';
        }
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $select_fields[] = 'clientes.nomeCliente, clientes.idClientes';
        }
        if ($this->db->table_exists('usuarios')) {
            $select_fields[] = 'usuarios.nome as nomeResponsavel, usuarios.email as emailResponsavel, usuarios.idUsuarios';
        }
        if ($this->db->table_exists('prazos')) {
            $select_fields[] = 'prazos.tipo as tipoPrazo, prazos.descricao as descricaoPrazo, prazos.dataVencimento as dataVencimentoPrazo, prazos.idPrazos';
        }
        
        // Fazer um único select com todos os campos
        $this->db->select(implode(', ', $select_fields), false);
        $this->db->from('audiencias');

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        }

        // Join com clientes através de processos
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios
        if ($this->db->table_exists('usuarios')) {
            $this->db->join('usuarios', 'usuarios.idUsuarios = audiencias.usuarios_id', 'left');
        }

        // Join com prazos (para compromissos tipo prazo)
        if ($this->db->table_exists('prazos')) {
            $this->db->join('prazos', 'prazos.idPrazos = audiencias.prazos_id', 'left');
        }

        $this->db->where('audiencias.idAudiencias', $id);
        $this->db->limit(1);

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Audiencias_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::add");
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::edit");
            return false;
        }

        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Audiencias_model::delete");
            return false;
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        if (!$this->db->table_exists($table)) {
            return 0;
        }
        return $this->db->count_all($table);
    }

    /**
     * Busca audiências de hoje
     */
    public function getAudienciasHoje()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('DATE(dataHora)', date('Y-m-d'));
        $this->db->where('status', 'agendada');
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasHoje: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca próximas audiências (próximos 7 dias)
     */
    public function getAudienciasProximas()
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('DATE(dataHora) >', date('Y-m-d'));
        $this->db->where('DATE(dataHora) <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->where('status', 'agendada');
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasProximas: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Verifica se há conflito de horário (double booking) para um usuário
     * 
     * RN 5.2 - Sistema não permite double booking (mesmo horário para mesmo advogado)
     * 
     * @param int $usuarios_id ID do usuário
     * @param string $dataHora Data/hora do compromisso (Y-m-d H:i:s)
     * @param int $duracao_estimada Duração estimada em minutos (opcional)
     * @param int $excluir_id ID do compromisso a excluir da verificação (para edição)
     * @return bool True se há conflito, False se está disponível
     */
    public function verificarDisponibilidade($usuarios_id, $dataHora, $duracao_estimada = 60, $excluir_id = null)
    {
        if (!$this->db->table_exists('audiencias') || empty($usuarios_id) || empty($dataHora)) {
            return false; // Se não há tabela ou dados, considerar disponível
        }

        $inicio = strtotime($dataHora);
        if ($inicio === false) {
            log_message('error', 'verificarDisponibilidade: dataHora inválida: ' . $dataHora);
            return false;
        }
        
        $fim = $inicio + ($duracao_estimada * 60); // Converter minutos para segundos

        $dataHora_inicio_novo = date('Y-m-d H:i:s', $inicio);
        $dataHora_fim_novo = date('Y-m-d H:i:s', $fim);
        
        $this->db->where('usuarios_id', $usuarios_id);
        // Não considerar canceladas ou realizadas (case-insensitive)
        // Usar where_not_in com múltiplas variações de case
        $this->db->group_start();
        $this->db->where("status !=", 'cancelada');
        $this->db->where("status !=", 'Cancelada');
        $this->db->where("status !=", 'CANCELADA');
        $this->db->where("status !=", 'realizada');
        $this->db->where("status !=", 'Realizada');
        $this->db->where("status !=", 'REALIZADA');
        $this->db->group_end();
        
        // Excluir o próprio compromisso se estiver editando
        if ($excluir_id !== null) {
            $this->db->where('idAudiencias !=', $excluir_id);
        }

        // Verificar sobreposição de horários
        // Dois intervalos [A1, A2] e [B1, B2] se sobrepõem se: A1 < B2 AND A2 > B1
        // Novo compromisso: [$dataHora_inicio_novo, $dataHora_fim_novo]
        // Existente: [dataHora, DATE_ADD(dataHora, INTERVAL duracao_estimada MINUTE)]
        // Sobreposição se: $dataHora_inicio_novo < DATE_ADD(dataHora, INTERVAL duracao_estimada MINUTE) 
        //              AND $dataHora_fim_novo > dataHora
        
        $this->db->where("dataHora <", $dataHora_fim_novo);
        $this->db->where("DATE_ADD(dataHora, INTERVAL IFNULL(duracao_estimada, 60) MINUTE) >", $dataHora_inicio_novo);

        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query verificarDisponibilidade: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        $num_rows = $query->num_rows();
        
        // Log para debug (pode remover depois)
        if ($num_rows > 0) {
            log_message('debug', "verificarDisponibilidade: Conflito detectado! usuarios_id={$usuarios_id}, dataHora={$dataHora}, duracao={$duracao_estimada}, conflitos={$num_rows}");
        }

        // Se encontrou algum compromisso, há conflito
        return $num_rows > 0;
    }

    /**
     * Busca compromissos de um usuário em um período
     * 
     * @param int $usuarios_id ID do usuário
     * @param string $data_inicio Data inicial (Y-m-d)
     * @param string $data_fim Data final (Y-m-d)
     * @param string $visibilidade Filtrar por visibilidade (opcional)
     * @return array Lista de compromissos
     */
    public function getByUsuario($usuarios_id, $data_inicio = null, $data_fim = null, $visibilidade = null)
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('usuarios_id', $usuarios_id);
        $this->db->where('status !=', 'cancelada');

        if ($data_inicio) {
            $this->db->where('DATE(dataHora) >=', $data_inicio);
        }
        if ($data_fim) {
            $this->db->where('DATE(dataHora) <=', $data_fim);
        }
        if ($visibilidade) {
            $this->db->where('visibilidade', $visibilidade);
        }

        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getByUsuario: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca compromissos por usuário e período
     * 
     * @param int $usuarios_id ID do usuário
     * @param string $data_inicio Data inicial (Y-m-d)
     * @param string $data_fim Data final (Y-m-d)
     * @param string|null $visibilidade Filtrar por visibilidade (opcional)
     * @return array Lista de compromissos
     */
    public function getByUsuarioEPeriodo($usuarios_id, $data_inicio, $data_fim, $visibilidade = null)
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('usuarios_id', $usuarios_id);
        $this->db->where('DATE(dataHora) >=', $data_inicio);
        $this->db->where('DATE(dataHora) <=', $data_fim);

        if ($visibilidade) {
            $this->db->where_in('visibilidade', ['publico', 'equipe', $visibilidade]);
        }

        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getByUsuarioEPeriodo: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca compromissos por tipo
     * 
     * @param string $tipo_compromisso Tipo de compromisso
     * @param int $perpage Número de registros por página
     * @param int $start Offset
     * @return array Lista de compromissos
     */
    public function getByTipoCompromisso($tipo_compromisso, $perpage = 0, $start = 0)
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('tipo_compromisso', $tipo_compromisso);
        $this->db->order_by('dataHora', 'ASC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getByTipoCompromisso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca próximas audiências por cliente
     */
    public function getAudienciasProximasByCliente($cliente_id, $limit = 5)
    {
        if (!$this->db->table_exists('audiencias') || !$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de clientes e verificar se processos.clientes_id existe
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        // Verificar se tabelas existem
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        if ($this->db->table_exists('clientes')) {
            $this->db->select('audiencias.*, processos.numeroProcesso');
            $this->db->from('audiencias');
            $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
            $this->db->where('processos.clientes_id', $cliente_id);
            $this->db->where('audiencias.status', 'agendada');
            $this->db->where('DATE(audiencias.dataHora) >=', date('Y-m-d'));
            $this->db->where('DATE(audiencias.dataHora) <=', date('Y-m-d', strtotime('+7 days')));
            $this->db->order_by('audiencias.dataHora', 'ASC');
            $this->db->limit($limit);
            $query = $this->db->get();
            
            if ($query === false) {
                log_message('error', 'Erro na query getAudienciasProximasByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
                return [];
            }
            
            return $query->result();
        }

        return [];
    }

    /**
     * Busca audiências por processo
     */
    public function getAudienciasByProcesso($processo_id)
    {
        if (!$this->db->table_exists('audiencias')) {
            return [];
        }

        $this->db->where('processos_id', $processo_id);
        $this->db->order_by('dataHora', 'ASC');
        $query = $this->db->get('audiencias');
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca audiências por cliente
     */
    public function getAudienciasByCliente($cliente_id, $perpage = 0, $start = 0)
    {
        if (!$this->db->table_exists('audiencias') || !$this->db->table_exists('processos')) {
            return [];
        }

        $this->db->select('audiencias.*');
        // Verificar se as colunas existem antes de selecioná-las
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
        // Verificar se processos.clientes_id existe
        if (!$this->db->table_exists('processos')) {
            return [];
        }
        
        if (!$this->db->table_exists('processos') || !$this->db->table_exists('clientes')) {
            return [];
        }
        
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where("processos.clientes_id", $cliente_id);
        $this->db->order_by('audiencias.dataHora', 'DESC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getAudienciasByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Buscar estatísticas de audiências para dashboard do cliente (Fase 6 - Sprint 2)
     * 
     * @param int $cliente_id ID do cliente
     * @return object Estatísticas (proxima, esta_semana, total_agendadas)
     */
    public function getEstatisticasByCliente($cliente_id)
    {
        if (!$this->db->table_exists('audiencias') || !$this->db->table_exists('processos')) {
            return (object)[
                'total_agendadas' => 0,
                'proxima' => null,
                'esta_semana' => 0,
                'este_mes' => 0
            ];
        }

        $hoje = date('Y-m-d');
        $fimSemana = date('Y-m-d', strtotime('+7 days'));
        $fimMes = date('Y-m-d', strtotime('+30 days'));

        // Total agendadas
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('audiencias.status', 'agendada');
        $this->db->where('DATE(audiencias.dataHora) >=', $hoje);
        $total_agendadas = $this->db->count_all_results();

        // Esta semana
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('audiencias.status', 'agendada');
        $this->db->where('DATE(audiencias.dataHora) >=', $hoje);
        $this->db->where('DATE(audiencias.dataHora) <=', $fimSemana);
        $esta_semana = $this->db->count_all_results();

        // Este mês
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('audiencias.status', 'agendada');
        $this->db->where('DATE(audiencias.dataHora) >=', $hoje);
        $this->db->where('DATE(audiencias.dataHora) <=', $fimMes);
        $este_mes = $this->db->count_all_results();

        // Próxima audiência
        $this->db->select('audiencias.*, processos.numeroProcesso');
        $this->db->from('audiencias');
        $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('audiencias.status', 'agendada');
        $this->db->where('DATE(audiencias.dataHora) >=', $hoje);
        $this->db->order_by('audiencias.dataHora', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get();
        $proxima = $query->num_rows() > 0 ? $query->row() : null;

        return (object)[
            'total_agendadas' => (int)$total_agendadas,
            'proxima' => $proxima,
            'esta_semana' => (int)$esta_semana,
            'este_mes' => (int)$este_mes
        ];
    }

}

