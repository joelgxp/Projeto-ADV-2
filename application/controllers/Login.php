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
        $this->load->view('adv/login');
    }

    public function sair()
    {
        $this->session->sess_destroy();

        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function verificarLogin()
    {
        header('Access-Control-Allow-Origin: ' . base_url());
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email|required|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|trim');
        if ($this->form_validation->run() == false) {
            $json = ['result' => false, 'message' => validation_errors()];
            echo json_encode($json);
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('senha');
            $this->load->model('Sistema_model');
            $user = $this->Sistema_model->check_credentials($email);

            if ($user) {
                // Verificar se acesso está expirado (se a coluna existir)
                if (isset($user->dataExpiracao) && $this->chk_date($user->dataExpiracao)) {
                    $json = ['result' => false, 'message' => 'A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.'];
                    echo json_encode($json);
                    exit();
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
                    $this->session->set_userdata($session_admin_data);
                    log_info('Efetuou login no sistema');
                    $json = [
                        'result' => true,
                        'message' => 'Login realizado com sucesso!',
                        'ADV_TOKEN' => $this->security->get_csrf_hash()
                    ];
                    echo json_encode($json);
                } else {
                    $json = [
                        'result' => false, 
                        'message' => 'Os dados de acesso estão incorretos.', 
                        'ADV_TOKEN' => $this->security->get_csrf_hash()
                    ];
                    echo json_encode($json);
                }
            } else {
                $json = [
                    'result' => false, 
                    'message' => 'Usuário não encontrado, verifique se suas credenciais estão corretas.', 
                    'ADV_TOKEN' => $this->security->get_csrf_hash()
                ];
                echo json_encode($json);
            }
        }
        exit();
    }

    private function chk_date($data_banco)
    {
        $data_banco = new DateTime($data_banco);
        $data_hoje = new DateTime('now');

        return $data_banco < $data_hoje;
    }
}
