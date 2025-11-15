<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . 'libraries/REST_Controller.php';

class ProcessosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('processos_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vProcesso')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Processos',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->get('search', true));
            $where = $search ? "numeroProcesso LIKE '%{$search}%' OR classe LIKE '%{$search}%' OR assunto LIKE '%{$search}%'" : '';

            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ?: 0;
            $start = $page ? ($perPage * $page) : 0;

            $processos = $this->processos_model->get('processos', '*', $where, $perPage, $start);

            $this->response([
                'status' => true,
                'message' => 'Listando Processos',
                'result' => $processos,
            ], REST_Controller::HTTP_OK);
        }

        if ($id && is_numeric($id)) {
            $processo = $this->processos_model->getById($id);

            $this->response([
                'status' => true,
                'message' => 'Detalhes do Processo',
                'result' => $processo,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhum Processo localizado.',
            'result' => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aProcesso')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Processos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('processos') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $valorCausa = $this->post('valorCausa', true);
        $valorCausa = str_replace(',', '.', str_replace('.', '', $valorCausa));

        $data = [
            'numeroProcesso' => $this->post('numeroProcesso', true),
            'classe' => $this->post('classe', true) ? $this->post('classe', true) : '',
            'assunto' => $this->post('assunto', true) ? $this->post('assunto', true) : '',
            'tipo_processo' => $this->post('tipo_processo', true) ? $this->post('tipo_processo', true) : 'civel',
            'status' => $this->post('status', true) ? $this->post('status', true) : 'em_andamento',
            'valorCausa' => $valorCausa ? $valorCausa : 0,
            'dataDistribuicao' => $this->post('dataDistribuicao', true) ? $this->post('dataDistribuicao', true) : null,
            'vara' => $this->post('vara', true) ? $this->post('vara', true) : '',
            'comarca' => $this->post('comarca', true) ? $this->post('comarca', true) : '',
            'tribunal' => $this->post('tribunal', true) ? $this->post('tribunal', true) : '',
            'segmento' => $this->post('segmento', true) ? $this->post('segmento', true) : '',
            'clientes_id' => $this->post('clientes_id', true) ? $this->post('clientes_id', true) : null,
            'usuarios_id' => $this->post('usuarios_id', true) ? $this->post('usuarios_id', true) : $this->logged_user()->id,
            'observacoes' => $this->post('observacoes', true) ? $this->post('observacoes', true) : '',
        ];

        if ($this->processos_model->add('processos', $data)) {
            $this->response([
                'status' => true,
                'message' => 'Processo adicionado com sucesso!',
                'result' => $this->processos_model->get('processos', '*', "numeroProcesso = '{$data['numeroProcesso']}'", 1, 0, true),
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Processo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eProcesso')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Editar Processos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Processo!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $inputData = json_decode(trim(file_get_contents('php://input')));

        if (! $this->put('numeroProcesso', true)) {
            $this->response([
                'status' => false,
                'message' => 'Preencha todos os campos obrigatórios!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $valorCausa = $this->put('valorCausa', true);
        $valorCausa = $valorCausa ? str_replace(',', '.', str_replace('.', '', $valorCausa)) : 0;

        $data = [
            'numeroProcesso' => $this->put('numeroProcesso', true),
            'classe' => $this->put('classe', true) ? $this->put('classe', true) : '',
            'assunto' => $this->put('assunto', true) ? $this->put('assunto', true) : '',
            'tipo_processo' => $this->put('tipo_processo', true) ? $this->put('tipo_processo', true) : 'civel',
            'status' => $this->put('status', true) ? $this->put('status', true) : 'em_andamento',
            'valorCausa' => $valorCausa,
            'dataDistribuicao' => $this->put('dataDistribuicao', true) ? $this->put('dataDistribuicao', true) : null,
            'vara' => $this->put('vara', true) ? $this->put('vara', true) : '',
            'comarca' => $this->put('comarca', true) ? $this->put('comarca', true) : '',
            'tribunal' => $this->put('tribunal', true) ? $this->put('tribunal', true) : '',
            'segmento' => $this->put('segmento', true) ? $this->put('segmento', true) : '',
            'clientes_id' => $this->put('clientes_id', true) ? $this->put('clientes_id', true) : null,
            'usuarios_id' => $this->put('usuarios_id', true) ? $this->put('usuarios_id', true) : $this->logged_user()->id,
            'observacoes' => $this->put('observacoes', true) ? $this->put('observacoes', true) : '',
        ];

        if ($this->processos_model->edit('processos', $data, 'idProcessos', $id)) {
            $this->response([
                'status' => true,
                'message' => 'Processo editado com sucesso!',
                'result' => $this->processos_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Processo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dProcesso')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar Processos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Processo!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->processos_model->delete('processos', 'idProcessos', $id)) {
            $this->log_app('Removeu um Processo. ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'Processo excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Processo. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }
}

