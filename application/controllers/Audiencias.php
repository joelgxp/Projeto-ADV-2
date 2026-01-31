<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Audiencias extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('audiencias_model');
        $this->load->model('processos_model');
        $this->data['menuAudiencias'] = 'audiencias';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar audiências.');
            redirect(base_url());
        }

        // Se for requisição AJAX do DataTables, retornar JSON
        // Verificar se é requisição do DataTables (tem parâmetros específicos)
        $hasDatatablesParams = (
            $this->input->get('sEcho') !== false || 
            $this->input->get('draw') !== false ||
            $this->input->get('iDisplayStart') !== false ||
            $this->input->get('start') !== false ||
            $this->input->get('iDisplayLength') !== false ||
            $this->input->get('length') !== false
        );
        
        if ($this->input->is_ajax_request() && $hasDatatablesParams) {
            $this->datatables_ajax();
            return;
        }

        $pesquisa = $this->input->get('pesquisa');
        $status = $this->input->get('status');
        $data_inicio = $this->input->get('data_inicio');
        $data_fim = $this->input->get('data_fim');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('audiencias/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->audiencias_model->count('audiencias');
        
        $suffix_parts = [];
        if($pesquisa) $suffix_parts[] = "pesquisa={$pesquisa}";
        if($status) $suffix_parts[] = "status={$status}";
        if($data_inicio) $suffix_parts[] = "data_inicio={$data_inicio}";
        if($data_fim) $suffix_parts[] = "data_fim={$data_fim}";
        
        if (!empty($suffix_parts)) {
            $this->data['configuration']['suffix'] = "?" . implode('&', $suffix_parts);
            $this->data['configuration']['first_url'] = base_url("index.php/audiencias") . "?" . implode('&', $suffix_parts);
        }

        $this->pagination->initialize($this->data['configuration']);

        $where = '';
        if ($pesquisa) {
            $where = $pesquisa;
        }
        if ($status) {
            $where .= ($where ? ' AND ' : '') . "status = '{$status}'";
        }
        if ($data_inicio) {
            $where .= ($where ? ' AND ' : '') . "DATE(dataHora) >= '{$data_inicio}'";
        }
        if ($data_fim) {
            $where .= ($where ? ' AND ' : '') . "DATE(dataHora) <= '{$data_fim}'";
        }

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->audiencias_model->get('audiencias', '*', $where, $this->data['configuration']['per_page'], 0);
        $this->data['status'] = $status;
        $this->data['data_inicio'] = $data_inicio;
        $this->data['data_fim'] = $data_fim;

        $this->data['view'] = 'audiencias/audiencias';

        return $this->layout();
    }

    /**
     * Retorna dados para DataTables (server-side processing)
     */
    private function datatables_ajax()
    {
        // DataTables 1.9.4 usa parâmetros diferentes
        // sEcho deve ser retornado exatamente como foi enviado
        $sEcho = $this->input->get('sEcho');
        if ($sEcho === false || $sEcho === null) {
            $sEcho = $this->input->get('draw') ?: 1;
        }
        $draw = intval($sEcho);
        
        $start = intval($this->input->get('iDisplayStart') ?: $this->input->get('start') ?: 0);
        $length = intval($this->input->get('iDisplayLength') ?: $this->input->get('length') ?: 20);
        
        // Buscar termo de pesquisa
        $search = '';
        $search_param = $this->input->get('sSearch');
        if ($search_param !== false && $search_param !== null && $search_param !== '') {
            $search = $search_param;
        } else {
            $search_array = $this->input->get('search');
            if (is_array($search_array) && isset($search_array['value'])) {
                $search = $search_array['value'];
            }
        }
        
        // Total de registros sem filtro
        $recordsTotal = $this->audiencias_model->count('audiencias');
        
        // Construir where para busca
        $where = '';
        if ($search) {
            $where = $search; // O model trata como like
        }
        
        // Buscar registros com paginação e filtro
        $results = $this->audiencias_model->get('audiencias', '*', $where, $length, $start);
        
        // Total de registros com filtro aplicado
        $recordsFiltered = $recordsTotal;
        if ($search) {
            // Resetar query builder
            $this->db->reset_query();
            
            $this->db->from('audiencias');
            
            // Join com processos se necessário
            if ($this->db->table_exists('processos')) {
                $this->db->join('processos', 'processos.idProcessos = audiencias.processos_id', 'left');
            }
            
            $this->db->group_start();
            $this->db->like('audiencias.tipo', $search);
            $this->db->or_like('audiencias.local', $search);
            $this->db->or_like('audiencias.observacoes', $search);
            if ($this->db->table_exists('processos')) {
                $this->db->or_like('processos.numeroProcesso', $search);
            }
            $this->db->group_end();
            
            $recordsFiltered = $this->db->count_all_results();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                // Formatar data e hora
                $dataHora = '';
                if (isset($r->dataHora) && $r->dataHora) {
                    $dataHora = date('d/m/Y H:i', strtotime($r->dataHora));
                }
                
                // Status com cores
                $status_labels = [
                    'Agendada' => ['label' => 'Agendada', 'class' => 'label-info'],
                    'Realizada' => ['label' => 'Realizada', 'class' => 'label-success'],
                    'Cancelada' => ['label' => 'Cancelada', 'class' => 'label-danger'],
                    'Adiada' => ['label' => 'Adiada', 'class' => 'label-warning'],
                ];
                $status = $r->status ?? 'Agendada';
                $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => 'label-default'];
                
                // Ações
                $acoes = '';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/audiencias/visualizar/' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/audiencias/editar/' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Audiência"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dAudiencia')) {
                    $acoes .= '<a href="#modal-excluir" role="button" data-toggle="modal" audiencia="' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Audiência"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                
                // Formatar data com indicador de hoje/passada
                $dataHoraFormatada = $dataHora;
                if (isset($r->dataHora) && $r->dataHora) {
                    $hoje = strtotime('today');
                    $dataAud = strtotime(date('Y-m-d', strtotime($r->dataHora)));
                    
                    if ($dataAud == $hoje) {
                        $dataHoraFormatada = '<span class="label label-info">' . $dataHora . ' (Hoje)</span>';
                    } elseif ($dataAud < $hoje) {
                        $dataHoraFormatada = '<span class="label label-default">' . $dataHora . ' (Passada)</span>';
                    } else {
                        $dataHoraFormatada = $dataHora;
                    }
                }
                
                $numeroProcessoLink = '-';
                if (isset($r->numeroProcesso) && isset($r->processos_id)) {
                    $numeroProcessoLink = '<a href="' . base_url() . 'index.php/processos/visualizar/' . $r->processos_id . '">' . $r->numeroProcesso . '</a>';
                } elseif (isset($r->numeroProcesso)) {
                    $numeroProcessoLink = $r->numeroProcesso;
                }
                
                $data[] = [
                    $r->idAudiencias,
                    $numeroProcessoLink,
                    $r->tipo ?? '-',
                    $dataHoraFormatada,
                    $r->local ?? '-',
                    '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>',
                    $acoes
                ];
            }
        }
        
        // DataTables 1.9.4 usa formato diferente
        // sEcho deve ser retornado exatamente como foi enviado (pode ser string)
        $response = [
            'sEcho' => $sEcho, // Manter original, não converter para int
            'iTotalRecords' => intval($recordsTotal),
            'iTotalDisplayRecords' => intval($recordsFiltered),
            'aaData' => $data
        ];
        
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar audiências.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->load->model('prazos_model');
        $this->load->model('usuarios_model', 'sistema_model');
        $this->data['custom_error'] = '';

        // Validações base
        $this->form_validation->set_rules('tipo_compromisso', 'Tipo de Compromisso', 'required|trim|in_list[audiencia,reuniao,diligencia,prazo,evento]');
        $this->form_validation->set_rules('dataHora', 'Data e Hora', 'required|trim');
        $this->form_validation->set_rules('usuarios_id', 'Responsável', 'required|trim|integer');
        $this->form_validation->set_rules('visibilidade', 'Visibilidade', 'trim|in_list[privado,publico,equipe]');
        
        // Validações condicionais por tipo
        $tipo_compromisso = $this->input->post('tipo_compromisso');
        if ($tipo_compromisso == 'audiencia' || $tipo_compromisso == 'prazo') {
            // Audiência e Prazo precisam de processo
            $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim|integer');
        }
        if ($tipo_compromisso == 'prazo') {
            // Prazo precisa estar vinculado a um prazo
            $this->form_validation->set_rules('prazos_id', 'Prazo', 'required|trim|integer');
        }
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            // Montar data básica
            $hora = $this->input->post('hora') ?: '09:00';
            $dataHoraCompleta = $this->input->post('dataHora') . ' ' . $hora . ':00';
            
            $data = [
                'tipo_compromisso' => $tipo_compromisso,
                'processos_id' => $this->input->post('processos_id') ?: null,
                'prazos_id' => $this->input->post('prazos_id') ?: null,
                'tipo' => $this->input->post('tipo') ?: '', // Mantido para compatibilidade (ex: tipo de audiência)
                'dataHora' => $dataHoraCompleta,
                'duracao_estimada' => $this->input->post('duracao_estimada') ? intval($this->input->post('duracao_estimada')) : 60,
                'local' => $this->input->post('local'),
                'observacoes' => $this->input->post('observacoes'),
                'status' => $this->input->post('status') ?: 'agendada',
                'usuarios_id' => intval($this->input->post('usuarios_id')),
                'visibilidade' => $this->input->post('visibilidade') ?: 'publico',
            ];

            // Campos específicos por tipo
            if ($tipo_compromisso == 'audiencia') {
                $data['tribunal'] = $this->input->post('tribunal');
                $data['juiz'] = $this->input->post('juiz');
                $data['tipo_audiencia'] = $this->input->post('tipo_audiencia') ?: $this->input->post('tipo');
            }
            
            if ($tipo_compromisso == 'reuniao') {
                $participantes = $this->input->post('participantes');
                if (is_array($participantes)) {
                    $data['participantes'] = json_encode($participantes);
                } else {
                    $data['participantes'] = $participantes ?: null;
                }
            }
            
            if ($tipo_compromisso == 'diligencia') {
                $data['tipo_diligencia'] = $this->input->post('tipo_diligencia');
            }
            
            if ($tipo_compromisso == 'evento') {
                $data['tipo_evento'] = $this->input->post('tipo_evento');
                $data['abrangencia'] = $this->input->post('abrangencia') ?: 'pessoal';
            }

            // Verificar disponibilidade (double booking)
            if ($this->audiencias_model->verificarDisponibilidade($data['usuarios_id'], $dataHoraCompleta, $data['duracao_estimada'])) {
                $this->data['custom_error'] = '<div class="form_error"><p>Já existe um compromisso agendado neste horário para o responsável selecionado. Por favor, escolha outro horário ou outro responsável.</p></div>';
            } else {
                if ($this->audiencias_model->add('audiencias', $data) == true) {
                    $this->session->set_flashdata('success', 'Compromisso adicionado com sucesso!');
                    log_info('Adicionou um compromisso do tipo: ' . $tipo_compromisso);
                    redirect(site_url('audiencias/gerenciar'));
                } else {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o compromisso.</p></div>';
                }
            }
        }

        // Carregar dados para a view
        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['prazos'] = $this->prazos_model->get('prazos', '*', "status IN ('pendente', 'proximo_vencer', 'vencendo_hoje')", 0, 0, false, 'array');
        
        // Carregar usuários ativos para o campo Responsável (usando query builder direto)
        $this->db->select('idUsuarios, nome, email');
        $this->db->from('usuarios');
        $this->db->where('situacao', 1); // Apenas usuários ativos
        $this->db->order_by('nome', 'ASC');
        $query_usuarios = $this->db->get();
        
        if ($query_usuarios !== false && $query_usuarios->num_rows() > 0) {
            $this->data['usuarios'] = $query_usuarios->result();
        } else {
            $this->data['usuarios'] = [];
            log_message('warning', 'Audiencias::adicionar() - Nenhum usuário ativo encontrado. Verifique a tabela usuarios.');
        }
        $this->data['view'] = 'audiencias/adicionarAudiencia';

        return $this->layout();
    }

    public function editar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar audiências.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar editar audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        $this->load->library('form_validation');
        $this->load->model('prazos_model');
        $this->load->model('usuarios_model', 'sistema_model');
        $this->data['custom_error'] = '';

        // Validações base
        $this->form_validation->set_rules('tipo_compromisso', 'Tipo de Compromisso', 'required|trim|in_list[audiencia,reuniao,diligencia,prazo,evento]');
        $this->form_validation->set_rules('dataHora', 'Data e Hora', 'required|trim');
        $this->form_validation->set_rules('usuarios_id', 'Responsável', 'required|trim|integer');
        $this->form_validation->set_rules('visibilidade', 'Visibilidade', 'trim|in_list[privado,publico,equipe]');
        
        // Validações condicionais por tipo
        $tipo_compromisso = $this->input->post('tipo_compromisso');
        if ($tipo_compromisso == 'audiencia' || $tipo_compromisso == 'prazo') {
            $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim|integer');
        }
        if ($tipo_compromisso == 'prazo') {
            $this->form_validation->set_rules('prazos_id', 'Prazo', 'required|trim|integer');
        }
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            // Montar data básica
            $hora = $this->input->post('hora') ?: '09:00';
            $dataHoraCompleta = $this->input->post('dataHora') . ' ' . $hora . ':00';
            
            $data = [
                'tipo_compromisso' => $tipo_compromisso,
                'processos_id' => $this->input->post('processos_id') ?: null,
                'prazos_id' => $this->input->post('prazos_id') ?: null,
                'tipo' => $this->input->post('tipo') ?: '',
                'dataHora' => $dataHoraCompleta,
                'duracao_estimada' => $this->input->post('duracao_estimada') ? intval($this->input->post('duracao_estimada')) : 60,
                'local' => $this->input->post('local'),
                'observacoes' => $this->input->post('observacoes'),
                'status' => $this->input->post('status'),
                'usuarios_id' => intval($this->input->post('usuarios_id')),
                'visibilidade' => $this->input->post('visibilidade') ?: 'publico',
            ];

            // Campos específicos por tipo
            if ($tipo_compromisso == 'audiencia') {
                $data['tribunal'] = $this->input->post('tribunal');
                $data['juiz'] = $this->input->post('juiz');
                $data['tipo_audiencia'] = $this->input->post('tipo_audiencia') ?: $this->input->post('tipo');
            }
            
            if ($tipo_compromisso == 'reuniao') {
                $participantes = $this->input->post('participantes');
                if (is_array($participantes)) {
                    $data['participantes'] = json_encode($participantes);
                } else {
                    $data['participantes'] = $participantes ?: null;
                }
            }
            
            if ($tipo_compromisso == 'diligencia') {
                $data['tipo_diligencia'] = $this->input->post('tipo_diligencia');
            }
            
            if ($tipo_compromisso == 'evento') {
                $data['tipo_evento'] = $this->input->post('tipo_evento');
                $data['abrangencia'] = $this->input->post('abrangencia') ?: 'pessoal';
            }

            // Verificar disponibilidade (double booking) - excluir o próprio compromisso
            if ($this->audiencias_model->verificarDisponibilidade($data['usuarios_id'], $dataHoraCompleta, $data['duracao_estimada'], $id)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Já existe um compromisso agendado neste horário para o responsável selecionado. Por favor, escolha outro horário ou outro responsável.</p></div>';
            } else {
                if ($this->audiencias_model->edit('audiencias', $data, 'idAudiencias', $id) == true) {
                    $this->session->set_flashdata('success', 'Compromisso editado com sucesso!');
                    log_info('Alterou um compromisso. ID: ' . $id);
                    redirect(site_url('audiencias/gerenciar'));
                } else {
                    $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o compromisso.</p></div>';
                }
            }
        }

        // Carregar dados para a view
        $this->data['result'] = $this->audiencias_model->getById($id);
        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['prazos'] = $this->prazos_model->get('prazos', '*', "status IN ('pendente', 'proximo_vencer', 'vencendo_hoje')", 0, 0, false, 'array');
        
        // Carregar usuários ativos para o campo Responsável (usando query builder direto)
        $this->db->select('idUsuarios, nome, email');
        $this->db->from('usuarios');
        $this->db->where('situacao', 1); // Apenas usuários ativos
        $this->db->order_by('nome', 'ASC');
        $query_usuarios = $this->db->get();
        
        if ($query_usuarios !== false && $query_usuarios->num_rows() > 0) {
            $this->data['usuarios'] = $query_usuarios->result();
        } else {
            $this->data['usuarios'] = [];
            log_message('warning', 'Audiencias::editar() - Nenhum usuário ativo encontrado. Verifique a tabela usuarios.');
        }
        $this->data['view'] = 'audiencias/editarAudiencia';

        return $this->layout();
    }

    public function visualizar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar audiências.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar visualizar audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        $this->data['result'] = $this->audiencias_model->getById($id);
        $this->data['view'] = 'audiencias/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dAudiencia')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir audiências.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir audiência.');
            redirect(site_url('audiencias/gerenciar'));
        }

        if ($this->audiencias_model->delete('audiencias', 'idAudiencias', $id) == true) {
            $this->session->set_flashdata('success', 'Audiência excluída com sucesso!');
            log_info('Removeu uma audiência. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir a audiência.');
        }

        redirect(site_url('audiencias/gerenciar'));
    }

    /**
     * Método de debug para verificar carregamento de usuários
     * Acesse: /audiencias/debug_usuarios
     */
    public function debug_usuarios()
    {
        echo "<h2>Debug: Carregamento de Usuários</h2>";
        
        // Teste 1: Query direta com filtro de ativos
        echo "<h3>Teste 1: Query Builder Direto (Filtro: situacao = 1)</h3>";
        $this->db->select('idUsuarios, nome, email, situacao');
        $this->db->from('usuarios');
        $this->db->where('situacao', 1);
        $this->db->order_by('nome', 'ASC');
        $query1 = $this->db->get();
        $resultado1 = ($query1 !== false) ? $query1->result() : [];
        
        echo "<p><strong>Quantidade encontrada:</strong> " . count($resultado1) . "</p>";
        echo "<pre>";
        print_r($resultado1);
        echo "</pre>";
        
        // Teste 2: Todos os usuários (sem filtro)
        echo "<h3>Teste 2: Todos os Usuários (Sem Filtro)</h3>";
        $this->db->reset_query();
        $this->db->select('idUsuarios, nome, email, situacao');
        $this->db->from('usuarios');
        $query2 = $this->db->get();
        $resultado2 = ($query2 !== false) ? $query2->result() : [];
        
        echo "<p><strong>Quantidade total:</strong> " . count($resultado2) . "</p>";
        echo "<pre>";
        print_r($resultado2);
        echo "</pre>";
        
        // Teste 3: Sistema_model
        echo "<h3>Teste 3: Sistema_model</h3>";
        $this->load->model('sistema_model');
        $this->db->reset_query();
        $resultado3 = $this->sistema_model->get('usuarios', '*', '', 0, 0, false);
        
        echo "<p><strong>Quantidade:</strong> " . (is_array($resultado3) ? count($resultado3) : 'N/A') . "</p>";
        echo "<p><strong>Tipo de retorno:</strong> " . gettype($resultado3) . "</p>";
        if (!empty($resultado3)) {
            echo "<pre>";
            print_r(is_array($resultado3) ? array_slice($resultado3, 0, 3) : $resultado3);
            echo "</pre>";
        }
        
        // Teste 4: Verificar estrutura da tabela
        echo "<h3>Teste 4: Estrutura da Tabela usuarios</h3>";
        if ($this->db->table_exists('usuarios')) {
            $fields = $this->db->list_fields('usuarios');
            echo "<p><strong>Campos da tabela:</strong></p>";
            echo "<pre>";
            print_r($fields);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'><strong>ERRO: Tabela usuarios não existe!</strong></p>";
        }
        
        echo "<hr>";
        echo "<p><strong>Recomendação:</strong> Se o Teste 1 retornou 0 usuários, você precisa criar usuários ativos no sistema ou verificar o valor do campo 'situacao' na tabela usuarios.</p>";
    }
}

