<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pecas_geradas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($where = [], $perpage = 0, $start = 0, $one = false)
    {
        if (!$this->db->table_exists('pecas_geradas')) {
            return $one ? null : [];
        }

        $this->db->select('pecas_geradas.*');
        $this->db->select('processos.numeroProcesso, processos.classe, processos.assunto');
        $this->db->select('clientes.nomeCliente');
        $this->db->select('modelos_pecas.nome as nomeModelo');
        $this->db->select('usuarios_gerador.nome as nomeGerador');
        $this->db->select('usuarios_aprovador.nome as nomeAprovador');
        $this->db->from('pecas_geradas');
        $this->db->join('processos', 'processos.idProcessos = pecas_geradas.processos_id', 'left');
        $this->db->join('clientes', 'clientes.idClientes = pecas_geradas.clientes_id', 'left');
        $this->db->join('clientes as clientes_proc', 'clientes_proc.idClientes = processos.clientes_id', 'left');
        $this->db->join('modelos_pecas', 'modelos_pecas.id = pecas_geradas.modelos_pecas_id', 'left');
        $this->db->join('usuarios as usuarios_gerador', 'usuarios_gerador.idUsuarios = pecas_geradas.usuarios_gerador_id', 'left');
        $this->db->join('usuarios as usuarios_aprovador', 'usuarios_aprovador.idUsuarios = pecas_geradas.usuarios_aprovador_id', 'left');

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    if (strpos($key, ' LIKE') !== false) {
                        $field = str_replace(' LIKE', '', $key);
                        $this->db->like($field, $value);
                    } else {
                        $this->db->where($key, $value);
                    }
                }
            } else {
                $this->db->where($where);
            }
        }

        $this->db->order_by('pecas_geradas.dataCadastro', 'DESC');

        if ($perpage > 0) {
            $this->db->limit($perpage, $start);
        }

        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Erro na query Pecas_geradas_model::get: ' . ($this->db->error()['message'] ?? 'Erro desconhecido'));
            return $one ? null : [];
        }

        $results = $one ? $query->row() : $query->result();

        if (!$one && $results) {
            foreach ($results as $r) {
                if (empty($r->nomeCliente) && !empty($r->processos_id)) {
                    $proc = $this->db->select('clientes.nomeCliente')
                        ->from('processos')
                        ->join('clientes', 'clientes.idClientes = processos.clientes_id', 'left')
                        ->where('processos.idProcessos', $r->processos_id)
                        ->get()->row();
                    if ($proc) {
                        $r->nomeCliente = $proc->nomeCliente;
                    }
                }
            }
        }

        return $results;
    }

    public function getById($id)
    {
        return $this->get(['pecas_geradas.id' => $id], 0, 0, true);
    }

    public function getByProcesso($processos_id)
    {
        return $this->get(['pecas_geradas.processos_id' => $processos_id]);
    }

    public function getByPrazo($prazos_id)
    {
        return $this->get(['pecas_geradas.prazos_id' => $prazos_id]);
    }

    public function add($data)
    {
        if (!$this->db->table_exists('pecas_geradas')) {
            return false;
        }

        if (!isset($data['dataCadastro'])) {
            $data['dataCadastro'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('pecas_geradas', $data);

        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function edit($data, $id)
    {
        if (!$this->db->table_exists('pecas_geradas')) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update('pecas_geradas', $data);

        return $this->db->affected_rows() >= 0;
    }

    public function getUltimaVersao($pecas_geradas_id)
    {
        if (!$this->db->table_exists('pecas_versoes')) {
            return null;
        }

        $this->db->select('*');
        $this->db->from('pecas_versoes');
        $this->db->where('pecas_geradas_id', $pecas_geradas_id);
        $this->db->order_by('numero_versao', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getVersaoIA($pecas_geradas_id)
    {
        if (!$this->db->table_exists('pecas_versoes')) {
            return null;
        }

        $this->db->select('*');
        $this->db->from('pecas_versoes');
        $this->db->where('pecas_geradas_id', $pecas_geradas_id);
        $this->db->where('origem', 'ia');
        $this->db->order_by('numero_versao', 'ASC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getVersaoAprovada($pecas_geradas_id)
    {
        if (!$this->db->table_exists('pecas_versoes')) {
            return null;
        }

        $this->db->select('*');
        $this->db->from('pecas_versoes');
        $this->db->where('pecas_geradas_id', $pecas_geradas_id);
        $this->db->where('origem', 'aprovado');
        $this->db->order_by('numero_versao', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function addVersao($pecas_geradas_id, $conteudo, $origem, $usuarios_id = null, $diff_anterior = null)
    {
        if (!$this->db->table_exists('pecas_versoes')) {
            return false;
        }

        $ultima = $this->getUltimaVersao($pecas_geradas_id);
        $numero_versao = $ultima ? (int) $ultima->numero_versao + 1 : 1;

        $data = [
            'pecas_geradas_id' => $pecas_geradas_id,
            'numero_versao' => $numero_versao,
            'conteudo' => $conteudo,
            'origem' => $origem,
            'usuarios_id' => $usuarios_id,
            'ip' => get_instance()->input->ip_address(),
            'dataCadastro' => date('Y-m-d H:i:s'),
        ];

        if ($diff_anterior !== null) {
            $data['diff_anterior'] = is_string($diff_anterior) ? $diff_anterior : json_encode($diff_anterior);
        }

        $this->db->insert('pecas_versoes', $data);

        return $this->db->affected_rows() == 1 ? $this->db->insert_id() : false;
    }

    public function addLogGeracao($pecas_geradas_id, $pecas_versoes_id, $prompt, $contexto_ids, $resposta_llm, $modelo_llm, $chamada_local, $usuarios_id)
    {
        if (!$this->db->table_exists('logs_geracao_pecas')) {
            return false;
        }

        $data = [
            'pecas_geradas_id' => $pecas_geradas_id,
            'pecas_versoes_id' => $pecas_versoes_id,
            'prompt' => $prompt,
            'contexto_ids' => is_string($contexto_ids) ? $contexto_ids : json_encode($contexto_ids),
            'resposta_llm' => $resposta_llm,
            'modelo_llm' => $modelo_llm,
            'chamada_local' => (int) $chamada_local,
            'usuarios_id' => $usuarios_id,
            'ip' => get_instance()->input->ip_address(),
            'dataCadastro' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('logs_geracao_pecas', $data);

        return $this->db->insert_id();
    }

    public function getChecklistItens()
    {
        return [
            'coerencia_fatos' => 'Coerência dos fatos',
            'dados_partes' => 'Correção dos dados das partes',
            'fundamentos_juridicos' => 'Pertinência dos fundamentos jurídicos',
            'adequacao_pedidos' => 'Adequação dos pedidos',
            'citacoes' => 'Verificação de citações (leis/jurisprudência)',
            'interesse_cliente' => 'Adequação ao interesse do cliente e estratégia do caso',
        ];
    }

    public function getChecklist($pecas_geradas_id)
    {
        if (!$this->db->table_exists('checklist_revisao')) {
            return [];
        }

        $this->db->from('checklist_revisao');
        $this->db->where('pecas_geradas_id', $pecas_geradas_id);

        return $this->db->get()->result();
    }

    public function saveChecklist($pecas_geradas_id, $itens, $usuarios_id)
    {
        if (!$this->db->table_exists('checklist_revisao')) {
            return false;
        }

        $this->db->where('pecas_geradas_id', $pecas_geradas_id);
        $this->db->delete('checklist_revisao');

        $itens_lista = $this->getChecklistItens();
        $dataCadastro = date('Y-m-d H:i:s');

        foreach ($itens_lista as $key => $label) {
            $marcado = isset($itens[$key]) && $itens[$key] ? 1 : 0;
            $this->db->insert('checklist_revisao', [
                'pecas_geradas_id' => $pecas_geradas_id,
                'item' => $key,
                'marcado' => $marcado,
                'usuarios_id' => $usuarios_id,
                'dataCadastro' => $dataCadastro,
            ]);
        }

        return true;
    }

    public function checklistCompleto($pecas_geradas_id)
    {
        $itens = $this->getChecklistItens();
        $checklist = $this->getChecklist($pecas_geradas_id);

        $marcados = [];
        foreach ($checklist as $c) {
            $marcados[$c->item] = (bool) $c->marcado;
        }

        foreach ($itens as $key => $label) {
            if (empty($marcados[$key])) {
                return false;
            }
        }

        return true;
    }

    public function count($where = [])
    {
        if (!$this->db->table_exists('pecas_geradas')) {
            return 0;
        }

        $this->db->from('pecas_geradas');

        if (!empty($where)) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $this->db->where($key, $value);
                }
            } else {
                $this->db->where($where);
            }
        }

        return $this->db->count_all_results();
    }
}
