<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Hook de Rate Limiting (RN 12.3)
 * 
 * Aplica rate limiting globalmente antes de processar requisições
 */
class Rate_limit
{
    public function aplicar()
    {
        $CI = &get_instance();
        $CI->load->helper('rate_limit');
        
        // Exclui algumas rotas do rate limiting
        $rotas_excluidas = ['login', 'api', 'backups/download'];
        $rota_atual = $CI->uri->segment(1);
        
        if (in_array($rota_atual, $rotas_excluidas)) {
            return;
        }
        
        $rate_limit = verificar_rate_limit(100);
        
        if (!$rate_limit['permitido']) {
            $CI->output
                ->set_status_header(429, 'Too Many Requests')
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'erro' => true,
                    'mensagem' => $rate_limit['mensagem']
                ]));
            exit;
        }
    }
}

