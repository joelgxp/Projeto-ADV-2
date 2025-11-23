<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Prazos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('prazos_model');
        $this->load->model('processos_model');
        $this->data['menuPrazos'] = 'prazos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar prazos.');
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

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('prazos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->prazos_model->count('prazos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/prazos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $where = '';
        if ($pesquisa) {
            $where = $pesquisa;
        }
        if ($status) {
            $where .= ($where ? ' AND ' : '') . "status = '{$status}'";
        }

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->prazos_model->get('prazos', '*', $where, $this->data['configuration']['per_page'], 0);
        $this->data['status'] = $status;

        $this->data['view'] = 'prazos/prazos';

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
        $recordsTotal = $this->prazos_model->count('prazos');
        
        // Buscar filtro de status da URL
        $status = $this->input->get('status');
        
        // Construir where para busca e status
        $where = '';
        if ($search) {
            $where = $search; // O model trata como like
        }
        if ($status) {
            $where .= ($where ? ' AND ' : '') . "status = '{$status}'";
        }
        
        // Buscar registros com paginação e filtro
        $results = $this->prazos_model->get('prazos', '*', $where, $length, $start);
        
        // Total de registros com filtro aplicado
        $recordsFiltered = $recordsTotal;
        if ($search || $status) {
            // Resetar query builder
            $this->db->reset_query();
            
            $this->db->from('prazos');
            
            // Join com processos se necessário
            if ($this->db->table_exists('processos')) {
                $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
            }
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('prazos.descricao', $search);
                $this->db->or_like('prazos.tipo', $search);
                if ($this->db->table_exists('processos')) {
                    $this->db->or_like('processos.numeroProcesso', $search);
                }
                $this->db->group_end();
            }
            
            if ($status) {
                $this->db->where('prazos.status', $status);
            }
            
            $recordsFiltered = $this->db->count_all_results();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                // Formatar datas
                $dataPrazo = isset($r->dataPrazo) ? date('d/m/Y', strtotime($r->dataPrazo)) : '-';
                
                // Data de vencimento com destaque se vencido
                $dataVencimento = isset($r->dataVencimento) ? date('d/m/Y', strtotime($r->dataVencimento)) : '-';
                $vencido = isset($r->dataVencimento) && strtotime($r->dataVencimento) < strtotime('today') && ($r->status ?? '') == 'Pendente';
                $vencendo = isset($r->dataVencimento) && strtotime($r->dataVencimento) <= strtotime('+3 days') && strtotime($r->dataVencimento) >= strtotime('today') && ($r->status ?? '') == 'Pendente';
                
                $dataVencimentoFormatada = $dataVencimento;
                if ($vencido) {
                    $dataVencimentoFormatada = '<span class="label label-important">' . $dataVencimento . ' (Vencido)</span>';
                } elseif ($vencendo) {
                    $dataVencimentoFormatada = '<span class="label label-warning">' . $dataVencimento . ' (Vencendo)</span>';
                }
                
                // Status com cores
                $status_labels = [
                    'Pendente' => ['label' => 'Pendente', 'class' => 'label-warning'],
                    'Cumprido' => ['label' => 'Cumprido', 'class' => 'label-success'],
                    'Vencido' => ['label' => 'Vencido', 'class' => 'label-important'],
                ];
                $status = $r->status ?? 'Pendente';
                $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => 'label-default'];
                
                // Prioridade
                $prioridade_labels = [
                    'Baixa' => ['label' => 'Baixa', 'class' => 'label-default'],
                    'Normal' => ['label' => 'Normal', 'class' => 'label-info'],
                    'Alta' => ['label' => 'Alta', 'class' => 'label-warning'],
                    'Urgente' => ['label' => 'Urgente', 'class' => 'label-important'],
                ];
                $prioridade = $r->prioridade ?? 'Normal';
                $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => $prioridade, 'class' => 'label-default'];
                
                // Ações
                $acoes = '';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/prazos/visualizar/' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/prazos/editar/' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Prazo"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dPrazo')) {
                    $acoes .= '<a href="#modal-excluir" role="button" data-toggle="modal" prazo="' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Prazo"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                
                $numeroProcessoLink = '-';
                if (isset($r->numeroProcesso) && isset($r->processos_id)) {
                    $numeroProcessoLink = '<a href="' . base_url() . 'index.php/processos/visualizar/' . $r->processos_id . '">' . $r->numeroProcesso . '</a>';
                } elseif (isset($r->numeroProcesso)) {
                    $numeroProcessoLink = $r->numeroProcesso;
                }
                
                $data[] = [
                    $r->idPrazos,
                    $numeroProcessoLink,
                    $r->tipo ?? '-',
                    $r->descricao ?? '-',
                    $dataPrazo,
                    $dataVencimentoFormatada,
                    '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>',
                    '<span class="label ' . $prioridade_info['class'] . '">' . $prioridade_info['label'] . '</span>',
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
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar prazos.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required|trim');
        $this->form_validation->set_rules('dataVencimento', 'Data de Vencimento', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'descricao' => $this->input->post('descricao'),
                'dataPrazo' => $this->input->post('dataPrazo'),
                'dataVencimento' => $this->input->post('dataVencimento'),
                'status' => $this->input->post('status') ?: 'Pendente',
                'prioridade' => $this->input->post('prioridade') ?: 'Normal',
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if ($this->prazos_model->add('prazos', $data) == true) {
                $this->session->set_flashdata('success', 'Prazo adicionado com sucesso!');
                log_info('Adicionou um prazo processual.');
                redirect(site_url('prazos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o prazo.</p></div>';
            }
        }

        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'prazos/adicionarPrazo';

        return $this->layout();
    }

    public function editar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar prazos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar editar prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('processos_id', 'Processo', 'required|trim');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|trim');
        $this->form_validation->set_rules('descricao', 'Descrição', 'required|trim');
        $this->form_validation->set_rules('dataVencimento', 'Data de Vencimento', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'processos_id' => $this->input->post('processos_id'),
                'tipo' => $this->input->post('tipo'),
                'descricao' => $this->input->post('descricao'),
                'dataPrazo' => $this->input->post('dataPrazo'),
                'dataVencimento' => $this->input->post('dataVencimento'),
                'status' => $this->input->post('status'),
                'prioridade' => $this->input->post('prioridade'),
            ];

            if ($this->prazos_model->edit('prazos', $data, 'idPrazos', $id) == true) {
                $this->session->set_flashdata('success', 'Prazo editado com sucesso!');
                log_info('Alterou um prazo processual. ID: ' . $id);
                redirect(site_url('prazos/gerenciar'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o prazo.</p></div>';
            }
        }

        $this->data['result'] = $this->prazos_model->getById($id);
        $this->data['processos'] = $this->processos_model->get('processos', '*', '', 0, 0, false, 'array');
        $this->data['view'] = 'prazos/editarPrazo';

        return $this->layout();
    }

    public function visualizar($id = null)
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar prazos.');
            redirect(base_url());
        }

        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar visualizar prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        $this->data['result'] = $this->prazos_model->getById($id);
        $this->data['view'] = 'prazos/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dPrazo')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir prazos.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir prazo.');
            redirect(site_url('prazos/gerenciar'));
        }

        if ($this->prazos_model->delete('prazos', 'idPrazos', $id) == true) {
            $this->session->set_flashdata('success', 'Prazo excluído com sucesso!');
            log_info('Removeu um prazo processual. ID: ' . $id);
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar excluir o prazo.');
        }

        redirect(site_url('prazos/gerenciar'));
    }
}

