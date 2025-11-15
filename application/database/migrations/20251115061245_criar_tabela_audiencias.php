<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_audiencias extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('audiencias')) {
            $this->dbforge->add_field([
                'idAudiencias' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'auto_increment' => true,
                ],
                'processos_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'tipo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                    'comment' => 'Tipo: audiencia, conciliacao, depoimento, etc.',
                ],
                'dataHora' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'local' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'observacoes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => false,
                    'default' => 'agendada',
                    'comment' => 'Status: agendada, realizada, cancelada',
                ],
                'usuarios_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'Usuário responsável',
                ],
                'alertado' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => '0',
                    'comment' => 'Se já foi enviado alerta',
                ],
                'dataCadastro' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->dbforge->add_key('idAudiencias', true);
            $this->dbforge->add_key('processos_id');
            $this->dbforge->add_key('dataHora');
            $this->dbforge->add_key('status');

            $this->dbforge->create_table('audiencias', true);
            $this->db->query('ALTER TABLE `audiencias` ENGINE = InnoDB');
            $this->db->query('ALTER TABLE `audiencias` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Foreign key para processos
            if ($this->db->table_exists('processos')) {
                $this->db->query("ALTER TABLE `audiencias` ADD CONSTRAINT `fk_audiencias_processos` FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE");
            }

            echo "✅ Tabela 'audiencias' criada com sucesso!\n";
        } else {
            echo "⚠️  Tabela 'audiencias' já existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('audiencias')) {
            $this->dbforge->drop_table('audiencias', true);
            echo "✅ Tabela 'audiencias' removida com sucesso!\n";
        }
    }
}

