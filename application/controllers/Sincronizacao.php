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
                // Consulta processo na API (RN 10.2)
                $dadosProcesso = $this->cnjapi->consultarProcesso($processo->numeroProcesso);
                
                if ($dadosProcesso === false) {
                    // Se API indisponível, tenta novamente em 1 hora (RN 10.2)
                    $this->processos_model->edit('processos', [
                        'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                    ], 'idProcessos', $processo->idProcessos);
                    
                    $erros[] = "Processo ID {$processo->idProcessos}: API indisponível, tentará novamente em 1 hora";
                    log_message('warning', "Sincronizacao: API indisponível para processo ID {$processo->idProcessos}, agendando retry em 1 hora");
                    continue;
                }
                
                // Detecta mudanças (RN 10.2)
                $mudancas = $this->detectarMudancas($processo, $dadosProcesso);
                $novasMovimentacoes = 0;
                
                // Sincroniza movimentações (RN 10.3)
                $movimentacoes = $this->cnjapi->sincronizarMovimentacoes($processo->idProcessos, $processo->numeroProcesso);

                if ($movimentacoes !== false) {
                    $novasMovimentacoes = is_array($movimentacoes) ? count($movimentacoes) : 0;
                    $total_movimentacoes += $novasMovimentacoes;
                    
                    // Notifica responsável sobre novas movimentações importantes (RN 10.3)
                    if ($novasMovimentacoes > 0) {
                        $this->notificarNovasMovimentacoes($processo, $movimentacoes);
                    }
                }

                // Atualiza dados do processo se houver mudanças (RN 10.4 - não sobrescreve dados locais se parciais)
                $dadosAtualizacao = [
                    'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
                    'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+6 hours'))
                ];
                
                // Verifica se dados são parciais (RN 10.4)
                $dadosParciais = $this->verificarDadosParciais($dadosProcesso);
                if ($dadosParciais) {
                    $dadosAtualizacao['sincronizado_parcialmente'] = 1;
                    log_message('info', "Sincronizacao: Processo ID {$processo->idProcessos} sincronizado parcialmente");
                } else {
                    $dadosAtualizacao['sincronizado_parcialmente'] = 0;
                }
                
                // Atualiza apenas campos que não sobrescrevem dados locais (RN 10.4)
                if ($mudancas['status_mudou']) {
                    $dadosAtualizacao['status'] = $mudancas['novo_status'];
                }
                
                $this->processos_model->edit('processos', $dadosAtualizacao, 'idProcessos', $processo->idProcessos);

                $total_sincronizados++;
                log_message('info', "Sincronizacao: Processo ID {$processo->idProcessos} sincronizado. {$novasMovimentacoes} movimentações novas.");

                // Pequeno delay para não sobrecarregar a API
                usleep(500000); // 0.5 segundos

            } catch (Exception $e) {
                // Em caso de erro, agenda retry em 1 hora (RN 10.2)
                $this->processos_model->edit('processos', [
                    'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ], 'idProcessos', $processo->idProcessos);
                
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

        // Atualiza datas de consulta (sincronização a cada 6 horas conforme RN 10.2)
        $this->processos_model->edit('processos', [
            'ultimaConsultaAPI' => date('Y-m-d H:i:s'),
            'proximaConsultaAPI' => date('Y-m-d H:i:s', strtotime('+6 hours'))
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

    /**
     * Detecta mudanças no processo comparando dados locais com API (RN 10.2)
     * 
     * @param object $processo Processo local
     * @param array $dadosProcesso Dados retornados pela API
     * @return array Array com informações sobre mudanças
     */
    private function detectarMudancas($processo, $dadosProcesso)
    {
        $mudancas = [
            'status_mudou' => false,
            'novo_status' => null,
            'tem_movimentacoes_novas' => false
        ];

        // Verifica mudança de status (RN 10.2)
        if (isset($dadosProcesso['status']) && !empty($dadosProcesso['status'])) {
            $statusAPI = strtolower(trim($dadosProcesso['status']));
            $statusLocal = strtolower(trim($processo->status ?? ''));
            
            if ($statusAPI !== $statusLocal) {
                $mudancas['status_mudou'] = true;
                $mudancas['novo_status'] = $dadosProcesso['status'];
            }
        }

        // Verifica se há movimentações novas (será verificado na sincronização)
        if (isset($dadosProcesso['movimentos']) && is_array($dadosProcesso['movimentos']) && !empty($dadosProcesso['movimentos'])) {
            $mudancas['tem_movimentacoes_novas'] = true;
        }

        return $mudancas;
    }

    /**
     * Verifica se dados retornados pela API são parciais (RN 10.4)
     * 
     * @param array $dadosProcesso Dados retornados pela API
     * @return bool True se dados são parciais
     */
    private function verificarDadosParciais($dadosProcesso)
    {
        // Considera dados parciais se:
        // 1. Não tem número de processo
        // 2. Não tem classe
        // 3. Não tem partes
        // 4. Não tem movimentações
        // 5. Campos essenciais estão vazios
        
        $camposEssenciais = ['numero', 'classe', 'assunto'];
        $camposVazios = 0;
        
        foreach ($camposEssenciais as $campo) {
            if (empty($dadosProcesso[$campo])) {
                $camposVazios++;
            }
        }
        
        // Se mais de 1 campo essencial está vazio, considera parcial
        if ($camposVazios > 1) {
            return true;
        }
        
        // Se não tem partes nem movimentações, pode ser parcial
        $temPartes = isset($dadosProcesso['partes']) && !empty($dadosProcesso['partes']);
        $temMovimentacoes = isset($dadosProcesso['movimentos']) && !empty($dadosProcesso['movimentos']);
        
        if (!$temPartes && !$temMovimentacoes) {
            return true;
        }
        
        return false;
    }

    /**
     * Notifica responsável sobre novas movimentações importantes (RN 10.3)
     * 
     * @param object $processo Processo
     * @param array $movimentacoes Array de movimentações importadas
     */
    private function notificarNovasMovimentacoes($processo, $movimentacoes)
    {
        if (empty($movimentacoes) || !is_array($movimentacoes)) {
            return;
        }

        // Identifica movimentações importantes (sentença, decisão, etc.)
        $movimentacoesImportantes = [];
        $palavrasChave = ['sentença', 'decisão', 'acórdão', 'julgamento', 'despacho', 'intimação'];
        
        foreach ($movimentacoes as $mov) {
            $tipo = strtolower($mov['tipo'] ?? '');
            $descricao = strtolower($mov['descricao'] ?? '');
            
            foreach ($palavrasChave as $palavra) {
                if (strpos($tipo, $palavra) !== false || strpos($descricao, $palavra) !== false) {
                    $movimentacoesImportantes[] = $mov;
                    break;
                }
            }
        }

        // Se não há movimentações importantes, notifica apenas o responsável
        if (empty($movimentacoesImportantes)) {
            $this->notificarResponsavel($processo, count($movimentacoes));
            return;
        }

        // Notifica responsável sobre movimentações importantes
        $this->notificarResponsavel($processo, count($movimentacoes), $movimentacoesImportantes);

        // Notifica cliente se houver movimentações importantes (RN 10.3)
        if ($processo->clientes_id) {
            $this->load->model('clientes_model');
            $cliente = $this->clientes_model->getById($processo->clientes_id);
            
            if ($cliente && !empty($cliente->email)) {
                $this->load->helper('notificacoes');
                
                foreach ($movimentacoesImportantes as $mov) {
                    notificar_nova_movimentacao($processo->idProcessos, (object)$mov);
                }
            }
        }
    }

    /**
     * Notifica responsável do processo sobre novas movimentações
     * 
     * @param object $processo Processo
     * @param int $totalMovimentacoes Total de movimentações
     * @param array $movimentacoesImportantes Movimentações importantes (opcional)
     */
    private function notificarResponsavel($processo, $totalMovimentacoes, $movimentacoesImportantes = [])
    {
        // Obter responsável principal
        $this->load->model('Advogados_processo_model');
        $advogados = $this->Advogados_processo_model->getByProcesso($processo->idProcessos);
        
        $responsavelPrincipal = null;
        foreach ($advogados as $adv) {
            if (isset($adv->papel) && strtolower($adv->papel) === 'principal') {
                $responsavelPrincipal = $adv;
                break;
            }
        }
        
        if (!$responsavelPrincipal) {
            // Se não tem principal, pega o primeiro
            $responsavelPrincipal = !empty($advogados) ? $advogados[0] : null;
        }
        
        if (!$responsavelPrincipal || empty($responsavelPrincipal->email)) {
            log_message('warning', "Sincronizacao: Processo ID {$processo->idProcessos} não tem responsável com e-mail para notificar");
            return;
        }

        // Criar notificação
        $this->load->helper('notificacoes');
        
        $titulo = "Nova(s) Movimentação(ões) - " . ($processo->numeroProcesso ?? 'Processo');
        $mensagem = "{$totalMovimentacoes} nova(s) movimentação(ões) foi(ram) importada(s) do processo {$processo->numeroProcesso}.";
        
        if (!empty($movimentacoesImportantes)) {
            $mensagem .= "\n\nMovimentações importantes:";
            foreach ($movimentacoesImportantes as $mov) {
                $mensagem .= "\n- " . ($mov['tipo'] ?? 'Movimentação');
            }
        }
        
        enviar_notificacao_email(
            $responsavelPrincipal->usuarios_id,
            null,
            'movimentacao',
            $titulo,
            $mensagem,
            site_url('processos/visualizar/' . $processo->idProcessos),
            'nova_movimentacao',
            [
                'destinatario' => $responsavelPrincipal,
                'processo' => $processo,
                'total_movimentacoes' => $totalMovimentacoes,
                'movimentacoes_importantes' => $movimentacoesImportantes,
            ]
        );
    }
}

