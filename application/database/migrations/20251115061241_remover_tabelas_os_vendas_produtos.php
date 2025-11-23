<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_remover_tabelas_os_vendas_produtos extends CI_Migration
{
    public function up()
    {
        // Remover tabelas relacionadas primeiro (devido a foreign keys)
        $tabelas_relacionadas = [
            'produtos_os',
            'servicos_os',
            'produtos_vendas',
            'servicos_vendas',
            'equipamentos_os',
            'garantias',
            'equipamentos',
        ];

        foreach ($tabelas_relacionadas as $tabela) {
            if ($this->db->table_exists($tabela)) {
                $this->dbforge->drop_table($tabela, true);
                echo "✅ Tabela '{$tabela}' removida com sucesso!\n";
            } else {
                echo "⚠️  Tabela '{$tabela}' não existe.\n";
            }
        }

        // Remover tabelas principais
        $tabelas_principais = [
            'os',
            'vendas',
            'produtos',
        ];

        foreach ($tabelas_principais as $tabela) {
            if ($this->db->table_exists($tabela)) {
                $this->dbforge->drop_table($tabela, true);
                echo "✅ Tabela '{$tabela}' removida com sucesso!\n";
            } else {
                echo "⚠️  Tabela '{$tabela}' não existe.\n";
            }
        }
    }

    public function down()
    {
        // Reverter - recriar tabelas (não implementado completamente, pois seria muito extenso)
        // Em caso de rollback, seria necessário restaurar do backup
        echo "⚠️  Rollback não implementado. Use backup para restaurar tabelas removidas.\n";
    }
}

