<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Logs extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('logs');
        $this->data['menuLogs'] = 'logs';
    }

    public function index()
    {
        // Por enquanto, permite acesso para todos os usuários autenticados
        // Logs são importantes para debug e diagnóstico do sistema
        // Se quiser restringir no futuro, descomente as linhas abaixo:
        // if (!$this->isAdmin() && !$this->hasPermission('vLog')) {
        //     $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
        //     redirect(base_url());
        // }

        $this->data['logs'] = $this->listarLogs();
        $this->data['view'] = 'logs/index';
        return $this->layout();
    }

    public function visualizar($tipo = null, $arquivo = null)
    {
        // Por enquanto, permite acesso para todos os usuários autenticados
        // if (!$this->isAdmin() && !$this->hasPermission('vLog')) {
        //     $this->session->set_flashdata('error', 'Você não tem permissão para visualizar logs do sistema.');
        //     redirect(base_url());
        // }

        if (!$tipo || !$arquivo) {
            $this->session->set_flashdata('error', 'Parâmetros inválidos.');
            redirect('logs');
        }

        $logPath = $this->obterCaminhoLog($tipo, $arquivo);
        
        if (!$logPath || !file_exists($logPath)) {
            $this->session->set_flashdata('error', 'Arquivo de log não encontrado.');
            redirect('logs');
        }

        // Lê o arquivo de log
        $conteudo = file_get_contents($logPath);
        $linhas = explode("\n", $conteudo);
        
        // Limita a 1000 linhas para não sobrecarregar
        if (count($linhas) > 1000) {
            $linhas = array_slice($linhas, -1000);
            $this->data['aviso'] = 'Mostrando apenas as últimas 1000 linhas do log.';
        }

        $this->data['tipo'] = $tipo;
        $this->data['arquivo'] = $arquivo;
        $this->data['linhas'] = $linhas;
        $this->data['total_linhas'] = count(explode("\n", $conteudo));
        $this->data['tamanho'] = filesize($logPath);
        $this->data['data_modificacao'] = date('d/m/Y H:i:s', filemtime($logPath));
        $this->data['view'] = 'logs/visualizar';
        return $this->layout();
    }

    public function download($tipo = null, $arquivo = null)
    {
        // Por enquanto, permite acesso para todos os usuários autenticados
        // if (!$this->isAdmin() && !$this->hasPermission('vLog')) {
        //     $this->session->set_flashdata('error', 'Você não tem permissão para baixar logs do sistema.');
        //     redirect(base_url());
        // }

        if (!$tipo || !$arquivo) {
            $this->session->set_flashdata('error', 'Parâmetros inválidos.');
            redirect('logs');
        }

        $logPath = $this->obterCaminhoLog($tipo, $arquivo);
        
        if (!$logPath || !file_exists($logPath)) {
            $this->session->set_flashdata('error', 'Arquivo de log não encontrado.');
            redirect('logs');
        }

        $this->load->helper('download');
        force_download($arquivo, file_get_contents($logPath));
    }

    public function limpar($tipo = null, $arquivo = null)
    {
        // Verifica se é admin ou tem permissão de limpar logs
        if (!$this->isAdmin() && !$this->hasPermission('eLog')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para limpar logs do sistema.');
            redirect(base_url());
        }

        if (!$tipo || !$arquivo) {
            $this->session->set_flashdata('error', 'Parâmetros inválidos.');
            redirect('logs');
        }

        $logPath = $this->obterCaminhoLog($tipo, $arquivo);
        
        if (!$logPath || !file_exists($logPath)) {
            $this->session->set_flashdata('error', 'Arquivo de log não encontrado.');
            redirect('logs');
        }

        // Limpa o arquivo
        file_put_contents($logPath, '');
        
        $this->session->set_flashdata('success', 'Log limpo com sucesso.');
        redirect('logs/visualizar/' . $tipo . '/' . $arquivo);
    }

    private function listarLogs()
    {
        $logs = [
            'gerais' => [],
            'cnj_api' => []
        ];

        $logsPath = APPPATH . 'logs/';

        // Lista logs gerais do CodeIgniter
        $gerais = glob($logsPath . 'log-*.php');
        if ($gerais) {
            foreach ($gerais as $arquivo) {
                $nomeArquivo = basename($arquivo);
                $logs['gerais'][] = [
                    'nome' => $nomeArquivo,
                    'tamanho' => filesize($arquivo),
                    'data_modificacao' => filemtime($arquivo),
                    'tipo' => 'geral'
                ];
            }
            // Ordena por data de modificação (mais recente primeiro)
            usort($logs['gerais'], function($a, $b) {
                return $b['data_modificacao'] - $a['data_modificacao'];
            });
        }

        // Lista logs de debug da API CNJ
        $cnjApi = glob($logsPath . 'cnj_api_debug_*.log');
        if ($cnjApi) {
            foreach ($cnjApi as $arquivo) {
                $nomeArquivo = basename($arquivo);
                $logs['cnj_api'][] = [
                    'nome' => $nomeArquivo,
                    'tamanho' => filesize($arquivo),
                    'data_modificacao' => filemtime($arquivo),
                    'tipo' => 'cnj_api'
                ];
            }
            // Ordena por data de modificação (mais recente primeiro)
            usort($logs['cnj_api'], function($a, $b) {
                return $b['data_modificacao'] - $a['data_modificacao'];
            });
        }

        return $logs;
    }

    private function obterCaminhoLog($tipo, $arquivo)
    {
        $logsPath = APPPATH . 'logs/';
        
        // Valida o tipo
        if (!in_array($tipo, ['geral', 'cnj_api'])) {
            return false;
        }

        // Valida o nome do arquivo (prevenção de path traversal)
        $arquivo = basename($arquivo);
        
        if ($tipo === 'geral') {
            // Logs gerais do CodeIgniter
            if (preg_match('/^log-\d{4}-\d{2}-\d{2}\.php$/', $arquivo)) {
                return $logsPath . $arquivo;
            }
        } elseif ($tipo === 'cnj_api') {
            // Logs de debug da API CNJ
            if (preg_match('/^cnj_api_debug_\d{4}-\d{2}-\d{2}\.log$/', $arquivo)) {
                return $logsPath . $arquivo;
            }
        }

        return false;
    }

    private function formatarTamanho($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Verifica se o usuário é administrador
     * 
     * @return bool
     */
    private function isAdmin()
    {
        $permissao = $this->session->userdata('permissao');
        
        // Se for string "admin" ou "administrador"
        if (is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador')) {
            return true;
        }
        
        // Se for numérico, verificar se é admin através do ID do usuário
        if (is_numeric($permissao)) {
            $userId = $this->session->userdata('id_admin');
            if ($userId) {
                // Verificar se o usuário tem nível admin
                $this->load->database();
                if ($this->db->table_exists('usuarios')) {
                    $columns = $this->db->list_fields('usuarios');
                    $id_col = in_array('idUsuarios', $columns) ? 'idUsuarios' : (in_array('id', $columns) ? 'id' : null);
                    $nivel_col = in_array('nivel', $columns) ? 'nivel' : null;
                    
                    if ($id_col && $nivel_col) {
                        $this->db->select($nivel_col);
                        $this->db->where($id_col, $userId);
                        $user = $this->db->get('usuarios')->row();
                        
                        if ($user && isset($user->$nivel_col) && strtolower($user->$nivel_col) === 'admin') {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Verifica se o usuário tem uma permissão específica
     * 
     * @param string $permission Nome da permissão (ex: 'vLog', 'eLog')
     * @return bool
     */
    private function hasPermission($permission)
    {
        $permissao = $this->session->userdata('permissao');
        return $this->permission->checkPermission($permissao, $permission);
    }
}

