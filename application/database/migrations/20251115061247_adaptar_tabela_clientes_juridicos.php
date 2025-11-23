<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adaptar_tabela_clientes_juridicos extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('clientes')) {
            $columns = $this->db->list_fields('clientes');

            // Adicionar campo OAB (se advogado)
            if (!in_array('oab', $columns)) {
                $this->dbforge->add_column('clientes', [
                    'oab' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'comment' => 'Número OAB (se for advogado)',
                        'after' => 'documento',
                    ],
                ]);
                echo "✅ Coluna 'oab' adicionada à tabela 'clientes'!\n";
            }

            // Adicionar campo tipo_cliente
            if (!in_array('tipo_cliente', $columns)) {
                $this->dbforge->add_column('clientes', [
                    'tipo_cliente' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                        'default' => 'fisica',
                        'comment' => 'Tipo: fisica, juridica, advogado',
                        'after' => 'pessoa_fisica',
                    ],
                ]);
                echo "✅ Coluna 'tipo_cliente' adicionada à tabela 'clientes'!\n";
            }

            // Adicionar campo ramo_atividade
            if (!in_array('ramo_atividade', $columns)) {
                $this->dbforge->add_column('clientes', [
                    'ramo_atividade' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                        'comment' => 'Ramo de atividade (para PJ)',
                    ],
                ]);
                echo "✅ Coluna 'ramo_atividade' adicionada à tabela 'clientes'!\n";
            }

            // Adicionar campo observacoes_juridicas
            if (!in_array('observacoes_juridicas', $columns)) {
                $this->dbforge->add_column('clientes', [
                    'observacoes_juridicas' => [
                        'type' => 'TEXT',
                        'null' => true,
                        'comment' => 'Observações específicas do contexto jurídico',
                    ],
                ]);
                echo "✅ Coluna 'observacoes_juridicas' adicionada à tabela 'clientes'!\n";
            }
        } else {
            echo "⚠️  Tabela 'clientes' não existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('clientes')) {
            $columns = $this->db->list_fields('clientes');

            if (in_array('oab', $columns)) {
                $this->dbforge->drop_column('clientes', 'oab');
            }
            if (in_array('tipo_cliente', $columns)) {
                $this->dbforge->drop_column('clientes', 'tipo_cliente');
            }
            if (in_array('ramo_atividade', $columns)) {
                $this->dbforge->drop_column('clientes', 'ramo_atividade');
            }
            if (in_array('observacoes_juridicas', $columns)) {
                $this->dbforge->drop_column('clientes', 'observacoes_juridicas');
            }

            echo "✅ Colunas jurídicas removidas da tabela 'clientes'!\n";
        }
    }
}

