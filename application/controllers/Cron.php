<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Controller de Cron Jobs
 * 
 * Executa tarefas agendadas (backups, limpeza, etc.)
 */
class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Verifica se é chamado via CLI ou com token secreto
        $token = $this->input->get('token');
        $token_secreto = $this->config->item('cron_token') ?? 'cron_secret_token_2025';
        
        if (!$this->input->is_cli_request() && $token !== $token_secreto) {
            show_404();
        }
    }
    
    /**
     * Executa backup diário
     * 
     * Chamar via: php index.php cron backup_diario
     * Ou via URL: /cron/backup_diario?token=cron_secret_token_2025
     */
    public function backup_diario()
    {
        $this->load->library('Backup_manager');
        
        echo "Iniciando backup diário...\n";
        
        $resultado = $this->backup_manager->executar_backup('diario', true);
        
        if ($resultado['sucesso']) {
            echo "✅ Backup executado com sucesso: {$resultado['arquivo']}\n";
            echo "Tamanho: " . number_format($resultado['tamanho'] / 1024 / 1024, 2) . " MB\n";
        } else {
            echo "❌ Erro ao executar backup: {$resultado['mensagem']}\n";
        }
    }
    
    /**
     * Limpa rate limits antigos
     */
    public function limpar_rate_limits()
    {
        $this->load->helper('rate_limit');
        
        echo "Limpando rate limits antigos...\n";
        
        limpar_rate_limits_antigos(3600); // Mantém últimos 60 minutos
        
        echo "✅ Rate limits limpos com sucesso\n";
    }
    
    /**
     * Executa todas as tarefas agendadas
     */
    public function executar_todas()
    {
        echo "=== Executando tarefas agendadas ===\n\n";
        
        // Backup diário
        $this->backup_diario();
        echo "\n";
        
        // Limpar rate limits
        $this->limpar_rate_limits();
        echo "\n";
        
        echo "=== Tarefas concluídas ===\n";
    }
}

