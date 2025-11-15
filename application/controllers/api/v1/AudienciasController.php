<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . 'libraries/REST_Controller.php';

class AudienciasController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('audiencias_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vAudiencia')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Audiências',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->get('search', true));
            $where = $search ? "tipo LIKE '%{$search}%' OR local LIKE '%{$search}%' OR observacoes LIKE '%{$search}%'" : '';

            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ?: 0;
            $start = $page ? ($perPage * $page) : 0;

            $audiencias = $this->audiencias_model->get('audiencias', '*', $where, $perPage, $start);

            $this->response([
                'status' => true,
                'message' => 'Listando Audiências',
                'result' => $audiencias,
            ], REST_Controller::HTTP_OK);
        }

        if ($id && is_numeric($id)) {
            $audiencia = $this->audiencias_model->getById($id);

            $this->response([
                'status' => true,
                'message' => 'Detalhes da Audiência',
                'result' => $audiencia,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhuma Audiência localizada.',
            'result' => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aAudiencia')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Audiências!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('audiencias') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'processos_id' => $this->post('processos_id', true) ? $this->post('processos_id', true) : null,
            'tipo' => $this->post('tipo', true),
            'dataHora' => $this->post('dataHora', true) ? $this->post('dataHora', true) : date('Y-m-d H:i:s'),
            'local' => $this->post('local', true) ? $this->post('local', true) : '',
            'observacoes' => $this->post('observacoes', true) ? $this->post('observacoes', true) : '',
            'status' => $this->post('status', true) ? $this->post('status', true) : 'agendada',
            'usuarios_id' => $this->post('usuarios_id', true) ? $this->post('usuarios_id', true) : $this->logged_user()->id,
        ];

        if ($this->audiencias_model->add('audiencias', $data)) {
            $this->response([
                'status' => true,
                'message' => 'Audiência adicionada com sucesso!',
                'result' => $this->audiencias_model->get('audiencias', '*', "idAudiencias = '{$this->db->insert_id()}'", 1, 0, true),
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar a Audiência. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eAudiencia')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Editar Audiências!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID da Audiência!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $inputData = json_decode(trim(file_get_contents('php://input')));

        if (! $this->put('tipo', true) || ! $this->put('dataHora', true)) {
            $this->response([
                'status' => false,
                'message' => 'Preencha todos os campos obrigatórios!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'processos_id' => $this->put('processos_id', true) ? $this->put('processos_id', true) : null,
            'tipo' => $this->put('tipo', true),
            'dataHora' => $this->put('dataHora', true),
            'local' => $this->put('local', true) ? $this->put('local', true) : '',
            'observacoes' => $this->put('observacoes', true) ? $this->put('observacoes', true) : '',
            'status' => $this->put('status', true) ? $this->put('status', true) : 'agendada',
            'usuarios_id' => $this->put('usuarios_id', true) ? $this->put('usuarios_id', true) : $this->logged_user()->id,
        ];

        if ($this->audiencias_model->edit('audiencias', $data, 'idAudiencias', $id)) {
            $this->response([
                'status' => true,
                'message' => 'Audiência editada com sucesso!',
                'result' => $this->audiencias_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar a Audiência. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dAudiencia')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar Audiências!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID da Audiência!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->audiencias_model->delete('audiencias', 'idAudiencias', $id)) {
            $this->log_app('Removeu uma Audiência. ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'Audiência excluída com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir a Audiência. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }
}

