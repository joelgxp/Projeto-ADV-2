<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration para criar tabela de advogados responsáveis por processo
 * Suporta múltiplos advogados com diferentes papéis (Principal, Coadjuvante, Estagiário)
 */
class Migration_Create_advogados_processo_table extends CI_Migration
{
    public function up()
    {
        // Criar tabela advogados_processo
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'processos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE,
                'comment' => 'ID do processo'
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE,
                'comment' => 'ID do usuário (advogado)'
            ],
            'papel' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => FALSE,
                'default' => 'coadjuvante',
                'comment' => 'Papel: principal, coadjuvante, estagiario'
            ],
            'data_atribuicao' => [
                'type' => 'DATETIME',
                'null' => FALSE,
                'comment' => 'Data/hora da atribuição do advogado ao processo'
            ],
            'data_remocao' => [
                'type' => 'DATETIME',
                'null' => TRUE,
                'comment' => 'Data/hora da remoção (soft delete)'
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=ativo, 0=removido'
            ],
            'notificado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1=notificado por email, 0=não notificado'
            ],
            'data_notificacao' => [
                'type' => 'DATETIME',
                'null' => TRUE,
                'comment' => 'Data/hora da notificação por email'
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'Observações sobre a atribuição'
            ]
        ]);

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('processos_id');
        $this->dbforge->add_key('usuarios_id');
        $this->dbforge->add_key(['processos_id', 'usuarios_id', 'ativo']);
        
        // Criar tabela
        $this->dbforge->create_table('advogados_processo', TRUE);
        
        // Adicionar foreign keys
        $this->db->query('ALTER TABLE `advogados_processo` 
            ADD CONSTRAINT `fk_advogados_processo_processos` 
            FOREIGN KEY (`processos_id`) 
            REFERENCES `processos` (`idProcessos`) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE');
            
        $this->db->query('ALTER TABLE `advogados_processo` 
            ADD CONSTRAINT `fk_advogados_processo_usuarios` 
            FOREIGN KEY (`usuarios_id`) 
            REFERENCES `usuarios` (`idUsuarios`) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE');
            
        // Índice único para garantir que um advogado só pode ter um papel ativo por processo
        $this->db->query('CREATE UNIQUE INDEX `idx_processo_usuario_ativo` 
            ON `advogados_processo` (`processos_id`, `usuarios_id`, `ativo`) 
            WHERE `ativo` = 1');
    }

    public function down()
    {
        $this->dbforge->drop_table('advogados_processo', TRUE);
    }
}

