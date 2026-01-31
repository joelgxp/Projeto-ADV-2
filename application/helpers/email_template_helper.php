<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper de Templates de E-mail - Fase 9: Notificações e Comunicação
 * Funções para renderizar templates de e-mail
 */

if (!function_exists('render_email_template')) {
    /**
     * Renderiza template de e-mail com dados
     * 
     * @param string $template Nome do template (sem extensão)
     * @param array $data Dados para o template
     * @return string HTML renderizado
     */
    function render_email_template($template, $data = [])
    {
        $ci = &get_instance();
        
        // Caminho do template
        $template_path = 'emails/templates/' . $template . '.php';
        
        // Verificar se template existe
        if (!file_exists(APPPATH . 'views/' . $template_path)) {
            log_message('error', "Template de e-mail não encontrado: {$template_path}");
            return '<p>Erro: Template não encontrado.</p>';
        }
        
        // Extrair dados para variáveis
        extract($data);
        
        // Capturar output
        ob_start();
        include(APPPATH . 'views/' . $template_path);
        $html = ob_get_clean();
        
        return $html;
    }
}

if (!function_exists('enqueue_email_with_template')) {
    /**
     * Adiciona e-mail à fila usando template
     * 
     * @param string $para E-mail do destinatário
     * @param string $assunto Assunto do e-mail
     * @param string $template Nome do template
     * @param array $dados_template Dados para o template
     * @param string $prioridade Prioridade (baixa, normal, alta, critica)
     * @return int|false ID do e-mail na fila ou false
     */
    function enqueue_email_with_template($para, $assunto, $template, $dados_template = [], $prioridade = 'normal')
    {
        $ci = &get_instance();
        $ci->load->helper('email_template');
        
        // Renderizar template
        $mensagem = render_email_template($template, $dados_template);
        
        // Adicionar à fila
        $ci->load->model('Email_model');
        
        $email_data = [
            'to' => $para,
            'subject' => $assunto,
            'message' => $mensagem,
            'template' => $template,
            'dados_template' => !empty($dados_template) ? json_encode($dados_template, JSON_UNESCAPED_UNICODE) : null,
            'prioridade' => $prioridade,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        if ($ci->Email_model->add('email_queue', $email_data)) {
            $email_id = $ci->db->insert_id();
            
            // Registrar na auditoria
            $ci->load->helper('audit');
            log_info("E-mail adicionado à fila: Template '{$template}', Para: {$para}, Prioridade: {$prioridade}");
            
            return $email_id;
        }
        
        return false;
    }
}

if (!function_exists('enviar_notificacao_email')) {
    /**
     * Envia notificação por e-mail (adiciona à fila)
     * 
     * @param int|null $usuario_id ID do usuário (NULL = cliente)
     * @param int|null $cliente_id ID do cliente (NULL = usuário interno)
     * @param string $categoria Categoria da notificação
     * @param string $titulo Título da notificação
     * @param string $mensagem Mensagem da notificação
     * @param string $url URL relacionada (opcional)
     * @param string $template Template de e-mail (opcional)
     * @param array $dados_template Dados para template (opcional)
     * @return int|false ID da notificação ou false
     */
    function enviar_notificacao_email($usuario_id, $cliente_id, $categoria, $titulo, $mensagem, $url = null, $template = null, $dados_template = [])
    {
        $ci = &get_instance();
        $ci->load->model('Notificacoes_model');
        $ci->load->model('Preferencias_notificacao_model');
        
        // Verificar preferências
        if ($usuario_id) {
            $pref = $ci->Preferencias_notificacao_model->getPreferencia($usuario_id, 'email', $categoria);
            if ($pref && !$pref->habilitado) {
                return false; // Usuário desabilitou esta notificação
            }
        } elseif ($cliente_id) {
            $pref = $ci->Preferencias_notificacao_model->getPreferenciaCliente($cliente_id, 'email', $categoria);
            if ($pref && !$pref->habilitado) {
                return false; // Cliente desabilitou esta notificação
            }
        }
        
        // Obter e-mail do destinatário
        $email_destinatario = null;
        if ($usuario_id) {
            $ci->load->model('usuarios_model');
            $usuario = $ci->usuarios_model->getById($usuario_id);
            $email_destinatario = $usuario->email ?? null;
        } elseif ($cliente_id) {
            $ci->load->model('clientes_model');
            $cliente = $ci->clientes_model->getById($cliente_id);
            $email_destinatario = $cliente->email ?? null;
        }
        
        if (!$email_destinatario) {
            log_message('warning', "Não foi possível obter e-mail para notificação. Usuario ID: {$usuario_id}, Cliente ID: {$cliente_id}");
            return false;
        }
        
        // Criar notificação
        $notificacao_id = $ci->Notificacoes_model->add([
            'usuario_id' => $usuario_id,
            'cliente_id' => $cliente_id,
            'tipo' => 'email',
            'categoria' => $categoria,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'url' => $url,
        ]);
        
        if (!$notificacao_id) {
            return false;
        }
        
        // Adicionar e-mail à fila
        if ($template) {
            $email_id = enqueue_email_with_template($email_destinatario, $titulo, $template, $dados_template, 'normal');
        } else {
            // Usar template padrão
            $ci->load->model('Email_model');
            $email_data = [
                'to' => $email_destinatario,
                'subject' => $titulo,
                'message' => $mensagem,
                'prioridade' => 'normal',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $ci->Email_model->add('email_queue', $email_data);
            $email_id = $ci->db->insert_id();
        }
        
        // Atualizar notificação com status de envio
        if ($email_id) {
            $ci->Notificacoes_model->update($notificacao_id, [
                'enviada' => 1,
                'data_envio' => date('Y-m-d H:i:s'),
            ]);
        }
        
        return $notificacao_id;
    }
}

