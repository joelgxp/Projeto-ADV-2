<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_planos extends CI_Migration
{
    public function up()
    {
        // Criar tabela de planos
        $this->dbforge->add_field([
            'idPlanos' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'valor_mensal' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'limite_processos' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '0 = ilimitado',
            ],
            'limite_prazos' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '0 = ilimitado',
            ],
            'limite_audiencias' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '0 = ilimitado',
            ],
            'limite_documentos' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '0 = ilimitado',
            ],
            'acesso_portal' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Permite acesso ao portal do cliente',
            ],
            'acesso_api' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Permite acesso à API',
            ],
            'suporte_prioritario' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Suporte prioritário',
            ],
            'relatorios_avancados' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Acesso a relatórios avançados',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1 = ativo, 0 = inativo',
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'dataAtualizacao' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->dbforge->add_key('idPlanos', true);
        $this->dbforge->create_table('planos', true);

        // Inserir planos padrão
        $planos_padrao = [
            [
                'nome' => 'Básico',
                'descricao' => 'Plano básico com funcionalidades essenciais',
                'valor_mensal' => 0.00,
                'limite_processos' => 10,
                'limite_prazos' => 50,
                'limite_audiencias' => 20,
                'limite_documentos' => 100,
                'acesso_portal' => 1,
                'acesso_api' => 0,
                'suporte_prioritario' => 0,
                'relatorios_avancados' => 0,
                'status' => 1,
                'dataCadastro' => date('Y-m-d H:i:s'),
            ],
            [
                'nome' => 'Profissional',
                'descricao' => 'Plano profissional com recursos avançados',
                'valor_mensal' => 99.90,
                'limite_processos' => 50,
                'limite_prazos' => 200,
                'limite_audiencias' => 100,
                'limite_documentos' => 500,
                'acesso_portal' => 1,
                'acesso_api' => 1,
                'suporte_prioritario' => 1,
                'relatorios_avancados' => 1,
                'status' => 1,
                'dataCadastro' => date('Y-m-d H:i:s'),
            ],
            [
                'nome' => 'Enterprise',
                'descricao' => 'Plano enterprise com recursos ilimitados',
                'valor_mensal' => 299.90,
                'limite_processos' => 0,
                'limite_prazos' => 0,
                'limite_audiencias' => 0,
                'limite_documentos' => 0,
                'acesso_portal' => 1,
                'acesso_api' => 1,
                'suporte_prioritario' => 1,
                'relatorios_avancados' => 1,
                'status' => 1,
                'dataCadastro' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->insert_batch('planos', $planos_padrao);

        // Adicionar coluna planos_id na tabela clientes se não existir
        if (!$this->db->field_exists('planos_id', 'clientes')) {
            $fields = [
                'planos_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'fornecedor',
                ],
            ];
            $this->dbforge->add_column('clientes', $fields);

            // Adicionar foreign key
            $this->db->query('ALTER TABLE `clientes` ADD CONSTRAINT `fk_clientes_planos` FOREIGN KEY (`planos_id`) REFERENCES `planos` (`idPlanos`) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        echo "✅ Tabela de planos criada com sucesso!\n";
    }

    public function down()
    {
        // Remover foreign key
        $this->db->query('ALTER TABLE `clientes` DROP FOREIGN KEY `fk_clientes_planos`');
        
        // Remover coluna planos_id
        if ($this->db->field_exists('planos_id', 'clientes')) {
            $this->dbforge->drop_column('clientes', 'planos_id');
        }

        // Remover tabela planos
        $this->dbforge->drop_table('planos', true);
        
        echo "✅ Tabela de planos removida com sucesso!\n";
    }
}

