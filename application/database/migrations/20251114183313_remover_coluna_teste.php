<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_remover_coluna_teste extends CI_Migration
{
    public function up()
    {
        // Remover coluna de teste criada pela migration anterior
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

    public function down()
    {
        // Reverter - adicionar a coluna de volta (caso precise)
        if ($this->db->table_exists('usuarios')) {
            $columns = $this->db->list_fields('usuarios');
            
            if (!in_array('teste_migration', $columns)) {
                $this->dbforge->add_column('usuarios', [
                    'teste_migration' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'default' => 'teste',
                    ],
                ]);
                echo "✅ Coluna 'teste_migration' adicionada novamente!\n";
            }
        }
    }
}
