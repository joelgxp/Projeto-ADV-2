<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tickets extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->model('Tickets_cliente_model');
        $this->load->model('Tickets_respostas_model');
        $this->load->model('processos_model');
        $this->load->model('clientes_model');
    }

    /**
     * Listar tickets do cliente (Portal do Cliente)
     * Fase 6 - Sprint 4
     */
    public function index()
    {
        // Verificar se é cliente logado no portal
        if ($this->session->userdata('conectado') && ($this->session->userdata('isCliente') || $this->session->userdata('cliente_id'))) {
            $this->listarCliente();
            return;
        }

        // Se não for cliente, verificar se é advogado/admin logado
        if ($this->session->userdata('logado')) {
            $this->listarAdvogado();
            return;
        }
        
        // Se não for nem cliente nem advogado, redirecionar
        $this->session->set_flashdata('error', 'Você precisa estar logado para acessar esta página.');
        redirect('mine');
    }

    /**
     * Listar tickets do cliente (Portal)
     */
    private function listarCliente()
    {
        // Verificar sessão do cliente
        if (!$this->session->userdata('conectado') || !$this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Você precisa estar logado para acessar esta página.');
            redirect('mine');
            return;
        }
        
        $cliente_id = $this->session->userdata('cliente_id');
        
        $status = $this->input->get('status');
        $data['tickets'] = $this->Tickets_cliente_model->getByCliente($cliente_id, $status);
        $data['nao_lidos'] = $this->Tickets_cliente_model->countNaoLidosByCliente($cliente_id);
        $data['menuTickets'] = 'tickets'; // Menu ativo
        
        $data['output'] = 'conecte/tickets';
        $this->load->view('conecte/template', $data);
    }

    /**
     * Listar tickets do advogado (Sistema Admin)
     */
    private function listarAdvogado()
    {
        // Verificar se é admin/advogado logado
        if (!$this->session->userdata('logado')) {
            $this->session->set_flashdata('error', 'Você precisa estar logado como advogado para acessar esta página.');
            redirect('login');
            return;
        }
        
        $usuario_id = $this->session->userdata('id_admin');
        
        if (!$usuario_id) {
            $this->session->set_flashdata('error', 'Usuário não identificado.');
            redirect('login');
            return;
        }
        
        $status = $this->input->get('status');
        $data['tickets'] = $this->Tickets_cliente_model->getByAdvogado($usuario_id, $status);
        $data['nao_lidos'] = $this->Tickets_cliente_model->countNaoLidosByAdvogado($usuario_id);
        
        // Para admin, usar layout do sistema (se existir view)
        // Por enquanto, mostrar mensagem informativa
        $data['menuTickets'] = 'tickets';
        $this->session->set_flashdata('info', 'Visualização de tickets para advogados em desenvolvimento. Por favor, acesse via portal do cliente.');
        redirect(base_url());
    }

    /**
     * Visualizar ticket (Cliente)
     */
    public function visualizar($id = null)
    {
        if (!$this->session->userdata('conectado') || (!$this->session->userdata('isCliente') && !$this->session->userdata('cliente_id'))) {
            redirect('mine');
        }

        if (!$id) {
            $this->session->set_flashdata('error', 'Ticket não encontrado.');
            redirect('tickets');
        }

        $cliente_id = $this->session->userdata('cliente_id');
        $ticket = $this->Tickets_cliente_model->getById($id);

        if (!$ticket || $ticket->clientes_id != $cliente_id) {
            $this->session->set_flashdata('error', 'Ticket não encontrado ou você não tem permissão para visualizá-lo.');
            redirect('tickets');
        }

        // Marcar como lido
        $this->Tickets_cliente_model->marcarLidoCliente($id);

        // Buscar respostas
        $data['ticket'] = $ticket;
        $data['respostas'] = $this->Tickets_respostas_model->getByTicket($id);
        
        // Buscar processo se vinculado
        if ($ticket->processos_id) {
            $data['processo'] = $this->processos_model->getById($ticket->processos_id);
        }

        $data['output'] = 'conecte/visualizar_ticket';
        $this->load->view('conecte/template', $data);
    }

    /**
     * Abrir novo ticket (Cliente)
     */
    public function abrir()
    {
        if (!$this->session->userdata('conectado') || (!$this->session->userdata('isCliente') && !$this->session->userdata('cliente_id'))) {
            redirect('mine');
        }

        $cliente_id = $this->session->userdata('cliente_id');

        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('assunto', 'Assunto', 'required|trim');
            $this->form_validation->set_rules('mensagem', 'Mensagem', 'required|trim');

            if ($this->form_validation->run() == false) {
                $data['error'] = validation_errors();
            } else {
                // Buscar processo do cliente para vincular advogado
                $processos_id = $this->input->post('processos_id') ?: null;
                $usuarios_id = null;

                if ($processos_id) {
                    $processo = $this->processos_model->getById($processos_id);
                    if ($processo && $processo->clientes_id == $cliente_id) {
                        $usuarios_id = $processo->usuarios_id; // Advogado responsável
                    }
                }

                $ticket_data = [
                    'clientes_id' => $cliente_id,
                    'processos_id' => $processos_id,
                    'usuarios_id' => $usuarios_id,
                    'assunto' => $this->input->post('assunto'),
                    'mensagem' => $this->input->post('mensagem'),
                    'status' => 'aberto',
                    'prioridade' => $this->input->post('prioridade') ?: 'normal'
                ];

                $ticket_id = $this->Tickets_cliente_model->add($ticket_data);

                if ($ticket_id) {
                    // Enviar e-mail para advogado se houver
                    if ($usuarios_id) {
                        $this->enviarEmailNovoTicket($ticket_id);
                    }

                    $this->session->set_flashdata('success', 'Ticket criado com sucesso!');
                    redirect('tickets/visualizar/' . $ticket_id);
                } else {
                    $data['error'] = 'Erro ao criar ticket. Tente novamente.';
                }
            }
        }

        // Buscar processos do cliente para dropdown
        $this->load->model('processos_model');
        $data['processos'] = $this->processos_model->getProcessosByCliente($cliente_id);

        $data['output'] = 'conecte/abrir_ticket';
        $this->load->view('conecte/template', $data);
    }

    /**
     * Responder ticket (Cliente ou Advogado)
     */
    public function responder($ticket_id = null)
    {
        if (!$ticket_id) {
            $this->session->set_flashdata('error', 'Ticket não encontrado.');
            redirect('tickets');
        }

        $ticket = $this->Tickets_cliente_model->getById($ticket_id);

        if (!$ticket) {
            $this->session->set_flashdata('error', 'Ticket não encontrado.');
            redirect('tickets');
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('mensagem', 'Mensagem', 'required|trim');

            if ($this->form_validation->run() == false) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $is_cliente = $this->session->userdata('isCliente') || $this->session->userdata('cliente_id');
                $resposta_data = [
                    'tickets_id' => $ticket_id,
                    'mensagem' => $this->input->post('mensagem')
                ];

                if ($is_cliente) {
                    $resposta_data['clientes_id'] = $this->session->userdata('cliente_id');
                    $resposta_data['usuarios_id'] = null;
                } else {
                    $resposta_data['usuarios_id'] = $this->session->userdata('id_admin');
                    $resposta_data['clientes_id'] = null;
                }

                $resposta_id = $this->Tickets_respostas_model->add($resposta_data);

                if ($resposta_id) {
                    // Atualizar status do ticket
                    if ($is_cliente) {
                        $this->Tickets_cliente_model->edit($ticket_id, [
                            'status' => 'em_andamento',
                            'lido_advogado' => 0
                        ]);
                    } else {
                        $this->Tickets_cliente_model->edit($ticket_id, [
                            'status' => 'respondido',
                            'data_resposta' => date('Y-m-d H:i:s'),
                            'lido_cliente' => 0
                        ]);
                    }

                    // Enviar e-mail
                    if ($is_cliente) {
                        $this->enviarEmailRespostaTicket($ticket_id, true); // Para advogado
                    } else {
                        $this->enviarEmailRespostaTicket($ticket_id, false); // Para cliente
                    }

                    $this->session->set_flashdata('success', 'Resposta enviada com sucesso!');
                    redirect('tickets/visualizar/' . $ticket_id);
                } else {
                    $this->session->set_flashdata('error', 'Erro ao enviar resposta. Tente novamente.');
                }
            }
        }

        redirect('tickets/visualizar/' . $ticket_id);
    }

    /**
     * Fechar ticket (Cliente ou Advogado)
     */
    public function fechar($id = null)
    {
        if (!$id) {
            $this->session->set_flashdata('error', 'Ticket não encontrado.');
            redirect('tickets');
        }

        $ticket = $this->Tickets_cliente_model->getById($id);

        if (!$ticket) {
            $this->session->set_flashdata('error', 'Ticket não encontrado.');
            redirect('tickets');
        }

        // Verificar permissão
        $is_cliente = $this->session->userdata('isCliente') || $this->session->userdata('cliente_id');
        if ($is_cliente && $ticket->clientes_id != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para fechar este ticket.');
            redirect('tickets');
        }

        if ($this->Tickets_cliente_model->fechar($id)) {
            $this->session->set_flashdata('success', 'Ticket fechado com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao fechar ticket.');
        }

        redirect('tickets');
    }

    /**
     * Enviar e-mail de novo ticket para advogado
     */
    private function enviarEmailNovoTicket($ticket_id)
    {
        $ticket = $this->Tickets_cliente_model->getById($ticket_id);
        if (!$ticket || !$ticket->usuarios_id) {
            return false;
        }

        $this->load->model('usuarios_model');
        $advogado = $this->usuarios_model->getById($ticket->usuarios_id);
        if (!$advogado || !$advogado->email) {
            return false;
        }

        $cliente = $this->clientes_model->getById($ticket->clientes_id);
        
        $this->load->model('sistema_model');
        $emitente = $this->sistema_model->get('emitente', '*', '', 0, 0, true);

        $this->load->model('email_model');
        
        $mensagem = $this->load->view('conecte/emails/novo_ticket', [
            'ticket' => $ticket,
            'cliente' => $cliente,
            'advogado' => $advogado,
            'emitente' => $emitente
        ], true);

        $email_data = [
            'to' => $advogado->email,
            'subject' => 'Novo Ticket - ' . $ticket->assunto,
            'message' => $mensagem
        ];

        return $this->email_model->add('email_queue', $email_data);
    }

    /**
     * Enviar e-mail de resposta ao ticket
     */
    private function enviarEmailRespostaTicket($ticket_id, $para_advogado = true)
    {
        $ticket = $this->Tickets_cliente_model->getById($ticket_id);
        if (!$ticket) {
            return false;
        }

        $cliente = $this->clientes_model->getById($ticket->clientes_id);
        
        $this->load->model('sistema_model');
        $emitente = $this->sistema_model->get('emitente', '*', '', 0, 0, true);

        $this->load->model('email_model');

        if ($para_advogado && $ticket->usuarios_id) {
            $this->load->model('usuarios_model');
            $advogado = $this->usuarios_model->getById($ticket->usuarios_id);
            if (!$advogado || !$advogado->email) {
                return false;
            }

            $mensagem = $this->load->view('conecte/emails/resposta_ticket', [
                'ticket' => $ticket,
                'cliente' => $cliente,
                'advogado' => $advogado,
                'emitente' => $emitente,
                'quem_respondeu' => 'cliente'
            ], true);

            $email_data = [
                'to' => $advogado->email,
                'subject' => 'Nova Resposta no Ticket - ' . $ticket->assunto,
                'message' => $mensagem
            ];
        } else {
            // Para cliente
            if (!$cliente || !$cliente->email) {
                return false;
            }

            $mensagem = $this->load->view('conecte/emails/resposta_ticket', [
                'ticket' => $ticket,
                'cliente' => $cliente,
                'emitente' => $emitente,
                'quem_respondeu' => 'advogado'
            ], true);

            $email_data = [
                'to' => $cliente->email,
                'subject' => 'Resposta ao seu Ticket - ' . $ticket->assunto,
                'message' => $mensagem
            ];
        }

        return $this->email_model->add('email_queue', $email_data);
    }
}

