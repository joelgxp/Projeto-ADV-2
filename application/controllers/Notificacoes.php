<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notificacoes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Notificacoes_model');
        $this->data['menuNotificacoes'] = 'notificacoes';
    }

    /**
     * Lista notificações do usuário logado
     */
    public function index()
    {
        $usuario_id = $this->session->userdata('id_admin');
        $cliente_id = $this->session->userdata('id_cliente');
        
        if ($usuario_id) {
            $this->data['notificacoes'] = $this->Notificacoes_model->getByUsuario($usuario_id, 50);
            $this->data['nao_lidas'] = $this->Notificacoes_model->countNaoLidas($usuario_id);
        } elseif ($cliente_id) {
            $this->data['notificacoes'] = $this->Notificacoes_model->getByCliente($cliente_id, 50);
            $this->data['nao_lidas'] = $this->Notificacoes_model->countNaoLidas(null, $cliente_id);
        } else {
            $this->data['notificacoes'] = [];
            $this->data['nao_lidas'] = 0;
        }
        
        $this->data['view'] = 'notificacoes/index';
        return $this->layout();
    }

    /**
     * Retorna contador de notificações não lidas (AJAX)
     */
    public function contador()
    {
        $usuario_id = $this->session->userdata('id_admin');
        $cliente_id = $this->session->userdata('id_cliente');
        
        $count = 0;
        if ($usuario_id) {
            $count = $this->Notificacoes_model->countNaoLidas($usuario_id);
        } elseif ($cliente_id) {
            $count = $this->Notificacoes_model->countNaoLidas(null, $cliente_id);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * Marca notificação como lida
     */
    public function marcar_lida()
    {
        $id = $this->input->post('id');
        
        if (!$id || !is_numeric($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        
        if ($this->Notificacoes_model->marcar_lida($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar como lida']);
        }
    }

    /**
     * Marca todas como lidas
     */
    public function marcar_todas_lidas()
    {
        $usuario_id = $this->session->userdata('id_admin');
        $cliente_id = $this->session->userdata('id_cliente');
        
        if ($usuario_id) {
            $notificacoes = $this->Notificacoes_model->getByUsuario($usuario_id, 1000, true);
        } elseif ($cliente_id) {
            $notificacoes = $this->Notificacoes_model->getByCliente($cliente_id, 1000, true);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuário não identificado']);
            return;
        }
        
        foreach ($notificacoes as $notificacao) {
            $this->Notificacoes_model->marcar_lida($notificacao->id);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => count($notificacoes)]);
    }
}

