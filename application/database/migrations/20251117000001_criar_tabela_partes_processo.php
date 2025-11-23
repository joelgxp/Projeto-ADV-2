<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_criar_tabela_partes_processo extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'idPartes' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'processos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'FK para cliente já cadastrado (nullable para cadastro rápido)',
            ],
            'tipo_polo' => [
                'type' => 'ENUM',
                'constraint' => ['ativo', 'passivo'],
                'null' => false,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Nome da parte (para cadastro rápido)',
            ],
            'cpf_cnpj' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'CPF/CNPJ da parte (para cadastro rápido)',
            ],
            'tipo_pessoa' => [
                'type' => 'ENUM',
                'constraint' => ['fisica', 'juridica'],
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->dbforge->add_key('idPartes', true);
        $this->dbforge->add_key('processos_id');
        $this->dbforge->add_key('clientes_id');

        $this->dbforge->create_table('partes_processo', true);

        // Adicionar foreign keys
        if ($this->db->table_exists('processos')) {
            $this->db->query('ALTER TABLE `partes_processo` 
                ADD CONSTRAINT `fk_partes_processos` 
                FOREIGN KEY (`processos_id`) 
                REFERENCES `processos` (`idProcessos`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE');
        }

        if ($this->db->table_exists('clientes')) {
            $clientes_columns = $this->db->list_fields('clientes');
            $clientes_id_col = in_array('idClientes', $clientes_columns) ? 'idClientes' : (in_array('id', $clientes_columns) ? 'id' : null);
            
            if ($clientes_id_col) {
                $this->db->query("ALTER TABLE `partes_processo` 
                    ADD CONSTRAINT `fk_partes_clientes` 
                    FOREIGN KEY (`clientes_id`) 
                    REFERENCES `clientes` (`{$clientes_id_col}`) 
                    ON DELETE SET NULL 
                    ON UPDATE CASCADE");
            }
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('partes_processo', true);
    }
}

