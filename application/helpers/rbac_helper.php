<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('can_view_sensitive_data')) {
    /**
     * Verifica se o usuário pode visualizar dados sensíveis
     *
     * @param mixed $user_permission ID da permissão do usuário ou array de permissões
     * @return bool
     */
    function can_view_sensitive_data($user_permission)
    {
        $ci = &get_instance();
        $ci->load->library('permission');
        
        // Se for admin, sempre permitir
        if (is_string($user_permission) && (strtolower($user_permission) === 'admin' || strtolower($user_permission) === 'administrador')) {
            return true;
        }
        
        return $ci->permission->checkPermission($user_permission, 'vClienteDadosSensiveis');
    }
}

if (!function_exists('can_edit_sensitive_data')) {
    /**
     * Verifica se o usuário pode editar dados sensíveis
     *
     * @param mixed $user_permission ID da permissão do usuário ou array de permissões
     * @return bool
     */
    function can_edit_sensitive_data($user_permission)
    {
        $ci = &get_instance();
        $ci->load->library('permission');
        
        // Se for admin, sempre permitir
        if (is_string($user_permission) && (strtolower($user_permission) === 'admin' || strtolower($user_permission) === 'administrador')) {
            return true;
        }
        
        return $ci->permission->checkPermission($user_permission, 'eClienteDadosSensiveis');
    }
}

if (!function_exists('mask_sensitive_data')) {
    /**
     * Mascara dados sensíveis conforme o tipo
     *
     * @param string $data Dado a ser mascarado
     * @param string $type Tipo de dado (cpf, rg, email, telefone, cnpj)
     * @return string Dado mascarado
     */
    function mask_sensitive_data($data, $type = 'cpf')
    {
        if (empty($data)) {
            return '';
        }
        
        // Remove formatação
        $clean = preg_replace('/[^0-9]/', '', $data);
        
        switch (strtolower($type)) {
            case 'cpf':
                if (strlen($clean) == 11) {
                    return substr($clean, 0, 3) . '.' . substr($clean, 3, 3) . '.***-' . substr($clean, 9, 2);
                }
                return $data;
                
            case 'cnpj':
                if (strlen($clean) == 14) {
                    return substr($clean, 0, 2) . '.' . substr($clean, 2, 3) . '.' . substr($clean, 5, 3) . '/****-' . substr($clean, 12, 2);
                }
                return $data;
                
            case 'rg':
                // RG pode ter formatos variados, tenta mascarar os últimos dígitos
                if (strlen($clean) >= 7) {
                    $visible = substr($clean, 0, 3);
                    $hidden = str_repeat('*', min(4, strlen($clean) - 3));
                    $last = substr($clean, -1);
                    return $visible . '.' . $hidden . '-' . $last;
                }
                return str_repeat('*', strlen($data));
                
            case 'email':
                if (strpos($data, '@') !== false) {
                    list($user, $domain) = explode('@', $data);
                    if (strlen($user) > 2) {
                        $masked_user = substr($user, 0, 2) . str_repeat('*', max(3, strlen($user) - 2));
                    } else {
                        $masked_user = str_repeat('*', strlen($user));
                    }
                    return $masked_user . '@' . $domain;
                }
                return str_repeat('*', strlen($data));
                
            case 'telefone':
            case 'celular':
                if (strlen($clean) >= 10) {
                    $ddd = substr($clean, 0, 2);
                    $prefix = substr($clean, 2, 4);
                    $suffix = substr($clean, -4);
                    return '(' . $ddd . ') ' . $prefix . '-****';
                }
                return str_repeat('*', strlen($data));
                
            default:
                // Para outros tipos, mascarar parcialmente
                if (strlen($data) > 4) {
                    return substr($data, 0, 2) . str_repeat('*', strlen($data) - 4) . substr($data, -2);
                }
                return str_repeat('*', strlen($data));
        }
    }
}

if (!function_exists('can_view_cliente_processos')) {
    /**
     * Verifica se o usuário pode visualizar processos do cliente
     *
     * @param mixed $user_permission ID da permissão do usuário
     * @return bool
     */
    function can_view_cliente_processos($user_permission)
    {
        $ci = &get_instance();
        $ci->load->library('permission');
        
        // Se for admin, sempre permitir
        if (is_string($user_permission) && (strtolower($user_permission) === 'admin' || strtolower($user_permission) === 'administrador')) {
            return true;
        }
        
        return $ci->permission->checkPermission($user_permission, 'vClienteProcessos');
    }
}

if (!function_exists('can_view_cliente_documentos')) {
    /**
     * Verifica se o usuário pode visualizar documentos do cliente
     *
     * @param mixed $user_permission ID da permissão do usuário
     * @return bool
     */
    function can_view_cliente_documentos($user_permission)
    {
        $ci = &get_instance();
        $ci->load->library('permission');
        
        // Se for admin, sempre permitir
        if (is_string($user_permission) && (strtolower($user_permission) === 'admin' || strtolower($user_permission) === 'administrador')) {
            return true;
        }
        
        return $ci->permission->checkPermission($user_permission, 'vClienteDocumentos');
    }
}

if (!function_exists('can_view_cliente_financeiro')) {
    /**
     * Verifica se o usuário pode visualizar dados financeiros do cliente
     *
     * @param mixed $user_permission ID da permissão do usuário
     * @return bool
     */
    function can_view_cliente_financeiro($user_permission)
    {
        $ci = &get_instance();
        $ci->load->library('permission');
        
        // Se for admin, sempre permitir
        if (is_string($user_permission) && (strtolower($user_permission) === 'admin' || strtolower($user_permission) === 'administrador')) {
            return true;
        }
        
        return $ci->permission->checkPermission($user_permission, 'vClienteFinanceiro');
    }
}

