<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper de Notificações - Fase 9: Notificações e Comunicação
 * Funções auxiliares para criar notificações
 */

if (!function_exists('notificar_nova_movimentacao')) {
    /**
     * Notifica sobre nova movimentação de processo
     * 
     * @param int $processo_id ID do processo
     * @param object $movimentacao Dados da movimentação
     * @return bool
     */
    function notificar_nova_movimentacao($processo_id, $movimentacao)
    {
        $ci = &get_instance();
        $ci->load->model('processos_model');
        $ci->load->model('Advogados_processo_model');
        $ci->load->helper('email_template');
        
        $processo = $ci->processos_model->getById($processo_id);
        if (!$processo) {
            return false;
        }
        
        // Obter advogados responsáveis
        $advogados = $ci->Advogados_processo_model->getByProcesso($processo_id);
        
        $notificados = 0;
        foreach ($advogados as $advogado) {
            if (empty($advogado->email)) {
                continue;
            }
            
            $dados_template = [
                'destinatario' => $advogado,
                'processo' => $processo,
                'movimentacao' => $movimentacao,
                'url_processo' => site_url('processos/visualizar/' . $processo_id),
            ];
            
            $mensagem = "Nova movimentação no processo {$processo->numeroProcesso}: " . ($movimentacao->descricao ?? 'Movimentação registrada');
            
            $notificacao_id = enviar_notificacao_email(
                $advogado->idUsuarios,
                null,
                'movimentacao',
                'Nova Movimentação - ' . $processo->numeroProcesso,
                $mensagem,
                site_url('processos/visualizar/' . $processo_id),
                'nova_movimentacao',
                $dados_template
            );
            
            if ($notificacao_id) {
                $notificados++;
            }
        }
        
        // Notificar cliente se habilitado
        if ($processo->clientes_id) {
            $ci->load->model('clientes_model');
            $cliente = $ci->clientes_model->getById($processo->clientes_id);
            
            if ($cliente && !empty($cliente->email)) {
                $dados_template = [
                    'destinatario' => $cliente,
                    'processo' => $processo,
                    'movimentacao' => $movimentacao,
                    'url_processo' => site_url('mine/processos/visualizar/' . $processo_id),
                ];
                
                enviar_notificacao_email(
                    null,
                    $cliente->idClientes,
                    'movimentacao',
                    'Nova Movimentação no Seu Processo',
                    "Nova movimentação registrada no processo {$processo->numeroProcesso}",
                    site_url('mine/processos/visualizar/' . $processo_id),
                    'nova_movimentacao',
                    $dados_template
                );
            }
        }
        
        return $notificados > 0;
    }
}

if (!function_exists('notificar_prazo_vencendo')) {
    /**
     * Notifica sobre prazo vencendo
     * 
     * @param object $prazo Dados do prazo
     * @param int $dias_antes Dias antes do vencimento
     * @return bool
     */
    function notificar_prazo_vencendo($prazo, $dias_antes = 7)
    {
        $ci = &get_instance();
        $ci->load->model('prazos_model');
        $ci->load->model('processos_model');
        $ci->load->helper('email_template');
        
        // Obter responsável
        $usuario_id = $prazo->usuarios_id ?? null;
        if (!$usuario_id) {
            return false;
        }
        
        $ci->load->model('usuarios_model');
        $usuario = $ci->load->model('usuarios_model');
        $usuario = $ci->usuarios_model->getById($usuario_id);
        
        if (!$usuario || empty($usuario->email)) {
            return false;
        }
        
        // Calcular dias restantes
        $data_vencimento = strtotime($prazo->dataVencimento);
        $hoje = strtotime('today');
        $dias_restantes = ceil(($data_vencimento - $hoje) / 86400);
        
        // Obter processo se existir
        $processo = null;
        if (isset($prazo->processos_id) && $prazo->processos_id) {
            $processo = $ci->processos_model->getById($prazo->processos_id);
        }
        
        $dados_template = [
            'destinatario' => $usuario,
            'prazo' => $prazo,
            'processo' => $processo,
            'dias_restantes' => $dias_restantes,
            'urgente' => $dias_restantes <= 1,
            'url_prazo' => site_url('prazos/visualizar/' . $prazo->idPrazos),
            'mensagem' => "Prazo vencendo em {$dias_restantes} dia(s). Ação necessária!",
        ];
        
        $titulo = "Alerta de Prazo - " . ($prazo->tipo ?? 'Prazo Processual');
        if ($dias_restantes <= 1) {
            $titulo = "⚠️ URGENTE: " . $titulo;
        }
        
        return enviar_notificacao_email(
            $usuario_id,
            null,
            'prazo',
            $titulo,
            $dados_template['mensagem'],
            site_url('prazos/visualizar/' . $prazo->idPrazos),
            'prazo_vencendo',
            $dados_template
        );
    }
}

if (!function_exists('notificar_fatura_emitida')) {
    /**
     * Notifica sobre fatura emitida
     * 
     * @param int $fatura_id ID da fatura
     * @return bool
     */
    function notificar_fatura_emitida($fatura_id)
    {
        $ci = &get_instance();
        $ci->load->model('Faturas_model');
        $ci->load->model('clientes_model');
        $ci->load->helper('email_template');
        
        $fatura = $ci->Faturas_model->getById($fatura_id);
        if (!$fatura) {
            return false;
        }
        
        $cliente = $ci->clientes_model->getById($fatura->clientes_id);
        if (!$cliente || empty($cliente->email)) {
            return false;
        }
        
        $dados_template = [
            'cliente' => $cliente,
            'fatura' => $fatura,
            'url_fatura' => site_url('faturas/visualizar/' . $fatura_id),
        ];
        
        return enviar_notificacao_email(
            null,
            $cliente->idClientes,
            'fatura',
            'Fatura Emitida - ' . ($fatura->numero ?? 'Nº ' . $fatura_id),
            'Uma nova fatura foi emitida para você. Valor: R$ ' . number_format($fatura->valor_total ?? 0, 2, ',', '.'),
            site_url('faturas/visualizar/' . $fatura_id),
            'fatura_emitida',
            $dados_template
        );
    }
}

