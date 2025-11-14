<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_fix_check_credentials_error extends CI_Migration
{
    public function up()
    {
        // Verificar se a tabela usuarios existe
        if ($this->db->table_exists('usuarios')) {
            // Adicionar índice no email se não existir (melhora performance da busca)
            $this->db->query("
                CREATE INDEX IF NOT EXISTS idx_email_situacao 
                ON usuarios(email, situacao)
            ");
            
            // Verificar se a coluna situacao existe, se não, criar
            $columns = $this->db->list_fields('usuarios');
            if (!in_array('situacao', $columns)) {
                $this->dbforge->add_column('usuarios', [
                    'situacao' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'default' => 1,
                        'null' => false,
                    ],
                ]);
            }
        } else {
            log_message('error', 'Migration: Tabela usuarios não existe!');
        }
    }

    public function down()
    {
        // Remover índice se existir
        if ($this->db->table_exists('usuarios')) {
            $this->db->query("DROP INDEX IF EXISTS idx_email_situacao ON usuarios");
        }
    }
}
