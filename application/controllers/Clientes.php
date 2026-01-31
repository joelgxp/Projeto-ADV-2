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
        $this->load->helper('form'); // Helper necessário para form_hidden() na view
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
        // Parâmetro $array = 'object' para retornar objetos (view espera objetos, não arrays)
        $this->data['results'] = $this->clientes_model->get('clientes', '*', $pesquisa, $this->data['configuration']['per_page'], 0, false, 'object');

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
                        // RN 2.3: Registrar interação de criação
                        registrar_interacao_cliente(
                            $result,
                            'criacao',
                            'Cliente cadastrado no sistema',
                            null,
                            $data
                        );
                        
                        // Enviar email de boas-vindas se houver email cadastrado (RN 9.1)
                        $clienteEmail = isset($data['email']) && !empty($data['email']) ? $data['email'] : null;
                        if ($clienteEmail) {
                            $this->load->helper('email_template');
                            $cliente_data = $this->clientes_model->getById($result);
                            
                            if ($cliente_data) {
                                $dados_template = [
                                    'cliente' => $cliente_data,
                                ];
                                
                                // Tentar obter link de acesso se existir
                                $this->db->where('clientes_id', $result);
                                $this->db->where('ativo', 1);
                                $acesso = $this->db->get('acessos_cliente')->row();
                                if ($acesso) {
                                    $dados_template['link_acesso'] = site_url('mine/acesso/' . $acesso->token_acesso);
                                }
                                
                                $email_id = enqueue_email_with_template(
                                    $clienteEmail,
                                    'Bem-vindo ao Sistema de Gestão Jurídica',
                                    'boas_vindas',
                                    $dados_template,
                                    'normal'
                                );
                                
                                if ($email_id) {
                                    log_message('info', "Email de boas-vindas adicionado à fila para cliente ID: {$result}, Email: {$clienteEmail}");
                                    
                                    // Criar notificação (RN 9.2)
                                    $this->load->helper('email_template');
                                    enviar_notificacao_email(
                                        null,
                                        $result,
                                        'sistema',
                                        'Bem-vindo ao Sistema',
                                        'Seu cadastro foi realizado com sucesso. Bem-vindo ao nosso sistema!',
                                        site_url('mine/painel'),
                                        'boas_vindas',
                                        $dados_template
                                    );
                                } else {
                                    log_message('warning', "Email de boas-vindas NÃO foi adicionado à fila para cliente ID: {$result}, Email: {$clienteEmail}");
                                }
                            }
                        }
                        
                        $this->session->set_flashdata('success', 'Cliente adicionado com sucesso!');
                        
                        // Auditoria: Registrar criação (RN 8.1)
                        $this->load->helper('audit');
                        $cliente_data = $this->clientes_model->getById($result);
                        if ($cliente_data) {
                            $dados_novos = [
                                'idClientes' => $cliente_data->idClientes ?? null,
                                'nomeCliente' => $cliente_data->nomeCliente ?? '',
                                'tipo_cliente' => $cliente_data->tipo_cliente ?? 'fisica',
                                'documento' => $cliente_data->documento ?? '',
                                'email' => $cliente_data->email ?? '',
                            ];
                            log_create('cliente', $result, $dados_novos);
                            
                            // Histórico de alterações (RN 8.5)
                            $this->load->model('Historico_alteracoes_model');
                            $this->Historico_alteracoes_model->registrar('clientes', $result, 'create', [], $dados_novos);
                        }
                        
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

                    // RN 2.3: Preparar dados para histórico (comparar antes de salvar)
                    $camposAlterados = [];
                    if ($clienteAtual) {
                        foreach ($data as $campo => $valorNovo) {
                            $valorAnterior = isset($clienteAtual->$campo) ? $clienteAtual->$campo : null;
                            
                            // Converter para string para comparação
                            $valorAnteriorStr = is_array($valorAnterior) ? json_encode($valorAnterior) : (string)$valorAnterior;
                            $valorNovoStr = is_array($valorNovo) ? json_encode($valorNovo) : (string)$valorNovo;
                            
                            if ($valorAnteriorStr !== $valorNovoStr) {
                                $camposAlterados[$campo] = [
                                    'anterior' => $valorAnterior,
                                    'novo' => $valorNovo
                                ];
                            }
                        }
                    }

                    $result = $this->clientes_model->edit('clientes', $data, 'idClientes', $idCliente);

                    if ($result !== false) {
                        // RN 2.3: Registrar interação de edição com o que mudou
                        if (!empty($camposAlterados)) {
                            $descricao = 'Cliente editado. Campos alterados: ' . implode(', ', array_keys($camposAlterados));
                            registrar_interacao_cliente(
                                $idCliente,
                                'edicao',
                                $descricao,
                                array_map(function($v) { return $v['anterior']; }, $camposAlterados),
                                array_map(function($v) { return $v['novo']; }, $camposAlterados)
                            );
                        } else {
                            registrar_interacao_cliente(
                                $idCliente,
                                'edicao',
                                'Cliente editado (sem alterações detectadas)'
                            );
                        }
                        
                        // Auditoria: Registrar atualização (RN 8.1)
                        $this->load->helper('audit');
                        $cliente_anterior = $this->clientes_model->getById($idCliente);
                        if ($cliente_anterior) {
                            // Obter dados anteriores
                            $dados_anteriores = [
                                'nomeCliente' => $cliente_anterior->nomeCliente,
                                'tipo_cliente' => $cliente_anterior->tipo_cliente ?? 'fisica',
                                'documento' => $cliente_anterior->documento ?? '',
                                'email' => $cliente_anterior->email ?? '',
                            ];
                            
                            // Obter dados novos (do POST)
                            $dados_novos = [
                                'nomeCliente' => $this->input->post('nomeCliente'),
                                'tipo_cliente' => $this->input->post('tipo_cliente'),
                                'documento' => $this->input->post('documento'),
                                'email' => $this->input->post('email'),
                            ];
                            
                            log_update('cliente', $idCliente, $dados_anteriores, $dados_novos);
                            
                            // Histórico de alterações (RN 8.5)
                            $this->load->model('Historico_alteracoes_model');
                            $this->Historico_alteracoes_model->registrar('clientes', $idCliente, 'update', $dados_anteriores, $dados_novos);
                        }
                        
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
        
        // RN 2.3: Carregar histórico de interações do cliente
        $this->load->model('Interacoes_cliente_model');
        $this->data['interacoes'] = $this->Interacoes_cliente_model->getByCliente($clienteId, null, 50);
        
        // RN 6.1: Carregar informações de acesso do cliente (Fase 6)
        $this->load->model('Acessos_cliente_model');
        $acessoAtivo = $this->Acessos_cliente_model->getAcessoAtivoByCliente($clienteId);
        
        // Preparar dados do acesso para a view
        if ($acessoAtivo) {
            $dataExpiracao = strtotime($acessoAtivo->data_expiracao);
            $dataAtual = time();
            $diasRestantes = floor(($dataExpiracao - $dataAtual) / 86400);
            $estaExpirado = $dataExpiracao < $dataAtual;
            
            $this->data['acesso_ativo'] = $acessoAtivo;
            $this->data['acesso_dias_restantes'] = $diasRestantes;
            $this->data['acesso_expirado'] = $estaExpirado;
            $this->data['link_acesso_completo'] = base_url('index.php/mine/acesso/' . $acessoAtivo->token_acesso);
        } else {
            $this->data['acesso_ativo'] = null;
            $this->data['acesso_dias_restantes'] = 0;
            $this->data['acesso_expirado'] = false;
            $this->data['link_acesso_completo'] = null;
        }
        
        $this->data['acessos'] = $this->Acessos_cliente_model->getByCliente($clienteId);
        
        $this->data['view'] = 'clientes/visualizar';

        return $this->layout();
    }

    /**
     * Gerar link de acesso único para cliente (Fase 6 - RN 6.1)
     */
    public function gerarLinkAcesso()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para gerar link de acesso.');
            redirect(base_url());
        }

        $clienteId = $this->input->post('cliente_id');
        
        if (!$clienteId || !is_numeric($clienteId)) {
            $this->session->set_flashdata('error', 'ID do cliente inválido.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $cliente = $this->clientes_model->getById($clienteId);
        if (!$cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $this->load->model('Acessos_cliente_model');
        
        // Criar novo acesso (desativa anteriores automaticamente)
        $acesso = $this->Acessos_cliente_model->criarAcesso($clienteId, $_SERVER['REMOTE_ADDR'] ?? null);
        
        if ($acesso) {
            // Enviar e-mail com o link
            $linkEnviado = $this->enviarEmailLinkAcesso($cliente, $acesso['token'], $acesso['data_expiracao']);
            
            if ($linkEnviado) {
                $this->session->set_flashdata('success', 'Link de acesso gerado e enviado por e-mail com sucesso!');
                log_info("Link de acesso gerado para cliente ID: {$clienteId}");
            } else {
                $this->session->set_flashdata('success', 'Link de acesso gerado com sucesso, porém houve erro ao enviar o e-mail. Você pode copiar o link manualmente.');
            }
        } else {
            $this->session->set_flashdata('error', 'Erro ao gerar link de acesso.');
        }

        redirect(site_url('clientes/visualizar/' . $clienteId));
    }

    /**
     * Renovar link de acesso (prorrogar por mais 365 dias) - Fase 6
     */
    public function renovarLinkAcesso()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para renovar link de acesso.');
            redirect(base_url());
        }

        $clienteId = $this->input->post('cliente_id');
        $acessoId = $this->input->post('acesso_id');
        
        if (!$clienteId || !is_numeric($clienteId)) {
            $this->session->set_flashdata('error', 'ID do cliente inválido.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $cliente = $this->clientes_model->getById($clienteId);
        if (!$cliente) {
            $this->session->set_flashdata('error', 'Cliente não encontrado.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $this->load->model('Acessos_cliente_model');
        
        if ($acessoId && is_numeric($acessoId)) {
            // Renovar acesso existente
            $renovado = $this->Acessos_cliente_model->renovarAcesso($acessoId);
            
            if ($renovado) {
                $acesso = $this->Acessos_cliente_model->getById($acessoId);
                if ($acesso) {
                    // Enviar e-mail com o novo link
                    $linkEnviado = $this->enviarEmailLinkAcesso($cliente, $acesso->token_acesso, $acesso->data_expiracao);
                    
                    if ($linkEnviado) {
                        $this->session->set_flashdata('success', 'Link de acesso renovado e enviado por e-mail com sucesso!');
                    } else {
                        $this->session->set_flashdata('success', 'Link de acesso renovado com sucesso, porém houve erro ao enviar o e-mail.');
                    }
                }
            } else {
                $this->session->set_flashdata('error', 'Erro ao renovar link de acesso.');
            }
        } else {
            // Criar novo acesso se não houver ID
            $acesso = $this->Acessos_cliente_model->criarAcesso($clienteId, $_SERVER['REMOTE_ADDR'] ?? null);
            
            if ($acesso) {
                $linkEnviado = $this->enviarEmailLinkAcesso($cliente, $acesso['token'], $acesso['data_expiracao']);
                
                if ($linkEnviado) {
                    $this->session->set_flashdata('success', 'Link de acesso gerado e enviado por e-mail com sucesso!');
                } else {
                    $this->session->set_flashdata('success', 'Link de acesso gerado, porém houve erro ao enviar o e-mail.');
                }
            } else {
                $this->session->set_flashdata('error', 'Erro ao gerar link de acesso.');
            }
        }

        redirect(site_url('clientes/visualizar/' . $clienteId));
    }

    /**
     * Desativar link de acesso - Fase 6
     */
    public function desativarLinkAcesso()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para desativar link de acesso.');
            redirect(base_url());
        }

        $clienteId = $this->input->post('cliente_id');
        
        if (!$clienteId || !is_numeric($clienteId)) {
            $this->session->set_flashdata('error', 'ID do cliente inválido.');
            redirect(site_url('clientes/gerenciar/'));
        }

        $this->load->model('Acessos_cliente_model');
        
        // Desativar todos os acessos do cliente
        $desativado = $this->Acessos_cliente_model->desativarAcessosByCliente($clienteId);
        
        if ($desativado) {
            $this->session->set_flashdata('success', 'Link de acesso desativado com sucesso!');
            log_info("Link de acesso desativado para cliente ID: {$clienteId}");
        } else {
            $this->session->set_flashdata('error', 'Erro ao desativar link de acesso.');
        }

        redirect(site_url('clientes/visualizar/' . $clienteId));
    }

    /**
     * Enviar e-mail com link de acesso - Fase 6
     */
    private function enviarEmailLinkAcesso($cliente, $token, $dataExpiracao)
    {
        if (empty($cliente->email)) {
            log_message('warning', 'Cliente ID: ' . $cliente->idClientes . ' não possui e-mail cadastrado para envio de link de acesso.');
            return false;
        }

        $this->load->model('sistema_model');
        $this->load->model('email_model');
        
        $dados = [];
        $dados['emitente'] = $this->sistema_model->getEmitente();
        $dados['cliente'] = $cliente;
        $dados['token'] = $token;
        $dados['link_acesso'] = base_url('index.php/mine/acesso/' . $token);
        $dados['data_expiracao'] = $dataExpiracao;
        $dados['data_expiracao_formatada'] = date('d/m/Y', strtotime($dataExpiracao));
        
        $emitente = $dados['emitente'];
        
        if ($emitente == null) {
            log_message('error', 'Emitente não configurado. Não foi possível enviar email de link de acesso.');
            return false;
        }
        
        // Carregar template de e-mail
        $html = $this->load->view('emails/link_acesso_cliente', $dados, true);
        
        $assunto = 'Link de Acesso ao Portal - ' . $this->config->item('app_name');
        
        $email_data = [
            'to' => $cliente->email,
            'subject' => $assunto,
            'message' => $html,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($this->email_model->add('email_queue', $email_data)) {
            log_message('info', "Email de link de acesso adicionado à fila para: {$cliente->email} (Cliente ID: {$cliente->idClientes})");
            return true;
        } else {
            log_message('error', "Falha ao adicionar email de link de acesso à fila para: {$cliente->email}");
            return false;
        }
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

        // RN 2.3: Registrar interação de exclusão ANTES de deletar
        $clienteExcluido = $this->clientes_model->getById($id);
        if ($clienteExcluido) {
            registrar_interacao_cliente(
                $id,
                'exclusao',
                'Cliente excluído do sistema',
                (array)$clienteExcluido,
                null
            );
        }

        // Auditoria: Registrar exclusão (RN 8.1) - ANTES de deletar
        $this->load->helper('audit');
        if ($clienteExcluido) {
            $dados_anteriores = [
                'idClientes' => $clienteExcluido->idClientes,
                'nomeCliente' => $clienteExcluido->nomeCliente,
                'tipo_cliente' => $clienteExcluido->tipo_cliente ?? 'fisica',
                'documento' => $clienteExcluido->documento ?? '',
                'email' => $clienteExcluido->email ?? '',
            ];
            log_delete('cliente', $id, $dados_anteriores);
            
            // Histórico de alterações (RN 8.5)
            $this->load->model('Historico_alteracoes_model');
            $this->Historico_alteracoes_model->registrar('clientes', $id, 'delete', $dados_anteriores, []);
        }

        $this->clientes_model->delete('clientes', 'idClientes', $id);
        log_info('Removeu um cliente. ID' . $id);

        $this->session->set_flashdata('success', 'Cliente excluido com sucesso!');
        redirect(site_url('clientes/gerenciar/'));
    }

    /**
     * Envia email de boas-vindas para novo cliente
     * Usa o mesmo padrão do reset de senha (envio direto)
     * 
     * @param int $idClientes ID do cliente
     * @return bool True se enviado com sucesso, False caso contrário
     */
    private function enviarEmailBoasVindas($idClientes)
    {
        log_message('info', '=== INÍCIO enviarEmailBoasVindas ===');
        log_message('info', 'ID Cliente: ' . $idClientes);

        $this->load->model('sistema_model');
        $this->load->model('clientes_model');

        $cliente = $this->clientes_model->getById($idClientes);
        
        if (!$cliente || empty($cliente->email)) {
            log_message('error', 'Cliente não encontrado ou sem email. ID: ' . $idClientes);
            return false;
        }

        $dados = [];
        $dados['emitente'] = $this->sistema_model->getEmitente();
        $dados['cliente'] = $cliente;
        
        log_message('info', 'Emitente: ' . ($dados['emitente'] ? 'Encontrado' : 'NULL'));
        log_message('info', 'Cliente: ' . $cliente->nomeCliente . ' - Email: ' . $cliente->email);

        if ($dados['emitente'] == null) {
            log_message('error', 'Emitente não configurado!');
            return false;
        }

        // Tentar carregar view de email de boas-vindas
        // Se não encontrar, usar HTML básico
        $html = null;
        $view_paths = [
            'emails/cliente_novo',
            'conecte/emails/boas_vindas'
        ];
        
        foreach ($view_paths as $view_path) {
            // Verificar se o arquivo existe antes de tentar carregar
            $view_file = APPPATH . 'views/' . $view_path . '.php';
            if (file_exists($view_file)) {
                try {
                    $html = $this->load->view($view_path, $dados, true);
                    log_message('info', 'View de email encontrada e carregada: ' . $view_path);
                    break;
                } catch (Exception $e) {
                    log_message('debug', 'Erro ao carregar view: ' . $view_path . ' - ' . $e->getMessage());
                    continue;
                }
            } else {
                log_message('debug', 'View não encontrada (arquivo não existe): ' . $view_path);
            }
        }
        
        // Se não encontrou view, criar HTML básico
        if (!$html) {
            log_message('info', 'Nenhuma view de email encontrada, criando HTML básico');
            $app_name = $this->config->item('app_name') ?: 'Sistema';
            $nome_emitente = $dados['emitente']->nome ?? 'Equipe';
            
            // HTML básico de boas-vindas usando o mesmo estilo do clientenovasenha
            $html = '
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .invoice-box {
            max-width: 1100px;
            margin: auto;
            padding: 10px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="details">
                <td colspan="4" style="text-align: left">
                    <h2>Bem-vindo ao ' . htmlspecialchars($app_name) . '!</h2>
                </td>
            </tr>
            <tr class="details">
                <td colspan="4" style="text-align: left">
                    Caro(a) <b>' . htmlspecialchars($cliente->nomeCliente) . '</b>,
                    <br><br>
                    Seu cadastro foi realizado com sucesso no nosso sistema.
                    <br><br>
                    Você já pode acessar o portal do cliente com suas credenciais de acesso.
                    <br><br>
                </td>
            </tr>
            <tr class="details">
                <td colspan="4" style="text-align: left">
                    Um abraço!<br>
                    Equipe ' . htmlspecialchars($nome_emitente) . '
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
        }

        // Enviar email diretamente (mesmo padrão do testarEmail e reset de senha)
        $this->load->library('email');
        $this->load->config('email');
        $config = $this->config->item('email');
        $smtp_user = $this->config->item('smtp_user');
        $app_name = $this->config->item('app_name') ?: 'Sistema';

        if (empty($smtp_user)) {
            log_message('error', 'Erro: E-mail remetente não configurado. EMAIL_SMTP_USER não está definido.');
            return false;
        }

        // Inicializar email com configurações
        if ($config) {
            $this->email->initialize($config);
        }

        // Configurar remetente e destinatário
        $this->email->clear();
        $this->email->from($smtp_user, $app_name);
        $this->email->to($cliente->email);
        $this->email->subject('Bem-vindo ao ' . $app_name . '!');
        $this->email->message($html);

        log_message('info', 'Tentando enviar email de boas-vindas para: ' . $cliente->email . ' (usando remetente: ' . $smtp_user . ')');

        // Enviar diretamente
        if ($this->email->send(true)) {
            log_message('info', '✅ Email de boas-vindas enviado com sucesso para: ' . $cliente->email);

            // Adicionar à fila para registro/auditoria
            $this->load->model('email_model');
            $email_data = [
                'to' => $cliente->email,
                'subject' => 'Bem-vindo ao ' . $app_name . '!',
                'message' => $html,
                'status' => 'sent',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->email_model->add('email_queue', $email_data);

            log_message('info', '=== FIM enviarEmailBoasVindas (SUCESSO) ===');
            return true;
        } else {
            $error_msg = $this->email->print_debugger();
            log_message('error', '❌ Erro ao enviar email de boas-vindas para ' . $cliente->email . ': ' . $error_msg);

            // Adicionar à fila como failed para tentar depois
            $this->load->model('email_model');
            $email_data = [
                'to' => $cliente->email,
                'subject' => 'Bem-vindo ao ' . $app_name . '!',
                'message' => $html,
                'status' => 'failed',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $this->email_model->add('email_queue', $email_data);

            log_message('info', '=== FIM enviarEmailBoasVindas (ERRO) ===');
            return false;
        }
    }
}
