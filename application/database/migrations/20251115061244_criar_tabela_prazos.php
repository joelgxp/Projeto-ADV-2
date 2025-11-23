<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_prazos extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('prazos')) {
            $this->dbforge->add_field([
                'idPrazos' => [
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
                    'comment' => 'Tipo: intimacao, audiencia, recurso, etc.',
                ],
                'descricao' => [
                    'type' => 'TEXT',
                    'null' => false,
                ],
                'dataPrazo' => [
                    'type' => 'DATE',
                    'null' => false,
                    'comment' => 'Data do prazo',
                ],
                'dataVencimento' => [
                    'type' => 'DATE',
                    'null' => false,
                    'comment' => 'Data de vencimento do prazo',
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => false,
                    'default' => 'pendente',
                    'comment' => 'Status: pendente, vencido, concluido',
                ],
                'alertado' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => '0',
                    'comment' => 'Se já foi enviado alerta',
                ],
                'usuarios_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'Usuário responsável',
                ],
                'prioridade' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'default' => 'normal',
                    'comment' => 'Prioridade: baixa, normal, alta, critica',
                ],
                'dataCadastro' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->dbforge->add_key('idPrazos', true);
            $this->dbforge->add_key('processos_id');
            $this->dbforge->add_key('dataVencimento');
            $this->dbforge->add_key('status');

            $this->dbforge->create_table('prazos', true);
            $this->db->query('ALTER TABLE `prazos` ENGINE = InnoDB');
            $this->db->query('ALTER TABLE `prazos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Foreign key para processos
            if ($this->db->table_exists('processos')) {
                $this->db->query("ALTER TABLE `prazos` ADD CONSTRAINT `fk_prazos_processos` FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE");
            }

            echo "✅ Tabela 'prazos' criada com sucesso!\n";
        } else {
            echo "⚠️  Tabela 'prazos' já existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('prazos')) {
            $this->dbforge->drop_table('prazos', true);
            echo "✅ Tabela 'prazos' removida com sucesso!\n";
        }
    }
}

