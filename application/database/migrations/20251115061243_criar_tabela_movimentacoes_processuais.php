<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_movimentacoes_processuais extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('movimentacoes_processuais')) {
            $this->dbforge->add_field([
                'idMovimentacoes' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'auto_increment' => true,
                ],
                'processos_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'dataMovimentacao' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'titulo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => false,
                ],
                'descricao' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'tipo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Tipo de movimentação',
                ],
                'origem' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => false,
                    'default' => 'manual',
                    'comment' => 'Origem: manual ou api_cnj',
                ],
                'dados_api' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Dados JSON da API CNJ',
                ],
                'importado_api' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => '0',
                ],
                'usuarios_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'Usuário que cadastrou (se manual)',
                ],
                'dataCadastro' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->dbforge->add_key('idMovimentacoes', true);
            $this->dbforge->add_key('processos_id');
            $this->dbforge->add_key('dataMovimentacao');

            $this->dbforge->create_table('movimentacoes_processuais', true);
            $this->db->query('ALTER TABLE `movimentacoes_processuais` ENGINE = InnoDB');
            $this->db->query('ALTER TABLE `movimentacoes_processuais` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Foreign key para processos
            if ($this->db->table_exists('processos')) {
                $this->db->query("ALTER TABLE `movimentacoes_processuais` ADD CONSTRAINT `fk_movimentacoes_processos` FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE");
            }

            echo "✅ Tabela 'movimentacoes_processuais' criada com sucesso!\n";
        } else {
            echo "⚠️  Tabela 'movimentacoes_processuais' já existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('movimentacoes_processuais')) {
            $this->dbforge->drop_table('movimentacoes_processuais', true);
            echo "✅ Tabela 'movimentacoes_processuais' removida com sucesso!\n";
        }
    }
}

