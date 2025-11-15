<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_documentos_processuais extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('documentos_processuais')) {
            $this->dbforge->add_field([
                'idDocumentos' => [
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
                'titulo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => false,
                ],
                'descricao' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'arquivo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => false,
                    'comment' => 'Nome do arquivo',
                ],
                'tipo_documento' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Tipo: peticao, sentenca, documento, etc.',
                ],
                'dataUpload' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'usuarios_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'Usuário que fez upload',
                ],
                'tamanho' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'null' => true,
                    'comment' => 'Tamanho do arquivo em bytes',
                ],
                'mime_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Tipo MIME do arquivo',
                ],
            ]);

            $this->dbforge->add_key('idDocumentos', true);
            $this->dbforge->add_key('processos_id');
            $this->dbforge->add_key('tipo_documento');

            $this->dbforge->create_table('documentos_processuais', true);
            $this->db->query('ALTER TABLE `documentos_processuais` ENGINE = InnoDB');
            $this->db->query('ALTER TABLE `documentos_processuais` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Foreign key para processos
            if ($this->db->table_exists('processos')) {
                $this->db->query("ALTER TABLE `documentos_processuais` ADD CONSTRAINT `fk_documentos_processos` FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE");
            }

            echo "✅ Tabela 'documentos_processuais' criada com sucesso!\n";
        } else {
            echo "⚠️  Tabela 'documentos_processuais' já existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('documentos_processuais')) {
            $this->dbforge->drop_table('documentos_processuais', true);
            echo "✅ Tabela 'documentos_processuais' removida com sucesso!\n";
        }
    }
}

