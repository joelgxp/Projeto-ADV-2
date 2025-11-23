<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adaptar_notificacoes_juridicas extends CI_Migration
{
    public function up()
    {
        // Atualizar template padrão do WhatsApp para contexto jurídico
        $template_juridico = 'Prezado(a) {CLIENTE_NOME},

O processo *{NUMERO_PROCESSO}* teve uma atualização:
• *Status:* {STATUS_PROCESSO}
• *Classe:* {CLASSE_PROCESSO}
• *Assunto:* {ASSUNTO_PROCESSO}
• *Vara:* {VARA}
• *Comarca:* {COMARCA}

Para mais informações entre em contato conosco.

Atenciosamente,
{EMITENTE}
{TELEFONE_EMITENTE}';

        // Atualizar template existente se já existir
        $this->db->where('config', 'notifica_whats');
        $existe = $this->db->get('configuracoes')->row();
        
        if ($existe) {
            $this->db->where('config', 'notifica_whats');
            $this->db->update('configuracoes', ['valor' => $template_juridico]);
        } else {
            // Criar se não existir
            $this->db->insert('configuracoes', [
                'config' => 'notifica_whats',
                'valor' => $template_juridico
            ]);
        }

        // Adicionar novas configurações de notificação
        $novas_configs = [
            [
                'config' => 'processo_notification',
                'valor' => 'cliente',
            ],
            [
                'config' => 'prazo_notification',
                'valor' => 'todos',
            ],
            [
                'config' => 'audiencia_notification',
                'valor' => 'todos',
            ],
        ];

        foreach ($novas_configs as $config) {
            // Verificar se já existe
            $this->db->where('config', $config['config']);
            $existe = $this->db->get('configuracoes')->row();
            
            if (!$existe) {
                $this->db->insert('configuracoes', $config);
            }
        }

        echo "✅ Notificações adaptadas para contexto jurídico!\n";
    }

    public function down()
    {
        // Remover novas configurações
        $this->db->where_in('config', ['processo_notification', 'prazo_notification', 'audiencia_notification']);
        $this->db->delete('configuracoes');

        // Restaurar template antigo (opcional)
        $template_antigo = 'Prezado(a), {CLIENTE_NOME} a OS de nº {NUMERO_OS} teve o status alterado para :{STATUS_OS} segue a descrição {DESCRI_PRODUTOS} com valor total de {VALOR_OS}!\r\nPara mais informações entre em contato conosco.\r\nAtenciosamente, {EMITENTE} {TELEFONE_EMITENTE}.';
        
        $this->db->where('config', 'notifica_whats');
        $this->db->update('configuracoes', ['valor' => $template_antigo]);

        echo "✅ Notificações revertidas para configuração anterior!\n";
    }
}

