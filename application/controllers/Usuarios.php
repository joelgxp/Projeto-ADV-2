<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Usuarios extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar os usuários.');
            redirect(base_url());
        }

        $this->load->helper('form');
        $this->load->model('usuarios_model');
        $this->data['menuUsuarios'] = 'Usuários';
        $this->data['menuConfiguracoes'] = 'Configurações';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
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

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = base_url() . 'index.php/usuarios/gerenciar/';
        $this->data['configuration']['total_rows'] = $this->usuarios_model->count('usuarios');

        $this->pagination->initialize($this->data['configuration']);

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->usuarios_model->get($this->data['configuration']['per_page'], 0);

        $this->data['view'] = 'usuarios/usuarios';

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
        $recordsTotal = $this->usuarios_model->count('usuarios');
        
        // Buscar registros com paginação
        $results = $this->usuarios_model->get($length, $start);
        
        // Total de registros com filtro aplicado
        $recordsFiltered = $recordsTotal;
        if ($search) {
            // Resetar query builder
            $this->db->reset_query();
            
            $this->db->from('usuarios');
            $this->db->join('permissoes', 'usuarios.permissoes_id = permissoes.idPermissao', 'left');
            
            // Aplicar filtros de busca
            $this->db->group_start();
            $this->db->like('usuarios.nome', $search);
            $this->db->or_like('usuarios.cpf', $search);
            $this->db->or_like('permissoes.nome', $search);
            $this->db->group_end();
            
            $recordsFiltered = $this->db->count_all_results();
            
            // Buscar novamente com filtro
            $this->db->reset_query();
            $this->db->from('usuarios');
            $this->db->select('usuarios.*, permissoes.nome as permissao');
            $this->db->join('permissoes', 'usuarios.permissoes_id = permissoes.idPermissao', 'left');
            $this->db->group_start();
            $this->db->like('usuarios.nome', $search);
            $this->db->or_like('usuarios.cpf', $search);
            $this->db->or_like('permissoes.nome', $search);
            $this->db->group_end();
            $this->db->limit($length, $start);
            $results = $this->db->get()->result();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                // Situação com cores
                $situacao = (isset($r->situacao) && $r->situacao == 1) ? 'Ativo' : 'Inativo';
                $situacaoClasse = (isset($r->situacao) && $r->situacao == 1) ? 'situacao-ativo' : 'situacao-inativo';
                
                // Ações
                $acoes = '<a href="' . base_url('index.php/usuarios/editar/' . $r->idUsuarios) . '" class="btn-nwe3" title="Editar Usuário"><i class="bx bx-edit"></i></a>';
                
                $data[] = [
                    $r->idUsuarios ?? '-',
                    $r->nome ?? '-',
                    $r->cpf ?? '-',
                    $r->permissao ?? '-',
                    '<span class="badge ' . $situacaoClasse . '">' . ucfirst($situacao) . '</span>',
                    $r->dataExpiracao ?? '-',
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
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        // Adicionar validação de unicidade para CPF e Email
        $this->form_validation->set_rules('cpf', 'CPF', 'trim|required|verific_cpf_cnpj|is_unique[usuarios.cpf]', [
            'is_unique' => 'Este CPF já está cadastrado no sistema.',
            'verific_cpf_cnpj' => 'O campo %s não é um CPF válido.'
        ]);
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[usuarios.email]', [
            'is_unique' => 'Este email já está cadastrado no sistema.',
            'valid_email' => 'O campo %s deve conter um email válido.'
        ]);

        if ($this->form_validation->run('usuarios') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="alert alert-danger">' . validation_errors() . '</div>' : false);
        } else {
            $data = [
                'nome' => set_value('nome'),
                'oab' => set_value('oab'),
                'cpf' => set_value('cpf'),
                'cep' => set_value('cep'),
                'rua' => set_value('rua'),
                'numero' => set_value('numero'),
                'bairro' => set_value('bairro'),
                'cidade' => set_value('cidade'),
                'estado' => set_value('estado'),
                'email' => set_value('email'),
                'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
                'celular' => set_value('celular'),
                'dataExpiracao' => set_value('dataExpiracao'),
                'situacao' => set_value('situacao'),
                'permissoes_id' => $this->input->post('permissoes_id'),
                'dataCadastro' => date('Y-m-d'),
            ];

            if ($this->usuarios_model->add('usuarios', $data) == true) {
                $this->session->set_flashdata('success', 'Usuário cadastrado com sucesso!');
                log_info('Adicionou um usuário.');
                redirect(site_url('usuarios/adicionar/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->load->model('permissoes_model');
        $this->data['permissoes'] = $this->permissoes_model->getActive('permissoes', 'permissoes.idPermissao,permissoes.nome');
        $this->data['view'] = 'usuarios/adicionarUsuario';

        return $this->layout();
    }

    public function editar()
    {
          if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->usuarios_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Usuário não encontrado ou parâmetro inválido.');
            redirect('usuarios/gerenciar');
        }

        $idUsuario = $this->uri->segment(3);
        
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $this->form_validation->set_rules('nome', 'Nome', 'trim|required');
        // Validação de CPF único (exceto o próprio registro na edição)
        $this->form_validation->set_rules('cpf', 'CPF', 'trim|required|verific_cpf_cnpj|callback_check_unique_cpf[' . $idUsuario . ']', [
            'verific_cpf_cnpj' => 'O campo %s não é um CPF válido.',
            'check_unique_cpf' => 'Este CPF já está cadastrado no sistema.'
        ]);
        // Validação de Email único (exceto o próprio registro na edição)
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_check_unique_email[' . $idUsuario . ']', [
            'valid_email' => 'O campo %s deve conter um email válido.',
            'check_unique_email' => 'Este email já está cadastrado no sistema.'
        ]);
        $this->form_validation->set_rules('situacao', 'Situação', 'trim|required');
        $this->form_validation->set_rules('permissoes_id', 'Permissão', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            if ($this->input->post('idUsuarios') == 1 && $this->input->post('situacao') == 0) {
                $this->session->set_flashdata('error', 'O usuário super admin não pode ser desativado!');
                redirect(base_url() . 'index.php/usuarios/editar/' . $this->input->post('idUsuarios'));
            }

            $senha = $this->input->post('senha');
            if ($senha != null) {
                $senha = password_hash($senha, PASSWORD_DEFAULT);

                $data = [
                    'nome' => $this->input->post('nome'),
                    'oab' => $this->input->post('oab'),
                    'cpf' => $this->input->post('cpf'),
                    'cep' => $this->input->post('cep'),
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'email' => $this->input->post('email'),
                    'senha' => $senha,
                    'celular' => $this->input->post('celular'),
                    'dataExpiracao' => set_value('dataExpiracao'),
                    'situacao' => $this->input->post('situacao'),
                    'permissoes_id' => $this->input->post('permissoes_id'),
                ];
            } else {
                $data = [
                    'nome' => $this->input->post('nome'),
                    'oab' => $this->input->post('oab'),
                    'cpf' => $this->input->post('cpf'),
                    'cep' => $this->input->post('cep'),
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'email' => $this->input->post('email'),
                    'celular' => $this->input->post('celular'),
                    'dataExpiracao' => set_value('dataExpiracao'),
                    'situacao' => $this->input->post('situacao'),
                    'permissoes_id' => $this->input->post('permissoes_id'),
                ];
            }

            if ($this->usuarios_model->edit('usuarios', $data, 'idUsuarios', $this->input->post('idUsuarios')) == true) {
                $this->session->set_flashdata('success', 'Usuário editado com sucesso!');
                log_info('Alterou um usuário. ID: ' . $this->input->post('idUsuarios'));
                redirect(site_url('usuarios/editar/') . $this->input->post('idUsuarios'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
        }

        $this->data['result'] = $this->usuarios_model->getById($this->uri->segment(3));
        $this->load->model('permissoes_model');
        $this->data['permissoes'] = $this->permissoes_model->getActive('permissoes', 'permissoes.idPermissao,permissoes.nome');

        $this->data['view'] = 'usuarios/editarUsuario';

        return $this->layout();
    }

    public function excluir()
    {
        $id = $this->uri->segment(3);
        $this->usuarios_model->delete('usuarios', 'idUsuarios', $id);

        log_info('Removeu um usuário. ID: ' . $id);

        redirect(site_url('usuarios/gerenciar/'));
    }

    /**
     * Callback para validar CPF único na edição
     */
    public function check_unique_cpf($cpf, $idUsuario)
    {
        $this->db->where('cpf', $cpf);
        if ($idUsuario) {
            $this->db->where('idUsuarios !=', $idUsuario);
        }
        $query = $this->db->get('usuarios');
        
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('check_unique_cpf', 'Este CPF já está cadastrado no sistema.');
            return false;
        }
        return true;
    }

    /**
     * Callback para validar Email único na edição
     */
    public function check_unique_email($email, $idUsuario)
    {
        $this->db->where('email', $email);
        if ($idUsuario) {
            $this->db->where('idUsuarios !=', $idUsuario);
        }
        $query = $this->db->get('usuarios');
        
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('check_unique_email', 'Este email já está cadastrado no sistema.');
            return false;
        }
        return true;
    }
}
