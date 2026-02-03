<?php

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sistema_model');
    }

    public function index()
    {
        $data['emitente'] = $this->sistema_model->getEmitente();
        $this->load->view('adv/login', $data);
    }

    public function sair()
    {
        $this->session->sess_destroy();

        return redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Exibe formulário para o usuário criar sua senha (via link do e-mail)
     */
    public function definir_senha()
    {
        $token = $this->input->get('t');
        if (!$token) {
            $this->session->set_flashdata('error', 'Link inválido. Solicite um novo e-mail ao administrador.');
            redirect('login');
            return;
        }
        $token = trim(preg_replace('/[^a-f0-9]/i', '', $token));
        if (strlen($token) !== 64) {
            $this->session->set_flashdata('error', 'Link inválido ou expirado. Solicite um novo e-mail ao administrador.');
            redirect('login');
            return;
        }
        $this->load->model('Confirmacoes_email_model');
        $confirmacao = $this->Confirmacoes_email_model->getTokenValidoSemMarcar($token);
        if (!$confirmacao) {
            $this->session->set_flashdata('error', 'Link inválido ou expirado. Solicite um novo e-mail ao administrador.');
            redirect('login');
            return;
        }
        $data = [
            'token' => $token,
            'error' => $this->session->flashdata('error'),
            'emitente' => $this->sistema_model->getEmitente()
        ];
        $this->load->view('adv/definir_senha', $data);
    }

    /**
     * Processa o formulário de criação de senha
     */
    public function definir_senha_salvar()
    {
        $token = $this->input->post('t');
        if (!$token) {
            $this->session->set_flashdata('error', 'Link inválido.');
            redirect('login');
            return;
        }
        $token = trim(preg_replace('/[^a-f0-9]/i', '', $token));
        $senha = $this->input->post('senha');
        $confirmar = $this->input->post('confirmar_senha');

        if (strlen($senha) < 8) {
            $this->session->set_flashdata('error', 'A senha deve ter no mínimo 8 caracteres.');
            redirect('definir-senha?t=' . $token);
            return;
        }
        if ($senha !== $confirmar) {
            $this->session->set_flashdata('error', 'As senhas não coincidem.');
            redirect('definir-senha?t=' . $token);
            return;
        }

        $this->load->helper('password');
        $validacao = validar_senha_forte($senha);
        if (!$validacao['valido']) {
            $this->session->set_flashdata('error', implode(' ', $validacao['erros']));
            redirect('definir-senha?t=' . $token);
            return;
        }

        $this->load->model('Confirmacoes_email_model');
        $usuario = $this->Confirmacoes_email_model->validarToken($token);
        if (!$usuario) {
            $this->session->set_flashdata('error', 'Link inválido ou expirado. Solicite um novo e-mail ao administrador.');
            redirect('login');
            return;
        }

        $this->load->model('usuarios_model');
        $this->usuarios_model->edit('usuarios', [
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'email_confirmado' => 1
        ], 'idUsuarios', $usuario->idUsuarios);

        $this->session->set_flashdata('success', 'Senha criada com sucesso! Faça login para acessar o sistema.');
        redirect('login');
    }

    public function verificarLogin()
    {
        // Tratar requisição OPTIONS (preflight) - PRIMEIRO
        if ($this->input->method() === 'options' || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS')) {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
            // Em desenvolvimento, permitir qualquer origem; em produção, validar
            if (ENVIRONMENT !== 'production') {
                header('Access-Control-Allow-Origin: *');
            } else {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, ' . $this->security->get_csrf_token_name());
            header('Access-Control-Max-Age: 3600');
            http_response_code(200);
            exit(0);
        }
        
        // Configurar headers CORS - permitir origem específica em produção
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allowed_origins = [
            base_url(),
            rtrim(base_url(), '/'),
            'https://adv.joelsouza.com.br',
            'https://www.adv.joelsouza.com.br'
        ];
        
        // Verificar se a origem está permitida ou permitir todas em desenvolvimento
        if (ENVIRONMENT !== 'production') {
            header('Access-Control-Allow-Origin: *');
        } else {
            // Em produção, verificar se a origem está na lista de permitidas
            if (in_array($origin, $allowed_origins) || empty($origin)) {
                header('Access-Control-Allow-Origin: ' . ($origin ?: base_url()));
            } else {
                // Se não estiver na lista, usar a origem da requisição se for do mesmo domínio
                $base_host = parse_url(base_url(), PHP_URL_HOST);
                $origin_host = parse_url($origin, PHP_URL_HOST);
                if ($base_host === $origin_host) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                } else {
                    header('Access-Control-Allow-Origin: ' . base_url());
                }
            }
        }
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, ' . $this->security->get_csrf_token_name());
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 3600');
        
        // Verifica se é requisição AJAX pelo header ou parâmetro
        $is_ajax = $this->input->is_ajax_request() || 
                   (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                   $this->input->get('ajax') === '1';
        
        // Se não for requisição AJAX, redireciona para a página de login
        if (!$is_ajax) {
            redirect('login');
            return;
        }

        header('Content-Type: application/json');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email|required|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|trim');
        if ($this->form_validation->run() == false) {
            $json = ['result' => false, 'message' => validation_errors()];
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($json));
            return;
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('senha');
            $ip_address = $this->input->ip_address();
            $user_agent = $this->input->user_agent();
            
            // Carregar models
            $this->load->model('Sistema_model');
            $this->load->model('Tentativas_login_model');
            $this->load->model('Bloqueios_conta_model');
            
            // Buscar usuário primeiro para verificar se é admin
            $user = $this->Sistema_model->check_credentials($email);
            
            // Verificar se é usuário administrador (exceção: não bloqueia)
            $is_admin = false;
            if ($user) {
                $nivel_usuario = isset($user->nivel) ? strtolower($user->nivel) : null;
                $user_permissao = isset($user->permissoes_id) ? $user->permissoes_id : null;
                
                // Verificar se é admin pelo nível
                if ($nivel_usuario === 'admin') {
                    $is_admin = true;
                }
                
                // Verificar se é admin pela permissão
                if (!$is_admin && $user_permissao) {
                    // Se for string "admin" ou "administrador"
                    if (is_string($user_permissao) && (strtolower($user_permissao) === 'admin' || strtolower($user_permissao) === 'administrador')) {
                        $is_admin = true;
                    }
                    
                    // Se for numérico, verificar na tabela de permissões
                    if (!$is_admin && is_numeric($user_permissao) && $this->db->table_exists('permissoes')) {
                        $this->db->select('nome');
                        $this->db->where('idPermissao', $user_permissao);
                        $this->db->limit(1);
                        $perm = $this->db->get('permissoes')->row();
                        if ($perm && isset($perm->nome)) {
                            $permissao_nome = strtolower($perm->nome);
                            if (in_array($permissao_nome, ['admin', 'administrador'])) {
                                $is_admin = true;
                            }
                        }
                    }
                }
            }
            
            // Verificar se a conta está bloqueada (RN 1.4) - EXCETO se for admin
            if (!$is_admin) {
                $bloqueio = $this->Bloqueios_conta_model->verificarBloqueio($email);
                if ($bloqueio) {
                    $minutos_restantes = ceil((strtotime($bloqueio->bloqueado_ate) - time()) / 60);
                    $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, false);
                    
                    $json = [
                        'result' => false, 
                        'message' => "Conta bloqueada devido a múltiplas tentativas de login falhadas. Tente novamente em {$minutos_restantes} minuto(s).",
                        'ADV_TOKEN' => $this->security->get_csrf_hash()
                    ];
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($json));
                    return;
                }
            }

            if ($user) {
                // Verificar se acesso está expirado (se a coluna existir)
                if (isset($user->dataExpiracao) && $this->chk_date($user->dataExpiracao)) {
                    $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, false);
                    $json = ['result' => false, 'message' => 'A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.'];
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($json));
                    return;
                }
                
                // Verificar se e-mail foi confirmado (RN 1.1)
                if (isset($user->email_confirmado) && $user->email_confirmado == 0) {
                    $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, false);
                    $json = [
                        'result' => false, 
                        'message' => 'Sua senha ainda não foi criada. Verifique sua caixa de entrada e clique no link recebido por e-mail para criar sua senha de acesso.',
                        'ADV_TOKEN' => $this->security->get_csrf_hash()
                    ];
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($json));
                    return;
                }

                // Verificar credenciais do usuário
                if (password_verify($password, $user->senha)) {
                    // Adaptar campos baseado na estrutura da tabela
                    $user_email = isset($user->email) ? $user->email : (isset($user->usuario) ? $user->usuario : '');
                    $user_id = isset($user->idUsuarios) ? $user->idUsuarios : (isset($user->id) ? $user->id : 0);
                    $nivel_usuario = isset($user->nivel) ? strtolower($user->nivel) : null;
                    $user_permissao = isset($user->permissoes_id) ? $user->permissoes_id : (isset($user->nivel) ? $user->nivel : 1);

                    // Detecta se perfil vinculado é administrador pelo nome na tabela permissoes
                    $permissao_nome = null;
                    if (isset($user->permissoes_id) && $user->permissoes_id && $this->db->table_exists('permissoes')) {
                        $this->db->select('nome');
                        $this->db->where('idPermissao', $user->permissoes_id);
                        $this->db->limit(1);
                        $perm = $this->db->get('permissoes')->row();
                        if ($perm && isset($perm->nome)) {
                            $permissao_nome = strtolower($perm->nome);
                        }
                    }

                    if (($nivel_usuario === 'admin') || ($permissao_nome && in_array($permissao_nome, ['admin', 'administrador']))) {
                        $user_permissao = 'admin';
                        $nivel_usuario = 'admin';
                    }
                    $user_image = isset($user->url_image_user) ? $user->url_image_user : '';
                    
                    $session_admin_data = [
                        'nome_admin' => $user->nome,
                        'email_admin' => $user_email,
                        'url_image_user_admin' => $user_image,
                        'id_admin' => $user_id,
                        'permissao' => $user_permissao,
                        'logado' => true,
                        'nivel_admin' => $nivel_usuario
                    ];
                    // Login bem-sucedido - registrar tentativa e limpar bloqueios
                    $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, true);
                    $this->Bloqueios_conta_model->desbloquear($email);
                    
                    $this->session->set_userdata($session_admin_data);
                    log_info('Efetuou login no sistema');
                    $json = [
                        'result' => true,
                        'message' => 'Login realizado com sucesso!',
                        'ADV_TOKEN' => $this->security->get_csrf_hash()
                    ];
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($json));
                    return;
                } else {
                    // Senha incorreta - registrar tentativa falhada
                    $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, false);
                    
                    // Verificar se é admin (não bloqueia admin)
                    // Reutilizar a variável $is_admin já definida acima
                    
                    if (!$is_admin) {
                        // Contar tentativas falhadas (RN 1.4) - apenas para não-admins
                        $tentativas_falhadas = $this->Tentativas_login_model->contarFalhas($email, $ip_address);
                        
                        // Bloquear após 5 tentativas (RN 1.4)
                        if ($tentativas_falhadas >= 5) {
                            $this->Bloqueios_conta_model->bloquear($email, $ip_address, $tentativas_falhadas);
                            
                            $json = [
                                'result' => false, 
                                'message' => 'Muitas tentativas de login falhadas. Sua conta foi bloqueada por 15 minutos. Tente novamente mais tarde.',
                                'ADV_TOKEN' => $this->security->get_csrf_hash()
                            ];
                        } else {
                            $tentativas_restantes = 5 - $tentativas_falhadas;
                            $json = [
                                'result' => false, 
                                'message' => "Os dados de acesso estão incorretos. Você tem {$tentativas_restantes} tentativa(s) restante(s).",
                                'ADV_TOKEN' => $this->security->get_csrf_hash()
                            ];
                        }
                    } else {
                        // Admin: não bloqueia, apenas informa erro
                        $json = [
                            'result' => false, 
                            'message' => 'Os dados de acesso estão incorretos. Verifique seu e-mail e senha.',
                            'ADV_TOKEN' => $this->security->get_csrf_hash()
                        ];
                    }
                    
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($json));
                    return;
                }
            } else {
                // Usuário não encontrado - registrar tentativa falhada
                // Nota: Não contamos tentativas para usuários inexistentes (segurança)
                // Mas ainda registramos para auditoria
                $this->Tentativas_login_model->registrar($email, $ip_address, $user_agent, false);
                
                // Mensagem genérica para não revelar se o usuário existe (segurança)
                $json = [
                    'result' => false, 
                    'message' => 'Os dados de acesso estão incorretos. Verifique seu e-mail e senha.',
                    'ADV_TOKEN' => $this->security->get_csrf_hash()
                ];
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($json));
                return;
            }
        }
    }

    private function chk_date($data_banco)
    {
        $data_banco = new DateTime($data_banco);
        $data_hoje = new DateTime('now');

        return $data_banco < $data_hoje;
    }
}
