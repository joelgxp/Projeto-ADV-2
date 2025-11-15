<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientAudienciasController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('audiencias_model');
        $this->load->library('Authorization_Token');
    }

    public function index_get($id = '')
    {
        $clientLogged = $this->logged_client();
        
        if ($id && is_numeric($id)) {
            $audiencia = $this->audiencias_model->getById($id);
            
            if (!$audiencia) {
                $this->response([
                    'status' => false,
                    'message' => 'Audiência não encontrada'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            
            // Verificar se a audiência pertence a um processo do cliente
            $this->load->model('processos_model');
            $processo = $this->processos_model->getById($audiencia->processos_id);
            
            if (!$processo || $processo->clientes_id != $clientLogged->usuario->idClientes) {
                $this->response([
                    'status' => false,
                    'message' => 'Audiência não encontrada ou não pertence a este cliente'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            
            $this->response([
                'status' => true,
                'message' => 'Detalhes da Audiência',
                'result' => $audiencia
            ], REST_Controller::HTTP_OK);
        }
        
        $perPage = $this->get('perPage', true) ?: 20;
        $page = $this->get('page', true) ?: 0;
        $start = $page ? ($perPage * $page) : 0;
        
        $audiencias = $this->audiencias_model->getAudienciasByCliente($clientLogged->usuario->idClientes, $perPage, $start);
        
        if(empty($audiencias)) {
            $this->response([
                'status' => false,
                'message' => 'Nenhum resultado encontrado'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
        $this->response([
            'status' => true,
            'message' => 'Listando resultados',
            'result' => $audiencias
        ], REST_Controller::HTTP_OK);
    }
}

