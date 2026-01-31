<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Helper de Timezone (RN 12.2)
 * 
 * Converte timestamps entre UTC (armazenado no BD) e timezone do usuário
 */

/**
 * Obtém timezone padrão do sistema (Brasil/São Paulo)
 * 
 * @return string
 */
if (!function_exists('get_timezone_padrao')) {
    function get_timezone_padrao()
    {
        return 'America/Sao_Paulo';
    }
}

/**
 * Obtém timezone do usuário logado ou padrão
 * 
 * @return string
 */
if (!function_exists('get_timezone_usuario')) {
    function get_timezone_usuario()
    {
        $CI = &get_instance();
        
        // Tenta obter timezone do usuário logado
        if ($CI->session->userdata('timezone')) {
            return $CI->session->userdata('timezone');
        }
        
        // Tenta obter do sistema/configuração
        $CI->load->model('sistema_model');
        $config = $CI->sistema_model->get('sistema', '*', ['idSistema' => 1], 1, 0, true);
        
        if ($config && isset($config->timezone) && !empty($config->timezone)) {
            return $config->timezone;
        }
        
        // Retorna padrão
        return get_timezone_padrao();
    }
}

/**
 * Converte timestamp UTC para timezone do usuário
 * 
 * @param string|DateTime $data_utc Data em UTC (formato Y-m-d H:i:s ou DateTime)
 * @param string|null $timezone_destino Timezone de destino (null = timezone do usuário)
 * @param string $formato Formato de saída (padrão: 'd/m/Y H:i:s')
 * @return string
 */
if (!function_exists('converter_utc_para_timezone')) {
    function converter_utc_para_timezone($data_utc, $timezone_destino = null, $formato = 'd/m/Y H:i:s')
    {
        if (empty($data_utc)) {
            return '';
        }
        
        if ($timezone_destino === null) {
            $timezone_destino = get_timezone_usuario();
        }
        
        try {
            // Cria objeto DateTime em UTC
            if ($data_utc instanceof DateTime) {
                $dt = clone $data_utc;
                $dt->setTimezone(new DateTimeZone('UTC'));
            } else {
                $dt = new DateTime($data_utc, new DateTimeZone('UTC'));
            }
            
            // Converte para timezone de destino
            $dt->setTimezone(new DateTimeZone($timezone_destino));
            
            return $dt->format($formato);
        } catch (Exception $e) {
            log_message('error', 'Erro ao converter timezone: ' . $e->getMessage());
            return $data_utc;
        }
    }
}

/**
 * Converte timestamp do timezone do usuário para UTC
 * 
 * @param string|DateTime $data_local Data no timezone local (formato Y-m-d H:i:s ou DateTime)
 * @param string|null $timezone_origem Timezone de origem (null = timezone do usuário)
 * @param string $formato Formato de saída (padrão: 'Y-m-d H:i:s')
 * @return string
 */
if (!function_exists('converter_timezone_para_utc')) {
    function converter_timezone_para_utc($data_local, $timezone_origem = null, $formato = 'Y-m-d H:i:s')
    {
        if (empty($data_local)) {
            return '';
        }
        
        if ($timezone_origem === null) {
            $timezone_origem = get_timezone_usuario();
        }
        
        try {
            // Cria objeto DateTime no timezone de origem
            if ($data_local instanceof DateTime) {
                $dt = clone $data_local;
            } else {
                $dt = new DateTime($data_local, new DateTimeZone($timezone_origem));
            }
            
            // Converte para UTC
            $dt->setTimezone(new DateTimeZone('UTC'));
            
            return $dt->format($formato);
        } catch (Exception $e) {
            log_message('error', 'Erro ao converter timezone para UTC: ' . $e->getMessage());
            return $data_local;
        }
    }
}

/**
 * Obtém timestamp atual em UTC
 * 
 * @param string $formato Formato de saída (padrão: 'Y-m-d H:i:s')
 * @return string
 */
if (!function_exists('agora_utc')) {
    function agora_utc($formato = 'Y-m-d H:i:s')
    {
        $dt = new DateTime('now', new DateTimeZone('UTC'));
        return $dt->format($formato);
    }
}

/**
 * Obtém timestamp atual no timezone do usuário
 * 
 * @param string $formato Formato de saída (padrão: 'd/m/Y H:i:s')
 * @return string
 */
if (!function_exists('agora_timezone_usuario')) {
    function agora_timezone_usuario($formato = 'd/m/Y H:i:s')
    {
        $timezone = get_timezone_usuario();
        $dt = new DateTime('now', new DateTimeZone($timezone));
        return $dt->format($formato);
    }
}

/**
 * Formata data para exibição (converte UTC para timezone do usuário)
 * 
 * @param string $data_utc Data em UTC
 * @param string $formato Formato de saída
 * @return string
 */
if (!function_exists('formatar_data_exibicao')) {
    function formatar_data_exibicao($data_utc, $formato = 'd/m/Y H:i:s')
    {
        return converter_utc_para_timezone($data_utc, null, $formato);
    }
}

