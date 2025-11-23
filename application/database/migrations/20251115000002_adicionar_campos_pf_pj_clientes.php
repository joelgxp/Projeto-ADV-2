<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_adicionar_campos_pf_pj_clientes extends CI_Migration
{
    public function up()
    {
        // Campos para Pessoa Física
        $fields_pf = [
            'rg' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'documento',
            ],
            'data_nascimento' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'rg',
            ],
            'estado_civil' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'data_nascimento',
            ],
            'nacionalidade' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'after' => 'estado_civil',
            ],
            'profissao' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'nacionalidade',
            ],
            'nome_mae' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'profissao',
            ],
            'nome_pai' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'nome_mae',
            ],
            'dependentes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'nome_pai',
            ],
            'foto' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'dependentes',
            ],
            'documentos_adicionais' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'foto',
            ],
        ];

        // Campos para Pessoa Jurídica
        $fields_pj = [
            'razao_social' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'nomeCliente',
            ],
            'nome_fantasia' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'razao_social',
            ],
            'inscricao_estadual' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'documento',
            ],
            'inscricao_municipal' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'inscricao_estadual',
            ],
            'data_constituicao' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'inscricao_municipal',
            ],
            'cnae' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'data_constituicao',
            ],
            'site' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'email',
            ],
            'redes_sociais' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'site',
            ],
            'representantes_legais' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'redes_sociais',
            ],
            'socios' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'representantes_legais',
            ],
        ];

        // Adicionar campos PF
        foreach ($fields_pf as $field => $config) {
            if (!$this->db->field_exists($field, 'clientes')) {
                $this->dbforge->add_column('clientes', [$field => $config]);
            }
        }

        // Adicionar campos PJ
        foreach ($fields_pj as $field => $config) {
            if (!$this->db->field_exists($field, 'clientes')) {
                $this->dbforge->add_column('clientes', [$field => $config]);
            }
        }

        // Adicionar campo de observações gerais se não existir
        if (!$this->db->field_exists('observacoes', 'clientes')) {
            $this->dbforge->add_column('clientes', [
                'observacoes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'observacoes_juridicas',
                ],
            ]);
        }

        echo "✅ Campos PF e PJ adicionados com sucesso!\n";
    }

    public function down()
    {
        // Remover campos PF
        $fields_pf = ['rg', 'data_nascimento', 'estado_civil', 'nacionalidade', 'profissao', 'nome_mae', 'nome_pai', 'dependentes', 'foto', 'documentos_adicionais'];
        
        // Remover campos PJ
        $fields_pj = ['razao_social', 'nome_fantasia', 'inscricao_estadual', 'inscricao_municipal', 'data_constituicao', 'cnae', 'site', 'redes_sociais', 'representantes_legais', 'socios'];

        foreach (array_merge($fields_pf, $fields_pj) as $field) {
            if ($this->db->field_exists($field, 'clientes')) {
                $this->dbforge->drop_column('clientes', $field);
            }
        }

        if ($this->db->field_exists('observacoes', 'clientes')) {
            $this->dbforge->drop_column('clientes', 'observacoes');
        }

        echo "✅ Campos PF e PJ removidos com sucesso!\n";
    }
}

