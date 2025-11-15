<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_atualizar_permissoes_juridicas extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('permissoes')) {
            echo "⚠️  Tabela 'permissoes' não existe.\n";
            return;
        }

        // Buscar todas as permissões
        $permissoes = $this->db->get('permissoes')->result();

        foreach ($permissoes as $permissao) {
            $permissoes_array = unserialize($permissao->permissoes);
            
            if (!is_array($permissoes_array)) {
                continue;
            }

            // Remover permissões antigas
            unset($permissoes_array['aProduto']);
            unset($permissoes_array['eProduto']);
            unset($permissoes_array['dProduto']);
            unset($permissoes_array['vProduto']);
            unset($permissoes_array['rProduto']);
            
            unset($permissoes_array['aOs']);
            unset($permissoes_array['eOs']);
            unset($permissoes_array['dOs']);
            unset($permissoes_array['vOs']);
            unset($permissoes_array['rOs']);
            
            unset($permissoes_array['aVenda']);
            unset($permissoes_array['eVenda']);
            unset($permissoes_array['dVenda']);
            unset($permissoes_array['vVenda']);
            unset($permissoes_array['rVenda']);
            
            unset($permissoes_array['aGarantia']);
            unset($permissoes_array['eGarantia']);
            unset($permissoes_array['dGarantia']);
            unset($permissoes_array['vGarantia']);

            // Adicionar novas permissões jurídicas (se não existirem)
            // Migrar permissões de OS para Processos se existirem
            if (!isset($permissoes_array['aProcesso'])) {
                $permissoes_array['aProcesso'] = (isset($permissoes_array['aOs']) && $permissoes_array['aOs'] == '1') ? '1' : '0';
            }
            if (!isset($permissoes_array['eProcesso'])) {
                $permissoes_array['eProcesso'] = (isset($permissoes_array['eOs']) && $permissoes_array['eOs'] == '1') ? '1' : '0';
            }
            if (!isset($permissoes_array['dProcesso'])) {
                $permissoes_array['dProcesso'] = (isset($permissoes_array['dOs']) && $permissoes_array['dOs'] == '1') ? '1' : '0';
            }
            if (!isset($permissoes_array['vProcesso'])) {
                $permissoes_array['vProcesso'] = (isset($permissoes_array['vOs']) && $permissoes_array['vOs'] == '1') ? '1' : '0';
            }
            if (!isset($permissoes_array['sProcesso'])) {
                $permissoes_array['sProcesso'] = '1'; // Sincronizar processo - padrão ativo
            }

            if (!isset($permissoes_array['aPrazo'])) {
                $permissoes_array['aPrazo'] = '1';
            }
            if (!isset($permissoes_array['ePrazo'])) {
                $permissoes_array['ePrazo'] = '1';
            }
            if (!isset($permissoes_array['dPrazo'])) {
                $permissoes_array['dPrazo'] = '1';
            }
            if (!isset($permissoes_array['vPrazo'])) {
                $permissoes_array['vPrazo'] = '1';
            }

            if (!isset($permissoes_array['aAudiencia'])) {
                $permissoes_array['aAudiencia'] = '1';
            }
            if (!isset($permissoes_array['eAudiencia'])) {
                $permissoes_array['eAudiencia'] = '1';
            }
            if (!isset($permissoes_array['dAudiencia'])) {
                $permissoes_array['dAudiencia'] = '1';
            }
            if (!isset($permissoes_array['vAudiencia'])) {
                $permissoes_array['vAudiencia'] = '1';
            }

            if (!isset($permissoes_array['cConsultaProcessual'])) {
                $permissoes_array['cConsultaProcessual'] = '1';
            }

            // Atualizar relatórios - remover antigos e adicionar novos
            unset($permissoes_array['rOs']);
            unset($permissoes_array['rVenda']);
            unset($permissoes_array['rProduto']);
            
            if (!isset($permissoes_array['rProcesso'])) {
                $permissoes_array['rProcesso'] = (isset($permissoes_array['rOs']) && $permissoes_array['rOs'] == '1') ? '1' : '0';
            }
            if (!isset($permissoes_array['rPrazo'])) {
                $permissoes_array['rPrazo'] = '1';
            }
            if (!isset($permissoes_array['rAudiencia'])) {
                $permissoes_array['rAudiencia'] = '1';
            }

            // Serializar e atualizar
            $permissoes_serialized = serialize($permissoes_array);
            
            $this->db->where('idPermissao', $permissao->idPermissao);
            $this->db->update('permissoes', ['permissoes' => $permissoes_serialized]);
        }

        echo "✅ Permissões atualizadas com sucesso!\n";
    }

    public function down()
    {
        // Reverter não é necessário, mas podemos implementar se necessário
        echo "⚠️  Reversão não implementada.\n";
    }
}

