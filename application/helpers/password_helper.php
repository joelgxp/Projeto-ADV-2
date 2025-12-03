<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Valida senha forte: mínimo 8 caracteres com letras, números e caracteres especiais
 * 
 * @param string $senha
 * @return array ['valido' => bool, 'erros' => array]
 */
if (!function_exists('validar_senha_forte')) {
    function validar_senha_forte($senha)
    {
        $erros = [];
        
        // Verificar comprimento mínimo
        if (strlen($senha) < 8) {
            $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
        }
        
        // Verificar se tem pelo menos uma letra
        if (!preg_match('/[a-zA-Z]/', $senha)) {
            $erros[] = 'A senha deve conter pelo menos uma letra.';
        }
        
        // Verificar se tem pelo menos um número
        if (!preg_match('/[0-9]/', $senha)) {
            $erros[] = 'A senha deve conter pelo menos um número.';
        }
        
        // Verificar se tem pelo menos um caractere especial
        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) {
            $erros[] = 'A senha deve conter pelo menos um caractere especial (!@#$%^&*()_+-=[]{}|;:,.<>?).';
        }
        
        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
}

/**
 * Callback para validação de senha no CodeIgniter
 * 
 * @param string $senha
 * @return bool
 */
if (!function_exists('callback_validar_senha_forte')) {
    function callback_validar_senha_forte($senha)
    {
        $validacao = validar_senha_forte($senha);
        
        if (!$validacao['valido']) {
            $CI = &get_instance();
            $CI->form_validation->set_message('callback_validar_senha_forte', implode(' ', $validacao['erros']));
            return false;
        }
        
        return true;
    }
}

