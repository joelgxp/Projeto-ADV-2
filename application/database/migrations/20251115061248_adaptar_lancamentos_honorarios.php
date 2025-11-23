<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adaptar_lancamentos_honorarios extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('lancamentos')) {
            $columns = $this->db->list_fields('lancamentos');

            // Adicionar campo processos_id
            if (!in_array('processos_id', $columns)) {
                $this->dbforge->add_column('lancamentos', [
                    'processos_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                        'comment' => 'ID do processo relacionado',
                    ],
                ]);
                echo "✅ Coluna 'processos_id' adicionada à tabela 'lancamentos'!\n";
            }

            // Adicionar campo contratos_id
            if (!in_array('contratos_id', $columns)) {
                $this->dbforge->add_column('lancamentos', [
                    'contratos_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                        'comment' => 'ID do contrato relacionado',
                    ],
                ]);
                echo "✅ Coluna 'contratos_id' adicionada à tabela 'lancamentos'!\n";
            }

            // Verificar se tipo já existe e adaptar valores
            if (in_array('tipo', $columns)) {
                // Atualizar valores existentes se necessário
                // 'honorario' será um novo tipo válido
                echo "✅ Campo 'tipo' já existe - valores 'honorario' e 'custa' serão aceitos!\n";
            } else {
                $this->dbforge->add_column('lancamentos', [
                    'tipo' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'default' => 'receita',
                        'comment' => 'Tipo: honorario, custa, despesa, receita',
                    ],
                ]);
                echo "✅ Coluna 'tipo' adicionada à tabela 'lancamentos'!\n";
            }

            // Adicionar campos de pagamento se não existirem
            if (!in_array('forma_pagamento', $columns)) {
                $this->dbforge->add_column('lancamentos', [
                    'forma_pagamento' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true,
                        'comment' => 'Forma de pagamento',
                    ],
                ]);
                echo "✅ Coluna 'forma_pagamento' adicionada à tabela 'lancamentos'!\n";
            }

            if (!in_array('status_pagamento', $columns)) {
                $this->dbforge->add_column('lancamentos', [
                    'status_pagamento' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                        'default' => 'pendente',
                        'comment' => 'Status: pendente, pago, cancelado',
                    ],
                ]);
                echo "✅ Coluna 'status_pagamento' adicionada à tabela 'lancamentos'!\n";
            }
        } else {
            echo "⚠️  Tabela 'lancamentos' não existe.\n";
        }
    }

    public function down()
    {
        if ($this->db->table_exists('lancamentos')) {
            $columns = $this->db->list_fields('lancamentos');

            if (in_array('processos_id', $columns)) {
                $this->dbforge->drop_column('lancamentos', 'processos_id');
            }
            if (in_array('contratos_id', $columns)) {
                $this->dbforge->drop_column('lancamentos', 'contratos_id');
            }
            if (in_array('forma_pagamento', $columns)) {
                $this->dbforge->drop_column('lancamentos', 'forma_pagamento');
            }
            if (in_array('status_pagamento', $columns)) {
                $this->dbforge->drop_column('lancamentos', 'status_pagamento');
            }

            echo "✅ Colunas de honorários removidas da tabela 'lancamentos'!\n";
        }
    }
}

