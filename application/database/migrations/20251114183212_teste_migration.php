<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_teste_migration extends CI_Migration
{
    public function up()
    {
        // Migration de teste - adiciona coluna de teste na tabela usuarios
        // Esta coluna será removida no método down()
        
        if ($this->db->table_exists('usuarios')) {
            // Verificar se a coluna já existe antes de adicionar
            $columns = $this->db->list_fields('usuarios');
            
            if (!in_array('teste_migration', $columns)) {
                $this->dbforge->add_column('usuarios', [
                    'teste_migration' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'default' => 'teste',
                        'comment' => 'Coluna de teste criada pela migration - pode ser removida'
                    ],
                ]);
                
                echo "✅ Coluna 'teste_migration' adicionada com sucesso!\n";
            } else {
                echo "⚠️  Coluna 'teste_migration' já existe.\n";
            }
        } else {
            echo "❌ Tabela 'usuarios' não existe!\n";
        }
    }

    public function down()
    {
        // Reverter a migration - remover a coluna de teste
        if ($this->db->table_exists('usuarios')) {
            $columns = $this->db->list_fields('usuarios');
            
            if (in_array('teste_migration', $columns)) {
                $this->dbforge->drop_column('usuarios', 'teste_migration');
                echo "✅ Coluna 'teste_migration' removida com sucesso!\n";
            } else {
                echo "⚠️  Coluna 'teste_migration' não existe.\n";
            }
        }
    }
}
