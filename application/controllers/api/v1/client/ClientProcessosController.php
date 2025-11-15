<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientProcessosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('processos_model');
        $this->load->model('Conecte_model');
        $this->load->library('Authorization_Token');
    }

    public function index_get()
    {
        $clientLogged = $this->logged_client();
        
        $perPage = $this->get('perPage', true) ?: 20;
        $page = $this->get('page', true) ?: 0;
        $start = $page ? ($perPage * $page) : 0;
        
        $processos = $this->processos_model->getProcessosByCliente($clientLogged->usuario->idClientes, $perPage, $start);
        
        if(empty($processos)) {
            $this->response([
                'status' => false,
                'message' => 'Nenhum resultado encontrado'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        foreach ($processos as $processo) {
            unset($processo->senha);
        }
        
        $this->response([
            'status' => true,
            'message' => 'Listando resultados',
            'result' => $processos
        ], REST_Controller::HTTP_OK);
    }

    public function processos_get($id = '')
    {
        $clientLogged = $this->logged_client();
        
        if ($id && is_numeric($id)) {
            $processo = $this->processos_model->getById($id);
            
            if (!$processo || $processo->clientes_id != $clientLogged->usuario->idClientes) {
                $this->response([
                    'status' => false,
                    'message' => 'Processo não encontrado ou não pertence a este cliente'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            
            unset($processo->senha);
            
            $this->response([
                'status' => true,
                'message' => 'Detalhes do Processo',
                'result' => $processo
            ], REST_Controller::HTTP_OK);
        }
        
        $this->index_get();
    }
}

