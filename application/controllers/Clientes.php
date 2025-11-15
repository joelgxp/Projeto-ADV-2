<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Clientes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('clientes_model');
        $this->load->model('processos_model');
        $this->load->model('Audit_model');
        $this->load->helper(['rbac', 'audit']);
        $this->data['menuClientes'] = 'clientes';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $pesquisa = $this->input->get('pesquisa');

        $this->load->library('pagination');

        $this->data['configuration']['base_url'] = site_url('clientes/gerenciar/');
        $this->data['configuration']['total_rows'] = $this->clientes_model->count('clientes');
        if($pesquisa) {
            $this->data['configuration']['suffix'] = "?pesquisa={$pesquisa}";
            $this->data['configuration']['first_url'] = base_url("index.php/clientes")."\?pesquisa={$pesquisa}";
        }

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->clientes_model->get('clientes', '*', $pesquisa, $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'clientes/clientes';

        return $this->layout();
    }

    public function adicionar()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar clientes.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        $senhaCliente = $this->input->post('senha') ? $this->input->post('senha') : preg_replace('/[^\p{L}\p{N}\s]/', '', set_value('documento'));

        $cpf_cnpj = preg_replace('/[^\p{L}\p{N}\s]/', '', set_value('documento'));

        if (strlen($cpf_cnpj) == 11) {
            $pessoa_fisica = true;
        } else {
            $pessoa_fisica = false;
        }

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $email = set_value('email');
            if ($email && $this->clientes_model->emailExists($email)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                $data = [
                'nomeCliente' => set_value('nomeCliente'),
                'contato' => set_value('contato'),
                'pessoa_fisica' => $pessoa_fisica,
                'documento' => set_value('documento'),
                'telefone' => set_value('telefone'),
                'celular' => set_value('celular'),
                'email' => set_value('email'),
                'senha' => password_hash($senhaCliente, PASSWORD_DEFAULT),
                'rua' => set_value('rua'),
                'numero' => set_value('numero'),
                'complemento' => set_value('complemento'),
                'bairro' => set_value('bairro'),
                'cidade' => set_value('cidade'),
                'estado' => set_value('estado'),
                'cep' => set_value('cep'),
                'dataCadastro' => date('Y-m-d'),
                'fornecedor' => $this->input->post('fornecedor') ? 1 : 0,
                'planos_id' => $this->input->post('planos_id') ? intval($this->input->post('planos_id')) : null,
            ];

            if ($this->clientes_model->add('clientes', $data) == true) {
                $this->session->set_flashdata('success', 'Cliente adicionado com sucesso!');
                log_info('Adicionou um cliente.');
                redirect(site_url('clientes/'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
            }
        }

        $this->data['view'] = 'clientes/adicionarCliente';

        return $this->layout();
    }

    public function editar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3)) || ! $this->clientes_model->getById($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Cliente não encontrado ou parâmetro inválido.');
            redirect('clientes/gerenciar');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar clientes.');
            redirect(base_url());
        }

        // Verificar permissão para editar dados sensíveis
        $can_edit_sensitive = can_edit_sensitive_data($this->session->userdata('permissao'));
        
        // Registrar acesso à edição
        $cliente_id = $this->uri->segment(3);
        log_cliente_access($cliente_id, 'editar', $can_edit_sensitive);

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            
            $email = $this->input->post('email');
            $idCliente = $this->input->post('idClientes');
            if ($email && $this->clientes_model->emailExists($email, $idCliente)) {
                $this->data['custom_error'] = '<div class="form_error"><p>Este e-mail já está sendo utilizado por outro cliente.</p></div>';
            } else {
                // Buscar dados atuais do cliente para auditoria
                $cliente_atual = $this->clientes_model->getById($idCliente);
                
                // Campos sensíveis que precisam de permissão especial
                $campos_sensiveis = ['rg', 'documento', 'filiacao', 'email', 'telefone', 'celular', 'razao_social', 'inscricao_estadual', 'inscricao_municipal', 'representantes_legais', 'socios'];
                
                $senha = $this->input->post('senha');
            if ($senha != null) {
                $senha = password_hash($senha, PASSWORD_DEFAULT);

                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'contato' => $this->input->post('contato'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'senha' => $senha,
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'fornecedor' => (set_value('fornecedor') == true ? 1 : 0),
                    'planos_id' => $this->input->post('planos_id') ? intval($this->input->post('planos_id')) : null,
                ];
                
                // Adicionar campos sensíveis apenas se tiver permissão
                if ($can_edit_sensitive) {
                    $data['documento'] = $this->input->post('documento');
                    $data['email'] = $this->input->post('email');
                    if ($this->input->post('rg')) {
                        $data['rg'] = $this->input->post('rg');
                    }
                    if ($this->input->post('filiacao')) {
                        $data['filiacao'] = $this->input->post('filiacao');
                    }
                } else {
                    // Manter valores originais se não tiver permissão
                    if ($cliente_atual) {
                        $data['documento'] = $cliente_atual->documento;
                        $data['email'] = $cliente_atual->email;
                        if (isset($cliente_atual->rg)) {
                            $data['rg'] = $cliente_atual->rg;
                        }
                        if (isset($cliente_atual->filiacao)) {
                            $data['filiacao'] = $cliente_atual->filiacao;
                        }
                    }
                }
            } else {
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'contato' => $this->input->post('contato'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'fornecedor' => (set_value('fornecedor') == true ? 1 : 0),
                    'planos_id' => $this->input->post('planos_id') ? intval($this->input->post('planos_id')) : null,
                ];
                
                // Adicionar campos sensíveis apenas se tiver permissão
                if ($can_edit_sensitive) {
                    $data['documento'] = $this->input->post('documento');
                    $data['email'] = $this->input->post('email');
                    if ($this->input->post('rg')) {
                        $data['rg'] = $this->input->post('rg');
                    }
                    if ($this->input->post('filiacao')) {
                        $data['filiacao'] = $this->input->post('filiacao');
                    }
                } else {
                    // Manter valores originais se não tiver permissão
                    if ($cliente_atual) {
                        $data['documento'] = $cliente_atual->documento;
                        $data['email'] = $cliente_atual->email;
                        if (isset($cliente_atual->rg)) {
                            $data['rg'] = $cliente_atual->rg;
                        }
                        if (isset($cliente_atual->filiacao)) {
                            $data['filiacao'] = $cliente_atual->filiacao;
                        }
                    }
                }
            }

            // Registrar alterações em auditoria antes de salvar
            if ($cliente_atual) {
                foreach ($data as $campo => $valor_novo) {
                    $valor_anterior = isset($cliente_atual->$campo) ? $cliente_atual->$campo : '';
                    if ($valor_anterior != $valor_novo && in_array($campo, $campos_sensiveis)) {
                        log_cliente_edit($idCliente, $campo, $valor_anterior, $valor_novo);
                    }
                }
            }

            if ($this->clientes_model->edit('clientes', $data, 'idClientes', $this->input->post('idClientes')) == true) {
                $this->session->set_flashdata('success', 'Cliente editado com sucesso!');
                log_info('Alterou um cliente. ID' . $this->input->post('idClientes'));
                redirect(site_url('clientes/editar/') . $this->input->post('idClientes'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro</p></div>';
            }
            }
        }

        $this->data['result'] = $this->clientes_model->getById($this->uri->segment(3));
        $this->data['view'] = 'clientes/editarCliente';

        return $this->layout();
    }

    public function visualizar()
    {
        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('adv');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar clientes.');
            redirect(base_url());
        }

        $cliente_id = $this->uri->segment(3);
        $cliente = $this->clientes_model->getById($cliente_id);
        
        if (!$cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado.');
            redirect('clientes/gerenciar');
        }

        // Verificar se acessou dados sensíveis
        $can_view_sensitive = can_view_sensitive_data($this->session->userdata('permissao'));
        
        // Registrar acesso em auditoria
        log_cliente_access($cliente_id, 'visualizar', $can_view_sensitive);

        // Aplicar máscaras em dados sensíveis se não tiver permissão
        if (!$can_view_sensitive) {
            if (!empty($cliente->documento)) {
                $doc_limpo = preg_replace('/[^0-9]/', '', $cliente->documento);
                if (strlen($doc_limpo) == 11) {
                    $cliente->documento = mask_sensitive_data($cliente->documento, 'cpf');
                } elseif (strlen($doc_limpo) == 14) {
                    $cliente->documento = mask_sensitive_data($cliente->documento, 'cnpj');
                }
            }
            if (!empty($cliente->rg)) {
                $cliente->rg = mask_sensitive_data($cliente->rg, 'rg');
            }
            if (!empty($cliente->email)) {
                $cliente->email = mask_sensitive_data($cliente->email, 'email');
            }
            if (!empty($cliente->telefone)) {
                $cliente->telefone = mask_sensitive_data($cliente->telefone, 'telefone');
            }
            if (!empty($cliente->celular)) {
                $cliente->celular = mask_sensitive_data($cliente->celular, 'celular');
            }
        }

        // Buscar processos do cliente com filtros
        $filters = [
            'tipo_processo' => $this->input->get('tipo_processo'),
            'status' => $this->input->get('status'),
            'comarca' => $this->input->get('comarca'),
            'usuarios_id' => $this->input->get('advogado'),
        ];

        // Remover filtros vazios
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });

        $this->load->library('pagination');
        
        // Construir query string para filtros
        $query_string = http_build_query($filters);
        $suffix = $query_string ? '?' . $query_string : '';
        
        $this->data['configuration']['base_url'] = site_url('clientes/visualizar/' . $cliente_id . '/');
        $this->data['configuration']['suffix'] = $suffix;
        $this->data['configuration']['first_url'] = site_url('clientes/visualizar/' . $cliente_id) . $suffix;
        
        // Contar processos com filtros aplicados
        $total_processos = count($this->processos_model->getByClienteWithFilters($cliente_id, $filters, 1000, 0));
        $this->data['configuration']['total_rows'] = $total_processos;
        
        $this->pagination->initialize($this->data['configuration']);
        
        $perpage = $this->data['configuration']['per_page'];
        $start = $this->uri->segment(4) ?: 0;
        
        $this->data['processos'] = $this->processos_model->getByClienteWithFilters($cliente_id, $filters, $perpage, $start);
        $this->data['pagination'] = $this->pagination->create_links();
        
        // Buscar prazos do cliente
        $this->load->model('prazos_model');
        $this->data['prazos'] = $this->prazos_model->getPrazosByCliente($cliente_id, 10, 0);
        
        // Buscar audiências do cliente
        $this->load->model('audiencias_model');
        $this->data['audiencias'] = $this->audiencias_model->getAudienciasByCliente($cliente_id, 10, 0);
        
        // Buscar logs de auditoria do cliente
        $this->data['logs_auditoria'] = $this->Audit_model->get_by_entity('cliente', $cliente_id, 10);

        $this->data['custom_error'] = '';
        $this->data['result'] = $cliente;
        $this->data['can_view_sensitive'] = $can_view_sensitive;
        $this->data['can_view_processos'] = can_view_cliente_processos($this->session->userdata('permissao'));
        $this->data['can_view_documentos'] = can_view_cliente_documentos($this->session->userdata('permissao'));
        $this->data['can_view_financeiro'] = can_view_cliente_financeiro($this->session->userdata('permissao'));
        $this->data['view'] = 'clientes/visualizar';

        return $this->layout();
    }

    public function excluir()
    {
        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
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
