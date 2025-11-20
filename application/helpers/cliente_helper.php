<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Helper para preparação e manipulação de dados de clientes
 */

if (! function_exists('preparar_dados_cliente')) {
    /**
     * Prepara e normaliza dados do POST para inserção/atualização de cliente
     *
     * @param array $post_data Dados do POST
     * @param bool|null $pessoa_fisica Se é pessoa física (null para auto-detectar)
     * @param string|null $senha Senha a ser usada (null para não alterar)
     * @return array Dados preparados para o banco
     */
    function preparar_dados_cliente($post_data, $pessoa_fisica = null, $senha = null)
    {
        // Auto-detectar tipo de pessoa se não informado
        if ($pessoa_fisica === null) {
            $documento_limpo = preg_replace('/[^\p{L}\p{N}\s]/', '', $post_data['documento'] ?? '');
            $pessoa_fisica = (strlen($documento_limpo) == 11);
        }

        $data = [
            'nomeCliente' => $post_data['nomeCliente'] ?? '',
            'contato' => $post_data['contato'] ?? '',
            'pessoa_fisica' => $pessoa_fisica ? 1 : 0,
            'documento' => $post_data['documento'] ?? '',
            'telefone' => $post_data['telefone'] ?? '',
            'celular' => $post_data['celular'] ?? '',
            'email' => $post_data['email'] ?? '',
            'rua' => $post_data['rua'] ?? '',
            'numero' => $post_data['numero'] ?? '',
            'complemento' => $post_data['complemento'] ?? '',
            'bairro' => $post_data['bairro'] ?? '',
            'cidade' => $post_data['cidade'] ?? '',
            'estado' => $post_data['estado'] ?? '',
            'cep' => $post_data['cep'] ?? '',
            'fornecedor' => isset($post_data['fornecedor']) && $post_data['fornecedor'] ? 1 : 0,
        ];

        // Adicionar senha se fornecida
        if ($senha !== null && $senha !== '') {
            $data['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        // Adicionar data de cadastro apenas para novos clientes
        if (! isset($post_data['idClientes'])) {
            $data['dataCadastro'] = date('Y-m-d');
        }

        return $data;
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
     * Valida campos condicionais baseado no tipo de pessoa
     *
     * @param array $data Dados a validar
     * @param bool $pessoa_fisica Se é pessoa física
     * @return array Array vazio se válido, array com erros caso contrário
     */
    function validar_campos_condicionais($data, $pessoa_fisica)
    {
        $errors = [];

        // Validações específicas podem ser adicionadas aqui
        // Por exemplo, se pessoa física precisa de RG, etc.

        return $errors;
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
        // Remove caracteres especiais do documento
        $senha = preg_replace('/[^\p{L}\p{N}\s]/', '', $documento);
        
        // Se documento vazio, gera senha aleatória
        if (empty($senha)) {
            $senha = bin2hex(random_bytes(8));
        }
        
        return $senha;
    }
}
