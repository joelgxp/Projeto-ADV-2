<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller para processar alertas de prazos via cron
 * 
 * Conforme RN 4.4 - Sistema de Alertas Automáticos
 * 
 * Executar via cron a cada hora:
 * 0 * * * * /usr/bin/php /caminho/do/projeto/index.php alertas_prazos/processar
 */
class Alertas_prazos extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['prazos_model', 'alertas_prazos_model']);
        $this->load->library('email');
        $this->load->helper('prazo');
    }

    /**
     * Processa alertas de prazos pendentes
     * 
     * Busca prazos que precisam de alertas (7d, 2d, 1d, hoje)
     * e cria alertas na fila ou envia imediatamente
     */
    public function processar()
    {
        log_message('info', 'Iniciando processamento de alertas de prazos');

        // 1. Atualizar status dos prazos primeiro
        $this->prazos_model->atualizarStatusAutomatico();

        // 2. Buscar prazos que precisam de alertas
        $prazosParaAlertar = $this->buscarPrazosParaAlertar();

        $alertasCriados = 0;
        $alertasEnviados = 0;
        $erros = 0;

        foreach ($prazosParaAlertar as $prazo) {
            try {
                // Determinar quais alertas devem ser enviados
                $alertasNecessarios = $this->determinarAlertasNecessarios($prazo);

                foreach ($alertasNecessarios as $tipoAlerta => $dados) {
                    // Verificar se alerta já foi enviado
                    if ($this->alertas_prazos_model->jaEnviado($prazo->idPrazos, $tipoAlerta)) {
                        continue;
                    }

                    // Criar alerta ou enviar imediatamente
                    if ($dados['enviar_agora']) {
                        // Enviar imediatamente
                        $enviado = $this->enviarAlerta($prazo, $tipoAlerta, $dados);
                        if ($enviado) {
                            $alertasEnviados++;
                        } else {
                            $erros++;
                        }
                    } else {
                        // Criar alerta na fila
                        $this->alertas_prazos_model->add([
                            'prazos_id' => $prazo->idPrazos,
                            'tipo_alerta' => $tipoAlerta,
                            'data_envio_previsto' => $dados['data_envio'],
                            'status' => 'pendente',
                            'canal' => 'email',
                            'usuarios_id' => $prazo->usuarios_id
                        ]);
                        $alertasCriados++;
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Erro ao processar alertas para prazo ID ' . $prazo->idPrazos . ': ' . $e->getMessage());
                $erros++;
            }
        }

        // 3. Processar alertas pendentes na fila
        $alertasFila = $this->alertas_prazos_model->getParaEnviar();
        foreach ($alertasFila as $alerta) {
            $prazo = $this->prazos_model->getById($alerta->prazos_id);
            if ($prazo) {
                $enviado = $this->enviarAlerta($prazo, $alerta->tipo_alerta, ['descricao' => $this->getDescricaoAlerta($alerta->tipo_alerta)]);
                if ($enviado) {
                    $this->alertas_prazos_model->marcarEnviado($alerta->idAlertasPrazos);
                    $alertasEnviados++;
                } else {
                    $this->alertas_prazos_model->marcarFalhou($alerta->idAlertasPrazos, 'Falha ao enviar email');
                    $erros++;
                }
            }
        }

        $resultado = [
            'alertas_criados' => $alertasCriados,
            'alertas_enviados' => $alertasEnviados,
            'erros' => $erros,
            'total_processados' => count($prazosParaAlertar)
        ];

        log_message('info', 'Processamento de alertas concluído: ' . json_encode($resultado));

        if ($this->input->is_cli_request()) {
            echo "Alertas criados: {$resultado['alertas_criados']}\n";
            echo "Alertas enviados: {$resultado['alertas_enviados']}\n";
            echo "Erros: {$resultado['erros']}\n";
            echo "Total processados: {$resultado['total_processados']}\n";
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true] + $resultado));
        }
    }

    /**
     * Busca prazos que precisam de alertas
     * 
     * @return array Lista de prazos
     */
    private function buscarPrazosParaAlertar()
    {
        // Buscar prazos pendentes com vencimento nos próximos 7 dias ou hoje
        $hoje = date('Y-m-d');
        $seteDias = date('Y-m-d', strtotime('+7 days'));

        $this->db->where_in('status', ['pendente', 'proximo_vencer', 'vencendo_hoje']);
        $this->db->where('dataVencimento >=', $hoje);
        $this->db->where('dataVencimento <=', $seteDias);
        $query = $this->db->get('prazos');

        return $query->result();
    }

    /**
     * Determina quais alertas são necessários para um prazo
     * 
     * @param object $prazo Objeto do prazo
     * @return array Array com tipos de alertas e dados
     */
    private function determinarAlertasNecessarios($prazo)
    {
        $hoje = date('Y-m-d');
        $umDia = date('Y-m-d', strtotime('+1 day'));
        $doisDias = date('Y-m-d', strtotime('+2 days'));
        $seteDias = date('Y-m-d', strtotime('+7 days'));

        $dataVenc = $prazo->dataVencimento;
        $alertas = [];

        // Alerta de 7 dias
        if ($dataVenc == $seteDias) {
            $alertas['7d'] = [
                'descricao' => 'Faltam 7 dias para o vencimento',
                'data_envio' => date('Y-m-d H:i:s', strtotime('today 08:00')),
                'enviar_agora' => true
            ];
        }

        // Alerta de 2 dias
        if ($dataVenc == $doisDias) {
            $alertas['2d'] = [
                'descricao' => 'Faltam 2 dias para o vencimento',
                'data_envio' => date('Y-m-d H:i:s', strtotime('today 08:00')),
                'enviar_agora' => true
            ];
        }

        // Alerta de 1 dia
        if ($dataVenc == $umDia) {
            $alertas['1d'] = [
                'descricao' => 'Falta 1 dia para o vencimento',
                'data_envio' => date('Y-m-d H:i:s', strtotime('today 08:00')),
                'enviar_agora' => true
            ];
        }

        // Alerta do dia (hoje)
        if ($dataVenc == $hoje) {
            $alertas['hoje'] = [
                'descricao' => 'Prazo vence hoje!',
                'data_envio' => date('Y-m-d H:i:s', strtotime('today 08:00')),
                'enviar_agora' => true
            ];
        }

        return $alertas;
    }

    /**
     * Envia alerta por email
     * 
     * @param object $prazo Objeto do prazo
     * @param string $tipoAlerta Tipo do alerta
     * @param array $dados Dados adicionais
     * @return bool True se enviado com sucesso
     */
    private function enviarAlerta($prazo, $tipoAlerta, $dados)
    {
        // Buscar dados do usuário responsável
        $this->load->model('usuarios_model');
        $usuario = $this->usuarios_model->getById($prazo->usuarios_id);
        
        if (!$usuario || empty($usuario->email)) {
            log_message('error', 'Usuário responsável não encontrado ou sem email para prazo ID ' . $prazo->idPrazos);
            return false;
        }

        // Calcular dias restantes
        $data_vencimento = strtotime($prazo->dataVencimento);
        $hoje = strtotime('today');
        $dias_restantes = ceil(($data_vencimento - $hoje) / 86400);

        // Buscar dados do processo
        $processo = null;
        if ($prazo->processos_id) {
            $this->load->model('processos_model');
            $processo = $this->processos_model->getById($prazo->processos_id);
        }

        // Usar novo sistema de notificações (RN 9.1)
        $this->load->helper('notificacoes');
        
        $dados_template = [
            'destinatario' => $usuario,
            'prazo' => $prazo,
            'processo' => $processo,
            'dias_restantes' => $dias_restantes,
            'urgente' => $dias_restantes <= 1,
            'url_prazo' => site_url('prazos/visualizar/' . $prazo->idPrazos),
            'mensagem' => $dados['descricao'] ?? $this->getDescricaoAlerta($tipoAlerta),
        ];
        
        $titulo = 'Alerta de Prazo: ' . ($prazo->tipo ?? 'Prazo Processual');
        if ($dias_restantes <= 1) {
            $titulo = '⚠️ URGENTE: ' . $titulo;
        }
        
        $notificacao_id = notificar_prazo_vencendo($prazo, $dias_restantes);
        
        if ($notificacao_id) {
            log_message('info', "Alerta {$tipoAlerta} enviado para prazo ID {$prazo->idPrazos} - Email: {$usuario->email}");
            return true;
        } else {
            log_message('error', "Falha ao enviar alerta {$tipoAlerta} para prazo ID {$prazo->idPrazos}");
            return false;
        }
    }

    /**
     * Obtém descrição do tipo de alerta
     * 
     * @param string $tipoAlerta Tipo do alerta
     * @return string Descrição
     */
    private function getDescricaoAlerta($tipoAlerta)
    {
        $descricoes = [
            '7d' => 'Faltam 7 dias para o vencimento',
            '2d' => 'Faltam 2 dias para o vencimento',
            '1d' => 'Falta 1 dia para o vencimento',
            'hoje' => 'Prazo vence hoje!'
        ];

        return $descricoes[$tipoAlerta] ?? 'Alerta de prazo';
    }
}

