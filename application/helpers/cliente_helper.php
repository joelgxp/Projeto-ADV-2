<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Helper para preparação e manipulação de dados de clientes
 */

if (! function_exists('normalizar_tipo_cliente')) {
    /**
     * Normaliza o tipo de cliente para um dos valores aceitos
     *
     * @param string|null $tipo Tipo informado
     * @return string
     */
    function normalizar_tipo_cliente($tipo = null)
    {
        $tipo = strtolower(trim((string) $tipo));
        $permitidos = ['fisica', 'juridica'];

        if (! in_array($tipo, $permitidos, true)) {
            return 'fisica';
        }

        return $tipo;
    }
}

if (! function_exists('normalizar_documento')) {
    /**
     * Remove caracteres especiais do documento e mantém letras/números
     *
     * @param string|null $documento
     * @return string
     */
    function normalizar_documento($documento)
    {
        if ($documento === null) {
            return '';
        }

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $documento));
    }
}

if (! function_exists('formatar_documento')) {
    /**
     * Aplica máscara para CPF ou CNPJ (inclui formato alfanumérico)
     *
     * @param string $documento Documento sem máscara
     * @return string
     */
    function formatar_documento($documento)
    {
        $doc = normalizar_documento($documento);

        if (strlen($doc) === 11 && ctype_digit($doc)) {
            return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9, 2);
        }

        if (strlen($doc) === 14) {
            $parte1 = substr($doc, 0, 2);
            $parte2 = substr($doc, 2, 3);
            $parte3 = substr($doc, 5, 3);
            $parte4 = substr($doc, 8, 4);
            $parte5 = substr($doc, 12, 2);

            return "{$parte1}.{$parte2}.{$parte3}/{$parte4}-{$parte5}";
        }

        return $documento;
    }
}

if (! function_exists('normalizar_data')) {
    /**
     * Converte datas em string para o formato Y-m-d
     *
     * @param string|null $valor
     * @return string|null
     */
    function normalizar_data($valor)
    {
        if (empty($valor)) {
            return null;
        }

        $valor = str_replace('/', '-', $valor);
        $timestamp = strtotime($valor);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }
}

if (! function_exists('preparar_dados_cliente')) {
    /**
     * Prepara e normaliza dados do POST para inserção/atualização de cliente
     *
     * @param array $post_data Dados do POST
     * @param string|null $tipo_cliente Tipo de cliente informado (fisica|juridica)
     * @param string|null $senha Senha a ser usada (null para não alterar)
     * @return array Dados preparados para o banco
     */
    function preparar_dados_cliente($post_data, $tipo_cliente = null, $senha = null)
    {
        $tipo_cliente = normalizar_tipo_cliente($post_data['tipo_cliente'] ?? $tipo_cliente);

        $documento = normalizar_documento(
            $post_data['documento'] ??
            ($tipo_cliente === 'juridica' ? ($post_data['documento_pj'] ?? '') : ($post_data['documento_pf'] ?? ''))
        );

        $pessoa_fisica = $tipo_cliente === 'juridica' ? 0 : 1;

        $data = [
            'nomeCliente' => trim($post_data['nomeCliente'] ?? ''),
            'contato' => trim($post_data['contato'] ?? ''),
            'pessoa_fisica' => $pessoa_fisica,
            'tipo_cliente' => $tipo_cliente,
            'documento' => $documento,
            'telefone' => trim($post_data['telefone'] ?? ''),
            'celular' => trim($post_data['celular'] ?? ''),
            'email' => strtolower(trim($post_data['email'] ?? '')),
            'redes_sociais' => trim($post_data['redes_sociais'] ?? ''),
            'rua' => trim($post_data['rua'] ?? ''),
            'numero' => trim($post_data['numero'] ?? ''),
            'complemento' => trim($post_data['complemento'] ?? ''),
            'bairro' => trim($post_data['bairro'] ?? ''),
            'cidade' => trim($post_data['cidade'] ?? ''),
            'estado' => trim($post_data['estado'] ?? ''),
            'cep' => trim($post_data['cep'] ?? ''),
            'observacoes' => trim($post_data['observacoes'] ?? ''),
            'observacoes_juridicas' => trim($post_data['observacoes_juridicas'] ?? ''),
        ];

        // Campos PF / Advogado
        if ($pessoa_fisica) {
            $data['rg'] = trim($post_data['rg'] ?? '');
            $data['data_nascimento'] = normalizar_data($post_data['data_nascimento'] ?? null);
            $data['estado_civil'] = trim($post_data['estado_civil'] ?? '');
            $data['nacionalidade'] = trim($post_data['nacionalidade'] ?? '');
            $data['profissao'] = trim($post_data['profissao'] ?? '');
            $data['nome_mae'] = trim($post_data['nome_mae'] ?? '');
            $data['nome_pai'] = trim($post_data['nome_pai'] ?? '');
            $data['dependentes'] = trim($post_data['dependentes'] ?? '');
        } else {
            $data['rg'] = null;
            $data['data_nascimento'] = null;
            $data['estado_civil'] = null;
            $data['nacionalidade'] = null;
            $data['profissao'] = null;
            $data['nome_mae'] = null;
            $data['nome_pai'] = null;
            $data['dependentes'] = null;
        }

        // Campos PJ
        if ($tipo_cliente === 'juridica') {
            $data['razao_social'] = trim($post_data['razao_social'] ?? '');
            $data['nome_fantasia'] = trim($post_data['nome_fantasia'] ?? '');
            $data['inscricao_estadual'] = trim($post_data['inscricao_estadual'] ?? '');
            $data['inscricao_municipal'] = trim($post_data['inscricao_municipal'] ?? '');
            $data['data_constituicao'] = normalizar_data($post_data['data_constituicao'] ?? null);
            $data['cnae'] = trim($post_data['cnae'] ?? '');
            $data['ramo_atividade'] = trim($post_data['ramo_atividade'] ?? '');
            $data['representantes_legais'] = trim($post_data['representantes_legais'] ?? '');
            $data['socios'] = trim($post_data['socios'] ?? '');
        } else {
            $data['razao_social'] = null;
            $data['nome_fantasia'] = null;
            $data['inscricao_estadual'] = null;
            $data['inscricao_municipal'] = null;
            $data['data_constituicao'] = null;
            $data['cnae'] = null;
            $data['ramo_atividade'] = null;
            $data['representantes_legais'] = null;
            $data['socios'] = null;
        }

        // Adicionar senha se fornecida
        if ($senha !== null && $senha !== '') {
            $data['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        // Adicionar data de cadastro apenas para novos clientes
        if (! isset($post_data['idClientes'])) {
            $data['dataCadastro'] = date('Y-m-d');
        }

        return filtrar_campos_cliente($data);
    }
}

if (! function_exists('aplicar_mascaras_exibicao')) {
    /**
     * Aplica máscaras para exibição de dados do cliente
     *
     * @param object $cliente Objeto cliente do banco
     * @return object Cliente com máscaras aplicadas
     */
    function aplicar_mascaras_exibicao($cliente)
    {
        if (! is_object($cliente)) {
            return $cliente;
        }

        // Documento formatado
        if (isset($cliente->documento) && $cliente->documento) {
            $cliente->documento_raw = normalizar_documento($cliente->documento);
            $cliente->documento = formatar_documento($cliente->documento_raw);

            if (! empty($cliente->pessoa_fisica) || (isset($cliente->tipo_cliente) && $cliente->tipo_cliente !== 'juridica')) {
                $cliente->documento_pf = $cliente->documento;
                $cliente->documento_pj = '';
            } else {
                $cliente->documento_pj = $cliente->documento;
                $cliente->documento_pf = '';
            }
        }

        // Aplicar máscara de telefone se necessário
        if (isset($cliente->telefone) && $cliente->telefone) {
            $cliente->telefone = formatar_telefone($cliente->telefone);
        }

        if (isset($cliente->celular) && $cliente->celular) {
            $cliente->celular = formatar_telefone($cliente->celular);
        }

        // CEP já deve estar formatado, mas garantir
        if (isset($cliente->cep) && $cliente->cep) {
            $cep_limpo = preg_replace('/[^0-9]/', '', $cliente->cep);
            if (strlen($cep_limpo) == 8) {
                $cliente->cep = substr($cep_limpo, 0, 5) . '-' . substr($cep_limpo, 5);
            }
        }

        return $cliente;
    }
}

if (! function_exists('formatar_telefone')) {
    /**
     * Formata telefone para exibição
     *
     * @param string $telefone Telefone sem formatação
     * @return string Telefone formatado
     */
    function formatar_telefone($telefone)
    {
        $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($telefone_limpo) == 10) {
            return '(' . substr($telefone_limpo, 0, 2) . ') ' . substr($telefone_limpo, 2, 4) . '-' . substr($telefone_limpo, 6);
        } elseif (strlen($telefone_limpo) == 11) {
            return '(' . substr($telefone_limpo, 0, 2) . ') ' . substr($telefone_limpo, 2, 5) . '-' . substr($telefone_limpo, 7);
        }
        
        return $telefone;
    }
}

if (! function_exists('validar_campos_condicionais')) {
    /**
     * Valida campos condicionais baseado no tipo de cliente
     *
     * @param array $data Dados a validar
     * @param string|null $tipo_cliente Tipo informado
     * @return array Lista de mensagens de erro
     */
    function validar_campos_condicionais($data, $tipo_cliente = null)
    {
        $errors = [];
        $tipo = normalizar_tipo_cliente($tipo_cliente ?? ($data['tipo_cliente'] ?? null));

        $documento_pf = normalizar_documento($data['documento_pf'] ?? '');
        $documento_pj = normalizar_documento($data['documento_pj'] ?? '');
        $documento_hidden = normalizar_documento($data['documento'] ?? '');

        if ($tipo === 'juridica') {
            if (empty(trim($data['razao_social'] ?? ''))) {
                $errors[] = 'Razão social é obrigatória para pessoa jurídica.';
            }

            if (empty($documento_pj) && empty($documento_hidden)) {
                $errors[] = 'Informe um CNPJ válido para pessoa jurídica.';
            }
        } else {
            if (empty(trim($data['nomeCliente'] ?? ''))) {
                $errors[] = 'Nome completo é obrigatório para pessoa física.';
            }

            if (empty($documento_pf) && empty($documento_hidden)) {
                $errors[] = 'Informe um CPF válido para pessoa física.';
            }
        }

        return $errors;
    }
}

if (! function_exists('filtrar_campos_cliente')) {
    /**
     * Remove chaves não existentes na tabela clientes para evitar erros de SQL
     *
     * @param array $data
     * @return array
     */
    function filtrar_campos_cliente(array $data)
    {
        static $camposPermitidos = null;

        if ($camposPermitidos === null) {
            $CI = &get_instance();
            $CI->load->database();
            $camposPermitidos = array_flip($CI->db->list_fields('clientes'));
        }

        return array_intersect_key($data, $camposPermitidos);
    }
}

if (! function_exists('gerar_senha_padrao')) {
    /**
     * Gera senha padrão baseada no documento do cliente
     *
     * @param string $documento Documento do cliente
     * @return string Senha gerada
     */
    function gerar_senha_padrao($documento)
    {
        $senha = normalizar_documento($documento);
        
        // Se documento vazio, gera senha aleatória
        if (empty($senha)) {
            $senha = bin2hex(random_bytes(8));
        }
        
        return $senha;
    }
}

if (! function_exists('registrar_interacao_cliente')) {
    /**
     * Registra uma interação com um cliente
     * 
     * RN 2.3: Histórico completo de interações (timestamps, quem, o quê mudou)
     *
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de interação (criacao, edicao, exclusao -> mapeado para 'outro'; reuniao, telefonema, email, nota)
     * @param string $descricao Descrição da interação ou o que mudou
     * @param array|null $dados_anteriores Dados anteriores (para edições) - armazenado na descricao
     * @param array|null $dados_novos Dados novos (para edições) - armazenado na descricao
     * @param string|null $nota Nota livre adicionada pelo usuário - armazenado na descricao
     * @return int|false ID da interação ou false em caso de erro
     */
    function registrar_interacao_cliente($cliente_id, $tipo, $descricao, $dados_anteriores = null, $dados_novos = null, $nota = null)
    {
        $ci = &get_instance();
        $ci->load->model('Interacoes_cliente_model');
        
        // Obter ID do usuário logado
        $usuario_id = null;
        if ($ci->session->userdata('idUsuarios')) {
            $usuario_id = $ci->session->userdata('idUsuarios');
        } elseif ($ci->session->userdata('cliente_id')) {
            // Se for cliente logado, não há usuarios_id
            $usuario_id = null;
        }
        
        // Mapear tipos para os valores permitidos na tabela (ENUM)
        $tipos_permitidos = ['reuniao', 'telefonema', 'email', 'nota', 'outro'];
        
        // Mapear tipos especiais para 'outro'
        $tipos_mapeados = [
            'criacao' => 'outro',
            'edicao' => 'outro',
            'exclusao' => 'outro',
            'reuniao' => 'reuniao',
            'telefone' => 'telefonema',
            'telefonema' => 'telefonema',
            'email' => 'email',
            'nota' => 'nota',
            'status' => 'outro',
        ];
        
        $tipo_mapeado = isset($tipos_mapeados[$tipo]) ? $tipos_mapeados[$tipo] : 'outro';
        
        // Montar título baseado no tipo
        $titulos = [
            'criacao' => 'Cliente Cadastrado',
            'edicao' => 'Cliente Editado',
            'exclusao' => 'Cliente Excluído',
            'reuniao' => 'Reunião',
            'telefonema' => 'Telefonema',
            'email' => 'E-mail',
            'nota' => 'Nota',
            'outro' => 'Interação',
        ];
        
        $titulo = isset($titulos[$tipo]) ? $titulos[$tipo] : 'Interação';
        
        // Montar descrição completa com detalhes
        $descricao_completa = $descricao;
        
        // Adicionar informações sobre dados alterados (para edições)
        if ($dados_anteriores !== null && $dados_novos !== null && !empty($dados_anteriores)) {
            $campos_alterados = array_keys($dados_anteriores);
            $descricao_completa .= "\n\nCampos alterados: " . implode(', ', $campos_alterados);
            
            // Adicionar resumo das alterações principais
            $alteracoes_detalhadas = [];
            foreach ($campos_alterados as $campo) {
                $valor_ant = $dados_anteriores[$campo] ?? 'N/A';
                $valor_nov = $dados_novos[$campo] ?? 'N/A';
                
                // Limitar tamanho dos valores para não ficar muito longo
                $valor_ant_str = is_string($valor_ant) ? substr($valor_ant, 0, 50) : (string)$valor_ant;
                $valor_nov_str = is_string($valor_nov) ? substr($valor_nov, 0, 50) : (string)$valor_nov;
                
                $alteracoes_detalhadas[] = "\n  • {$campo}: '{$valor_ant_str}' → '{$valor_nov_str}'";
            }
            
            if (!empty($alteracoes_detalhadas)) {
                $descricao_completa .= "\n" . implode('', $alteracoes_detalhadas);
            }
        }
        
        // Adicionar nota se fornecida
        if ($nota !== null && !empty($nota)) {
            $descricao_completa .= "\n\nNota: " . $nota;
        }
        
        // Adicionar informações do usuário e contexto
        $usuario_info = 'Sistema';
        if ($ci->session->userdata('nome_admin')) {
            $usuario_info = $ci->session->userdata('nome_admin');
        } elseif ($ci->session->userdata('nome')) {
            $usuario_info = $ci->session->userdata('nome');
        }
        
        $descricao_completa .= "\n\nUsuário: {$usuario_info}";
        $descricao_completa .= "\nIP: " . $ci->input->ip_address();
        
        $data = [
            'clientes_id' => $cliente_id,
            'usuarios_id' => $usuario_id,
            'tipo' => $tipo_mapeado,
            'titulo' => $titulo,
            'descricao' => $descricao_completa,
            'data_hora' => date('Y-m-d H:i:s'),
            'dataCadastro' => date('Y-m-d H:i:s'),
        ];
        
        return $ci->Interacoes_cliente_model->add($data);
    }
}
