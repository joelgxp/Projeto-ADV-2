<?php

class MY_Controller extends CI_Controller
{
    public $data = [
        'configuration' => [
            'per_page' => 10,
            'next_link' => 'Próxima',
            'prev_link' => 'Anterior',
            'full_tag_open' => '<div class="pagination alternate"><ul>',
            'full_tag_close' => '</ul></div>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'cur_tag_open' => '<li><a style="color: #2D335B"><b>',
            'cur_tag_close' => '</b></a></li>',
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            'first_link' => 'Primeira',
            'last_link' => 'Última',
            'first_tag_open' => '<li>',
            'first_tag_close' => '</li>',
            'last_tag_open' => '<li>',
            'last_tag_close' => '</li>',
            'app_name' => 'Adv',
            'app_theme' => 'white',
            'os_notification' => 'cliente',
            'processo_notification' => 'cliente',
            'prazo_notification' => 'todos',
            'audiencia_notification' => 'todos',
            'control_estoque' => '1',
            'notifica_whats' => '',
            'control_baixa' => '0',
            'control_editos' => '1',
            'pix_key' => '',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if ((! session_id()) || (! $this->session->userdata('logado'))) {
            redirect('login');
        }
        $this->load_configuration();
    }

    /**
     * Trata erros do banco de dados de forma padronizada
     * 
     * @param mixed $result Resultado da operação no banco
     * @param string $operacao Nome da operação (ex: 'adicionar cliente', 'atualizar processo')
     * @param string $mensagem_sucesso Mensagem de sucesso personalizada
     * @return array ['success' => bool, 'message' => string, 'error_details' => array|null]
     */
    protected function tratar_erro_banco($result, $operacao = 'operacao', $mensagem_sucesso = null)
    {
        if ($result !== false && $result !== null) {
            return [
                'success' => true,
                'message' => $mensagem_sucesso ?: ucfirst($operacao) . ' realizado com sucesso!',
                'error_details' => null
            ];
        }
        
        $error = $this->db->error();
        $error_message = isset($error['message']) ? $error['message'] : 'Erro desconhecido ao ' . $operacao;
        $error_code = isset($error['code']) ? $error['code'] : null;
        
        // Log do erro
        log_message('error', 'Erro no banco de dados - ' . $operacao . ': ' . $error_message . ($error_code ? ' (Código: ' . $error_code . ')' : ''));
        
        return [
            'success' => false,
            'message' => 'Erro ao ' . $operacao . ': ' . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'),
            'error_details' => [
                'code' => $error_code,
                'message' => $error_message,
                'operation' => $operacao
            ]
        ];
    }

    private function load_configuration()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        
        if ($this->CI->db->table_exists('configuracoes')) {
            $configuracoes = $this->CI->db->get('configuracoes')->result();
            
            if ($configuracoes) {
                foreach ($configuracoes as $c) {
                    if (isset($c->config) && isset($c->valor)) {
                        $this->data['configuration'][$c->config] = $c->valor;
                    }
                }
            }
        }
    }

    public function layout()
    {
        // load views
        $this->load->view('tema/topo', $this->data);
        $this->load->view('tema/menu');
        $this->load->view('tema/conteudo');
        $this->load->view('tema/rodape');
    }
}
