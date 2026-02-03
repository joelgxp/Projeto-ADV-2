<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mine extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->helper('Security_helper');
    }

    public function index()
    {
        $this->load->model('sistema_model');
        $data['emitente'] = $this->sistema_model->getEmitente();
        $this->load->view('conecte/login', $data);
    }

    /**
     * Acesso ao portal via link único (token) - Fase 6 - RN 6.1
     */
    public function acesso($token = null)
    {
        if (!$token) {
            $this->session->set_flashdata('error', 'Link de acesso inválido. Verifique o link enviado por e-mail.');
            redirect('mine');
            return;
        }

        $this->load->model('Acessos_cliente_model');
        
        // Buscar acesso pelo token
        $acesso = $this->Acessos_cliente_model->getByToken($token);
        
        if (!$acesso) {
            $this->session->set_flashdata('error', 'Link de acesso inválido ou desativado. Entre em contato com o escritório para obter um novo link.');
            redirect('mine');
            return;
        }

        // Verificar se token é válido (não expirado)
        if (!$this->Acessos_cliente_model->isTokenValido($token)) {
            $this->session->set_flashdata('error', 'Este link de acesso expirou. Entre em contato com o escritório para obter um novo link válido.');
            redirect('mine');
            return;
        }

        // Buscar cliente
        $this->load->model('clientes_model');
        $cliente = $this->clientes_model->getById($acesso->clientes_id);
        
        if (!$cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado. Entre em contato com o escritório.');
            redirect('mine');
            return;
        }

        // Atualizar último acesso
        $this->Acessos_cliente_model->atualizarUltimoAcesso($token);

        // Criar sessão do cliente
        $session_mine_data = [
            'nome' => $cliente->nomeCliente, 
            'cliente_id' => $cliente->idClientes, 
            'email' => $cliente->email, 
            'conectado' => true, 
            'isCliente' => true,
            'acesso_via_token' => true,
            'token_acesso' => $token
        ];
        $this->session->set_userdata($session_mine_data);
        
        // Registrar acesso na auditoria
        $this->load->model('Audit_model');
        $log_data = [
            'usuario' => $cliente->nomeCliente,
            'tarefa' => 'Cliente ' . $cliente->nomeCliente . ' acessou o portal via link único',
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Audit_model->add($log_data);
        
        log_info('Cliente ' . $cliente->nomeCliente . ' acessou o portal via link único (Token: ' . substr($token, 0, 8) . '...)');

        // Redirecionar para o painel
        redirect('mine/painel');
    }

    public function sair()
    {
        $this->session->sess_destroy();
        redirect('mine');
    }

    public function resetarSenha()
    {
        $this->load->view('conecte/resetar_senha');
    }

    public function senhaSalvar()
    {
        $this->load->library('form_validation');
        $data['custom_error'] = '';
        $this->form_validation->set_rules('senha', 'Senha', 'required');

        if ($this->input->post('token') == null || $this->input->post('token') == '') {
            return redirect('mine');
        }
        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'message' => 'Por favor digite uma senha']);
        } else {
            $token = $this->check_token($this->input->post('token'));
            $cliente = $this->check_credentials($token->email);

            if ($token == null && $cliente == null) {
                $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                $this->session->set_userdata($session_mine_data);
                log_info('Alteração de senha. Porém, os dados de acesso estão incorretos.');
                echo json_encode(['result' => false, 'message' => 'Os dados de acesso estão incorretos.']);
            } else {
                if ($token->email == $cliente->email) {
                    $data = [
                        'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
                    ];

                    $dataToken = [
                        'token_utilizado' => true,
                    ];
                    $this->load->model('resetSenhas_model', '', true);
                    if ($this->Conecte_model->edit('clientes', $data, 'idClientes', $cliente->idClientes) == true) {
                        if ($this->resetSenhas_model->edit('resets_de_senha', $dataToken, 'id', $token->id) == true) {
                            $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                            $this->session->set_userdata($session_mine_data);
                            log_info('Alteração da senha realizada com sucesso.');
                            echo json_encode(['result' => true]);
                        }
                    }
                } else {
                    $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                    $this->session->set_userdata($session_mine_data);
                    log_info('Alteração de senha. Porém, dados divergentes.');
                    echo json_encode(['result' => false, 'message' => 'Dados divergentes.']);
                }
            }
        }
    }

    public function tokenManual()
    {
        $this->load->library('form_validation');
        $data['custom_error'] = '';
        $this->form_validation->set_rules('token', 'Token', 'required');

        if ($this->form_validation->run('token') == false) {
            $this->session->set_flashdata(['error' => (validation_errors() ? 'Por favor digite o token' : false)]);

            return $this->load->view('conecte/token_digita');
        } else {
            $token = $this->check_token($this->input->post('token'));

            if ($this->validateDate($token->data_expiracao)) {
                $this->session->set_flashdata(['error' => 'Token expirado']);
                $session_mine_data = $token->email ? ['nome' => $token->email] : ['nome' => 'Inexistente'];
                $this->session->set_userdata($session_mine_data);
                log_info('Digitou Token. Porém, Token expirado');

                return redirect(base_url() . 'index.php/mine');
            } else {
                if ($token) {
                    if (($cliente = $this->check_credentials($token->email)) == null) {
                        $this->session->set_flashdata(['error' => 'Os dados de acesso estão incorretos.']);
                        $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                        $this->session->set_userdata($session_mine_data);
                        log_info('Digitou Token. Porém, os dados de acesso estão incorretos.');

                        return $this->load->view('conecte/token_digita');
                    } else {
                        if ($token->email == $cliente->email && $token->token_utilizado == false) {
                            return $this->load->view('conecte/nova_senha', $token);
                        } else {
                            $this->session->set_flashdata('error', 'Dados divergentes ou Token invalido.');
                            $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                            $this->session->set_userdata($session_mine_data);
                            log_info('Digitou Token. Porém, dados divergentes ou Token invalido.');

                            return redirect(base_url() . 'index.php/mine');
                        }
                    }
                } else {
                    $this->session->set_flashdata(['error' => 'Token Invalido']);
                    $session_mine_data = $token->email ? ['nome' => $token->email] : ['nome' => 'Inexistente'];
                    $this->session->set_userdata($session_mine_data);
                    log_info('Digitou Token. Porém, Token invalido.');

                    return $this->load->view('conecte/token_digita');
                }
            }
        }
        $this->load->view('conecte/token_digita');
    }

    public function verifyTokenSenha()
    {
        $token = $this->uri->uri_to_assoc(3);
        $token = $this->check_token($token['token']);

        if ($token == null || $token == '') {
            $this->session->set_flashdata(['error' => 'Token invalido']);
            $session_mine_data = $token->email ? ['nome' => $token->email] : ['nome' => 'Inexistente'];
            $this->session->set_userdata($session_mine_data);
            log_info('Acesso via link do email (Token). Porém, Token invalido.');

            return $this->load->view('conecte/token_digita');
        } else {
            if ($this->validateDate($token->data_expiracao)) {
                $this->session->set_flashdata(['error' => 'Token expirado']);
                $session_mine_data = $token->email ? ['nome' => $token->email] : ['nome' => 'Inexistente'];
                $this->session->set_userdata($session_mine_data);
                log_info('Acesso via link do email (Token). Porém, Token expirado');

                return redirect(base_url() . 'index.php/mine');
            } else {
                if ($token) {
                    if (($cliente = $this->check_credentials($token->email)) == null) {
                        $this->session->set_flashdata(['error' => 'Os dados de acesso estão incorretos.']);
                        $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                        $this->session->set_userdata($session_mine_data);
                        log_info('Acesso via link do email (Token). Porém, dados de acesso estão incorretos.');

                        return $this->load->view('conecte/token_digita');
                    } else {
                        if ($token->email == $cliente->email && $token->token_utilizado == false) {
                            return $this->load->view('conecte/nova_senha', $token);
                        } else {
                            $this->session->set_flashdata('error', 'Dados divergentes ou Token invalido.');
                            $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                            $this->session->set_userdata($session_mine_data);
                            log_info('Acesso via link do email (Token). Porém, dados divergentes ou Token invalido.');

                            return redirect(base_url() . 'index.php/mine');
                        }
                    }
                } else {
                    $this->session->set_flashdata(['error' => 'Token Invalido']);
                    $session_mine_data = $token->email ? ['nome' => $token->email] : ['nome' => 'Inexistente'];
                    $this->session->set_userdata($session_mine_data);
                    log_info('Acesso via link do email (Token). Porém, Token invalido.');

                    return $this->load->view('conecte/token_digita');
                }

                return $this->load->view('conecte/nova_senha', $token);
            }
        }
    }

    public function gerarTokenResetarSenha()
    {
        log_message('info', '=== INÍCIO gerarTokenResetarSenha ===');
        log_message('info', 'Método: ' . $this->input->method());
        log_message('info', 'POST data: ' . json_encode($this->input->post()));
        
        // Verificar se é POST
        if ($this->input->method() !== 'post') {
            log_message('info', 'Método não é POST, redirecionando');
            $this->session->set_flashdata('error', 'Método não permitido.');
            redirect('mine/resetarSenha');
            return;
        }
        
        // Validar e-mail
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email|trim');
        
        if ($this->form_validation->run() == false) {
            $errors = validation_errors();
            log_message('info', 'Validação falhou: ' . $errors);
            $this->session->set_flashdata('error', $errors ?: 'E-mail inválido.');
            redirect('mine/resetarSenha');
            return;
        }
        
        $email = $this->input->post('email');
            log_message('info', 'Email recebido: ' . $email);
        
        $cliente = $this->check_credentials($email);
        if (! $cliente) {
            log_message('info', 'Cliente NÃO encontrado para email: ' . $email);
            $this->session->set_flashdata('error', 'E-mail não encontrado no sistema. Verifique se o e-mail está correto ou entre em contato com o administrador.');
            // Não usar log_info aqui pois não há usuário logado (causa erro de NULL)
            redirect('mine/resetarSenha');
        } else {
            log_message('info', 'Cliente encontrado - ID: ' . $cliente->idClientes . ', Nome: ' . $cliente->nomeCliente);
            
            $this->load->helper('string');
            $this->load->model('resetSenhas_model', '', true);
            $data = [
                'email' => $cliente->email,
                'token' => random_string('alnum', 32),
                'data_expiracao' => date('Y-m-d H:i:s', strtotime('+1 hour')), // RN 1.3: Token válido por 1 hora
            ];
            log_message('info', 'Dados do token: ' . json_encode($data));
            
            $tokenInserido = $this->resetSenhas_model->add('resets_de_senha', $data);
            log_message('info', 'Resultado inserção token: ' . ($tokenInserido ? 'true' : 'false'));
            
            if ($tokenInserido == true) {
                log_message('info', 'Token inserido com sucesso, tentando enviar email...');
                $emailEnviado = $this->enviarRecuperarSenha($cliente->idClientes, $cliente->email, 'Recuperar Senha', json_encode($data));
                log_message('info', 'Resultado envio email: ' . ($emailEnviado ? 'true' : 'false'));
                
                $session_mine_data = ['nome' => $cliente->nomeCliente];
                $this->session->set_userdata($session_mine_data);
                
                if ($emailEnviado) {
                    log_info('Cliente solicitou alteração de senha. Email enviado com sucesso para: ' . $cliente->email);
                    $this->session->set_flashdata('success', '✅ E-mail enviado com sucesso! <br> Um e-mail com as instruções de recuperação de senha foi enviado para <strong>' . $cliente->email . '</strong>. <br> Verifique sua caixa de entrada (e a pasta de spam).');
                } else {
                    log_message('error', 'Falha ao enviar email de recuperação de senha para: ' . $cliente->email);
                    $this->session->set_flashdata('error', '⚠️ Falha ao enviar o e-mail! <br> O token foi gerado, mas houve um erro ao enviar o e-mail. <br> Por favor, tente novamente ou entre em contato com o administrador do sistema.');
                }
                
                log_message('info', 'Redirecionando para mine/index');
                redirect('mine');
            } else {
                log_message('error', 'Falha ao inserir token na tabela resets_de_senha');
                $db_error = $this->db->error();
                log_message('error', 'Erro do banco: ' . json_encode($db_error));
                
                $this->session->set_flashdata('error', 'Falha ao realizar solicitação!');
                $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                $this->session->set_userdata($session_mine_data);
                log_info('Cliente solicitou alteração de senha. Porém falhou ao realizar solicitação!');
                redirect(current_url());
            }
        }
        log_message('info', '=== FIM gerarTokenResetarSenha ===');
    }

    public function login()
    {
        header('Access-Control-Allow-Origin: ' . base_url());
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email|required|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|trim');
        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'message' => validation_errors()]);
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('senha');
            $cliente = $this->check_credentials($email);

            if ($cliente) {
                // Verificar credenciais do usuário
                if (password_verify($password, $cliente->senha)) {
                    $session_mine_data = [
                        'nome' => $cliente->nomeCliente, 
                        'cliente_id' => $cliente->idClientes, 
                        'email' => $cliente->email, 
                        'conectado' => true, 
                        'isCliente' => true
                    ];
                    $this->session->set_userdata($session_mine_data);
                    log_info($_SERVER['REMOTE_ADDR'] . ' Efetuou login no sistema');

                    // Registrar login na auditoria
                    $this->load->model('Audit_model');
                    $log_data = [
                        'usuario' => $cliente->nomeCliente,
                        'tarefa' => 'Cliente ' . $cliente->nomeCliente . ' efetuou login',
                        'data' => date('Y-m-d'),
                        'hora' => date('H:i:s'),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ];

                    $this->Audit_model->add($log_data);

                    echo json_encode(['result' => true]);
                } else {
                    echo json_encode(['result' => false, 'message' => 'Os dados de acesso estão incorretos.', 'ADV_TOKEN' => $this->security->get_csrf_hash()]);
                }
            } else {
                echo json_encode(['result' => false, 'message' => 'Usuário não encontrado, verifique se suas credenciais estão corretas.', 'ADV_TOKEN' => $this->security->get_csrf_hash()]);
            }
        }
    }

    public function painel()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $cliente_id = $this->session->userdata('cliente_id');
        $data['menuPainel'] = 'painel';
        
        // Buscar processos do cliente
        $this->load->model('processos_model');
        $data['processos'] = $this->processos_model->getProcessosByCliente($cliente_id, 5);
        $data['estatisticas_processos'] = $this->processos_model->getEstatisticasByCliente($cliente_id);
        
        // Buscar prazos próximos
        $this->load->model('prazos_model');
        $data['prazos'] = $this->prazos_model->getPrazosProximosByCliente($cliente_id, 5);
        $data['estatisticas_prazos'] = $this->prazos_model->getEstatisticasByCliente($cliente_id);
        
        // Buscar audiências próximas
        $this->load->model('audiencias_model');
        $data['audiencias'] = $this->audiencias_model->getAudienciasProximasByCliente($cliente_id, 5);
        $data['estatisticas_audiencias'] = $this->audiencias_model->getEstatisticasByCliente($cliente_id);
        
        $data['output'] = 'conecte/painel';
        $this->load->view('conecte/template', $data);
    }

    /**
     * Redirecionar para tickets (Fase 6 - Sprint 4)
     * Mantido para compatibilidade, mas não necessário mais
     */
    public function tickets()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        // Redirecionar diretamente para o controller Tickets
        // O controller Tickets agora detecta automaticamente se é cliente
        redirect('tickets');
    }

    public function conta()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuConta'] = 'conta';
        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/conta';
        $this->load->view('conecte/template', $data);
    }

    public function editarDados()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuConta'] = 'conta';

        $this->load->library('form_validation');
        $data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $senha = $this->input->post('senha');
            if ($senha != null) {
                $senha = password_hash($senha, PASSWORD_DEFAULT);
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $this->input->post('email'),
                    'senha' => $senha,
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'contato' => $this->input->post('contato'),
                ];
            } else {
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $this->input->post('email'),
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'contato' => $this->input->post('contato'),
                ];
            }

             if ($this->Conecte_model->edit('clientes', $data, 'idClientes', $this->session->userdata('cliente_id')) == true) {
                $this->session->set_flashdata('success', 'Dados editados com sucesso!');
                redirect(base_url() . 'index.php/mine/conta');
            } else {
            }
        }

        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/editar_dados';
        $this->load->view('conecte/template', $data);
    }


    public function cobrancas()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $this->load->library('pagination');

        $data['menuCobrancas'] = 'cobrancas';

        $config['base_url'] = base_url() . 'index.php/mine/cobrancas/';
        $config['total_rows'] = $this->Conecte_model->count('cobrancas', $this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $data['results'] = $this->Conecte_model->getCobrancas('cobrancas', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('cliente_id'));
        $data['output'] = 'conecte/cobrancas';

        $this->load->view('conecte/template', $data);
    }

    public function atualizarcobranca($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('adv');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar cobrança.');
            redirect(base_url());
        }

        $this->load->model('cobrancas_model');
        $this->cobrancas_model->atualizarStatus($this->uri->segment(3));

        redirect(site_url('mine/cobrancas/'));
    }

    public function enviarcobranca()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('adv');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar cobrança.');
            redirect(base_url());
        }

        $this->load->model('cobrancas_model');
        $this->cobrancas_model->enviarEmail($this->uri->segment(3));
        $this->session->set_flashdata('success', 'Email adicionado na fila.');

        redirect(site_url('mine/cobrancas/'));
    }

    public function processos()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuProcessos'] = 'processos';
        $this->load->library('pagination');
        $this->load->model('processos_model');

        $config['base_url'] = base_url() . 'index.php/mine/processos/';
        $config['total_rows'] = $this->processos_model->countProcessosByCliente($this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        // Aplicar filtros se fornecidos
        $filters = [
            'tipo_processo' => $this->input->get('tipo_processo'),
            'status' => $this->input->get('status'),
            'comarca' => $this->input->get('comarca'),
            'usuarios_id' => $this->input->get('usuarios_id')
        ];
        
        // Remove filtros vazios
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (!empty($filters)) {
            // Usar método com filtros
            $data['results'] = $this->processos_model->getByClienteWithFilters(
                $this->session->userdata('cliente_id'), 
                $filters, 
                $config['per_page'], 
                $this->uri->segment(3)
            );
            // Recalcular total com filtros
            $this->load->model('processos_model');
            $total_com_filtros = count($this->processos_model->getByClienteWithFilters(
                $this->session->userdata('cliente_id'), 
                $filters, 
                0, 
                0
            ));
            $config['total_rows'] = $total_com_filtros;
            $this->pagination->initialize($config);
        } else {
            $data['results'] = $this->processos_model->getProcessosByCliente($this->session->userdata('cliente_id'), $config['per_page'], $this->uri->segment(3));
        }
        
        $data['pagination'] = $this->pagination->create_links();

        // Carregar advogados para filtro (Fase 6 - Correção Bug)
        $this->load->model('usuarios_model');
        $advogados = $this->usuarios_model->get('usuarios', 'idUsuarios, nome', '', 0, 0, false, 'array');
        $data['advogados'] = $advogados ?: [];

        $data['output'] = 'conecte/processos';
        $this->load->view('conecte/template', $data);
    }

    public function visualizarProcesso($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuProcessos'] = 'processos';
        $this->load->model('processos_model');
        $this->load->model('movimentacoes_processuais_model');
        $this->load->model('prazos_model');
        $this->load->model('audiencias_model');

        $processoId = $this->uri->segment(3);
        $data['result'] = $this->processos_model->getById($processoId);

        if (!$data['result'] || $data['result']->clientes_id != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Este processo não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        // Buscar movimentações
        $data['movimentacoes'] = $this->movimentacoes_processuais_model->getByProcesso($processoId);
        
        // Buscar prazos do processo
        $data['prazos'] = $this->prazos_model->getPrazosByProcesso($processoId);
        
        // Buscar audiências do processo
        $data['audiencias'] = $this->audiencias_model->getAudienciasByProcesso($processoId);

        $data['output'] = 'conecte/visualizar_processo';
        $this->load->view('conecte/template', $data);
    }

    public function prazos()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuPrazos'] = 'prazos';
        $this->load->library('pagination');
        $this->load->model('prazos_model');

        $config['base_url'] = base_url() . 'index.php/mine/prazos/';
        $config['total_rows'] = $this->prazos_model->countPrazosByCliente($this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $data['results'] = $this->prazos_model->getPrazosByCliente($this->session->userdata('cliente_id'), $config['per_page'], $this->uri->segment(3));
        $data['pagination'] = $this->pagination->create_links();

        $data['output'] = 'conecte/prazos';
        $this->load->view('conecte/template', $data);
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
        if ($dv1 != $cpf[9]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;

        return $dv2 == $cpf[10];
    }

    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma1 += $cnpj[$i] * $pos;
        }
        $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma2 += $cnpj[$i] * $pos;
        }
        $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);

        return $dv2 == $cnpj[13];
    }

    public function formatarChave($chave)
    {
        if ($this->validarCPF($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        } elseif ($this->validarCNPJ($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        } elseif (strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }

        return $chave;
    }

    public function gerarPagamentoGerencianetBoleto()
    {
        print_r(json_encode(['code' => 4001, 'error' => 'Erro interno', 'errorDescription' => 'Cobrança não pode ser gerada pelo lado do cliente']));

    }

    public function gerarPagamentoGerencianetLink()
    {
        print_r(json_encode(['code' => 4001, 'error' => 'Erro interno', 'errorDescription' => 'Cobrança não pode ser gerada pelo lado do cliente']));

    }


    public function cadastrar()
    {
        $this->load->model('clientes_model', '', true);
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $id = 0;

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } elseif (strtolower($this->input->post('captcha')) != strtolower($this->session->userdata('captchaWord'))) {
            $this->session->set_flashdata('error', 'Os caracteres da imagem não foram preenchidos corretamente!');
        } else {
            $data = [
                'nomeCliente' => set_value('nomeCliente'),
                'documento' => set_value('documento'),
                'telefone' => set_value('telefone'),
                'celular' => $this->input->post('celular'),
                'email' => set_value('email'),
                'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
                'rua' => set_value('rua'),
                'complemento' => set_value('complemento'),
                'numero' => set_value('numero'),
                'bairro' => set_value('bairro'),
                'cidade' => set_value('cidade'),
                'estado' => set_value('estado'),
                'cep' => set_value('cep'),
                'dataCadastro' => date('Y-m-d'),
                'contato' => $this->input->post('contato'),
            ];

            $id = $this->clientes_model->add('clientes', $data);

            if ($id > 0) {
                $this->enviarEmailBoasVindas($id);
                $this->enviarEmailTecnicoNotificaClienteNovo($id);
                $this->session->set_flashdata('success', 'Cadastro realizado com sucesso! <br> Um e-mail de boas vindas será enviado para ' . $data['email']);
                redirect(base_url() . 'index.php/mine');
            } else {
                $this->session->set_flashdata('error', 'Falha ao realizar cadastro!');
            }
        }

        $this->load->view('conecte/cadastrar', $this->data);
    }

    public function downloadanexo($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }
        if ($id != null && is_numeric($id)) {
            $this->db->where('idAnexos', $id);
            $file = $this->db->get('anexos', 1)->row();

            $this->load->library('zip');
            $path = $file->path;
            $this->zip->read_file($path . '/' . $file->anexo);
            $this->zip->download('file' . date('d-m-Y-H.i.s') . '.zip');
        }
    }

    private function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }

    private function check_token($token)
    {
        $this->db->where('token', $token);
        $this->db->limit(1);

        return $this->db->get('resets_de_senha')->row();
    }

    /**
     * Valida se a data de expiração passou (RN 1.3: token válido por 1 hora)
     * 
     * @param string $date Data de expiração
     * @param string $format Formato da data
     * @return bool True se expirou, False se ainda é válida
     */
    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $dateExpiration = new \DateTime($date);
        $dateNow = new \DateTime(date($format));

        // Retorna true se a data de expiração já passou (token inválido)
        return $dateExpiration < $dateNow;
    }

    /**
     * Recuperar email usando CPF/CNPJ
     */
    public function recuperarEmail()
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('documento', 'CPF/CNPJ', 'required|trim');
        
        if ($this->form_validation->run() == false) {
            $this->load->view('conecte/recuperar_email');
            return;
        }
        
        $documento = $this->input->post('documento');
        // Remover formatação do documento
        $documento = preg_replace('/[^0-9]/', '', $documento);
        
        // Buscar cliente por CPF/CNPJ
        $this->db->where('documento', $documento);
        $cliente = $this->db->get('clientes')->row();
        
        if (!$cliente) {
            $this->session->set_flashdata('error', 'CPF/CNPJ não encontrado no sistema.');
            $this->load->view('conecte/recuperar_email');
            return;
        }
        
        // Enviar email com o email cadastrado
        $this->enviarEmailRecuperacao($cliente->idClientes, $cliente->email, $cliente->nomeCliente);
        
        $this->session->set_flashdata('success', 'Um email com seu endereço de email cadastrado foi enviado para ' . $cliente->email);
        redirect(base_url() . 'index.php/mine');
    }

    private function enviarEmailRecuperacao($idClientes, $email, $nome)
    {
        $this->load->model('sistema_model');
        $this->load->model('clientes_model', '', true);
        $this->load->model('email_model');
        
        $dados = [];
        $dados['emitente'] = $this->sistema_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($idClientes);
        $dados['email_cadastrado'] = $email;
        
        $emitente = $dados['emitente'];
        
        if ($emitente == null) {
            log_message('error', 'Emitente não configurado. Não foi possível enviar email de recuperação.');
            return false;
        }
        
        $html = $this->load->view('conecte/emails/recuperar_email', $dados, true);
        
        $assunto = 'Recuperação de Email - ' . $this->config->item('app_name');
        
        $headers = [
            'From' => "\"$emitente->nome\" <$emitente->email>",
            'Subject' => $assunto,
            'Return-Path' => '',
        ];
        
        $email_data = [
            'to' => $email,
            'subject' => $assunto,
            'message' => $html,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($this->email_model->add('email_queue', $email_data)) {
            log_message('info', "Email de recuperação adicionado à fila para: {$email} (Cliente ID: {$idClientes})");
            return true;
        } else {
            log_message('error', "Falha ao adicionar email de recuperação à fila para: {$email}");
            return false;
        }
    }

    private function enviarRecuperarSenha($idClientes, $clienteEmail, $assunto, $token)
    {
        log_message('info', '=== INÍCIO enviarRecuperarSenha ===');
        log_message('info', 'ID Cliente: ' . $idClientes . ', Email: ' . $clienteEmail . ', Assunto: ' . $assunto);
        
        $dados = [];
        $this->load->model('sistema_model');
        $this->load->model('clientes_model', '', true);

        $dados['emitente'] = $this->sistema_model->getEmitente();
        log_message('info', 'Emitente: ' . ($dados['emitente'] ? 'Encontrado' : 'NULL'));
        
        $dados['cliente'] = $this->clientes_model->getById($idClientes);
        log_message('info', 'Cliente carregado: ' . ($dados['cliente'] ? 'Sim' : 'Não'));
        
        $dados['resets_de_senha'] = json_decode($token);
        log_message('info', 'Token decodificado: ' . ($dados['resets_de_senha'] ? 'OK' : 'ERRO'));

        $emitente = $dados['emitente'];
        $remetente = $clienteEmail;

        if ($emitente == null) {
            log_message('error', 'Emitente não configurado!');
            $this->session->set_flashdata(['error' => 'Cadastrar Emitente.\n\n Por favor contate o administrador do sistema.']);
            return false;
        }

        try {
            $html = $this->load->view('conecte/emails/clientenovasenha', $dados, true);
            log_message('info', 'View carregada. Tamanho HTML: ' . strlen($html) . ' bytes');
        } catch (Exception $e) {
            log_message('error', 'Erro ao carregar view: ' . $e->getMessage());
            return false;
        }

        // Enviar email diretamente (usando configurações da aba Email)
        $this->load->library('email');
        
        // Obter configurações do arquivo email.php (que lê do .env configurado na aba Email)
        $this->load->config('email');
        $config = $this->config->item('email');
        $smtp_user = $this->config->item('smtp_user');
        $app_name = $this->config->item('app_name') ?: 'Adv';
        
        // Validar se o remetente está configurado
        if (empty($smtp_user)) {
            log_message('error', 'Erro: E-mail remetente não configurado. EMAIL_SMTP_USER não está definido nas configurações.');
            $this->session->set_flashdata(['error' => 'E-mail remetente não configurado. Configure o EMAIL_SMTP_USER na aba Email das configurações.']);
            return false;
        }
        
        // Inicializar email com as configurações da aba Email (host, porta, criptografia, etc.)
        if ($config) {
            $this->email->initialize($config);
        }
        
        // Configurar remetente e destinatário
        $this->email->clear();
        $this->email->from($smtp_user, $app_name);
        $this->email->to($remetente);
        $this->email->subject($assunto);
        $this->email->message($html);
        
        log_message('info', 'Tentando enviar email de recuperação diretamente para: ' . $remetente . ' (usando remetente: ' . $smtp_user . ')');
        
        // Enviar diretamente (igual ao testarEmail que funciona)
        if ($this->email->send(true)) {
            log_message('info', '✅ Email de recuperação enviado com sucesso para: ' . $remetente);
            
            // Adicionar à fila para registro/auditoria (opcional)
            $this->load->model('email_model');
            $email_data = [
                'to' => $remetente,
                'subject' => $assunto,
                'message' => $html,
                'status' => 'sent',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->email_model->add('email_queue', $email_data);
            
            return true;
        } else {
            $error_msg = $this->email->print_debugger();
            log_message('error', '❌ Erro ao enviar email de recuperação para ' . $remetente . ': ' . $error_msg);
            
            // Adicionar à fila como failed para tentar depois
            $this->load->model('email_model');
            $email_data = [
                'to' => $remetente,
                'subject' => $assunto,
                'message' => $html,
                'status' => 'failed',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->email_model->add('email_queue', $email_data);
            
            return false;
        }
        
        log_message('info', '=== FIM enviarRecuperarSenha ===');
    }


    private function enviarEmailBoasVindas($id)
    {
        $dados = [];
        $this->load->model('sistema_model');
        $this->load->model('clientes_model', '', true);

        $dados['emitente'] = $this->sistema_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($id);

        $emitente = $dados['emitente'];
        $remetente = $dados['cliente'];
        $assunto = 'Bem-vindo!';

        $html = $this->load->view('os/emails/clientenovo', $dados, true);

        $this->load->model('email_model');

        $headers = [
            'From' => "\"$emitente->nome\" <$emitente->email>",
            'Subject' => $assunto,
            'Return-Path' => '',
        ];
        $email = [
            'to' => $remetente->email,
            'subject' => $assunto,
            'message' => $html,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->email_model->add('email_queue', $email);
    }

    private function enviarEmailTecnicoNotificaClienteNovo($id)
    {
        $dados = [];
        $this->load->model('sistema_model');
        $this->load->model('clientes_model', '', true);
        $this->load->model('usuarios_model');

        $dados['emitente'] = $this->sistema_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($id);

        $emitente = $dados['emitente'];
        $assunto = 'Novo Cliente Cadastrado no Sistema';

        $usuarios = [];
        $usuarios = $this->usuarios_model->getAll();

        foreach ($usuarios as $usuario) {
            $dados['usuario'] = $usuario;
            $html = $this->load->view('os/emails/clientenovonotifica', $dados, true);
            $headers = [
                'From' => "\"$emitente->nome\" <$emitente->email>",
                'Subject' => $assunto,
                'Return-Path' => '',
            ];
            $email = [
                'to' => $usuario->email,
                'subject' => $assunto,
                'message' => $html,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->email_model->add('email_queue', $email);
        }
    }

    public function captcha()
    {
        header('Content-type: image/jpeg');

        $arrFont = ['font-ZXX_Noise.otf', 'font-karabine.ttf', 'font-capture.ttf', 'font-captcha.ttf'];
        shuffle($arrFont);

        $codigoCaptcha = substr(md5(time()), 0, 7);
        $img = imagecreatefromjpeg('./assets/img/captcha_bg.jpg');
        $corCaptcha = imagecolorallocate($img, 255, 0, 0);
        $font = './assets/font-awesome/' . $arrFont[0];

        imagettftext($img, 23, 0, 5, rand(30, 35), $corCaptcha, $font, $codigoCaptcha);
        imagepng($img);
        imagedestroy($img);

        $this->session->set_userdata('captchaWord', $codigoCaptcha);
    }
    
}

/* End of file conecte.php */
/* Location: ./application/controllers/conecte.php */
