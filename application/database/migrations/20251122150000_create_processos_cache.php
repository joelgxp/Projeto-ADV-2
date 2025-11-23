<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_processos_cache extends CI_Migration
{
    public function up()
    {
        if (! $this->db->table_exists('processos_cache')) {
            $this->dbforge->add_field([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'numeroProcesso' => [
                    'type' => 'VARCHAR',
                    'constraint' => '32',
                    'null' => false,
                ],
                'payload' => [
                    'type' => 'LONGTEXT',
                    'null' => false,
                ],
                'hash_payload' => [
                    'type' => 'VARCHAR',
                    'constraint' => '64',
                    'null' => false,
                ],
                'ultima_atualizacao' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'ultimo_fetch' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('numeroProcesso');
            $this->dbforge->create_table('processos_cache');

            $this->db->query('CREATE UNIQUE INDEX idx_processos_cache_numero ON processos_cache (numeroProcesso)');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('processos_cache', true);
    }
}

