<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_processos extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('processos')) {
            $this->dbforge->add_field([
                'idProcessos' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'auto_increment' => true,
                ],
                'numeroProcesso' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                    'comment' => 'Número do processo (CNJ) - aceita formatado ou limpo',
                ],
                'classe' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Classe processual',
                ],
                'assunto' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Assunto do processo',
                ],
                'tipo_processo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Tipo: civel, trabalhista, tributario, criminal, etc.',
                ],
                'vara' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'comarca' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'tribunal' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Tribunal responsável',
                ],
                'segmento' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Segmento: estadual, federal, trabalho, eleitoral, militar',
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false,
                    'default' => 'em_andamento',
                    'comment' => 'Status: em_andamento, suspenso, arquivado, finalizado',
                ],
                'valorCausa' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                    'default' => '0.00',
                ],
                'dataDistribuicao' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'dataUltimaMovimentacao' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'clientes_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'usuarios_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'Advogado responsável',
                ],
                'observacoes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ultimaConsultaAPI' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Data da última consulta na API CNJ',
                ],
                'proximaConsultaAPI' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Data da próxima consulta agendada na API',
                ],
                'dataCadastro' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->dbforge->add_key('idProcessos', true);
            $this->dbforge->add_key('numeroProcesso');
            $this->dbforge->add_key('clientes_id');
            $this->dbforge->add_key('usuarios_id');
            $this->dbforge->add_key('status');

            $this->dbforge->create_table('processos', true);
            $this->db->query('ALTER TABLE `processos` ENGINE = InnoDB');
            $this->db->query('ALTER TABLE `processos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // Adicionar foreign keys se as tabelas existirem
            if ($this->db->table_exists('clientes')) {
                $clientes_id_col = $this->db->list_fields('clientes');
                $clientes_pk = in_array('idClientes', $clientes_id_col) ? 'idClientes' : (in_array('id', $clientes_id_col) ? 'id' : null);
                if ($clientes_pk) {
                    $this->db->query("ALTER TABLE `processos` ADD CONSTRAINT `fk_processos_clientes` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`{$clientes_pk}`) ON DELETE SET NULL ON UPDATE CASCADE");
                }
            }

            if ($this->db->table_exists('usuarios')) {
                $usuarios_id_col = $this->db->list_fields('usuarios');
                $usuarios_pk = in_array('idUsuarios', $usuarios_id_col) ? 'idUsuarios' : (in_array('id', $usuarios_id_col) ? 'id' : null);
                if ($usuarios_pk) {
                    $this->db->query("ALTER TABLE `processos` ADD CONSTRAINT `fk_processos_usuarios` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`{$usuarios_pk}`) ON DELETE SET NULL ON UPDATE CASCADE");
                }
            }

            echo "✅ Tabela 'processos' criada com sucesso!\n";
        } else {
            echo "⚠️  Tabela 'processos' já existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('processos')) {
            $this->dbforge->drop_table('processos', true);
            echo "✅ Tabela 'processos' removida com sucesso!\n";
        }
    }
}

