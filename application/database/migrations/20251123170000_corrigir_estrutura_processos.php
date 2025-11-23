<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_corrigir_estrutura_processos extends CI_Migration
{
    public function up()
    {
        // Verificar se tabela processos existe
        if (!$this->db->table_exists('processos')) {
            echo "⚠️  Tabela 'processos' não existe. Execute a migration criar_tabela_processos primeiro.\n";
            return;
        }

        $columns = $this->db->list_fields('processos');

        // 1. Verificar e adicionar coluna numeroProcesso
        if (!in_array('numeroProcesso', $columns)) {
            echo "⚠️  Coluna 'numeroProcesso' não existe. Adicionando...\n";
            
            $this->dbforge->add_column('processos', [
                'numeroProcesso' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                    'default' => '',
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

        // 2. Verificar e adicionar coluna clientes_id
        if (!in_array('clientes_id', $columns)) {
            echo "⚠️  Coluna 'clientes_id' não existe. Adicionando...\n";
            
            $this->dbforge->add_column('processos', [
                'clientes_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'ID do cliente vinculado ao processo',
                ],
            ]);

            // Adicionar índice se não existir
            $indexes = $this->db->query("SHOW INDEXES FROM processos WHERE Key_name = 'clientes_id'");
            if ($indexes->num_rows() == 0) {
                $this->db->query("ALTER TABLE `processos` ADD INDEX `clientes_id` (`clientes_id`)");
            }

            // Adicionar foreign key se a tabela clientes existir
            if ($this->db->table_exists('clientes')) {
                $clientes_columns = $this->db->list_fields('clientes');
                $clientes_pk = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
                
                if ($clientes_pk) {
                    // Verificar se a constraint já existe
                    $fk_check = $this->db->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'processos' 
                        AND COLUMN_NAME = 'clientes_id' 
                        AND REFERENCED_TABLE_NAME = 'clientes'
                    ");
                    
                    if ($fk_check->num_rows() == 0) {
                        $this->db->query("ALTER TABLE `processos` ADD CONSTRAINT `fk_processos_clientes` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`{$clientes_pk}`) ON DELETE SET NULL ON UPDATE CASCADE");
                    }
                }
            }

            echo "✅ Coluna 'clientes_id' adicionada com sucesso!\n";
        } else {
            echo "✅ Coluna 'clientes_id' já existe.\n";
        }

        // 3. Verificar outras colunas importantes
        $requiredColumns = [
            'classe' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'assunto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false, 'default' => 'em_andamento'],
            'dataCadastro' => ['type' => 'DATETIME', 'null' => false],
        ];

        foreach ($requiredColumns as $column => $config) {
            if (!in_array($column, $columns)) {
                echo "⚠️  Coluna '{$column}' não existe. Adicionando...\n";
                $this->dbforge->add_column('processos', [$column => $config]);
                echo "✅ Coluna '{$column}' adicionada!\n";
            }
        }

        echo "\n✅ Estrutura da tabela 'processos' corrigida com sucesso!\n";
    }

    public function down()
    {
        // Não remover colunas essenciais
        echo "⚠️  Rollback não recomendado. As colunas são essenciais para o funcionamento do sistema.\n";
    }
}

