<?php

defined('BASEPATH') or exit('No direct script access allowed');

// log info
function log_info($task)
{
    $ci = &get_instance();
    $ci->load->model('Audit_model');

    $data = [
        'usuario' => $ci->session->userdata('nome_admin'),
        'ip' => $ci->input->ip_address(),
        'tarefa' => $task,
        'data' => date('Y-m-d'),
        'hora' => date('H:i:s'),
    ];

    $ci->Audit_model->add($data);
}

if (!function_exists('log_cliente_access')) {
    /**
     * Registra acesso a um cliente
     *
     * @param int $cliente_id ID do cliente
     * @param string $acao Ação realizada (visualizar, editar, excluir)
     * @param bool $dados_sensiveis Se dados sensíveis foram acessados
     * @return bool
     */
    function log_cliente_access($cliente_id, $acao, $dados_sensiveis = false)
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = $ci->session->userdata('nome_admin') ?: 'Sistema';

        return $ci->Audit_model->log_access($usuario, 'cliente', $cliente_id, $acao, $dados_sensiveis);
    }
}

if (!function_exists('log_cliente_edit')) {
    /**
     * Registra edição em um cliente
     *
     * @param int $cliente_id ID do cliente
     * @param string $campo Campo alterado
     * @param mixed $valor_anterior Valor anterior
     * @param mixed $valor_novo Valor novo
     * @return bool
     */
    function log_cliente_edit($cliente_id, $campo, $valor_anterior, $valor_novo)
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = $ci->session->userdata('nome_admin') ?: 'Sistema';

        return $ci->Audit_model->log_edit($usuario, 'cliente', $cliente_id, $campo, $valor_anterior, $valor_novo);
    }
}
