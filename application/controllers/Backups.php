<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FASE 11: Controller de Backups (RN 12.4)
 * 
 * Gerencia backups do sistema
 */
class Backups extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Verifica permissão (apenas admin)
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aBackup')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para acessar backups.');
            redirect(base_url());
        }
        
    }
    
    /**
     * Lista backups disponíveis
     */
    public function index()
    {
        $this->load->library('Backup_manager');
        $this->data['backups'] = $this->backup_manager->listar_backups();
        $this->data['view'] = 'backups/listar';
        $this->layout();
    }
    
    /**
     * Executa backup manual
     */
    public function executar()
    {
        $this->load->library('Backup_manager');
        $tipo = $this->input->post('tipo') ?? 'diario';
        $criptografar = $this->input->post('criptografar') == '1';
        
        $resultado = $this->backup_manager->executar_backup($tipo, $criptografar);
        
        if ($resultado['sucesso']) {
            $this->session->set_flashdata('success', 'Backup executado com sucesso: ' . $resultado['arquivo']);
        } else {
            $this->session->set_flashdata('error', 'Erro ao executar backup: ' . $resultado['mensagem']);
        }
        
        redirect('backups');
    }
    
    /**
     * Download de backup
     */
    public function download($id)
    {
        $this->load->library('Backup_manager');
        $backups = $this->backup_manager->listar_backups();
        $backup = null;
        
        foreach ($backups as $b) {
            if ($b->id == $id) {
                $backup = $b;
                break;
            }
        }
        
        if (!$backup) {
            $this->session->set_flashdata('error', 'Backup não encontrado.');
            redirect('backups');
        }
        
        $arquivo = $backup->local_armazenamento . $backup->arquivo;
        
        if (!file_exists($arquivo)) {
            $this->session->set_flashdata('error', 'Arquivo de backup não encontrado.');
            redirect('backups');
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup->arquivo . '"');
        header('Content-Length: ' . filesize($arquivo));
        readfile($arquivo);
        exit;
    }
    
    /**
     * Exclui backup
     */
    public function excluir($id)
    {
        $this->load->library('Backup_manager');
        $backups = $this->backup_manager->listar_backups();
        $backup = null;
        
        foreach ($backups as $b) {
            if ($b->id == $id) {
                $backup = $b;
                break;
            }
        }
        
        if (!$backup) {
            $this->session->set_flashdata('error', 'Backup não encontrado.');
            redirect('backups');
        }
        
        $arquivo = $backup->local_armazenamento . $backup->arquivo;
        
        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
        
        $this->db->delete('backups', ['id' => $id]);
        
        $this->session->set_flashdata('success', 'Backup excluído com sucesso.');
        redirect('backups');
    }
}

