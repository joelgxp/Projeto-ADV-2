<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_estender_logs_auditoria extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('logs')) {
            echo "⚠️  Tabela 'logs' não existe.\n";
            return;
        }

        $columns = $this->db->list_fields('logs');

        // Adicionar coluna entidade_tipo
        if (!in_array('entidade_tipo', $columns)) {
            $this->dbforge->add_column('logs', [
                'entidade_tipo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Tipo de entidade acessada (cliente, processo, etc.)',
                ],
            ]);
            echo "✅ Coluna 'entidade_tipo' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna entidade_id
        if (!in_array('entidade_id', $columns)) {
            $this->dbforge->add_column('logs', [
                'entidade_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'comment' => 'ID da entidade acessada',
                ],
            ]);
            echo "✅ Coluna 'entidade_id' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna acao
        if (!in_array('acao', $columns)) {
            $this->dbforge->add_column('logs', [
                'acao' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Ação realizada (visualizar, editar, excluir)',
                ],
            ]);
            echo "✅ Coluna 'acao' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna campo_alterado
        if (!in_array('campo_alterado', $columns)) {
            $this->dbforge->add_column('logs', [
                'campo_alterado' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Campo específico alterado (se aplicável)',
                ],
            ]);
            echo "✅ Coluna 'campo_alterado' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna valor_anterior
        if (!in_array('valor_anterior', $columns)) {
            $this->dbforge->add_column('logs', [
                'valor_anterior' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Valor anterior (para edições)',
                ],
            ]);
            echo "✅ Coluna 'valor_anterior' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna valor_novo
        if (!in_array('valor_novo', $columns)) {
            $this->dbforge->add_column('logs', [
                'valor_novo' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Valor novo (para edições)',
                ],
            ]);
            echo "✅ Coluna 'valor_novo' adicionada à tabela 'logs'!\n";
        }

        // Adicionar coluna dados_sensiveis
        if (!in_array('dados_sensiveis', $columns)) {
            $this->dbforge->add_column('logs', [
                'dados_sensiveis' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                    'comment' => 'Flag indicando se dados sensíveis foram acessados',
                ],
            ]);
            echo "✅ Coluna 'dados_sensiveis' adicionada à tabela 'logs'!\n";
        }

        echo "\n✅ Tabela 'logs' estendida com sucesso para auditoria!\n";
    }

    public function down()
    {
        if (!$this->db->table_exists('logs')) {
            echo "⚠️  Tabela 'logs' não existe.\n";
            return;
        }

        $columns = $this->db->list_fields('logs');

        if (in_array('entidade_tipo', $columns)) {
            $this->dbforge->drop_column('logs', 'entidade_tipo');
        }
        if (in_array('entidade_id', $columns)) {
            $this->dbforge->drop_column('logs', 'entidade_id');
        }
        if (in_array('acao', $columns)) {
            $this->dbforge->drop_column('logs', 'acao');
        }
        if (in_array('campo_alterado', $columns)) {
            $this->dbforge->drop_column('logs', 'campo_alterado');
        }
        if (in_array('valor_anterior', $columns)) {
            $this->dbforge->drop_column('logs', 'valor_anterior');
        }
        if (in_array('valor_novo', $columns)) {
            $this->dbforge->drop_column('logs', 'valor_novo');
        }
        if (in_array('dados_sensiveis', $columns)) {
            $this->dbforge->drop_column('logs', 'dados_sensiveis');
        }

        echo "✅ Colunas de auditoria removidas da tabela 'logs'!\n";
    }
}

