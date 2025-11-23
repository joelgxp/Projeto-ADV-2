<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_renomear_servicos_para_servicos_juridicos extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('servicos') && !$this->db->table_exists('servicos_juridicos')) {
            // Renomear tabela
            $this->db->query('RENAME TABLE `servicos` TO `servicos_juridicos`');
            echo "✅ Tabela 'servicos' renomeada para 'servicos_juridicos'!\n";

            // Adicionar campos jurídicos se não existirem
            $columns = $this->db->list_fields('servicos_juridicos');

            if (!in_array('tipo_servico', $columns)) {
                $this->dbforge->add_column('servicos_juridicos', [
                    'tipo_servico' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                        'null' => true,
                        'comment' => 'Tipo de serviço jurídico',
                    ],
                ]);
                echo "✅ Coluna 'tipo_servico' adicionada!\n";
            }

            if (!in_array('modelo_peca_id', $columns)) {
                $this->dbforge->add_column('servicos_juridicos', [
                    'modelo_peca_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                        'comment' => 'ID do modelo de peça relacionado',
                    ],
                ]);
                echo "✅ Coluna 'modelo_peca_id' adicionada!\n";
            }

            if (!in_array('valor_base', $columns)) {
                $this->dbforge->add_column('servicos_juridicos', [
                    'valor_base' => [
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'null' => true,
                        'default' => '0.00',
                        'comment' => 'Valor base do serviço',
                    ],
                ]);
                echo "✅ Coluna 'valor_base' adicionada!\n";
            }

            if (!in_array('tempo_estimado', $columns)) {
                $this->dbforge->add_column('servicos_juridicos', [
                    'tempo_estimado' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                        'comment' => 'Tempo estimado em horas',
                    ],
                ]);
                echo "✅ Coluna 'tempo_estimado' adicionada!\n";
            }
        } elseif ($this->db->table_exists('servicos_juridicos')) {
            echo "⚠️  Tabela 'servicos_juridicos' já existe.\n";
        } else {
            echo "⚠️  Tabela 'servicos' não existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('servicos_juridicos') && !$this->db->table_exists('servicos')) {
            // Remover colunas adicionadas
            $columns = $this->db->list_fields('servicos_juridicos');

            if (in_array('tipo_servico', $columns)) {
                $this->dbforge->drop_column('servicos_juridicos', 'tipo_servico');
            }
            if (in_array('modelo_peca_id', $columns)) {
                $this->dbforge->drop_column('servicos_juridicos', 'modelo_peca_id');
            }
            if (in_array('valor_base', $columns)) {
                $this->dbforge->drop_column('servicos_juridicos', 'valor_base');
            }
            if (in_array('tempo_estimado', $columns)) {
                $this->dbforge->drop_column('servicos_juridicos', 'tempo_estimado');
            }

            // Renomear de volta
            $this->db->query('RENAME TABLE `servicos_juridicos` TO `servicos`');
            echo "✅ Tabela 'servicos_juridicos' renomeada de volta para 'servicos'!\n";
        }
    }
}

