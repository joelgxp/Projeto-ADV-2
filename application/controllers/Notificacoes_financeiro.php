<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Notificacoes_financeiro extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Faturas_model');
        $this->load->model('clientes_model');
    }

    /**
     * Processar notificações de faturas atrasadas
     * Deve ser executado via cron diariamente
     */
    public function processar()
    {
        // Buscar faturas atrasadas há 5 dias (primeira notificação)
        $data_5_dias = date('Y-m-d', strtotime('-5 days'));
        $this->db->where('data_vencimento <=', $data_5_dias);
        $this->db->where('status', 'atrasada');
        $this->db->where('status !=', 'paga');
        $this->db->where('status !=', 'cancelada');
        $faturas_5_dias = $this->db->get('faturas')->result();

        foreach ($faturas_5_dias as $fatura) {
            // Verificar se já foi notificado nos últimos 7 dias
            $this->db->where('tipo', 'fatura_atrasada_5d');
            $this->db->where('referencia_id', $fatura->id);
            $this->db->where('data_envio >=', date('Y-m-d', strtotime('-7 days')));
            $ja_notificado = $this->db->count_all_results('email_queue');

            if (!$ja_notificado) {
                $this->enviarNotificacaoAtraso($fatura, 5);
            }
        }

        // Buscar faturas atrasadas há 15 dias (segunda notificação)
        $data_15_dias = date('Y-m-d', strtotime('-15 days'));
        $this->db->where('data_vencimento <=', $data_15_dias);
        $this->db->where('status', 'atrasada');
        $this->db->where('status !=', 'paga');
        $this->db->where('status !=', 'cancelada');
        $faturas_15_dias = $this->db->get('faturas')->result();

        foreach ($faturas_15_dias as $fatura) {
            // Verificar se já foi notificado nos últimos 7 dias
            $this->db->where('tipo', 'fatura_atrasada_15d');
            $this->db->where('referencia_id', $fatura->id);
            $this->db->where('data_envio >=', date('Y-m-d', strtotime('-7 days')));
            $ja_notificado = $this->db->count_all_results('email_queue');

            if (!$ja_notificado) {
                $this->enviarNotificacaoAtraso($fatura, 15);
            }
        }

        echo "Notificações processadas com sucesso.\n";
    }

    private function enviarNotificacaoAtraso($fatura, $dias_atraso)
    {
        $cliente = $this->clientes_model->getById($fatura->clientes_id);
        if (!$cliente || !$cliente->email) {
            return false;
        }

        // Buscar responsável do cliente ou usar email padrão
        $email_destino = $cliente->email;

        $assunto = "Fatura em Atraso - {$fatura->numero}";
        $dias = $dias_atraso == 5 ? '5 dias' : '15 dias';
        
        $mensagem = "Prezado(a) " . $cliente->nomeCliente . ",\n\n";
        $mensagem .= "Informamos que a fatura {$fatura->numero} encontra-se em atraso há {$dias}.\n\n";
        $mensagem .= "Dados da Fatura:\n";
        $mensagem .= "Número: {$fatura->numero}\n";
        $mensagem .= "Vencimento: " . date('d/m/Y', strtotime($fatura->data_vencimento)) . "\n";
        $mensagem .= "Valor Total: R$ " . number_format($fatura->valor_total, 2, ',', '.') . "\n";
        $mensagem .= "Saldo Restante: R$ " . number_format($fatura->saldo_restante, 2, ',', '.') . "\n\n";
        $mensagem .= "Por favor, entre em contato para regularizar o pagamento.\n\n";
        $mensagem .= "Atenciosamente,\n";
        $mensagem .= "Equipe Jurídica";

        // Adicionar à fila de e-mails
        $tipo = $dias_atraso == 5 ? 'fatura_atrasada_5d' : 'fatura_atrasada_15d';
        
        $this->db->insert('email_queue', [
            'tipo' => $tipo,
            'destinatario' => $email_destino,
            'assunto' => $assunto,
            'mensagem' => $mensagem,
            'referencia_id' => $fatura->id,
            'data_envio' => date('Y-m-d H:i:s'),
            'enviado' => 0
        ]);

        return true;
    }
}

