<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Controller para sincronização automática de processos via cron job
 * 
 * Uso:
 * - Linux: 0,30 * * * * php /caminho/para/index.php sincronizacao/processar
 *   (executa a cada 30 minutos)
 * - Windows: Agendador de Tarefas executando: php index.php sincronizacao/processar
 */
class Sincronizacao extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('processos_model');
        $this->load->library('CnjApi');
        $this->load->model('movimentacoes_processuais_model');
    }

    /**
     * Processa sincronização automática de processos
     * 
     * Busca processos que precisam ser sincronizados (baseado em proximaConsultaAPI)
     * e sincroniza suas movimentações
     */
    public function processar()
    {
        // Verifica se está sendo executado via CLI (cron) ou se tem permissão
        if (!$this->input->is_cli_request()) {
            if (!$this->session->userdata('logado') || 
                !$this->permission->checkPermission($this->session->userdata('permissao'), 'sProcesso')) {
                show_error('Acesso negado. Este método deve ser executado via CLI ou por usuário autorizado.', 403);
                return;
            }
        }

        log_message('info', 'Sincronizacao: Iniciando sincronização automática de processos');

        // Busca processos que precisam ser sincronizados
        // Processos com proximaConsultaAPI <= agora ou NULL
        $this->db->where('proximaConsultaAPI <=', date('Y-m-d H:i:s'));
        $this->db->or_where('proximaConsultaAPI', null);
        $this->db->where('numeroProcesso IS NOT NULL');
        $this->db->where('numeroProcesso !=', '');
        $processos = $this->db->get('processos')->result();

        $total_processos = count($processos);
        $total_sincronizados = 0;
        $total_movimentacoes = 0;
        $erros = [];

        log_message('info', "Sincronizacao: Encontrados {$total_processos} processos para sincronizar");

        foreach ($processos as $processo) {
            try {
                // Sincroniza movimentações
                $movimentacoes = $this->cnjapi->sincronizarMovimentacoes($processo->idProcessos, $processo->numeroProcesso);

                if ($movimentacoes === false) {
                    $erros[] = "Processo ID {$processo->idProcessos}: Erro ao sincronizar";
                    log_message('error', "Sincronizacao: Erro ao sincronizar processo ID {$processo->idProcessos}");
                    continue;
                }

                $num_movimentacoes = is_array($movimentacoes) ? count($movimentacoes) : 0;
                $total_movimentacoes += $num_movimentacoes;

                // Atualiza datas de consulta
                $this->processos_model->edit('processos', [
                    'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
                    'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 day'))
                ], 'idProcessos', $processo->idProcessos);

                $total_sincronizados++;
                log_message('info', "Sincronizacao: Processo ID {$processo->idProcessos} sincronizado. {$num_movimentacoes} movimentações.");

                // Pequeno delay para não sobrecarregar a API
                usleep(500000); // 0.5 segundos

            } catch (Exception $e) {
                $erros[] = "Processo ID {$processo->idProcessos}: " . $e->getMessage();
                log_message('error', "Sincronizacao: Exceção ao sincronizar processo ID {$processo->idProcessos}: " . $e->getMessage());
            }
        }

        // Log do resultado
        $resultado = [
            'total_processos' => $total_processos,
            'total_sincronizados' => $total_sincronizados,
            'total_movimentacoes' => $total_movimentacoes,
            'erros' => $erros
        ];

        log_message('info', 'Sincronizacao: Resultado: ' . json_encode($resultado));

        // Se executado via CLI, exibe resultado
        if ($this->input->is_cli_request()) {
            echo "Sincronização concluída:\n";
            echo "- Processos processados: {$total_processos}\n";
            echo "- Processos sincronizados: {$total_sincronizados}\n";
            echo "- Movimentações importadas: {$total_movimentacoes}\n";
            if (!empty($erros)) {
                echo "- Erros: " . count($erros) . "\n";
                foreach ($erros as $erro) {
                    echo "  * {$erro}\n";
                }
            }
        } else {
            // Se executado via web, retorna JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Sincronização concluída',
                    'result' => $resultado
                ]));
        }
    }

    /**
     * Sincroniza um processo específico
     * 
     * @param int $processoId ID do processo
     */
    public function sincronizarProcesso($processoId = null)
    {
        if (!$this->input->is_cli_request()) {
            if (!$this->session->userdata('logado') || 
                !$this->permission->checkPermission($this->session->userdata('permissao'), 'sProcesso')) {
                show_error('Acesso negado.', 403);
                return;
            }
        }

        if (!$processoId) {
            $processoId = $this->input->get('id') ?: $this->input->post('id');
        }

        if (!$processoId) {
            if ($this->input->is_cli_request()) {
                echo "Erro: ID do processo não informado.\n";
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'ID do processo não informado']));
            }
            return;
        }

        $processo = $this->processos_model->getById($processoId);
        if (!$processo) {
            if ($this->input->is_cli_request()) {
                echo "Erro: Processo não encontrado.\n";
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Processo não encontrado']));
            }
            return;
        }

        $movimentacoes = $this->cnjapi->sincronizarMovimentacoes($processoId, $processo->numeroProcesso);

        if ($movimentacoes === false) {
            if ($this->input->is_cli_request()) {
                echo "Erro ao sincronizar processo.\n";
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Erro ao sincronizar processo']));
            }
            return;
        }

        $num_movimentacoes = is_array($movimentacoes) ? count($movimentacoes) : 0;

        // Atualiza datas de consulta
        $this->processos_model->edit('processos', [
            'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
            'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ], 'idProcessos', $processoId);

        if ($this->input->is_cli_request()) {
            echo "Processo sincronizado com sucesso. {$num_movimentacoes} movimentações importadas.\n";
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => "Sincronização concluída. {$num_movimentacoes} movimentação(ões) importada(s).",
                    'movimentacoes' => $num_movimentacoes
                ]));
        }
    }
}

