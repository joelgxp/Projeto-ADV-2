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

        $this->db->where('idPermissao', 2);
        $advogado_existe = $this->db->get($this->table)->num_rows() > 0;

        $this->db->where('idPermissao', 3);
        $assistente_existe = $this->db->get($this->table)->num_rows() > 0;

        if (!$advogado_existe) {
            $permissoes_advogado = [
                'aCliente' => '1',
                'eCliente' => '1',
                'vCliente' => '1',
                'vClienteDadosSensiveis' => '1',
                'eClienteDadosSensiveis' => '1',
                'vClienteProcessos' => '1',
                'vClienteDocumentos' => '1',
                'vClienteFinanceiro' => '1',
                'rCliente' => '1',
                'aProcesso' => '1',
                'eProcesso' => '1',
                'vProcesso' => '1',
                'sProcesso' => '1',
                'aPrazo' => '1',
                'ePrazo' => '1',
                'vPrazo' => '1',
                'dPrazo' => '1',
                'aAudiencia' => '1',
                'eAudiencia' => '1',
                'vAudiencia' => '1',
                'dAudiencia' => '1',
                'cConsultaProcessual' => '1',
                'gPeticaoIA' => '1',
                'vLancamento' => '1',
                'rFinanceiro' => '1',
                'aCobranca' => '1',
                'eCobranca' => '1',
                'vCobranca' => '1',
                'cEmail' => '0',
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
            echo 'Papel "Advogado" criado com sucesso (ID: 2)' . PHP_EOL;
        } else {
            echo 'Papel "Advogado" já existe (ID: 2)' . PHP_EOL;
            $this->db->where('idPermissao', 2);
            $adv = $this->db->get($this->table)->row();
            if ($adv && !empty($adv->permissoes)) {
                $perms = is_string($adv->permissoes) ? @unserialize($adv->permissoes) : $adv->permissoes;
                if (is_array($perms) && !isset($perms['gPeticaoIA'])) {
                    $perms['gPeticaoIA'] = '1';
                    $this->db->where('idPermissao', 2);
                    $this->db->update($this->table, ['permissoes' => serialize($perms)]);
                    echo '   Atualizado: gPeticaoIA adicionado ao Advogado.' . PHP_EOL;
                }
            }
        }

        if (!$assistente_existe) {
            $permissoes_assistente = [
                'aCliente' => '0',
                'eCliente' => '0',
                'vCliente' => '1',
                'vClienteDadosSensiveis' => '0',
                'eClienteDadosSensiveis' => '0',
                'vClienteProcessos' => '1',
                'vClienteDocumentos' => '0',
                'vClienteFinanceiro' => '0',
                'rCliente' => '1',
                'aProcesso' => '0',
                'eProcesso' => '0',
                'vProcesso' => '1',
                'sProcesso' => '0',
                'aPrazo' => '1',
                'ePrazo' => '1',
                'vPrazo' => '1',
                'dPrazo' => '0',
                'aAudiencia' => '1',
                'eAudiencia' => '1',
                'vAudiencia' => '1',
                'dAudiencia' => '1',
                'cConsultaProcessual' => '0',
                'gPeticaoIA' => '0',
                'vLancamento' => '0',
                'rFinanceiro' => '0',
                'aCobranca' => '0',
                'eCobranca' => '0',
                'vCobranca' => '1',
                'cEmail' => '0',
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
            echo 'Papel "Assistente" criado com sucesso (ID: 3)' . PHP_EOL;
        } else {
            echo 'Papel "Assistente" já existe (ID: 3)' . PHP_EOL;
            $this->db->where('idPermissao', 3);
            $ast = $this->db->get($this->table)->row();
            if ($ast && !empty($ast->permissoes)) {
                $perms = is_string($ast->permissoes) ? @unserialize($ast->permissoes) : $ast->permissoes;
                if (is_array($perms) && !isset($perms['gPeticaoIA'])) {
                    $perms['gPeticaoIA'] = '0';
                    $this->db->where('idPermissao', 3);
                    $this->db->update($this->table, ['permissoes' => serialize($perms)]);
                    echo '   Atualizado: gPeticaoIA definido para Assistente.' . PHP_EOL;
                }
            }
        }

        $this->db->where('idPermissao', 1);
        $admin = $this->db->get($this->table)->row();
        if ($admin && !empty($admin->permissoes)) {
            $perms = is_string($admin->permissoes) ? @unserialize($admin->permissoes) : $admin->permissoes;
            if (is_array($perms) && !isset($perms['gPeticaoIA'])) {
                $perms['gPeticaoIA'] = '1';
                $this->db->where('idPermissao', 1);
                $this->db->update($this->table, ['permissoes' => serialize($perms)]);
                echo 'Atualizado: gPeticaoIA adicionado ao Administrador.' . PHP_EOL;
            }
        }

        echo PHP_EOL . 'Seeder de Papeis Juridicos concluído!' . PHP_EOL;
    }
}
