<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . 'libraries/REST_Controller.php';

class PrazosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('prazos_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vPrazo')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Prazos',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->get('search', true));
            $where = $search ? "descricao LIKE '%{$search}%' OR tipo LIKE '%{$search}%'" : '';

            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ?: 0;
            $start = $page ? ($perPage * $page) : 0;

            $prazos = $this->prazos_model->get('prazos', '*', $where, $perPage, $start);

            $this->response([
                'status' => true,
                'message' => 'Listando Prazos',
                'result' => $prazos,
            ], REST_Controller::HTTP_OK);
        }

        if ($id && is_numeric($id)) {
            $prazo = $this->prazos_model->getById($id);

            $this->response([
                'status' => true,
                'message' => 'Detalhes do Prazo',
                'result' => $prazo,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhum Prazo localizado.',
            'result' => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aPrazo')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Prazos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('prazos') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'processos_id' => $this->post('processos_id', true) ? $this->post('processos_id', true) : null,
            'tipo' => $this->post('tipo', true),
            'descricao' => $this->post('descricao', true) ? $this->post('descricao', true) : '',
            'dataPrazo' => $this->post('dataPrazo', true) ? $this->post('dataPrazo', true) : date('Y-m-d'),
            'dataVencimento' => $this->post('dataVencimento', true) ? $this->post('dataVencimento', true) : date('Y-m-d'),
            'status' => $this->post('status', true) ? $this->post('status', true) : 'pendente',
            'prioridade' => $this->post('prioridade', true) ? $this->post('prioridade', true) : 'normal',
            'usuarios_id' => $this->post('usuarios_id', true) ? $this->post('usuarios_id', true) : $this->logged_user()->id,
        ];

        if ($this->prazos_model->add('prazos', $data)) {
            $this->response([
                'status' => true,
                'message' => 'Prazo adicionado com sucesso!',
                'result' => $this->prazos_model->get('prazos', '*', "idPrazos = '{$this->db->insert_id()}'", 1, 0, true),
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Prazo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'ePrazo')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Editar Prazos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Prazo!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $inputData = json_decode(trim(file_get_contents('php://input')));

        if (! $this->put('tipo', true) || ! $this->put('dataVencimento', true)) {
            $this->response([
                'status' => false,
                'message' => 'Preencha todos os campos obrigatórios!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'processos_id' => $this->put('processos_id', true) ? $this->put('processos_id', true) : null,
            'tipo' => $this->put('tipo', true),
            'descricao' => $this->put('descricao', true) ? $this->put('descricao', true) : '',
            'dataPrazo' => $this->put('dataPrazo', true) ? $this->put('dataPrazo', true) : date('Y-m-d'),
            'dataVencimento' => $this->put('dataVencimento', true),
            'status' => $this->put('status', true) ? $this->put('status', true) : 'pendente',
            'prioridade' => $this->put('prioridade', true) ? $this->put('prioridade', true) : 'normal',
            'usuarios_id' => $this->put('usuarios_id', true) ? $this->put('usuarios_id', true) : $this->logged_user()->id,
        ];

        if ($this->prazos_model->edit('prazos', $data, 'idPrazos', $id)) {
            $this->response([
                'status' => true,
                'message' => 'Prazo editado com sucesso!',
                'result' => $this->prazos_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Prazo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dPrazo')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar Prazos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Prazo!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->prazos_model->delete('prazos', 'idPrazos', $id)) {
            $this->log_app('Removeu um Prazo. ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'Prazo excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Prazo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }
}

