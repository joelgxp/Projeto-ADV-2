<?php

use Piggly\Pix\Parser;

if (! function_exists('multiplica_cnpj')) {
    function multiplica_cnpj($cnpj, $posicao = 5)
    {
        // Variável para o cálculo
        $calculo = 0;

        // Laço para percorrer os item do cnpj
        for ($i = 0; $i < strlen($cnpj); $i++) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ($cnpj[$i] * $posicao);

            // Decrementa a posição a cada volta do laço
            $posicao--;

            // Se a posição for menor que 2, ela se torna 9
            if ($posicao < 2) {
                $posicao = 9;
            }
        }

        // Retorna o cálculo
        return $calculo;
    }
}

if (! function_exists('valid_cnpj')) {
    function valid_cnpj($cnpj)
    {
        // Deixa o CNPJ com apenas números
        // $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Garante que o CNPJ é uma string
        //$cnpj = (string) $cnpj;

        // O valor original
        $cnpj_original = $cnpj;

        // Captura os primeiros 12 números do CNPJ
        $primeiros_numeros_cnpj = substr($cnpj, 0, 12);

        // Faz o primeiro cálculo
        $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ($primeiro_calculo % 11) < 2 ? 0 : 11 - ($primeiro_calculo % 11);

        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
        $segundo_digito = ($segundo_calculo % 11) < 2 ? 0 : 11 - ($segundo_calculo % 11);

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('valid_cpf')) {
    function valid_cpf($cpf)
    {
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

if (! function_exists('verific_cpf_cnpj')) {
    function verific_cpf_cnpj($cpfCnpjValor)
    {
        // Remove tudo que não for letra ou número
        $cpfCnpj = preg_replace('/[^a-zA-Z0-9]/', '', $cpfCnpjValor);
        $cpfCnpj = (string) $cpfCnpj;

        // CPF
        if (strlen($cpfCnpj) === 11 && ctype_digit($cpfCnpj)) {
            return valid_cpf($cpfCnpj);
        }

        // CNPJ tradicional
        if (strlen($cpfCnpj) === 14 && ctype_digit($cpfCnpj)) {
            return valid_cnpj($cpfCnpj);
        }

        // Novo CNPJ alfanumérico: 14 caracteres, letras e números
        if (strlen($cpfCnpj) === 14 && preg_match('/^[A-Z0-9]{14}$/i', $cpfCnpj)) {
            // Aqui você pode implementar uma validação mais avançada se desejar
            // Por enquanto, apenas aceita o formato
            return true;
        }

        return false;
    }
}

if (! function_exists('verific_cpf')) {
    /**
     * Valida apenas CPF (11 dígitos numéricos)
     * Não aceita CNPJ
     * 
     * @param string $cpfValor
     * @return bool
     */
    function verific_cpf($cpfValor)
    {
        // Remove tudo que não for número
        $cpf = preg_replace('/[^0-9]/', '', $cpfValor);
        $cpf = (string) $cpf;

        // CPF deve ter exatamente 11 dígitos
        if (strlen($cpf) !== 11 || !ctype_digit($cpf)) {
            return false;
        }

        // Validar CPF
        return valid_cpf($cpf);
    }
}

if (! function_exists('unique')) {
    function unique($value, $params)
    {
        $CI = &get_instance();
        $CI->load->database();

        $CI->form_validation->set_message('unique', 'O campo %s já está cadastrado.');

        $parts = explode('.', $params);
        $table = $parts[0] ?? '';
        $field = $parts[1] ?? '';
        $current_id = $parts[2] ?? '';
        $key = $parts[3] ?? 'id';

        // Se não há valor, não validar
        if (empty($value)) {
            return true;
        }

        $query = $CI->db->select()->from($table)->where($field, $value)->limit(1)->get();

        // Se encontrou um registro
        if ($query->num_rows() > 0) {
            $row = $query->row();
            // Se está editando e o ID é o mesmo, permitir
            if (!empty($current_id) && isset($row->{$key}) && $row->{$key} == $current_id) {
                return true;
            }
            // Se está criando ou editando com ID diferente, não permitir
            return false;
        }

        // Se não encontrou registro, permitir
        return true;
    }
}

if (! function_exists('valid_pix_key')) {
    function valid_pix_key($value)
    {
        if (Parser::validateDocument($value)) {
            return true;
        }

        if (Parser::validateEmail($value)) {
            return true;
        }

        if (Parser::validatePhone($value)) {
            return true;
        }

        if (Parser::validateRandom($value)) {
            return true;
        }

        return false;
    }
}

/**
 * Determina o tipo de pessoa (física ou jurídica) baseado no documento
 * 
 * @param string $documento Documento (CPF/CNPJ) com ou sem formatação
 * @param string $tipo_pessoa_indicado Tipo de pessoa indicado manualmente (opcional)
 * @return array ['tipo' => 'fisica'|'juridica', 'pessoa_fisica' => bool, 'documento_limpo' => string]
 */
if (! function_exists('determinar_tipo_pessoa')) {
    function determinar_tipo_pessoa($documento, $tipo_pessoa_indicado = null)
    {
        // Remove formatação do documento
        $documento_limpo = preg_replace('/[^0-9]/', '', $documento);
        
        // Se tipo foi indicado manualmente, usar ele
        if ($tipo_pessoa_indicado === 'fisica' || $tipo_pessoa_indicado === 'juridica') {
            return [
                'tipo' => $tipo_pessoa_indicado,
                'pessoa_fisica' => $tipo_pessoa_indicado === 'fisica',
                'documento_limpo' => $documento_limpo
            ];
        }
        
        // Determinar automaticamente pelo tamanho do documento
        if (strlen($documento_limpo) == 11) {
            return [
                'tipo' => 'fisica',
                'pessoa_fisica' => true,
                'documento_limpo' => $documento_limpo
            ];
        } elseif (strlen($documento_limpo) == 14) {
            return [
                'tipo' => 'juridica',
                'pessoa_fisica' => false,
                'documento_limpo' => $documento_limpo
            ];
        }
        
        // Padrão: pessoa física
        return [
            'tipo' => 'fisica',
            'pessoa_fisica' => true,
            'documento_limpo' => $documento_limpo
        ];
    }
}