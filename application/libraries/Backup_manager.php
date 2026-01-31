<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Gerenciador de Backups (RN 12.4)
 * 
 * Gerencia backups automáticos diários com retenção configurável
 */
class Backup_manager
{
    private $CI;
    private $db;
    
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->db = $this->CI->db;
        
        // Cria tabela de backups se não existir
        $this->criar_tabela_backups();
    }
    
    /**
     * Cria tabela de registro de backups
     */
    private function criar_tabela_backups()
    {
        if (!$this->db->table_exists('backups')) {
            $sql = "CREATE TABLE `backups` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tipo` ENUM('diario', 'semanal', 'mensal') NOT NULL DEFAULT 'diario',
                `arquivo` VARCHAR(255) NOT NULL,
                `tamanho` BIGINT(20) NOT NULL,
                `data_backup` DATETIME NOT NULL,
                `status` ENUM('sucesso', 'erro', 'em_andamento') NOT NULL DEFAULT 'em_andamento',
                `mensagem` TEXT NULL,
                `criptografado` TINYINT(1) NOT NULL DEFAULT 0,
                `local_armazenamento` VARCHAR(255) NULL,
                PRIMARY KEY (`id`),
                KEY `idx_tipo_data` (`tipo`, `data_backup`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $this->db->query($sql);
        }
    }
    
    /**
     * Executa backup do banco de dados
     * 
     * @param string $tipo Tipo de backup (diario, semanal, mensal)
     * @param bool $criptografar Se true, criptografa o backup
     * @return array ['sucesso' => bool, 'arquivo' => string, 'mensagem' => string]
     */
    public function executar_backup($tipo = 'diario', $criptografar = true)
    {
        $config = $this->CI->db->database;
        $host = $this->CI->db->hostname;
        $user = $this->CI->db->username;
        $pass = $this->CI->db->password;
        
        // Diretório de backups
        $diretorio_backup = APPPATH . '../backups/';
        if (!is_dir($diretorio_backup)) {
            mkdir($diretorio_backup, 0755, true);
        }
        
        // Nome do arquivo
        $timestamp = date('Y-m-d_H-i-s');
        $nome_arquivo = "backup_{$tipo}_{$timestamp}.sql";
        $caminho_completo = $diretorio_backup . $nome_arquivo;
        
        // Comando mysqldump
        $comando = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($config),
            escapeshellarg($caminho_completo)
        );
        
        // Executa backup
        exec($comando, $output, $return_var);
        
        if ($return_var !== 0 || !file_exists($caminho_completo)) {
            $mensagem = 'Erro ao executar backup: ' . implode("\n", $output);
            $this->registrar_backup($tipo, $nome_arquivo, 0, 'erro', $mensagem, false);
            
            return [
                'sucesso' => false,
                'arquivo' => null,
                'mensagem' => $mensagem
            ];
        }
        
        $tamanho = filesize($caminho_completo);
        
        // Criptografa se solicitado
        if ($criptografar) {
            $caminho_criptografado = $this->criptografar_backup($caminho_completo);
            if ($caminho_criptografado) {
                unlink($caminho_completo); // Remove arquivo não criptografado
                $caminho_completo = $caminho_criptografado;
                $nome_arquivo = basename($caminho_criptografado);
                $tamanho = filesize($caminho_completo);
            }
        }
        
        // Registra backup
        $this->registrar_backup($tipo, $nome_arquivo, $tamanho, 'sucesso', 'Backup executado com sucesso', $criptografar);
        
        // Limpa backups antigos
        $this->limpar_backups_antigos();
        
        return [
            'sucesso' => true,
            'arquivo' => $nome_arquivo,
            'mensagem' => 'Backup executado com sucesso',
            'tamanho' => $tamanho,
            'caminho' => $caminho_completo
        ];
    }
    
    /**
     * Criptografa arquivo de backup
     * 
     * @param string $arquivo Caminho do arquivo
     * @return string|false Caminho do arquivo criptografado ou false
     */
    private function criptografar_backup($arquivo)
    {
        $chave = $this->obter_chave_criptografia();
        $arquivo_criptografado = $arquivo . '.enc';
        
        $conteudo = file_get_contents($arquivo);
        $criptografado = openssl_encrypt($conteudo, 'AES-256-CBC', $chave, 0, substr($chave, 0, 16));
        
        if ($criptografado === false) {
            return false;
        }
        
        file_put_contents($arquivo_criptografado, $criptografado);
        
        return $arquivo_criptografado;
    }
    
    /**
     * Obtém chave de criptografia
     * 
     * @return string
     */
    private function obter_chave_criptografia()
    {
        // Usa chave do config ou gera uma
        $chave = $this->CI->config->item('encryption_key');
        if (empty($chave)) {
            $chave = hash('sha256', 'backup_key_default_' . $this->CI->config->item('encryption_key'));
        }
        
        return substr(hash('sha256', $chave), 0, 32);
    }
    
    /**
     * Registra backup na tabela
     */
    private function registrar_backup($tipo, $arquivo, $tamanho, $status, $mensagem, $criptografado)
    {
        $this->db->insert('backups', [
            'tipo' => $tipo,
            'arquivo' => $arquivo,
            'tamanho' => $tamanho,
            'data_backup' => date('Y-m-d H:i:s'),
            'status' => $status,
            'mensagem' => $mensagem,
            'criptografado' => $criptografado ? 1 : 0,
            'local_armazenamento' => APPPATH . '../backups/'
        ]);
    }
    
    /**
     * Limpa backups antigos conforme retenção
     */
    private function limpar_backups_antigos()
    {
        // Retenção: 7 dias (diários), 4 semanas (semanais)
        $limite_diarios = date('Y-m-d H:i:s', strtotime('-7 days'));
        $limite_semanais = date('Y-m-d H:i:s', strtotime('-4 weeks'));
        
        // Remove backups diários antigos
        $this->db->where('tipo', 'diario');
        $this->db->where('data_backup <', $limite_diarios);
        $backups_diarios = $this->db->get('backups')->result();
        
        foreach ($backups_diarios as $backup) {
            $arquivo = $backup->local_armazenamento . $backup->arquivo;
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
            $this->db->delete('backups', ['id' => $backup->id]);
        }
        
        // Remove backups semanais antigos
        $this->db->where('tipo', 'semanal');
        $this->db->where('data_backup <', $limite_semanais);
        $backups_semanais = $this->db->get('backups')->result();
        
        foreach ($backups_semanais as $backup) {
            $arquivo = $backup->local_armazenamento . $backup->arquivo;
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
            $this->db->delete('backups', ['id' => $backup->id]);
        }
    }
    
    /**
     * Lista backups disponíveis
     * 
     * @param string|null $tipo Tipo de backup (null = todos)
     * @return array
     */
    public function listar_backups($tipo = null)
    {
        $this->db->order_by('data_backup', 'DESC');
        
        if ($tipo) {
            $this->db->where('tipo', $tipo);
        }
        
        return $this->db->get('backups')->result();
    }
}

