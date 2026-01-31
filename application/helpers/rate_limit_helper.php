<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Helper de Rate Limiting (RN 12.3)
 * 
 * Limita requisições por usuário/IP (100 req/min por usuário)
 */

/**
 * Verifica e aplica rate limiting
 * 
 * @param int $limite Limite de requisições por minuto (padrão: 100)
 * @param string|null $identificador Identificador único (IP + user_id, ou null para auto)
 * @return array ['permitido' => bool, 'mensagem' => string, 'tentativas_restantes' => int]
 */
if (!function_exists('verificar_rate_limit')) {
    function verificar_rate_limit($limite = 100, $identificador = null)
    {
        $CI = &get_instance();
        $CI->load->database();
        
        // Gera identificador único (IP + user_id)
        if ($identificador === null) {
            $ip = $CI->input->ip_address();
            $user_id = $CI->session->userdata('id_admin') ?? 'anonimo';
            $identificador = md5($ip . '_' . $user_id);
        }
        
        // Verifica se tabela existe
        if (!$CI->db->table_exists('rate_limits')) {
            // Tabela não existe - retorna permitido (não bloqueia)
            // Tabela será criada quando script SQL for executado
            return [
                'permitido' => true,
                'mensagem' => 'Rate limiting não configurado (tabela não existe)',
                'tentativas_restantes' => 100
            ];
        }
        
        $agora = time();
        $janela_tempo = 60; // 1 minuto em segundos
        
        // Remove registros antigos (mais de 1 minuto)
        $CI->db->where('timestamp <', $agora - $janela_tempo);
        $CI->db->delete('rate_limits');
        
        // Conta requisições na janela de tempo
        $CI->db->where('identificador', $identificador);
        $CI->db->where('timestamp >=', $agora - $janela_tempo);
        $count = $CI->db->count_all_results('rate_limits');
        
        // Verifica se excedeu o limite
        if ($count >= $limite) {
            // Registra tentativa bloqueada
            $CI->db->insert('rate_limits', [
                'identificador' => $identificador,
                'timestamp' => $agora,
                'bloqueado' => 1
            ]);
            
            return [
                'permitido' => false,
                'mensagem' => 'Limite de requisições excedido. Tente novamente em alguns instantes.',
                'tentativas_restantes' => 0
            ];
        }
        
        // Registra requisição permitida
        $CI->db->insert('rate_limits', [
            'identificador' => $identificador,
            'timestamp' => $agora,
            'bloqueado' => 0
        ]);
        
        return [
            'permitido' => true,
            'mensagem' => 'Requisição permitida',
            'tentativas_restantes' => $limite - $count - 1
        ];
    }
}

/**
 * Cria tabela de rate limits se não existir
 * 
 * @param object $db Instância do database
 */
if (!function_exists('criar_tabela_rate_limits')) {
    function criar_tabela_rate_limits($db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `rate_limits` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `identificador` VARCHAR(64) NOT NULL,
            `timestamp` INT(11) NOT NULL,
            `bloqueado` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `idx_identificador_timestamp` (`identificador`, `timestamp`),
            KEY `idx_timestamp` (`timestamp`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->query($sql);
    }
}

/**
 * Limpa registros antigos de rate limits
 * 
 * @param int $tempo_limite Tempo em segundos para manter registros (padrão: 3600 = 1 hora)
 */
if (!function_exists('limpar_rate_limits_antigos')) {
    function limpar_rate_limits_antigos($tempo_limite = 3600)
    {
        $CI = &get_instance();
        $CI->load->database();
        
        if (!$CI->db->table_exists('rate_limits')) {
            return;
        }
        
        $agora = time();
        $CI->db->where('timestamp <', $agora - $tempo_limite);
        $CI->db->delete('rate_limits');
    }
}

