<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adicionar_permissoes_rbac_clientes extends CI_Migration
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

            // Adicionar novas permissões RBAC de clientes (se não existirem)
            if (!isset($permissoes_array['vClienteDadosSensiveis'])) {
                // Se tem permissão de visualizar cliente, dar permissão de dados sensíveis por padrão
                $permissoes_array['vClienteDadosSensiveis'] = (isset($permissoes_array['vCliente']) && $permissoes_array['vCliente'] == '1') ? '1' : '0';
            }
            
            if (!isset($permissoes_array['eClienteDadosSensiveis'])) {
                // Se tem permissão de editar cliente, dar permissão de editar dados sensíveis por padrão
                $permissoes_array['eClienteDadosSensiveis'] = (isset($permissoes_array['eCliente']) && $permissoes_array['eCliente'] == '1') ? '1' : '0';
            }
            
            if (!isset($permissoes_array['vClienteProcessos'])) {
                // Se tem permissão de visualizar cliente, dar permissão de ver processos por padrão
                $permissoes_array['vClienteProcessos'] = (isset($permissoes_array['vCliente']) && $permissoes_array['vCliente'] == '1') ? '1' : '0';
            }
            
            if (!isset($permissoes_array['vClienteDocumentos'])) {
                // Se tem permissão de visualizar cliente, dar permissão de ver documentos por padrão
                $permissoes_array['vClienteDocumentos'] = (isset($permissoes_array['vCliente']) && $permissoes_array['vCliente'] == '1') ? '1' : '0';
            }
            
            if (!isset($permissoes_array['vClienteFinanceiro'])) {
                // Por padrão, não dar acesso a dados financeiros (mais restritivo)
                $permissoes_array['vClienteFinanceiro'] = '0';
            }

            // Serializar e atualizar
            $permissoes_serialized = serialize($permissoes_array);
            
            $this->db->where('idPermissao', $permissao->idPermissao);
            $this->db->update('permissoes', ['permissoes' => $permissoes_serialized]);
        }

        echo "✅ Permissões RBAC de clientes adicionadas com sucesso!\n";
    }

    public function down()
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

            // Remover permissões RBAC de clientes
            unset($permissoes_array['vClienteDadosSensiveis']);
            unset($permissoes_array['eClienteDadosSensiveis']);
            unset($permissoes_array['vClienteProcessos']);
            unset($permissoes_array['vClienteDocumentos']);
            unset($permissoes_array['vClienteFinanceiro']);

            // Serializar e atualizar
            $permissoes_serialized = serialize($permissoes_array);
            
            $this->db->where('idPermissao', $permissao->idPermissao);
            $this->db->update('permissoes', ['permissoes' => $permissoes_serialized]);
        }

        echo "✅ Permissões RBAC de clientes removidas!\n";
    }
}

