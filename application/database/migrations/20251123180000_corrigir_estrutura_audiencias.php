<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_corrigir_estrutura_audiencias extends CI_Migration
{
    public function up()
    {
        // Verificar se tabela audiencias existe
        if (!$this->db->table_exists('audiencias')) {
            echo "⚠️  Tabela 'audiencias' não existe. Execute a migration criar_tabela_audiencias primeiro.\n";
            return;
        }

        $columns = $this->db->list_fields('audiencias');

        // Verificar e adicionar coluna dataHora se não existir
        if (!in_array('dataHora', $columns)) {
            echo "⚠️  Coluna 'dataHora' não existe. Adicionando...\n";
            
            $this->dbforge->add_column('audiencias', [
                'dataHora' => [
                    'type' => 'DATETIME',
                    'null' => false,
                    'after' => 'tipo',
                ],
            ]);

            // Adicionar índice se não existir
            $indexes = $this->db->query("SHOW INDEXES FROM audiencias WHERE Key_name = 'dataHora'");
            if ($indexes->num_rows() == 0) {
                $this->db->query("ALTER TABLE `audiencias` ADD INDEX `dataHora` (`dataHora`)");
            }

            echo "✅ Coluna 'dataHora' adicionada com sucesso!\n";
        } else {
            echo "✅ Coluna 'dataHora' já existe.\n";
        }

        // Verificar outras colunas importantes
        $requiredColumns = [
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'agendada'],
            'dataCadastro' => ['type' => 'DATETIME', 'null' => false],
        ];

        foreach ($requiredColumns as $column => $config) {
            if (!in_array($column, $columns)) {
                echo "⚠️  Coluna '{$column}' não existe. Adicionando...\n";
                $this->dbforge->add_column('audiencias', [$column => $config]);
                echo "✅ Coluna '{$column}' adicionada!\n";
            }
        }

        echo "\n✅ Estrutura da tabela 'audiencias' corrigida com sucesso!\n";
    }

    public function down()
    {
        // Não remover colunas essenciais
        echo "⚠️  Rollback não recomendado. As colunas são essenciais para o funcionamento do sistema.\n";
    }
}

