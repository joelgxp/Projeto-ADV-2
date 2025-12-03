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
        $this->load->helper('password');
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
                
                // Não mostrar botão de excluir para usuário do sistema (ID 1)
                if ($r->idUsuarios != 1) {
                    $acoes .= ' <a href="#modal-excluir" role="button" data-toggle="modal" usuario="' . $r->idUsuarios . '" class="btn-nwe4" title="Excluir Usuário"><i class="bx bx-trash-alt"></i></a>';
                }
                
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
        $this->form_validation->set_rules('cpf', 'CPF', 'trim|required|verific_cpf|is_unique[usuarios.cpf]', [
            'is_unique' => 'Este CPF já está cadastrado no sistema.',
            'verific_cpf' => 'O campo %s deve ser um CPF válido (11 dígitos).'
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
                'email_confirmado' => 0, // RN 1.1: E-mail não confirmado inicialmente
                'dataCadastro' => date('Y-m-d H:i:s'),
            ];

            if ($this->usuarios_model->add('usuarios', $data) == true) {
                $usuario_id = $this->db->insert_id();
                
                // Criar token de confirmação de e-mail (RN 1.1)
                $this->load->model('Confirmacoes_email_model');
                $token_data = $this->Confirmacoes_email_model->criarToken($usuario_id);
                
                if ($token_data) {
                    // Enviar e-mail de confirmação (RN 1.1)
                    log_message('info', "Iniciando envio de email de confirmação para usuário ID: {$usuario_id}, Email: " . set_value('email'));
                    $email_enviado = $this->enviarEmailConfirmacao(set_value('email'), set_value('nome'), $token_data['token']);
                    
                    if ($email_enviado) {
                        $this->session->set_flashdata('success', 'Usuário cadastrado com sucesso! Um e-mail de confirmação foi enviado. Verifique sua caixa de entrada (e spam).');
                        log_message('info', "✅ Processo de envio de email concluído com sucesso para: " . set_value('email'));
                    } else {
                        $this->session->set_flashdata('success', 'Usuário cadastrado com sucesso! Porém, houve um problema ao enviar o e-mail. Verifique os logs e a fila de emails.');
                        log_message('error', "❌ Falha no processo de envio de email para: " . set_value('email'));
                    }
                } else {
                    log_message('error', '❌ Erro ao criar token de confirmação para usuário ID: ' . $usuario_id);
                    $this->session->set_flashdata('success', 'Usuário cadastrado com sucesso! Porém, não foi possível criar o token de confirmação.');
                }
                
                log_info('Adicionou um usuário.');
                redirect(site_url('usuarios/gerenciar/'));
            } else {
                // Capturar erro do banco de dados para debug
                $db_error = $this->db->error();
                $error_message = 'Ocorreu um erro ao cadastrar o usuário.';
                
                if (!empty($db_error['message'])) {
                    log_message('error', 'Erro ao cadastrar usuário: ' . $db_error['message']);
                    $error_message .= ' Detalhes: ' . $db_error['message'];
                }
                
                $this->data['custom_error'] = '<div class="form_error"><p>' . $error_message . '</p></div>';
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
        $this->form_validation->set_rules('cpf', 'CPF', 'trim|required|verific_cpf|callback_check_unique_cpf[' . $idUsuario . ']', [
            'verific_cpf' => 'O campo %s deve ser um CPF válido (11 dígitos).',
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
        // Aceitar ID via POST (modal) ou GET (URL direta)
        $id = $this->input->post('id') ?: $this->uri->segment(3);
        
        if (!$id) {
            $this->session->set_flashdata('error', 'ID do usuário não informado.');
            redirect(site_url('usuarios/gerenciar/'));
            return;
        }
        
        // Proteger usuário do sistema (ID 1 - super admin)
        if ($id == 1) {
            $this->session->set_flashdata('error', 'O usuário do sistema não pode ser excluído!');
            redirect(site_url('usuarios/gerenciar/'));
            return;
        }
        
        // Verificar se o usuário existe
        $usuario = $this->usuarios_model->getById($id);
        if (!$usuario) {
            $this->session->set_flashdata('error', 'Usuário não encontrado.');
            redirect(site_url('usuarios/gerenciar/'));
            return;
        }
        
        $this->usuarios_model->delete('usuarios', 'idUsuarios', $id);

        $this->session->set_flashdata('success', 'Usuário excluído com sucesso!');
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

    /**
     * Callback para validar senha forte (RN 1.1)
     */
    public function validar_senha_forte($senha)
    {
        $validacao = validar_senha_forte($senha);
        
        if (!$validacao['valido']) {
            $this->form_validation->set_message('validar_senha_forte', implode(' ', $validacao['erros']));
            return false;
        }
        
        return true;
    }

    /**
     * Envia e-mail de confirmação para novo usuário (RN 1.1)
     * Envia imediatamente e também adiciona à fila como backup
     * 
     * @param string $email Email do destinatário
     * @param string $nome Nome do usuário
     * @param string $token Token de confirmação
     * @return bool True se enviado com sucesso, False caso contrário
     */
    private function enviarEmailConfirmacao($email, $nome, $token)
    {
        try {
            // IMPORTANTE: Carregar config ANTES da biblioteca para garantir que as configurações sejam aplicadas
            $this->load->config('email');
            $this->load->library('email');
            $this->load->model('email_model');
            
            $link_confirmacao = site_url('usuarios/confirmar_email/' . $token);
            $data_expiracao = date('d/m/Y H:i', strtotime('+24 hours'));
            
            $mensagem = "
            <html>
            <body>
                <h2>Confirmação de E-mail - Sistema Adv</h2>
                <p>Olá, <strong>{$nome}</strong>!</p>
                <p>Bem-vindo ao sistema. Para ativar sua conta, por favor clique no link abaixo para confirmar seu e-mail:</p>
                <p><a href=\"{$link_confirmacao}\" style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Confirmar E-mail</a></p>
                <p>Ou copie e cole este link no seu navegador:</p>
                <p>{$link_confirmacao}</p>
                <p><strong>Este link expira em 24 horas.</strong> (válido até {$data_expiracao})</p>
                <p>Se você não solicitou este cadastro, ignore este e-mail.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>Este é um e-mail automático, por favor não responda.</p>
            </body>
            </html>
            ";
            
            // Obter configurações de e-mail (já carregadas acima)
            $smtp_user = $this->config->item('smtp_user');
            $app_name = isset($this->data['configuration']['app_name']) ? $this->data['configuration']['app_name'] : 'Adv';
            
            // Validar se o remetente está configurado
            if (empty($smtp_user)) {
                log_message('error', 'Erro: E-mail remetente não configurado. Configure o EMAIL_SMTP_USER nas configurações.');
                // Adicionar à fila mesmo sem remetente configurado (pode ser configurado depois)
                $email_data = [
                    'to' => $email,
                    'subject' => 'Confirmação de E-mail - Sistema Adv',
                    'message' => $mensagem,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->email_model->add('email_queue', $email_data);
                return false;
            }
            
            // Adicionar à fila e processar imediatamente usando método centralizado
            $email_data = [
                'to' => $email,
                'subject' => 'Confirmação de E-mail - Sistema Adv',
                'message' => $mensagem,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if ($this->email_model->add('email_queue', $email_data)) {
                $email_id = $this->db->insert_id();
                log_message('info', "Email de confirmação adicionado à fila. ID: {$email_id}, Para: {$email} (Token: {$token})");
                
                // Processar imediatamente usando método centralizado
                $this->load->library('email');
                $enviado = $this->email->send_single($email_id);
                
                if ($enviado) {
                    log_message('info', "✅ Email de confirmação enviado com SUCESSO para: {$email} (Token: {$token})");
                    return true;
                } else {
                    log_message('warning', "⚠️ Email de confirmação adicionado à fila mas falhou no envio. ID: {$email_id}");
                    return false;
                }
            } else {
                log_message('error', "❌ Erro ao adicionar email de confirmação à fila para: {$email}");
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Exceção ao enviar email de confirmação: ' . $e->getMessage());
            
            // Em caso de exceção, tentar adicionar à fila
            try {
                $link_confirmacao = site_url('usuarios/confirmar_email/' . $token);
                $data_expiracao = date('d/m/Y H:i', strtotime('+24 hours'));
                $mensagem = "
                <html>
                <body>
                    <h2>Confirmação de E-mail - Sistema Adv</h2>
                    <p>Olá, <strong>{$nome}</strong>!</p>
                    <p>Bem-vindo ao sistema. Para ativar sua conta, por favor clique no link abaixo para confirmar seu e-mail:</p>
                    <p><a href=\"{$link_confirmacao}\" style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Confirmar E-mail</a></p>
                    <p>Ou copie e cole este link no seu navegador:</p>
                    <p>{$link_confirmacao}</p>
                    <p><strong>Este link expira em 24 horas.</strong> (válido até {$data_expiracao})</p>
                    <p>Se você não solicitou este cadastro, ignore este e-mail.</p>
                    <hr>
                    <p style='color: #666; font-size: 12px;'>Este é um e-mail automático, por favor não responda.</p>
                </body>
                </html>
                ";
                
                $email_data = [
                    'to' => $email,
                    'subject' => 'Confirmação de E-mail - Sistema Adv',
                    'message' => $mensagem,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->email_model->add('email_queue', $email_data);
                return true;
            } catch (Exception $e2) {
                log_message('error', 'Erro ao adicionar email à fila após exceção: ' . $e2->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Confirma e-mail do usuário via token (RN 1.1)
     */
    public function confirmar_email($token = null)
    {
        if (!$token) {
            $this->session->set_flashdata('error', 'Token de confirmação inválido.');
            redirect('login');
            return;
        }
        
        $this->load->model('Confirmacoes_email_model');
        $usuario = $this->Confirmacoes_email_model->validarToken($token);
        
        if ($usuario) {
            // Atualizar usuário como confirmado
            $this->db->where('idUsuarios', $usuario->idUsuarios);
            $this->db->update('usuarios', ['email_confirmado' => 1]);
            
            $this->session->set_flashdata('success', 'E-mail confirmado com sucesso! Você já pode fazer login no sistema.');
            redirect('login');
        } else {
            $this->session->set_flashdata('error', 'Token de confirmação inválido ou expirado. Entre em contato com o administrador.');
            redirect('login');
        }
    }
}
