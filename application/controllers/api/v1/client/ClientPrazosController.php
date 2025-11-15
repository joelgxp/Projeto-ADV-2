<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientPrazosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('prazos_model');
        $this->load->library('Authorization_Token');
    }

    public function index_get($id = '')
    {
        $clientLogged = $this->logged_client();
        
        if ($id && is_numeric($id)) {
            $prazo = $this->prazos_model->getById($id);
            
            if (!$prazo) {
                $this->response([
                    'status' => false,
                    'message' => 'Prazo não encontrado'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            
            // Verificar se o prazo pertence a um processo do cliente
            $this->load->model('processos_model');
            $processo = $this->processos_model->getById($prazo->processos_id);
            
            if (!$processo || $processo->clientes_id != $clientLogged->usuario->idClientes) {
                $this->response([
                    'status' => false,
                    'message' => 'Prazo não encontrado ou não pertence a este cliente'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            
            $this->response([
                'status' => true,
                'message' => 'Detalhes do Prazo',
                'result' => $prazo
            ], REST_Controller::HTTP_OK);
        }
        
        $perPage = $this->get('perPage', true) ?: 20;
        $page = $this->get('page', true) ?: 0;
        $start = $page ? ($perPage * $page) : 0;
        
        $prazos = $this->prazos_model->getPrazosByCliente($clientLogged->usuario->idClientes, $perPage, $start);
        
        if(empty($prazos)) {
            $this->response([
                'status' => false,
                'message' => 'Nenhum resultado encontrado'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
        $this->response([
            'status' => true,
            'message' => 'Listando resultados',
            'result' => $prazos
        ], REST_Controller::HTTP_OK);
    }
}

