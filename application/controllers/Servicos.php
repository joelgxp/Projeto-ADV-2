<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Servicos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('servicos_model');
        $this->data['menuServicos'] = 'Serviços Jurídicos';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar serviços.');
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

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('servicos/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->servicos_model->count('servicos_juridicos');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/servicos")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->servicos_model->get('servicos_juridicos', '*', $pesquisa, $this->data['configuration']['per_page'], 0);

        $this->data['view'] = 'servicos/servicos';

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
        $recordsTotal = $this->servicos_model->count('servicos_juridicos');
        
        // Buscar registros com paginação e filtro
        $results = $this->servicos_model->get('servicos_juridicos', '*', $search, $length, $start);
        
        // Total de registros com filtro aplicado
        $recordsFiltered = $recordsTotal;
        if ($search) {
            // Resetar query builder
            $this->db->reset_query();
            
            $tableName = $this->db->table_exists('servicos_juridicos') ? 'servicos_juridicos' : ($this->db->table_exists('servicos') ? 'servicos' : 'servicos_juridicos');
            $this->db->from($tableName);
            
            $this->db->group_start();
            $this->db->like('nome', $search);
            $this->db->or_like('descricao', $search);
            if ($this->db->table_exists($tableName)) {
                $columns = $this->db->list_fields($tableName);
                if (in_array('tipo_servico', $columns)) {
                    $this->db->or_like('tipo_servico', $search);
                }
            }
            $this->db->group_end();
            
            $recordsFiltered = $this->db->count_all_results();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                // Ações
                $acoes = '';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
                    $acoes .= '<a style="margin-right: 1%" href="' . base_url() . 'index.php/servicos/editar/' . $r->idServicos . '" class="btn-nwe3" title="Editar Serviço"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dServico')) {
                    $acoes .= '<a href="#modal-excluir" role="button" data-toggle="modal" servico="' . $r->idServicos . '" class="btn-nwe4" title="Excluir Serviço"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                
                $data[] = [
                    $r->idServicos,
                    $r->nome ?? '-',
                    (isset($r->tipo_servico) && $r->tipo_servico ? $r->tipo_servico : '-'),
                    'R$ ' . number_format($r->preco ?? 0, 2, ',', '.'),
                    (isset($r->tempo_estimado) && $r->tempo_estimado ? $r->tempo_estimado . 'h' : '-'),
                    $r->descricao ?? '-',
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
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar serviços.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(',', '', $preco);

            $data = [
                'nome' => set_value('nome'),
                'descricao' => set_value('descricao'),
                'preco' => $preco,
                'tipo_servico' => $this->input->post('tipo_servico'),
                'valor_base' => $preco,
                'tempo_estimado' => $this->input->post('tempo_estimado') ?: null,
            ];

            if ($this->servicos_model->add('servicos_juridicos', $data) == true) {
                $this->session->set_flashdata('success', 'Serviço adicionado com sucesso!');
                log_info('Adicionou um serviço');
                redirect(site_url('servicos/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }
        $this->data['view'] = 'servicos/adicionarServico';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->servicos_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Serviço não encontrado ou parâmetro inválido.');
            redirect('servicos/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar serviços.');
            redirect(base_url());
        }
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('servicos') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $preco = $this->input->post('preco');
            $preco = str_replace(',', '', $preco);
            $data = [
                'nome' => $this->input->post('nome'),
                'descricao' => $this->input->post('descricao'),
                'preco' => $preco,
                'tipo_servico' => $this->input->post('tipo_servico'),
                'valor_base' => $preco,
                'tempo_estimado' => $this->input->post('tempo_estimado') ?: null,
            ];

            if ($this->servicos_model->edit('servicos_juridicos', $data, 'idServicos', $this->input->post('idServicos')) == true) {
                $this->session->set_flashdata('success', 'Serviço editado com sucesso!');
                log_info('Alterou um serviço. ID: ' . $this->input->post('idServicos'));
                redirect(site_url('servicos/editar/') . $this->input->post('idServicos'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um errro.</p></div>';
            }
        }

        $this->data['result'] = $this->servicos_model->getById($this->uri->segment(3));

        $this->data['view'] = 'servicos/editarServico';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dServico')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir serviços.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir serviço.');
            redirect(site_url('servicos/gerenciar/'));
        }

        $this->servicos_model->delete('servicos_juridicos', 'idServicos', $id);

        log_info('Removeu um serviço. ID: ' . $id);

        $this->session->set_flashdata('success', 'Serviço excluido com sucesso!');
        redirect(site_url('servicos/gerenciar/'));
    }
}
