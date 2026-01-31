<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper de Auditoria - Fase 8: Sistema de Auditoria Automática
 * Registra todas as ações críticas do sistema (RN 8.1)
 */

/**
 * Obtém o nome do usuário atual (admin ou cliente)
 * 
 * @return string Nome do usuário
 */
function get_audit_usuario()
{
    $ci = &get_instance();
    $usuario = $ci->session->userdata('nome_admin');
    if (empty($usuario)) {
        $usuario = $ci->session->userdata('nome');
    }
    if (empty($usuario)) {
        $usuario = 'Sistema';
    }
    return $usuario;
}

/**
 * Registra uma ação genérica no log
 * 
 * @param string $task Descrição da tarefa
 * @return bool
 */
function log_info($task)
{
    $ci = &get_instance();
    $ci->load->model('Audit_model');

    $data = [
        'usuario' => get_audit_usuario(),
        'ip' => $ci->input->ip_address(),
        'user_agent' => $ci->input->user_agent(),
        'tarefa' => $task,
        'data' => date('Y-m-d'),
        'hora' => date('H:i:s'),
    ];

    return $ci->Audit_model->add($data);
}

/**
 * Registra criação de um registro (RN 8.1)
 * 
 * @param string $modulo Módulo (cliente, processo, fatura, contrato, etc.)
 * @param int $registro_id ID do registro criado
 * @param array $dados_novos Dados do registro criado
 * @return bool
 */
if (!function_exists('log_create')) {
    function log_create($modulo, $registro_id, $dados_novos = [])
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = get_audit_usuario();
        
        // Verificar se há dados sensíveis
        $campos_sensiveis = ['cpf', 'cnpj', 'documento', 'rg', 'filiacao', 'email', 'telefone', 'celular', 'senha', 'password'];
        $dados_sensiveis = false;
        foreach ($dados_novos as $campo => $valor) {
            if (in_array(strtolower($campo), $campos_sensiveis)) {
                $dados_sensiveis = true;
                break;
            }
        }

        $data = [
            'usuario' => $usuario,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'tarefa' => 'Criar ' . ucfirst($modulo) . ' #' . $registro_id,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $modulo,
            'entidade_id' => $registro_id,
            'acao' => 'create',
            'dados_anteriores' => null,
            'dados_novos' => !empty($dados_novos) ? json_encode($dados_novos) : null,
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $ci->Audit_model->add($data);
    }
}

/**
 * Registra atualização de um registro (RN 8.1)
 * 
 * @param string $modulo Módulo
 * @param int $registro_id ID do registro
 * @param array $dados_anteriores Dados antes da alteração
 * @param array $dados_novos Dados após a alteração
 * @return bool
 */
if (!function_exists('log_update')) {
    function log_update($modulo, $registro_id, $dados_anteriores = [], $dados_novos = [])
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = get_audit_usuario();
        
        // Verificar se há dados sensíveis
        $campos_sensiveis = ['cpf', 'cnpj', 'documento', 'rg', 'filiacao', 'email', 'telefone', 'celular', 'senha', 'password'];
        $dados_sensiveis = false;
        $campos_alterados = [];
        
        foreach ($dados_novos as $campo => $valor_novo) {
            $valor_anterior = isset($dados_anteriores[$campo]) ? $dados_anteriores[$campo] : null;
            
            if ($valor_anterior != $valor_novo) {
                $campos_alterados[] = $campo;
                if (in_array(strtolower($campo), $campos_sensiveis)) {
                    $dados_sensiveis = true;
                }
            }
        }

        $data = [
            'usuario' => $usuario,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'tarefa' => 'Atualizar ' . ucfirst($modulo) . ' #' . $registro_id . ' - Campos: ' . implode(', ', $campos_alterados),
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $modulo,
            'entidade_id' => $registro_id,
            'acao' => 'update',
            'dados_anteriores' => !empty($dados_anteriores) ? json_encode($dados_anteriores) : null,
            'dados_novos' => !empty($dados_novos) ? json_encode($dados_novos) : null,
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $ci->Audit_model->add($data);
    }
}

/**
 * Registra exclusão de um registro (RN 8.1)
 * 
 * @param string $modulo Módulo
 * @param int $registro_id ID do registro
 * @param array $dados_anteriores Dados completos antes da exclusão
 * @return bool
 */
if (!function_exists('log_delete')) {
    function log_delete($modulo, $registro_id, $dados_anteriores = [])
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = get_audit_usuario();
        
        // Verificar se há dados sensíveis
        $campos_sensiveis = ['cpf', 'cnpj', 'documento', 'rg', 'filiacao', 'email', 'telefone', 'celular', 'senha', 'password'];
        $dados_sensiveis = false;
        foreach ($dados_anteriores as $campo => $valor) {
            if (in_array(strtolower($campo), $campos_sensiveis)) {
                $dados_sensiveis = true;
                break;
            }
        }

        $data = [
            'usuario' => $usuario,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'tarefa' => 'Excluir ' . ucfirst($modulo) . ' #' . $registro_id,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $modulo,
            'entidade_id' => $registro_id,
            'acao' => 'delete',
            'dados_anteriores' => !empty($dados_anteriores) ? json_encode($dados_anteriores) : null,
            'dados_novos' => null,
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $ci->Audit_model->add($data);
    }
}

/**
 * Registra visualização de um registro (RN 8.1)
 * 
 * @param string $modulo Módulo
 * @param int $registro_id ID do registro
 * @param bool $dados_sensiveis Se dados sensíveis foram visualizados
 * @return bool
 */
if (!function_exists('log_view')) {
    function log_view($modulo, $registro_id, $dados_sensiveis = false)
    {
        $ci = &get_instance();
        $ci->load->model('Audit_model');

        $usuario = get_audit_usuario();

        $data = [
            'usuario' => $usuario,
            'ip' => $ci->input->ip_address(),
            'user_agent' => $ci->input->user_agent(),
            'tarefa' => 'Visualizar ' . ucfirst($modulo) . ' #' . $registro_id,
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'entidade_tipo' => $modulo,
            'entidade_id' => $registro_id,
            'acao' => 'view',
            'dados_sensiveis' => $dados_sensiveis ? 1 : 0,
        ];

        return $ci->Audit_model->add($data);
    }
}

// Funções específicas por módulo (mantidas para compatibilidade)

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

        $usuario = get_audit_usuario();

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

        $usuario = get_audit_usuario();

        return $ci->Audit_model->log_edit($usuario, 'cliente', $cliente_id, $campo, $valor_anterior, $valor_novo);
    }
}
