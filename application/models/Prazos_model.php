<?php

class Prazos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->select($fields . ', prazos.*');
        $this->db->from($table);

        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto, processos.status as statusProcesso');
            $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        }

        // Join com clientes através de processos
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente as nomeCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios (responsável)
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('usuarios.nome as nomeResponsavel');
            $this->db->join('usuarios', 'usuarios.idUsuarios = prazos.usuarios_id', 'left');
        }

        // Aplicar WHERE antes de ORDER BY e LIMIT
        if ($where) {
            if (is_string($where)) {
                // Se for string, usar where direto
                if (strpos($where, 'AND') !== false || strpos($where, 'OR') !== false) {
                    $this->db->where($where, null, false);
                } else {
                    $this->db->group_start();
                    $this->db->like('prazos.descricao', $where);
                    $this->db->or_like('prazos.tipo', $where);
                    if ($this->db->table_exists('processos')) {
                        $this->db->or_like('processos.numeroProcesso', $where);
                    }
                    $this->db->group_end();
                }
            } else {
                $this->db->where($where);
            }
        }
        
        $this->db->order_by('prazos.dataVencimento', 'ASC');
        
        // Aplicar LIMIT apenas se perpage > 0 (quando 0, retornar todos os registros)
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query Prazos_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        if (!$this->db->table_exists('prazos')) {
            return false;
        }

        // Selecionar campos base de prazos PRIMEIRO
        $this->db->select('prazos.*');
        
        // Join com processos
        if ($this->db->table_exists('processos')) {
            $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto, processos.status as statusProcesso, processos.idProcessos, processos.comarca, processos.tribunal');
            $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        }

        // Join com clientes
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('clientes.nomeCliente, clientes.idClientes, clientes.documento as documentoCliente');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
        }

        // Join com usuarios
        if ($this->db->table_exists('usuarios')) {
            $this->db->select('usuarios.nome as nomeResponsavel, usuarios.email as emailResponsavel, usuarios.idUsuarios');
            $this->db->join('usuarios', 'usuarios.idUsuarios = prazos.usuarios_id', 'left');
        }

        $this->db->where('prazos.idPrazos', $id);
        $this->db->limit(1);

        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query Prazos_model::getById: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        return $query->row();
    }

    public function add($table, $data)
    {
        if (!$this->db->table_exists($table)) {
            log_message('error', "Tabela '{$table}' não existe em Prazos_model::add");
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
            log_message('error', "Tabela '{$table}' não existe em Prazos_model::edit");
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
            log_message('error', "Tabela '{$table}' não existe em Prazos_model::delete");
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
     * Busca prazos vencidos
     */
    public function getPrazosVencidos()
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->where_in('status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $this->db->where('dataVencimento <', date('Y-m-d'));
        $this->db->order_by('dataVencimento', 'ASC');
        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosVencidos: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca prazos por status
     * 
     * @param string $status Status do prazo
     * @param int|null $usuarios_id ID do usuário responsável (opcional)
     * @return array Lista de prazos
     */
    public function getByStatus($status, $usuarios_id = null)
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->where('status', strtolower($status));
        
        if ($usuarios_id !== null) {
            $this->db->where('usuarios_id', $usuarios_id);
        }
        
        $this->db->order_by('dataVencimento', 'ASC');
        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query getByStatus: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Atualiza status dos prazos automaticamente
     * 
     * Conforme RN 4.3:
     * - pendente → proximo_vencer (faltam 2 dias)
     * - proximo_vencer → vencendo_hoje (faltam 1 dia ou hoje)
     * - vencendo_hoje → vencido (passou do dia)
     * 
     * @return array Estatísticas de atualização
     */
    public function atualizarStatusAutomatico()
    {
        if (!$this->db->table_exists('prazos')) {
            return ['atualizados' => 0, 'erros' => 0];
        }

        $this->load->helper('prazo');
        
        $hoje = date('Y-m-d');
        $doisDias = date('Y-m-d', strtotime('+2 days'));
        $umDia = date('Y-m-d', strtotime('+1 day'));
        
        $atualizados = 0;
        $erros = 0;
        
        // Buscar prazos pendentes que precisam atualização
        $this->db->where_in('status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $prazos = $this->db->get('prazos')->result();
        
        foreach ($prazos as $prazo) {
            $novoStatus = null;
            $dataVenc = $prazo->dataVencimento;
            
            // Verificar status atual e definir novo
            if (strtotime($dataVenc) < strtotime($hoje)) {
                // Vencido
                $novoStatus = 'vencido';
            } elseif ($dataVenc == $hoje || $dataVenc == $umDia) {
                // Vencendo hoje ou amanhã
                $novoStatus = 'vencendo_hoje';
            } elseif ($dataVenc <= $doisDias) {
                // Próximo de vencer (2 dias ou menos)
                $novoStatus = 'proximo_vencer';
            } else {
                // Ainda pendente
                $novoStatus = 'pendente';
            }
            
            // Atualizar apenas se status mudou
            if ($novoStatus !== null && strtolower($prazo->status) !== $novoStatus) {
                $this->db->where('idPrazos', $prazo->idPrazos);
                if ($this->db->update('prazos', ['status' => $novoStatus])) {
                    $atualizados++;
                    log_message('info', "Prazo ID {$prazo->idPrazos} atualizado de '{$prazo->status}' para '{$novoStatus}'");
                } else {
                    $erros++;
                    log_message('error', "Erro ao atualizar status do prazo ID {$prazo->idPrazos}");
                }
            }
        }
        
        return [
            'atualizados' => $atualizados,
            'erros' => $erros,
            'total_processados' => count($prazos)
        ];
    }

    /**
     * Prorroga um prazo
     * 
     * Conforme RN 4.5 - Máximo de 3 prorrogações por prazo
     */
    public function prorrogar($idPrazos, $novosDiasUteis, $motivo = null, $usuarios_id = null)
    {
        if (!$this->db->table_exists('prazos')) {
            return false;
        }

        $this->load->helper('prazo');
        $prazoOriginal = $this->getById($idPrazos);
        
        if (!$prazoOriginal) {
            return false;
        }

        $numeroProrrogacoes = $this->contarProrrogacoes($idPrazos);
        
        if ($numeroProrrogacoes >= 3) {
            log_message('error', "Prazo ID {$idPrazos} já possui 3 prorrogações.");
            return false;
        }

        if (strtotime($prazoOriginal->dataVencimento) < strtotime('-30 days')) {
            log_message('error', "Prazo ID {$idPrazos} vencido há mais de 30 dias.");
            return false;
        }

        $novaDataVencimento = adicionarDiasUteis($prazoOriginal->dataVencimento, $novosDiasUteis);

        $this->db->where('idPrazos', $idPrazos);
        $this->db->update('prazos', ['status' => 'prorrogado']);

        $dadosNovoPrazo = [
            'processos_id' => $prazoOriginal->processos_id,
            'tipo' => $prazoOriginal->tipo,
            'descricao' => $prazoOriginal->descricao . ' [PRORROGAÇÃO ' . ($numeroProrrogacoes + 1) . ']' . ($motivo ? ' - Motivo: ' . $motivo : ''),
            'dataPrazo' => $prazoOriginal->dataVencimento,
            'dataVencimento' => $novaDataVencimento,
            'diasUteis' => $novosDiasUteis,
            'legislacao' => $prazoOriginal->legislacao ?? 'CPC',
            'status' => 'pendente',
            'prioridade' => $prazoOriginal->prioridade ?? 'Normal',
            'usuarios_id' => $usuarios_id ?? $prazoOriginal->usuarios_id,
            'prazo_original_id' => $prazoOriginal->prazo_original_id ?? $idPrazos,
            'numero_prorrogacao' => $numeroProrrogacoes + 1,
            'dataCadastro' => date('Y-m-d H:i:s')
        ];

        if ($this->add('prazos', $dadosNovoPrazo)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Conta quantas prorrogações um prazo já possui
     */
    public function contarProrrogacoes($idPrazos)
    {
        if (!$this->db->table_exists('prazos')) {
            return 0;
        }

        $prazoOriginal = $this->getById($idPrazos);
        if (!$prazoOriginal) {
            return 0;
        }

        $prazoRaizId = $prazoOriginal->prazo_original_id ?? $idPrazos;

        $this->db->where('prazo_original_id', $prazoRaizId);
        $this->db->where('numero_prorrogacao >', 0);
        $query = $this->db->get('prazos');

        return $query->num_rows();
    }

    /**
     * Busca histórico de prorrogações
     */
    public function getHistoricoProrrogacoes($idPrazos)
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $prazo = $this->getById($idPrazos);
        if (!$prazo) {
            return [];
        }

        $prazoRaizId = $prazo->prazo_original_id ?? $idPrazos;

        $this->db->where('idPrazos', $prazoRaizId);
        $original = $this->db->get('prazos')->row();

        $this->db->where('prazo_original_id', $prazoRaizId);
        $this->db->order_by('numero_prorrogacao', 'ASC');
        $prorrogacoes = $this->db->get('prazos')->result();

        $historico = [];
        if ($original) {
            $historico[] = $original;
        }
        if ($prorrogacoes) {
            $historico = array_merge($historico, $prorrogacoes);
        }

        return $historico;
    }

    /**
     * Busca prazos próximos (próximos 7 dias)
     */
    public function getPrazosProximos()
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->where('status', 'Pendente');
        $this->db->where('dataVencimento >=', date('Y-m-d'));
        $this->db->where('dataVencimento <=', date('Y-m-d', strtotime('+7 days')));
        $this->db->order_by('dataVencimento', 'ASC');
        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosProximos: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca prazos próximos por cliente
     */
    public function getPrazosProximosByCliente($cliente_id, $limit = 5)
    {
        if (!$this->db->table_exists('prazos') || !$this->db->table_exists('processos')) {
            return [];
        }

        // Detectar coluna de ID de clientes e verificar se processos.clientes_id existe
        if ($this->db->table_exists('processos') && $this->db->table_exists('clientes')) {
            $this->db->select('prazos.*, processos.numeroProcesso');
            $this->db->from('prazos');
            $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
            $this->db->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left');
            $this->db->where('processos.clientes_id', $cliente_id);
            $this->db->where('prazos.status', 'pendente');
            $this->db->where('prazos.dataVencimento >=', date('Y-m-d'));
            $this->db->where('prazos.dataVencimento <=', date('Y-m-d', strtotime('+7 days')));
            $this->db->order_by('prazos.dataVencimento', 'ASC');
            $this->db->limit($limit);
            $query = $this->db->get();
            
            if ($query === false) {
                log_message('error', 'Erro na query getPrazosProximosByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
                return [];
            }
            
            return $query->result();
        }

        return [];
    }

    /**
     * Busca prazos por processo
     */
    public function getPrazosByProcesso($processo_id)
    {
        if (!$this->db->table_exists('prazos')) {
            return [];
        }

        $this->db->where('processos_id', $processo_id);
        $this->db->order_by('dataVencimento', 'ASC');
        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosByProcesso: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Busca prazos por cliente
     */
    public function getPrazosByCliente($cliente_id, $perpage = 0, $start = 0)
    {
        if (!$this->db->table_exists('prazos') || !$this->db->table_exists('processos')) {
            return [];
        }

        if (!$this->db->table_exists('processos')) {
            return [];
        }
        
        $this->db->select('prazos.*, processos.numeroProcesso, processos.classe, processos.assunto');
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where("processos.clientes_id", $cliente_id);
        $this->db->order_by('prazos.dataVencimento', 'ASC');
        
        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }
        
        $query = $this->db->get();
        
        if ($query === false) {
            log_message('error', 'Erro na query getPrazosByCliente: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return [];
        }
        
        return $query->result();
    }

    /**
     * Conta prazos por cliente
     */
    /**
     * Verifica se já existe prazo com a mesma data de vencimento para o mesmo processo
     * (Validação de double booking para prazos)
     * 
     * @param int $processos_id ID do processo
     * @param string $dataVencimento Data de vencimento (Y-m-d)
     * @param int $excluir_id ID do prazo a excluir da verificação (para edição)
     * @return bool True se já existe, False se está disponível
     */
    public function existePrazoNaData($processos_id, $dataVencimento, $excluir_id = null)
    {
        if (!$this->db->table_exists('prazos') || empty($processos_id) || empty($dataVencimento)) {
            return false;
        }

        $this->db->where('processos_id', $processos_id);
        $this->db->where('DATE(dataVencimento)', date('Y-m-d', strtotime($dataVencimento)));
        $this->db->where('status !=', 'cancelado'); // Não considerar cancelados
        $this->db->where('status !=', 'cumprido'); // Opcional: não considerar cumpridos
        
        // Excluir o próprio prazo se estiver editando
        if ($excluir_id !== null) {
            $this->db->where('idPrazos !=', $excluir_id);
        }

        $query = $this->db->get('prazos');
        
        if ($query === false) {
            log_message('error', 'Erro na query existePrazoNaData: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return false;
        }

        // Se encontrou algum prazo, há conflito
        return $query->num_rows() > 0;
    }

    public function countPrazosByCliente($cliente_id)
    {
        if (!$this->db->table_exists('prazos') || !$this->db->table_exists('processos')) {
            return 0;
        }

        if (!$this->db->table_exists('processos')) {
            return 0;
        }
        
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where("processos.clientes_id", $cliente_id);
        
        return $this->db->count_all_results();
    }

    /**
     * Buscar estatísticas de prazos para dashboard do cliente (Fase 6 - Sprint 2)
     * 
     * @param int $cliente_id ID do cliente
     * @return object Estatísticas (vencendo_hoje, proximos_7dias, vencidos, total)
     */
    public function getEstatisticasByCliente($cliente_id)
    {
        if (!$this->db->table_exists('prazos') || !$this->db->table_exists('processos')) {
            return (object)[
                'total' => 0,
                'vencendo_hoje' => 0,
                'proximos_7dias' => 0,
                'vencidos' => 0,
                'pendentes' => 0
            ];
        }

        $hoje = date('Y-m-d');
        $seteDias = date('Y-m-d', strtotime('+7 days'));

        // Total
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where_in('prazos.status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $total = $this->db->count_all_results();

        // Vencendo hoje
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('DATE(prazos.dataVencimento)', $hoje);
        $this->db->where_in('prazos.status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $vencendo_hoje = $this->db->count_all_results();

        // Próximos 7 dias
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('DATE(prazos.dataVencimento) >=', $hoje);
        $this->db->where('DATE(prazos.dataVencimento) <=', $seteDias);
        $this->db->where_in('prazos.status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $proximos_7dias = $this->db->count_all_results();

        // Vencidos
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('DATE(prazos.dataVencimento) <', $hoje);
        $this->db->where('prazos.status', 'vencido');
        $vencidos = $this->db->count_all_results();

        // Pendentes
        $this->db->from('prazos');
        $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
        $this->db->where('processos.clientes_id', $cliente_id);
        $this->db->where('prazos.status', 'pendente');
        $pendentes = $this->db->count_all_results();

        return (object)[
            'total' => (int)$total,
            'vencendo_hoje' => (int)$vencendo_hoje,
            'proximos_7dias' => (int)$proximos_7dias,
            'vencidos' => (int)$vencidos,
            'pendentes' => (int)$pendentes
        ];
    }
}

