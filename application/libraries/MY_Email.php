<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * CodeIgniter Email Queue
 *
 * A CodeIgniter library to queue e-mails.
 *
 * @category    Libraries
 *
 * @author      Thaynã Bruno Moretti
 *
 * @link    http://www.meau.com.br/
 *
 * @license http://www.opensource.org/licenses/mit-license.html
 *
 * Updated by @RamonSilva for Map-OS
 */
class MY_Email extends CI_Email
{
    // DB table
    private $table_email_queue = 'email_queue';

    // Main controller
    private $main_controller = 'email/process';

    // PHP Nohup command line
    private $phpcli = 'nohup php';

    private $expiration = null;

    // Status (pending, sending, sent, failed)
    private $status;

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        log_message('debug', 'Email Queue Class Initialized');

        $this->expiration = 60 * 5;
        $this->CI = &get_instance();

        $this->CI->load->database('default');
    }

    public function set_status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Monta o array de configuração de e-mail a partir das chaves planas do config/email.php.
     * O arquivo de config usa $config['protocol'], $config['smtp_host'], etc., então
     * config->item('email') retorna null. Este método lê cada chave e retorna o array
     * esperado por CI_Email::initialize().
     *
     * @return array|null Array de config ou null se não houver config carregada
     */
    private function get_email_config_array()
    {
        $keys = [
            'protocol', 'smtp_host', 'smtp_crypto', 'smtp_port', 'smtp_user', 'smtp_pass',
            'validate', 'mailtype', 'charset', 'newline', 'bcc_batch_mode', 'wordwrap',
            'priority', 'smtp_timeout', 'smtp_keepalive'
        ];
        $config = [];
        foreach ($keys as $key) {
            $val = $this->CI->config->item($key);
            if ($val !== null && $val !== false) {
                $config[$key] = $val;
            }
        }
        return !empty($config) ? $config : null;
    }

    /**
     * Get
     *
     * Get queue emails.
     *
     * @return mixed
     */
    public function get($limit = null, $offset = null)
    {
        if ($this->status != false) {
            $this->CI->db->where('status', $this->status);
        }

        $query = $this->CI->db->get("{$this->table_email_queue}", $limit, $offset);

        if ($query === false) {
            $err = $this->CI->db->error();
            log_message('error', 'Erro ao buscar emails da fila: ' . ($err['message'] ?? 'Erro desconhecido'));
            return [];
        }

        return $query->result();
    }

    /**
     * Save
     *
     * Add queue email to database.
     *
     * @return mixed
     */
    public function send($skip_job = false)
    {
        if ($skip_job === true) {
            return parent::send();
        }

        $date = date('Y-m-d H:i:s');

        $to = is_array($this->_recipients) ? implode(', ', $this->_recipients) : $this->_recipients;
        $cc = implode(', ', $this->_cc_array);
        $bcc = implode(', ', $this->_bcc_array);

        $dbdata = [
            'to' => $to,
            'subject' => $this->_subject ?: 'Sem assunto',
            'message' => $this->_body,
            'status' => 'pending',
            'prioridade' => 'normal',
            'created_at' => $date,
        ];

        return $this->CI->db->insert($this->table_email_queue, $dbdata);
    }

    /**
     * Start process
     *
     * Start php process to send emails
     *
     * @return mixed
     */
    public function start_process()
    {
        $filename = FCPATH . 'index.php';
        $exec = shell_exec("{$this->phpcli} {$filename} {$this->main_controller} > /dev/null &");

        return $exec;
    }

    /**
     * Send queue
     *
     * Send queue emails.
     *
     * @return void
     */
    public function send_queue()
    {
        $this->set_status('pending');
        
        // Ordenar por prioridade (critica > alta > normal > baixa)
        $this->CI->db->order_by("FIELD(prioridade, 'critica', 'alta', 'normal', 'baixa')", 'ASC', false);
        $this->CI->db->order_by('created_at', 'ASC');
        
        $emails = $this->get();

        $this->CI->db->where('status', 'pending');
        $this->CI->db->set('status', 'sending');
        $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
        $this->CI->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->CI->db->update($this->table_email_queue);

        // Obter configurações de e-mail para definir remetente padrão
        $this->CI->load->config('email');
        $smtp_user = $this->CI->config->item('smtp_user');
        $app_name = 'Adv'; // Nome padrão, pode ser obtido da configuração se necessário
        
        // Tentar obter nome do sistema
        if (isset($this->CI->data['configuration']['app_name'])) {
            $app_name = $this->CI->data['configuration']['app_name'];
        }

        foreach ($emails as $email) {
            // Limpar e reconfigurar antes de processar
            $this->clear();
            
            // Reconfigurar email com configurações do config (chaves planas em config/email.php)
            $config = $this->get_email_config_array();
            if ($config) {
                $this->initialize($config);
            }
            
            // Obter emitente para usar como remetente
            $this->CI->load->model('sistema_model');
            $emitente = $this->CI->sistema_model->getEmitente();
            
            // Definir remetente padrão
            if ($emitente && !empty($emitente->email)) {
                $from_name = $emitente->nome ?? $app_name;
                $this->from($emitente->email, $from_name);
            } elseif (!empty($smtp_user)) {
                $this->from($smtp_user, $app_name);
            }

            // Definir destinatário (pode ser string ou array)
            $to = is_string($email->to) ? $email->to : (is_array($email->to) ? implode(', ', $email->to) : '');
            if (!empty($to)) {
                $this->to($to);
            }
            
            // Definir assunto se existir
            if (isset($email->subject) && !empty($email->subject)) {
                $this->subject($email->subject);
            }

            $this->message($email->message);

            log_message('info', "Tentando enviar email ID {$email->id} para: {$to}");
            
            // Verificar se excedeu tentativas máximas
            $tentativas = isset($email->tentativas) ? (int)$email->tentativas : 0;
            $max_tentativas = isset($email->max_tentativas) ? (int)$email->max_tentativas : 3;
            
            if ($tentativas >= $max_tentativas) {
                $status = 'failed';
                $erro = "Excedeu número máximo de tentativas ({$max_tentativas})";
                log_message('error', "Email ID {$email->id} falhou após {$tentativas} tentativas");
            } else {
                // Verificar se tem template e renderizar
                if (isset($email->template) && !empty($email->template)) {
                    $this->CI->load->helper('email_template');
                    $dados_template = !empty($email->dados_template) ? json_decode($email->dados_template, true) : [];
                    $mensagem_template = render_email_template($email->template, $dados_template);
                    $this->message($mensagem_template);
                }
                
                if ($this->send(true)) {
                    $status = 'sent';
                    log_message('info', "Email ID {$email->id} enviado com sucesso para: {$to}");
                } else {
                    $error_msg = $this->print_debugger();
                    log_message('error', "Erro ao enviar e-mail ID {$email->id} da fila para {$to}: " . $error_msg);
                    $status = 'failed';
                    $erro = $error_msg;
                }
            }

            $this->CI->db->where('id', $email->id);
            $this->CI->db->set('status', $status);
            $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
            $this->CI->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->CI->db->set('tentativas', 'tentativas + 1', false);
            
            if (isset($erro)) {
                $this->CI->db->set('erro', $erro);
            }
            
            $this->CI->db->update($this->table_email_queue);
        }
    }

    /**
     * Retry failed emails
     *
     * Resend failed or expired emails
     *
     * @return void
     */
    public function retry_queue()
    {
        $expire = (time() - $this->expiration);
        $date_expire = date('Y-m-d H:i:s', $expire);

        $this->CI->db->set('status', 'pending');
        $this->CI->db->where("(last_attempt < '{$date_expire}' AND status = 'sending')");
        $this->CI->db->or_where("status = 'failed'");

        $this->CI->db->update($this->table_email_queue);

        log_message('debug', 'Email queue retrying...');
    }

    /**
     * Processa e envia um email específico da fila
     * 
     * Método centralizado para processar um único email por ID.
     * Todas as configurações de email devem ser feitas através deste método.
     * 
     * @param int $email_id ID do email na fila
     * @return bool true se enviado com sucesso, false caso contrário
     */
    public function send_single($email_id)
    {
        // Buscar o email na fila
        $email = $this->CI->db->where('id', $email_id)->get($this->table_email_queue)->row();
        
        if (!$email) {
            log_message('error', "Email ID {$email_id} não encontrado na fila");
            return false;
        }

        // Verificar se já foi enviado
        if ($email->status === 'sent') {
            log_message('info', "Email ID {$email_id} já foi enviado anteriormente");
            return true;
        }

        // Marcar como sending
        $this->CI->db->where('id', $email_id);
        $this->CI->db->set('status', 'sending');
        $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
        $this->CI->db->update($this->table_email_queue);

        // Obter configurações de e-mail (config/email.php usa chaves planas, não um array 'email')
        $this->CI->load->config('email');
        $config = $this->get_email_config_array();
        $smtp_user = $this->CI->config->item('smtp_user');
        $app_name = 'Adv'; // Nome padrão
        
        // Tentar obter nome do sistema
        if (isset($this->CI->data['configuration']['app_name'])) {
            $app_name = $this->CI->data['configuration']['app_name'];
        }

        // Limpar e reconfigurar
        $this->clear();
        
        if ($config) {
            $this->initialize($config);
        }
        
        // Obter emitente para usar como remetente
        $this->CI->load->model('sistema_model');
        $emitente = $this->CI->sistema_model->getEmitente();
        
        // Definir remetente padrão
        if ($emitente && !empty($emitente->email)) {
            $from_name = $emitente->nome ?? $app_name;
            $this->from($emitente->email, $from_name);
        } elseif (!empty($smtp_user)) {
            $this->from($smtp_user, $app_name);
        } else {
            log_message('error', "Email ID {$email_id}: Remetente não configurado (emitente ou smtp_user)");
            $this->CI->db->where('id', $email_id);
            $this->CI->db->set('status', 'failed');
            $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
            $this->CI->db->update($this->table_email_queue);
            return false;
        }

        // Definir destinatário
        $to = is_string($email->to) ? $email->to : (is_array($email->to) ? implode(', ', $email->to) : '');
        if (empty($to)) {
            log_message('error', "Email ID {$email_id}: Destinatário vazio");
            $this->CI->db->where('id', $email_id);
            $this->CI->db->set('status', 'failed');
            $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
            $this->CI->db->update($this->table_email_queue);
            return false;
        }
        
        $this->to($to);
        
        // Definir assunto
        if (isset($email->subject) && !empty($email->subject)) {
            $this->subject($email->subject);
        }
        
        $this->message($email->message);

        log_message('info', "Tentando enviar email ID {$email_id} para: {$to}");
        
        // Tentar enviar
        if ($this->send(true)) {
            $status = 'sent';
            log_message('info', "✅ Email ID {$email_id} enviado com sucesso para: {$to}");
        } else {
            $error_msg = $this->print_debugger();
            log_message('error', "❌ Erro ao enviar email ID {$email_id} para {$to}: " . $error_msg);
            $status = 'failed';
        }

        // Atualizar status
        $this->CI->db->where('id', $email_id);
        $this->CI->db->set('status', $status);
        $this->CI->db->set('last_attempt', date('Y-m-d H:i:s'));
        $this->CI->db->set('attempts', 'attempts + 1', false);
        $this->CI->db->update($this->table_email_queue);

        return $status === 'sent';
    }
}
