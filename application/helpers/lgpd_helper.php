<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper LGPD - Fase 8: Conformidade LGPD
 * Funções para gerenciar consentimentos e solicitações LGPD
 */

if (!function_exists('verificar_consentimento')) {
    /**
     * Verifica se cliente tem consentimento para um tipo específico
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de consentimento (tratamento_dados, comunicacao, marketing)
     * @return bool
     */
    function verificar_consentimento($cliente_id, $tipo)
    {
        $ci = &get_instance();
        $ci->load->model('Consentimentos_lgpd_model');
        return $ci->Consentimentos_lgpd_model->temConsentimento($cliente_id, $tipo);
    }
}

if (!function_exists('registrar_consentimento')) {
    /**
     * Registra consentimento do cliente
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de consentimento
     * @param bool $consentido Se consentiu
     * @return bool|int ID do registro ou false
     */
    function registrar_consentimento($cliente_id, $tipo, $consentido)
    {
        $ci = &get_instance();
        $ci->load->model('Consentimentos_lgpd_model');
        $result = $ci->Consentimentos_lgpd_model->registrarConsentimento($cliente_id, $tipo, $consentido);
        
        // Registrar na auditoria
        if ($result) {
            $ci->load->helper('audit');
            log_info("Consentimento LGPD registrado: Cliente #{$cliente_id}, Tipo: {$tipo}, Consentido: " . ($consentido ? 'Sim' : 'Não'));
        }
        
        return $result;
    }
}

if (!function_exists('criar_solicitacao_lgpd')) {
    /**
     * Cria nova solicitação LGPD
     * 
     * @param int $cliente_id ID do cliente
     * @param string $tipo Tipo de solicitação (esquecimento, portabilidade, acesso, retificacao, revogacao)
     * @param string $descricao Descrição da solicitação
     * @return int|false ID da solicitação ou false
     */
    function criar_solicitacao_lgpd($cliente_id, $tipo, $descricao = '')
    {
        $ci = &get_instance();
        $ci->load->model('Solicitacoes_lgpd_model');
        
        $data = [
            'clientes_id' => $cliente_id,
            'tipo_solicitacao' => $tipo,
            'descricao' => $descricao,
            'status' => 'pendente',
        ];
        
        $result = $ci->Solicitacoes_lgpd_model->add($data);
        
        // Registrar na auditoria
        if ($result) {
            $ci->load->helper('audit');
            log_info("Solicitação LGPD criada: Cliente #{$cliente_id}, Tipo: {$tipo}, ID: {$result}");
        }
        
        return $result;
    }
}

