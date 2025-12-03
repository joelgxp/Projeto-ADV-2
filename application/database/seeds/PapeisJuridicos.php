<?php

/**
 * Seeder para criar papéis RBAC específicos do sistema jurídico
 * 
 * Papéis criados:
 * - Advogado (ID 2)
 * - Assistente (ID 3)
 * 
 * O papel Cliente é gerenciado separadamente no portal Mine (conecte)
 */

class PapeisJuridicos extends Seeder
{
    private $table = 'permissoes';

    public function run()
    {
        echo 'Running Papeis Juridicos Seeder...' . PHP_EOL;

        // Verificar se já existem os papéis
        $this->db->where('idPermissao', 2);
        $advogado_existe = $this->db->get($this->table)->num_rows() > 0;
        
        $this->db->where('idPermissao', 3);
        $assistente_existe = $this->db->get($this->table)->num_rows() > 0;

        // ============================================
        // PAPEL 2: ADVOGADO
        // ============================================
        // Permissões: Acesso completo a processos, prazos, audiências
        // Pode gerenciar clientes (exceto excluir se houver processos ativos)
        // Pode criar/editar/visualizar tudo relacionado a processos jurídicos
        // Pode visualizar relatórios e financeiro (mas não editar valores sensíveis)
        
        if (!$advogado_existe) {
            $permissoes_advogado = [
                // Clientes - pode adicionar, editar, visualizar (mas não deletar se houver processos)
                'aCliente' => '1',
                'eCliente' => '1',
                'vCliente' => '1',
                'vClienteDadosSensiveis' => '1',
                'eClienteDadosSensiveis' => '1',
                'vClienteProcessos' => '1',
                'vClienteDocumentos' => '1',
                'vClienteFinanceiro' => '1',
                'rCliente' => '1',
                
                // Processos - acesso completo
                'aProcesso' => '1',
                'eProcesso' => '1',
                'vProcesso' => '1',
                'sProcesso' => '1', // Sincronizar com API CNJ
                
                // Prazos - acesso completo
                'aPrazo' => '1',
                'ePrazo' => '1',
                'vPrazo' => '1',
                'dPrazo' => '1',
                
                // Audiências - acesso completo
                'aAudiencia' => '1',
                'eAudiencia' => '1',
                'vAudiencia' => '1',
                'dAudiencia' => '1',
                
                // Consulta Processual - pode consultar
                'cConsultaProcessual' => '1',
                
                // Financeiro - pode visualizar e relatórios (mas não alterar valores sem permissão especial)
                'vLancamento' => '1',
                'rFinanceiro' => '1',
                
                // Cobranças - pode visualizar e editar
                'aCobranca' => '1',
                'eCobranca' => '1',
                'vCobranca' => '1',
                
                // Email - pode visualizar fila
                'cEmail' => '0', // Não pode configurar email, apenas visualizar
                
                // Não pode: gerenciar usuários, permissões, sistema, backup
                'cUsuario' => '0',
                'cPermissao' => '0',
                'cSistema' => '0',
                'cBackup' => '0',
                'cAuditoria' => '0',
                'cEmitente' => '0',
            ];

            $data_advogado = [
                'idPermissao' => 2,
                'nome' => 'Advogado',
                'permissoes' => serialize($permissoes_advogado),
                'situacao' => 1,
                'data' => date('Y-m-d'),
            ];

            $this->db->insert($this->table, $data_advogado);
            echo '✅ Papel "Advogado" criado com sucesso (ID: 2)' . PHP_EOL;
        } else {
            echo '⚠️ Papel "Advogado" já existe (ID: 2)' . PHP_EOL;
        }

        // ============================================
        // PAPEL 3: ASSISTENTE
        // ============================================
        // Permissões: Acesso limitado para apoio administrativo
        // Pode visualizar processos, prazos, audiências
        // Pode editar prazos e criar/editar audiências
        // Pode visualizar clientes mas não editar dados sensíveis
        // Não pode criar processos ou modificar dados críticos
        
        if (!$assistente_existe) {
            $permissoes_assistente = [
                // Clientes - apenas visualizar (não pode editar dados sensíveis)
                'aCliente' => '0',
                'eCliente' => '0',
                'vCliente' => '1',
                'vClienteDadosSensiveis' => '0', // Não pode ver dados sensíveis
                'eClienteDadosSensiveis' => '0',
                'vClienteProcessos' => '1',
                'vClienteDocumentos' => '0', // Não pode visualizar documentos
                'vClienteFinanceiro' => '0', // Não pode ver financeiro
                'rCliente' => '1', // Pode gerar relatórios básicos
                
                // Processos - apenas visualizar (não pode criar/editar)
                'aProcesso' => '0',
                'eProcesso' => '0',
                'vProcesso' => '1',
                'sProcesso' => '0', // Não pode sincronizar
                
                // Prazos - pode criar e editar (apoio ao advogado)
                'aPrazo' => '1',
                'ePrazo' => '1',
                'vPrazo' => '1',
                'dPrazo' => '0', // Não pode deletar
                
                // Audiências - pode criar e editar (agendamento)
                'aAudiencia' => '1',
                'eAudiencia' => '1',
                'vAudiencia' => '1',
                'dAudiencia' => '1',
                
                // Consulta Processual - não pode
                'cConsultaProcessual' => '0',
                
                // Financeiro - apenas visualizar relatórios básicos
                'vLancamento' => '0',
                'rFinanceiro' => '0',
                
                // Cobranças - apenas visualizar
                'aCobranca' => '0',
                'eCobranca' => '0',
                'vCobranca' => '1',
                
                // Email - pode visualizar fila
                'cEmail' => '0',
                
                // Não pode: gerenciar usuários, permissões, sistema, backup
                'cUsuario' => '0',
                'cPermissao' => '0',
                'cSistema' => '0',
                'cBackup' => '0',
                'cAuditoria' => '0',
                'cEmitente' => '0',
            ];

            $data_assistente = [
                'idPermissao' => 3,
                'nome' => 'Assistente',
                'permissoes' => serialize($permissoes_assistente),
                'situacao' => 1,
                'data' => date('Y-m-d'),
            ];

            $this->db->insert($this->table, $data_assistente);
            echo '✅ Papel "Assistente" criado com sucesso (ID: 3)' . PHP_EOL;
        } else {
            echo '⚠️ Papel "Assistente" já existe (ID: 3)' . PHP_EOL;
        }

        echo PHP_EOL . 'Seeder de Papeis Juridicos concluído!' . PHP_EOL;
    }
}

