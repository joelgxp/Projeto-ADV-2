<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
class Adv extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sistema_model');
    }

    public function index()
    {
        $this->load->model('processos_model');
        
        // Dados de processos jurídicos
        $this->data['processos_em_andamento'] = $this->sistema_model->getProcessosByStatus('em_andamento') ?: [];
        $this->data['prazos_vencidos'] = $this->sistema_model->getPrazosVencidos() ?: [];
        $this->data['audiencias_agendadas'] = $this->sistema_model->getAudienciasAgendadas() ?: [];
        
        // Lançamentos financeiros (honorários)
        $this->data['lancamentos'] = $this->sistema_model->getLancamentos() ?: [];
        
        // Estatísticas financeiras
        $this->data['estatisticas_financeiro'] = $this->sistema_model->getEstatisticasFinanceiro() ?: (object)[];
        
        $year = $this->input->get('year') ?: date('Y');
        $this->data['financeiro_mes_dia'] = $this->sistema_model->getEstatisticasFinanceiroDia($year) ?: (object)[];
        $this->data['financeiro_mes'] = $this->sistema_model->getEstatisticasFinanceiroMes($year) ?: (object)[];
        $this->data['financeiro_mesinadipl'] = $this->sistema_model->getEstatisticasFinanceiroMesInadimplencia($year) ?: (object)[];
        
        $this->data['menuPainel'] = 'Painel';
        $this->data['view'] = 'adv/painel';

        return $this->layout();
    }

    public function minhaConta()
    {
        $this->data['usuario'] = $this->sistema_model->getById($this->session->userdata('id_admin'));
        $this->data['view'] = 'adv/minhaConta';

        return $this->layout();
    }

    public function alterarSenha()
    {
        $current_user = $this->sistema_model->getById($this->session->userdata('id_admin'));

        if (!$current_user) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao pesquisar usuário!');
            redirect(site_url('adv/minhaConta'));
        }

        $oldSenha = $this->input->post('oldSenha');
        $senha = $this->input->post('novaSenha');

        if (!password_verify($oldSenha, $current_user->senha)) {
            $this->session->set_flashdata('error', 'A senha atual não corresponde com a senha informada.');
            redirect(site_url('adv/minhaConta'));
        }

        $result = $this->sistema_model->alterarSenha($senha);

        if ($result) {
            $this->session->set_flashdata('success', 'Senha alterada com sucesso!');
            redirect(site_url('adv/minhaConta'));
        }

        $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar a senha!');
        redirect(site_url('adv/minhaConta'));
    }

    public function pesquisar()
    {
        $termo = $this->input->get('termo');

        $data['results'] = $this->sistema_model->pesquisar($termo);
        $this->data['processos'] = $data['results']['processos'] ?? [];
        $this->data['servicos'] = $data['results']['servicos'] ?? [];
        $this->data['prazos'] = $data['results']['prazos'] ?? [];
        $this->data['audiencias'] = $data['results']['audiencias'] ?? [];
        $this->data['clientes'] = $data['results']['clientes'] ?? [];
        $this->data['view'] = 'adv/pesquisa';

        return $this->layout();
    }

    public function backup()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cBackup')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para efetuar backup.');
            redirect(base_url());
        }

        $this->load->dbutil();
        $prefs = [
            'format' => 'zip',
            'foreign_key_checks' => false,
            'filename' => 'backup' . date('d-m-Y') . '.sql',
        ];

        $backup = $this->dbutil->backup($prefs);

        $this->load->helper('file');
        write_file(base_url() . 'backup/backup.zip', $backup);

        log_info('Efetuou backup do banco de dados.');

        $this->load->helper('download');
        force_download('backup' . date('d-m-Y H:m:s') . '.zip', $backup);
    }

    public function emitente()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->data['menuConfiguracoes'] = 'Configuracoes';
        $this->data['dados'] = $this->sistema_model->getEmitente();
        $this->data['view'] = 'adv/emitente';

        return $this->layout();
    }

    public function do_upload()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('upload');

        $image_upload_folder = FCPATH . 'assets/uploads';

        if (!file_exists($image_upload_folder)) {
            mkdir($image_upload_folder, DIR_WRITE_MODE, true);
        }

        $this->upload_config = [
            'upload_path' => $image_upload_folder,
            'allowed_types' => 'png|jpg|jpeg|bmp|svg',
            'max_size' => 2048,
            'remove_space' => true,
            'encrypt_name' => true,
        ];

        $this->upload->initialize($this->upload_config);

        if (!$this->upload->do_upload()) {
            $upload_error = $this->upload->display_errors();
            log_message('error', 'Erro no upload: ' . $upload_error);
            $this->session->set_flashdata('error', 'Erro ao fazer upload: ' . $upload_error);
            return false;
        } else {
            $file_info = [$this->upload->data()];

            return $file_info[0]['file_name'];
        }
    }

    public function do_upload_user()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('upload');

        $image_upload_folder = FCPATH . 'assets/userImage/';

        if (!file_exists($image_upload_folder)) {
            mkdir($image_upload_folder, DIR_WRITE_MODE, true);
        }

        $this->upload_config = [
            'upload_path' => $image_upload_folder,
            'allowed_types' => 'png|jpg|jpeg|bmp',
            'max_size' => 2048,
            'remove_space' => true,
            'encrypt_name' => true,
        ];

        $this->upload->initialize($this->upload_config);

        if (!$this->upload->do_upload()) {
            $upload_error = $this->upload->display_errors();
            log_message('error', 'Erro no upload: ' . $upload_error);
            $this->session->set_flashdata('error', 'Erro ao fazer upload: ' . $upload_error);
            return false;
        } else {
            $file_info = [$this->upload->data()];

            return $file_info[0]['file_name'];
        }
    }

    public function cadastrarEmitente()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Razão Social', 'required|trim');
        $this->form_validation->set_rules('cnpj', 'CNPJ', 'required|trim');
        $this->form_validation->set_rules('ie', 'IE', 'trim');
        $this->form_validation->set_rules('cep', 'CEP', 'required|trim');
        $this->form_validation->set_rules('logradouro', 'Logradouro', 'required|trim');
        $this->form_validation->set_rules('numero', 'Número', 'required|trim');
        $this->form_validation->set_rules('bairro', 'Bairro', 'required|trim');
        $this->form_validation->set_rules('cidade', 'Cidade', 'required|trim');
        $this->form_validation->set_rules('uf', 'UF', 'required|trim');
        $this->form_validation->set_rules('telefone', 'Telefone', 'required|trim');
        $this->form_validation->set_rules('celular', 'Celular', 'trim');
        $this->form_validation->set_rules('email', 'E-mail', 'required|trim');
        $this->form_validation->set_rules('site', 'Site', 'trim');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', 'Campos obrigatórios não foram preenchidos.');
            redirect(site_url('adv/emitente'));
        } else {
            $nome = $this->input->post('nome');
            $cnpj = $this->input->post('cnpj');
            $cep = $this->input->post('cep');
            $logradouro = $this->input->post('logradouro');
            $numero = $this->input->post('numero');
            $bairro = $this->input->post('bairro');
            $cidade = $this->input->post('cidade');
            $uf = $this->input->post('uf');
            $telefone = $this->input->post('telefone');
            $celular = $this->input->post('celular');
            $email = $this->input->post('email');
            $site = $this->input->post('site');
            $image = $this->do_upload();
            $logo = base_url() . 'assets/uploads/' . $image;

            $retorno = $this->sistema_model->addEmitente($nome, $cnpj, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $email, $site, $logo);
            if ($retorno) {
                $this->session->set_flashdata('success', 'As informações foram inseridas com sucesso.');
                log_info('Adicionou informações de emitente.');
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar inserir as informações.');
            }
            redirect(site_url('adv/emitente'));
        }
    }

    public function editarEmitente()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('nome', 'Nome do Escritório', 'required|trim');
        $this->form_validation->set_rules('cnpj', 'CNPJ', 'trim');
        $this->form_validation->set_rules('cep', 'CEP', 'required|trim');
        $this->form_validation->set_rules('logradouro', 'Logradouro', 'required|trim');
        $this->form_validation->set_rules('numero', 'Número', 'required|trim');
        $this->form_validation->set_rules('bairro', 'Bairro', 'required|trim');
        $this->form_validation->set_rules('cidade', 'Cidade', 'required|trim');
        $this->form_validation->set_rules('uf', 'UF', 'required|trim');
        $this->form_validation->set_rules('telefone', 'Telefone', 'required|trim');
        $this->form_validation->set_rules('celular', 'Celular', 'trim');
        $this->form_validation->set_rules('email', 'E-mail', 'required|trim');
        $this->form_validation->set_rules('site', 'Site', 'trim');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', 'Campos obrigatórios não foram preenchidos.');
            redirect(site_url('adv/emitente'));
        } else {
            $nome = $this->input->post('nome');
            $cnpj = $this->input->post('cnpj');
            $cep = $this->input->post('cep');
            $logradouro = $this->input->post('logradouro');
            $numero = $this->input->post('numero');
            $bairro = $this->input->post('bairro');
            $cidade = $this->input->post('cidade');
            $uf = $this->input->post('uf');
            $telefone = $this->input->post('telefone');
            $celular = $this->input->post('celular');
            $email = $this->input->post('email');
            $site = $this->input->post('site');
            $id = $this->input->post('id');

            $retorno = $this->sistema_model->editEmitente($id, $nome, $cnpj, $cep, $logradouro, $numero, $bairro, $cidade, $uf, $telefone, $celular, $email, $site);
            if ($retorno) {
                $this->session->set_flashdata('success', 'As informações foram alteradas com sucesso.');
                log_info('Alterou informações de emitente.');
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar as informações.');
            }
            redirect(site_url('adv/emitente'));
        }
    }

    public function editarLogo()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar emitente.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar a logomarca.');
            redirect(site_url('adv/emitente'));
        }
        $this->load->helper('file');
        delete_files(FCPATH . 'assets/uploads/');

        $image = $this->do_upload();
        $logo = base_url() . 'assets/uploads/' . $image;

        $retorno = $this->sistema_model->editLogo($id, $logo);
        if ($retorno) {
            $this->session->set_flashdata('success', 'As informações foram alteradas com sucesso.');
            log_info('Alterou a logomarca do emitente.');
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar as informações.');
        }
        redirect(site_url('adv/emitente'));
    }

    public function uploadUserImage()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para mudar a foto.');
            redirect(base_url());
        }

        $id = $this->session->userdata('id_admin');
        if ($id == null || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar sua foto.');
            redirect(site_url('adv/minhaConta'));
        }

        $usuario = $this->sistema_model->getById($id);

        if (is_file(FCPATH . 'assets/userImage/' . $usuario->url_image_user)) {
            unlink(FCPATH . 'assets/userImage/' . $usuario->url_image_user);
        }

        $image = $this->do_upload_user();
        $imageUserPath = $image;
        $retorno = $this->sistema_model->editImageUser($id, $imageUserPath);

        if ($retorno) {
            $this->session->set_userdata('url_image_user', $imageUserPath);
            $this->session->set_flashdata('success', 'Foto alterada com sucesso.');
            log_info('Alterou a Imagem do Usuario.');
        } else {
            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar alterar sua foto.');
        }
        redirect(site_url('adv/minhaConta'));
    }

    public function emails()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar fila de e-mails');
            redirect(base_url());
        }

        $this->data['menuConfiguracoes'] = 'Email';

        $this->load->library('pagination');
        $this->load->model('email_model');

        $this->data['configuration']['base_url'] = site_url('adv/emails/');
        $this->data['configuration']['total_rows'] = $this->email_model->count('email_queue');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->email_model->get('email_queue', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'emails/emails';

        return $this->layout();
    }

    public function excluirEmail()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir e-mail da fila.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir e-mail da fila.');
            redirect(site_url('adv/emails/'));
        }

        $this->load->model('email_model');
        $this->email_model->delete('email_queue', 'id', $id);

        log_info('Removeu um e-mail da fila de envio. ID: ' . $id);

        $this->session->set_flashdata('success', 'E-mail removido da fila de envio!');
        redirect(site_url('adv/emails/'));
    }

    public function configurar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }
        $this->data['menuConfiguracoes'] = 'Sistema';

        $this->load->library('form_validation');
        $this->load->model('sistema_model');

        $this->data['custom_error'] = '';

        $this->form_validation->set_rules('app_name', 'Nome do Sistema', 'required|trim');
        $this->form_validation->set_rules('per_page', 'Registros por página', 'required|numeric|trim');
        $this->form_validation->set_rules('app_theme', 'Tema do Sistema', 'required|trim');
        // Campos opcionais - só validar se foram enviados
        if ($this->input->post('email_automatico') !== false) {
            $this->form_validation->set_rules('email_automatico', 'Enviar Email Automático', 'trim');
        }
        if ($this->input->post('notifica_whats') !== false) {
            $this->form_validation->set_rules('notifica_whats', 'Notificação Whatsapp', 'trim');
        }

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="alert">' . validation_errors() . '</div>' : false);
        } else {
            // Edição do .env
            $dataDotEnv = [
                'EMAIL_PROTOCOL' => $this->input->post('EMAIL_PROTOCOL'),
                'EMAIL_SMTP_HOST' => $this->input->post('EMAIL_SMTP_HOST'),
                'EMAIL_SMTP_CRYPTO' => $this->input->post('EMAIL_SMTP_CRYPTO'),
                'EMAIL_SMTP_PORT' => $this->input->post('EMAIL_SMTP_PORT'),
                'EMAIL_SMTP_USER' => $this->input->post('EMAIL_SMTP_USER'),
                'EMAIL_SMTP_PASS' => $this->input->post('EMAIL_SMTP_PASS'),
            ];

            if (!$this->editDontEnv($dataDotEnv)) {
                $this->data['custom_error'] = '<div class="alert">Falha ao editar o .env</div>';
            }
            // FIM Edição do .env

            $data = [
                'app_name' => $this->input->post('app_name'),
                'per_page' => $this->input->post('per_page'),
                'app_theme' => $this->input->post('app_theme'),
            ];
            
            // Campos opcionais - só adiciona se foram enviados
            if ($this->input->post('email_automatico') !== false && $this->input->post('email_automatico') !== null) {
                $data['email_automatico'] = $this->input->post('email_automatico');
            }
            if ($this->input->post('notifica_whats') !== false && $this->input->post('notifica_whats') !== null) {
                $data['notifica_whats'] = $this->input->post('notifica_whats');
            }
            if ($this->input->post('processo_notification') !== false && $this->input->post('processo_notification') !== null) {
                $data['processo_notification'] = $this->input->post('processo_notification');
            }
            if ($this->input->post('prazo_notification') !== false && $this->input->post('prazo_notification') !== null) {
                $data['prazo_notification'] = $this->input->post('prazo_notification');
            }
            if ($this->input->post('audiencia_notification') !== false && $this->input->post('audiencia_notification') !== null) {
                $data['audiencia_notification'] = $this->input->post('audiencia_notification');
            }
            if ($this->sistema_model->saveConfiguracao($data) == true) {
                $this->session->set_flashdata('success', 'Configurações do sistema atualizadas com sucesso!');
                redirect(site_url('adv/configurar'));
            } else {
                $this->data['custom_error'] = '<div class="alert">Ocorreu um errro.</div>';
            }
        }

        $this->data['view'] = 'adv/configurar';

        return $this->layout();
    }

    public function atualizarBanco()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }

        try {
            // Verificar se tabela migrations existe, se não, criar
            if (!$this->db->table_exists('migrations')) {
                $this->db->query("CREATE TABLE IF NOT EXISTS migrations (version BIGINT(20) NOT NULL)");
                log_message('info', 'Tabela migrations criada automaticamente');
            }

            // Verificar se diretório de migrations existe
            $migrationPath = APPPATH . 'database/migrations/';
            if (!is_dir($migrationPath)) {
                $this->session->set_flashdata('error', 'Diretório de migrations não encontrado: ' . $migrationPath);
                log_message('error', 'Diretório de migrations não encontrado: ' . $migrationPath);
                redirect(site_url('adv/configurar'));
            }

            // Verificar permissões do diretório
            if (!is_readable($migrationPath)) {
                $this->session->set_flashdata('error', 'Sem permissão de leitura no diretório de migrations');
                log_message('error', 'Sem permissão de leitura: ' . $migrationPath);
                redirect(site_url('adv/configurar'));
            }

            $this->load->library('migration');

            // Executar migrations com tratamento de erro detalhado
            $result = $this->migration->latest();

            if ($result === false) {
                $error = $this->migration->error_string();
                
                // Log detalhado do erro
                log_message('error', 'Erro ao executar migrations: ' . $error);
                
                // Verificar erro do banco também
                $db_error = $this->db->error();
                if (!empty($db_error['message'])) {
                    log_message('error', 'Erro do banco: ' . $db_error['message']);
                    $error .= ' | Erro do banco: ' . $db_error['message'];
                }

                // Mensagem mais amigável
                $errorMessage = 'Erro ao atualizar banco de dados. ';
                $errorMessage .= 'Verifique os logs em application/logs/ para mais detalhes. ';
                $errorMessage .= 'Erro: ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
                
                $this->session->set_flashdata('error', $errorMessage);
            } else {
                $this->session->set_flashdata('success', 'Banco de dados atualizado com sucesso!');
                log_message('info', 'Migrations executadas com sucesso');
            }

        } catch (Exception $e) {
            $errorMsg = 'Exceção ao executar migrations: ' . $e->getMessage();
            log_message('error', $errorMsg);
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->session->set_flashdata('error', 'Erro inesperado: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '. Verifique os logs.');
        } catch (Error $e) {
            $errorMsg = 'Erro fatal ao executar migrations: ' . $e->getMessage();
            log_message('error', $errorMsg);
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->session->set_flashdata('error', 'Erro fatal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '. Verifique os logs.');
        }

        return redirect(site_url('adv/configurar'));
    }

    public function atualizarAdv()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'cSistema')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para configurar o sistema');
            redirect(base_url());
        }

        $this->load->library('github_updater');

        if (!$this->github_updater->has_update()) {
            $this->session->set_flashdata('success', 'Seu Adv já está atualizado!');

            return redirect(site_url('adv/configurar'));
        }

        $success = $this->github_updater->update();

        if ($success) {
            $this->session->set_flashdata('success', 'Adv atualizado com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao atualizar Adv!');
        }

        return redirect(site_url('adv/configurar'));
    }

    public function calendario()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia') && 
            !$this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(403)
                ->set_output(json_encode(['error' => 'Você não tem permissão para visualizar calendário.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        
        try {
            $this->load->model('audiencias_model');
            $this->load->model('prazos_model');
            
            $tipoEvento = $this->input->get('tipoEvento') ?: null;
            $start = $this->input->get('start') ?: null;
            $end = $this->input->get('end') ?: null;
            
            // Log para debug (remover em produção se necessário)
            log_message('debug', 'Calendário - tipoEvento: ' . $tipoEvento . ', start: ' . $start . ', end: ' . $end);

            $events = [];

            // Buscar audiências se tipoEvento for 'audiencia' ou vazio
            if ($tipoEvento == 'audiencia' || $tipoEvento == '') {
                try {
                    $allAudiencias = $this->sistema_model->calendario($start, $end, null);
                    
                    if ($allAudiencias === false || !is_array($allAudiencias)) {
                        log_message('error', 'Erro ao buscar audiências no calendário - retorno inválido: ' . gettype($allAudiencias));
                        $allAudiencias = [];
                    }
                } catch (Exception $e) {
                    log_message('error', 'Exceção ao buscar audiências: ' . $e->getMessage());
                    $allAudiencias = [];
                } catch (Error $e) {
                    log_message('error', 'Erro fatal ao buscar audiências: ' . $e->getMessage());
                    $allAudiencias = [];
                }
                
                foreach ($allAudiencias as $audiencia) {
                    // Validar data/hora da audiência
                    if (empty($audiencia->dataHora)) {
                        continue; // Pular se não houver data/hora
                    }
                    
                    // Garantir que a data/hora está no formato correto
                    $dataHora = date('Y-m-d H:i:s', strtotime($audiencia->dataHora));
                    if ($dataHora === false || $dataHora === '1970-01-01 00:00:00') {
                        log_message('error', 'Data/hora inválida para audiência ID: ' . ($audiencia->idAudiencias ?? 'N/A'));
                        continue;
                    }
                    
                    switch ($audiencia->status) {
                        case 'agendada':
                            $cor = '#436eee';
                            break;
                        case 'realizada':
                            $cor = '#256';
                            break;
                        case 'cancelada':
                            $cor = '#CD0000';
                            break;
                        default:
                            $cor = '#E0E4CC';
                            break;
                    }

                    $events[] = [
                        'title' => "Audiência: " . ($audiencia->tipo ?? 'N/A'),
                        'start' => $dataHora,
                        'end' => date('Y-m-d H:i:s', strtotime($dataHora . ' +1 hour')),
                        'color' => $cor,
                        'extendedProps' => [
                            'id' => $audiencia->idAudiencias ?? null,
                            'tipo' => '<b>Tipo:</b> ' . ($audiencia->tipo ?? 'N/A'),
                            'processo' => '<b>Processo:</b> ' . ($audiencia->numeroProcesso ?? 'N/A'),
                            'dataHora' => '<b>Data/Hora:</b> ' . ($dataHora ? date('d/m/Y H:i', strtotime($dataHora)) : 'N/A'),
                            'local' => '<b>Local:</b> ' . ($audiencia->local ?? 'N/A'),
                            'status' => '<b>Status:</b> ' . ucfirst($audiencia->status ?? 'N/A'),
                            'observacoes' => '<b>Observações:</b> ' . strip_tags(html_entity_decode($audiencia->observacoes ?? '')),
                            'editar' => $this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia'),
                        ],
                    ];
                }
            }

            // Buscar prazos se tipoEvento for 'prazo' ou vazio
            if ($tipoEvento == 'prazo' || $tipoEvento == '') {
                if ($this->db->table_exists('prazos')) {
                    $selects = ['prazos.*', 'processos.numeroProcesso', 'processos.classe'];
                    
                    $this->db->select(implode(', ', $selects));
                    $this->db->from('prazos');
                    $this->db->join('processos', 'processos.idProcessos = prazos.processos_id', 'left');
                    
                    // Limpar timezone das datas se presente
                    $start_clean = $start ? preg_replace('/T.*$/', '', $start) : null;
                    $end_clean = $end ? preg_replace('/T.*$/', '', $end) : null;
                    
                    if ($start_clean) {
                        $this->db->where('DATE(prazos.dataVencimento) >=', $start_clean);
                    }
                    if ($end_clean) {
                        $this->db->where('DATE(prazos.dataVencimento) <=', $end_clean);
                    }
                    
                    $this->db->where('prazos.status', 'pendente');
                    $this->db->order_by('prazos.dataVencimento', 'ASC');
                    
                    $query = $this->db->get();
                    
                    if ($query === false) {
                        $error = $this->db->error();
                        log_message('error', 'Erro na query de prazos no calendário: ' . ($error['message'] ?? 'Erro desconhecido'));
                        $allPrazos = [];
                    } else {
                        $allPrazos = $query->result();
                    }
                    
                    foreach ($allPrazos as $prazo) {
                        // Validar e formatar data de vencimento
                        if (empty($prazo->dataVencimento)) {
                            continue; // Pular se não houver data
                        }
                        
                        // Garantir que a data está no formato correto
                        $dataVencimento = date('Y-m-d', strtotime($prazo->dataVencimento));
                        if ($dataVencimento === false || $dataVencimento === '1970-01-01') {
                            log_message('error', 'Data de vencimento inválida para prazo ID: ' . ($prazo->idPrazos ?? 'N/A'));
                            continue;
                        }
                        
                        $dataVenc = strtotime($dataVencimento);
                        $hoje = strtotime(date('Y-m-d'));
                        $diasRestantes = floor(($dataVenc - $hoje) / 86400);
                        
                        $cor = '#436eee'; // Normal
                        if ($diasRestantes < 0) {
                            $cor = '#CD0000'; // Vencido
                        } elseif ($diasRestantes <= 2) {
                            $cor = '#FF7F00'; // Urgente
                        } elseif ($diasRestantes <= 5) {
                            $cor = '#AEB404'; // Atenção
                        }

                        $events[] = [
                            'title' => "Prazo: " . ($prazo->tipo ?? 'N/A'),
                            'start' => $dataVencimento . ' 09:00:00',
                            'end' => $dataVencimento . ' 10:00:00',
                            'color' => $cor,
                            'extendedProps' => [
                                'id' => $prazo->idPrazos ?? null,
                                'tipo' => '<b>Tipo:</b> ' . ($prazo->tipo ?? 'N/A'),
                                'processo' => '<b>Processo:</b> ' . ($prazo->numeroProcesso ?? 'N/A'),
                                'dataHora' => '<b>Data Vencimento:</b> ' . ($dataVencimento ? date('d/m/Y', strtotime($dataVencimento)) : 'N/A'),
                                'local' => '',
                                'status' => '<b>Status:</b> ' . ucfirst($prazo->status ?? 'N/A'),
                                'observacoes' => '<b>Descrição:</b> ' . strip_tags(html_entity_decode($prazo->descricao ?? '')),
                                'editar' => $this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo'),
                            ],
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Erro no método calendario: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'error' => 'Erro ao buscar eventos do calendário.',
                    'message' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (Error $e) {
            log_message('error', 'Erro fatal no método calendario: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode([
                    'error' => 'Erro fatal ao buscar eventos do calendário.',
                    'message' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        // Garantir que $events é sempre um array
        if (!is_array($events)) {
            log_message('error', 'Events não é um array: ' . gettype($events));
            $events = [];
        }

        log_message('debug', 'Retornando ' . count($events) . ' eventos do calendário');
        
        // Tentar codificar JSON e verificar erros
        $json = json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $jsonError = json_last_error_msg();
            log_message('error', 'Erro ao codificar JSON: ' . $jsonError);
            log_message('error', 'JSON Error Code: ' . json_last_error());
            $events = []; // Retornar array vazio em caso de erro
            $json = json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output($json);
    }

    private function editDontEnv(array $data)
    {
        $env_file_path = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '.env';
        $env_file = file_get_contents($env_file_path);

        foreach ($data as $constante => $valor) {
            if (isset($_ENV[$constante])) {
                $env_file = str_replace("$constante={$_ENV[$constante]}", "$constante={$valor}", $env_file);
            } else {
                file_put_contents($env_file_path, $env_file . "\n{$constante}={$valor}\n");
                $env_file = file_get_contents($env_file_path);
            }
        }
        return file_put_contents($env_file_path, $env_file) ? true : false;
    }
}

