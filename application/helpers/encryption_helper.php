<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper de Criptografia - Fase 8: Criptografia de Dados Sensíveis
 * Funções para criptografar/descriptografar dados sensíveis
 */

if (!function_exists('encrypt_sensitive')) {
    /**
     * Criptografa dado sensível
     * 
     * @param string $data Dado a criptografar
     * @return string|false Dado criptografado ou false em caso de erro
     */
    function encrypt_sensitive($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        $ci = &get_instance();
        $ci->load->library('encryption');
        
        // Usar chave de criptografia do config ou gerar uma
        $key = $ci->config->item('encryption_key');
        if (empty($key)) {
            log_message('error', 'Chave de criptografia não configurada');
            return false;
        }
        
        $ci->encryption->initialize([
            'cipher' => 'aes-256',
            'mode' => 'cbc',
            'key' => $key
        ]);
        
        return $ci->encryption->encrypt($data);
    }
}

if (!function_exists('decrypt_sensitive')) {
    /**
     * Descriptografa dado sensível
     * 
     * @param string $encrypted_data Dado criptografado
     * @return string|false Dado descriptografado ou false em caso de erro
     */
    function decrypt_sensitive($encrypted_data)
    {
        if (empty($encrypted_data)) {
            return $encrypted_data;
        }
        
        $ci = &get_instance();
        $ci->load->library('encryption');
        
        $key = $ci->config->item('encryption_key');
        if (empty($key)) {
            log_message('error', 'Chave de criptografia não configurada');
            return false;
        }
        
        $ci->encryption->initialize([
            'cipher' => 'aes-256',
            'mode' => 'cbc',
            'key' => $key
        ]);
        
        return $ci->encryption->decrypt($encrypted_data);
    }
}

if (!function_exists('mask_sensitive_data')) {
    /**
     * Mascara dado sensível para exibição
     * 
     * @param string $data Dado a mascarar
     * @param string $type Tipo (cpf, cnpj, email, telefone)
     * @return string Dado mascarado
     */
    function mask_sensitive_data($data, $type = 'cpf')
    {
        if (empty($data)) {
            return '';
        }
        
        $data = preg_replace('/[^0-9]/', '', $data);
        
        switch ($type) {
            case 'cpf':
                if (strlen($data) == 11) {
                    return substr($data, 0, 3) . '.***.***-' . substr($data, -2);
                }
                break;
            case 'cnpj':
                if (strlen($data) == 14) {
                    return substr($data, 0, 2) . '.***.***/****-' . substr($data, -2);
                }
                break;
            case 'email':
                $parts = explode('@', $data);
                if (count($parts) == 2) {
                    $name = $parts[0];
                    $domain = $parts[1];
                    $masked_name = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 4)) . substr($name, -2);
                    return $masked_name . '@' . $domain;
                }
                break;
            case 'telefone':
                if (strlen($data) >= 10) {
                    return substr($data, 0, 2) . ' ****-****';
                }
                break;
        }
        
        return str_repeat('*', min(strlen($data), 10));
    }
}

