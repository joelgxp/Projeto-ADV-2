<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adicionar_campos_pf_pj_clientes extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('clientes')) {
            echo "⚠️  Tabela 'clientes' não existe.\n";
            return;
        }

        $columns = $this->db->list_fields('clientes');

        // ========== CAMPOS PARA PESSOA FÍSICA (PF) ==========
        
        // RG
        if (!in_array('rg', $columns)) {
            $this->dbforge->add_column('clientes', [
                'rg' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'comment' => 'RG (Registro Geral) - para PF',
                    'after' => 'documento',
                ],
            ]);
            echo "✅ Coluna 'rg' adicionada à tabela 'clientes'!\n";
        }

        // Filiação (nome do pai e mãe)
        if (!in_array('filiacao', $columns)) {
            $this->dbforge->add_column('clientes', [
                'filiacao' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Filiação (nome do pai e mãe) - para PF',
                    'after' => 'rg',
                ],
            ]);
            echo "✅ Coluna 'filiacao' adicionada à tabela 'clientes'!\n";
        }

        // Profissão
        if (!in_array('profissao', $columns)) {
            $this->dbforge->add_column('clientes', [
                'profissao' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'comment' => 'Profissão - para PF',
                    'after' => 'filiacao',
                ],
            ]);
            echo "✅ Coluna 'profissao' adicionada à tabela 'clientes'!\n";
        }

        // ========== CAMPOS PARA PESSOA JURÍDICA (PJ) ==========
        
        // Razão Social (separado do nome fantasia)
        if (!in_array('razao_social', $columns)) {
            $this->dbforge->add_column('clientes', [
                'razao_social' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'comment' => 'Razão Social - para PJ (nomeCliente pode ser nome fantasia)',
                    'after' => 'nomeCliente',
                ],
            ]);
            echo "✅ Coluna 'razao_social' adicionada à tabela 'clientes'!\n";
        }

        // Inscrição Estadual
        if (!in_array('inscricao_estadual', $columns)) {
            $this->dbforge->add_column('clientes', [
                'inscricao_estadual' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Inscrição Estadual - para PJ',
                    'after' => 'documento',
                ],
            ]);
            echo "✅ Coluna 'inscricao_estadual' adicionada à tabela 'clientes'!\n";
        }

        // Inscrição Municipal
        if (!in_array('inscricao_municipal', $columns)) {
            $this->dbforge->add_column('clientes', [
                'inscricao_municipal' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'comment' => 'Inscrição Municipal - para PJ',
                    'after' => 'inscricao_estadual',
                ],
            ]);
            echo "✅ Coluna 'inscricao_municipal' adicionada à tabela 'clientes'!\n";
        }

        // Representantes Legais (JSON ou TEXT)
        if (!in_array('representantes_legais', $columns)) {
            $this->dbforge->add_column('clientes', [
                'representantes_legais' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Representantes Legais (JSON ou texto) - para PJ',
                ],
            ]);
            echo "✅ Coluna 'representantes_legais' adicionada à tabela 'clientes'!\n";
        }

        // Sócios (JSON ou TEXT)
        if (!in_array('socios', $columns)) {
            $this->dbforge->add_column('clientes', [
                'socios' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Sócios (JSON ou texto) - para PJ',
                ],
            ]);
            echo "✅ Coluna 'socios' adicionada à tabela 'clientes'!\n";
        }

        // Campo adicional para múltiplos e-mails
        if (!in_array('emails_adicionais', $columns)) {
            $this->dbforge->add_column('clientes', [
                'emails_adicionais' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'E-mails adicionais (JSON ou texto separado por vírgula)',
                    'after' => 'email',
                ],
            ]);
            echo "✅ Coluna 'emails_adicionais' adicionada à tabela 'clientes'!\n";
        }

        // Campo adicional para múltiplos telefones
        if (!in_array('telefones_adicionais', $columns)) {
            $this->dbforge->add_column('clientes', [
                'telefones_adicionais' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Telefones adicionais (JSON ou texto separado por vírgula)',
                    'after' => 'celular',
                ],
            ]);
            echo "✅ Coluna 'telefones_adicionais' adicionada à tabela 'clientes'!\n";
        }

        echo "\n✅ Todos os campos PF/PJ foram adicionados com sucesso!\n";
    }

    public function down()
    {
        if (!$this->db->table_exists('clientes')) {
            echo "⚠️  Tabela 'clientes' não existe.\n";
            return;
        }

        $columns = $this->db->list_fields('clientes');

        // Remover campos PF
        if (in_array('rg', $columns)) {
            $this->dbforge->drop_column('clientes', 'rg');
        }
        if (in_array('filiacao', $columns)) {
            $this->dbforge->drop_column('clientes', 'filiacao');
        }
        if (in_array('profissao', $columns)) {
            $this->dbforge->drop_column('clientes', 'profissao');
        }

        // Remover campos PJ
        if (in_array('razao_social', $columns)) {
            $this->dbforge->drop_column('clientes', 'razao_social');
        }
        if (in_array('inscricao_estadual', $columns)) {
            $this->dbforge->drop_column('clientes', 'inscricao_estadual');
        }
        if (in_array('inscricao_municipal', $columns)) {
            $this->dbforge->drop_column('clientes', 'inscricao_municipal');
        }
        if (in_array('representantes_legais', $columns)) {
            $this->dbforge->drop_column('clientes', 'representantes_legais');
        }
        if (in_array('socios', $columns)) {
            $this->dbforge->drop_column('clientes', 'socios');
        }

        // Remover campos adicionais
        if (in_array('emails_adicionais', $columns)) {
            $this->dbforge->drop_column('clientes', 'emails_adicionais');
        }
        if (in_array('telefones_adicionais', $columns)) {
            $this->dbforge->drop_column('clientes', 'telefones_adicionais');
        }

        echo "✅ Campos PF/PJ removidos da tabela 'clientes'!\n";
    }
}
