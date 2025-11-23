<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_corrigir_coluna_numero_processo extends CI_Migration
{
    public function up()
    {
        // Verificar se tabela processos existe
        if (!$this->db->table_exists('processos')) {
            echo "⚠️  Tabela 'processos' não existe. Execute a migration criar_tabela_processos primeiro.\n";
            return;
        }

        $columns = $this->db->list_fields('processos');

        // Verificar se a coluna numeroProcesso existe
        if (!in_array('numeroProcesso', $columns)) {
            echo "⚠️  Coluna 'numeroProcesso' não existe. Adicionando...\n";
            
            // Adicionar coluna numeroProcesso
            $this->dbforge->add_column('processos', [
                'numeroProcesso' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                    'comment' => 'Número do processo (CNJ) - aceita formatado ou limpo',
                    'after' => 'idProcessos',
                ],
            ]);

            // Adicionar índice se não existir
            $indexes = $this->db->query("SHOW INDEXES FROM processos WHERE Key_name = 'numeroProcesso'");
            if ($indexes->num_rows() == 0) {
                $this->db->query("ALTER TABLE `processos` ADD INDEX `numeroProcesso` (`numeroProcesso`)");
            }

            echo "✅ Coluna 'numeroProcesso' adicionada com sucesso!\n";
        } else {
            echo "✅ Coluna 'numeroProcesso' já existe.\n";
        }

        // Verificar outras colunas importantes
        $requiredColumns = [
            'classe' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'assunto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false, 'default' => 'em_andamento'],
        ];

        foreach ($requiredColumns as $column => $config) {
            if (!in_array($column, $columns)) {
                echo "⚠️  Coluna '{$column}' não existe. Adicionando...\n";
                $this->dbforge->add_column('processos', [$column => $config]);
                echo "✅ Coluna '{$column}' adicionada!\n";
            }
        }
    }

    public function down()
    {
        // Não remover a coluna numeroProcesso pois é essencial
        // Se necessário, execute a migration down da criação da tabela
        echo "⚠️  Rollback não recomendado. A coluna numeroProcesso é essencial.\n";
    }
}

