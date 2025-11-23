<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Clientes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('clientes_model');
        $this->load->library('cliente_upload');
        $this->load->helper('cliente');
        $this->data['menuClientes'] = 'clientes';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        // Se for requisição AJAX do DataTables, retornar JSON
        // Verificar se é requisição do DataTables (tem parâmetros específicos)
        $hasDatatablesParams = (
            $this->input->get('sEcho') !== false || 
            $this->input->get('draw') !== false ||
            $this->input->get('iDisplayStart') !== false ||
            $this->input->get('start') !== false ||
            $this->input->get('iDisplayLength') !== false ||
            $this->input->get('length') !== false
        );
        
        if ($this->input->is_ajax_request() && $hasDatatablesParams) {
            $this->datatables_ajax();
            return;
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('clientes/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->clientes_model->count('clientes');
        if ($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/clientes") . "\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        // Carregar apenas primeira página para fallback (quando JS desabilitado)
        $this->data['results'] = $this->clientes_model->get('clientes', '*', $pesquisa, $this->data['configuration']['per_page'], 0);

        $this->data['view'] = 'clientes/clientes';

        return $this->layout();
    }

    /**
     * Retorna dados para DataTables (server-side processing)
     */
    private function datatables_ajax()
    {
        // DataTables 1.9.4 usa parâmetros diferentes
        // sEcho deve ser retornado exatamente como foi enviado
        $sEcho = $this->input->get('sEcho');
        if ($sEcho === false || $sEcho === null) {
            $sEcho = $this->input->get('draw') ?: 1;
        }
        $draw = intval($sEcho);
        
        $start = intval($this->input->get('iDisplayStart') ?: $this->input->get('start') ?: 0);
        $length = intval($this->input->get('iDisplayLength') ?: $this->input->get('length') ?: 20);
        
        // Buscar termo de pesquisa
        $search = '';
        $search_param = $this->input->get('sSearch');
        if ($search_param !== false && $search_param !== null && $search_param !== '') {
            $search = $search_param;
        } else {
            $search_array = $this->input->get('search');
            if (is_array($search_array) && isset($search_array['value'])) {
                $search = $search_array['value'];
            }
        }
        
        // Total de registros sem filtro
        $recordsTotal = $this->clientes_model->count('clientes');
        
        // Buscar registros com paginação e filtro
        $results = $this->clientes_model->get('clientes', '*', $search, $length, $start);
        
        // Total de registros com filtro aplicado
        $recordsFiltered = $recordsTotal;
        if ($search) {
            $this->db->group_start();
            $this->db->like('nomeCliente', $search);
            $this->db->or_like('documento', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('telefone', $search);
            $this->db->or_like('celular', $search);
            $this->db->group_end();
            $this->db->from('clientes');
            $recordsFiltered = $this->db->count_all_results();
        }
        
        // Formatar dados para DataTables
        $data = [];
        if ($results) {
            foreach ($results as $r) {
                // Ações
                $acoes = '';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 5px" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                    $acoes .= '<a href="' . base_url() . 'index.php/mine?e=' . urlencode($r->email ?? '') . '" target="new" style="margin-right: 5px" class="btn-nwe2" title="Área do cliente"><i class="bx bx-key bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                    $acoes .= '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" style="margin-right: 5px" class="btn-nwe3" title="Editar Cliente"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
                    $acoes .= '<a href="#modal-excluir" role="button" data-toggle="modal" cliente="' . $r->idClientes . '" style="margin-right: 5px" class="btn-nwe4" title="Excluir Cliente"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                
                $data[] = [
                    $r->idClientes,
                    '<a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '">' . htmlspecialchars($r->nomeCliente, ENT_QUOTES, 'UTF-8') . '</a>',
                    htmlspecialchars($r->documento ?? '', ENT_QUOTES, 'UTF-8'),
                    $r->telefone ? htmlspecialchars($r->telefone, ENT_QUOTES, 'UTF-8') : '-',
                    $r->celular ? htmlspecialchars($r->celular, ENT_QUOTES, 'UTF-8') : '-',
                    $r->email ? htmlspecialchars($r->email, ENT_QUOTES, 'UTF-8') : '-',
                    $acoes
                ];
            }
        }
        
        // DataTables 1.9.4 usa formato diferente
        // sEcho deve ser retornado exatamente como foi enviado (pode ser string)
        $response = [
            'sEcho' => $sEcho, // Manter original, não converter para int
            'iTotalRecords' => intval($recordsTotal),
            'iTotalDisplayRecords' => intval($recordsFiltered),
            'aaData' => $data
        ];
        
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar clientes.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $email = set_value('email');
            $tipoCliente = $this->input->post('tipo_cliente');
            $documento = $this->input->post('documento') ?: ($this->input->post('documento_pf') ?: $this->input->post('documento_pj'));
            $documento_limpo = preg_replace('/[^a-zA-Z0-9]/', '', $documento);
            
            $errosCondicionais = validar_campos_condicionais($this->input->post(), $tipoCliente);

            if (!empty($errosCondicionais)) {
                $this->data['custom_error'] = '<div class="form_error"><p>' . implode('<br>', $errosCondicionais) . '</p></div>';
            } elseif ($documento_limpo && $this->clientes_model->documentoExists($documento_limpo)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este CPF/CNPJ já está cadastrado no sistema.</p></div>';
            } elseif ($email && $this->clientes_model->emailExists($email)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                try {
                    $data = $this->_montarDadosCliente();
                    $result = $this->clientes_model->add('clientes', $data);

                    if ($result !== false) {
                        $this->session->set_flashdata('success', 'Cliente adicionado com sucesso!');
                        log_info('Adicionou um cliente.');
                        redirect(site_url('clientes/'));
                    } else {
                        $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao adicionar o cliente.</p></div>';
                    }
                } catch (RuntimeException $exception) {
                    $this->data['custom_error'] = '<div class="form_error"><p>' . $exception->getMessage() . '</p></div>';
                }
            }
        }

        $this->data['view'] = 'clientes/adicionarCliente';

        return $this->layout();
    }

    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3)) || !$this->clientes_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Cliente não encontrado ou parâmetro inválido.');
            redirect('clientes/gerenciar');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar clientes.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $email = $this->input->post('email');
            $idCliente = $this->input->post('idClientes');
            $tipoCliente = $this->input->post('tipo_cliente');
            $documento = $this->input->post('documento') ?: ($this->input->post('documento_pf') ?: $this->input->post('documento_pj'));
            $documento_limpo = preg_replace('/[^a-zA-Z0-9]/', '', $documento);
            
            $errosCondicionais = validar_campos_condicionais($this->input->post(), $tipoCliente);

            if (!empty($errosCondicionais)) {
                $this->data['custom_error'] = '<div class="form_error"><p>' . implode('<br>', $errosCondicionais) . '</p></div>';
            } elseif ($documento_limpo && $this->clientes_model->documentoExists($documento_limpo, $idCliente)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este CPF/CNPJ já está cadastrado no sistema.</p></div>';
            } elseif ($email && $this->clientes_model->emailExists($email, $idCliente)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                try {
                    $clienteAtual = $this->clientes_model->getById($idCliente);
                    $data = $this->_montarDadosCliente($clienteAtual);

                    $result = $this->clientes_model->edit('clientes', $data, 'idClientes', $idCliente);

                    if ($result !== false) {
                        $this->session->set_flashdata('success', 'Cliente editado com sucesso!');
                        log_info('Alterou um cliente. ID' . $idCliente);
                        redirect(site_url('clientes/editar/') . $idCliente);
                    } else {
                        $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro ao editar o cliente.</p></div>';
                    }
                } catch (RuntimeException $exception) {
                    $this->data['custom_error'] = '<div class="form_error"><p>' . $exception->getMessage() . '</p></div>';
                }
            }
        }

        $cliente = $this->clientes_model->getById($this->uri->segment(3));
        $cliente = aplicar_mascaras_exibicao($cliente);

        $this->data['result'] = $cliente;
        $this->data['view'] = 'clientes/editarCliente';

        return $this->layout();
    }

    /**
     * Callback para validar se o documento é obrigatório
     * Usado em form_validation.php
     *
     * @param string $documento Documento a validar
     * @return bool True se válido, False caso contrário
     */
    public function validar_documento_obrigatorio($documento)
    {
        $documento_limpo = preg_replace('/[^a-zA-Z0-9]/', '', $documento);

        if (empty($documento_limpo)) {
            $this->form_validation->set_message('validar_documento_obrigatorio', 'O campo %s é obrigatório.');
            return false;
        }

        return true;
    }

    /**
     * Monta dados do cliente com uploads e normalizações
     *
     * @param object|null $clienteAtual
     * @return array
     *
     * @throws RuntimeException
     */
    private function _montarDadosCliente($clienteAtual = null)
    {
        $post_data = $this->input->post();
        $tipo_cliente = $post_data['tipo_cliente'] ?? ($clienteAtual->tipo_cliente ?? 'fisica');
        $senhaInformada = $this->input->post('senha');

        if ($clienteAtual === null && empty($senhaInformada)) {
            $senhaInformada = gerar_senha_padrao(
                $post_data['documento'] ?? $post_data['documento_pf'] ?? $post_data['documento_pj'] ?? ''
            );
        } elseif ($clienteAtual !== null && empty($senhaInformada)) {
            $senhaInformada = null;
        }

        $dados = preparar_dados_cliente($post_data, $tipo_cliente, $senhaInformada);

        if ($clienteAtual && $this->input->post('remover_foto')) {
            $this->cliente_upload->removerArquivoAntigo($clienteAtual->foto);
            $dados['foto'] = null;
        }

        if ($clienteAtual && $this->input->post('remover_documentos')) {
            $this->cliente_upload->removerArquivoAntigo($clienteAtual->documentos_adicionais);
            $dados['documentos_adicionais'] = null;
        }

        $foto = $this->cliente_upload->fazerUpload('fotoCliente', [
            'upload_path' => './assets/uploads/clientes/fotos/',
            'allowed_types' => 'gif|jpg|jpeg|png',
        ]);

        if (!$foto['success']) {
            throw new RuntimeException($foto['error']);
        }

        if (!empty($foto['relative_path'])) {
            if ($clienteAtual && !empty($clienteAtual->foto)) {
                $this->cliente_upload->removerArquivoAntigo($clienteAtual->foto);
            }
            $dados['foto'] = $foto['relative_path'];
        }

        $documentos = $this->cliente_upload->fazerUpload('documentosCliente', [
            'upload_path' => './assets/uploads/clientes/documentos/',
            'allowed_types' => 'pdf|doc|docx|zip|rar|jpg|jpeg|png',
            'max_size' => 5120,
        ]);

        if (!$documentos['success']) {
            throw new RuntimeException($documentos['error']);
        }

        if (!empty($documentos['relative_path'])) {
            if ($clienteAtual && !empty($clienteAtual->documentos_adicionais)) {
                $this->cliente_upload->removerArquivoAntigo($clienteAtual->documentos_adicionais);
            }
            $dados['documentos_adicionais'] = $documentos['relative_path'];
        }

        return $dados;
    }

    public function visualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('adv');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $clienteId = (int) $this->uri->segment(3);

        $this->data['custom_error'] = '';
        $this->data['result'] = $this->clientes_model->getById($clienteId);
        $this->data['results'] = $this->clientes_model->getOsByCliente($clienteId);
        $this->data['result_vendas'] = $this->clientes_model->getAllVendasByClient($clienteId);
        $this->data['logs_auditoria'] = [];
        $this->data['view'] = 'clientes/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir clientes.');
            redirect(base_url());
        }

        $id = $this->input->post('id');
        if ($id == null) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir cliente.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $os = $this->clientes_model->getAllOsByClient($id);
        if ($os != null) {
            $this->clientes_model->removeClientOs($os);
        }

        // excluindo Vendas vinculadas ao cliente
        $vendas = $this->clientes_model->getAllVendasByClient($id);
        if ($vendas != null) {
            $this->clientes_model->removeClientVendas($vendas);
        }

        $this->clientes_model->delete('clientes', 'idClientes', $id);
        log_info('Removeu um cliente. ID' . $id);

        $this->session->set_flashdata('success', 'Cliente excluido com sucesso!');
        redirect(site_url('clientes/gerenciar/'));
    }
}
