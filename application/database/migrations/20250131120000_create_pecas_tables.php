<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration para criar tabelas do módulo de geração de petições com IA
 * Inclui: modelos_pecas, pecas_geradas, pecas_versoes, logs_geracao_pecas, checklist_revisao, jurisprudencia_base
 */
class Migration_Create_pecas_tables extends CI_Migration
{
    public function up()
    {
        // 1. Tabela modelos_pecas
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'area' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'civel, trabalhista, tributario, etc.',
            ],
            'tipo_peca' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'peticao_inicial, contestacao, replica, recurso, peticao_simples',
            ],
            'corpo' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Template com {{NOME_AUTOR}}, {{NOME_REU}}, etc.',
            ],
            'versao' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => 'Controle de versão',
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('tipo_peca');
        $this->dbforge->add_key('area');
        $this->dbforge->create_table('modelos_pecas', true);

        $this->db->query('ALTER TABLE `modelos_pecas`
            ADD CONSTRAINT `fk_modelos_pecas_usuarios`
            FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');

        // 2. Tabela pecas_geradas
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'processos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'prazos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'audiencias_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'contratos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tipo_peca' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'status' => [
                'type' => "ENUM('rascunho_ia', 'em_revisao', 'aprovado', 'reprovado')",
                'default' => 'rascunho_ia',
            ],
            'modelos_pecas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'usuarios_gerador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'usuarios_aprovador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'data_aprovacao' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tese_principal' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pontos_enfatizar' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tom' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'tecnico, didatico, conciso',
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Para geração sem processo vinculado',
            ],
            'contexto_manual' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Contexto textual quando sem processo',
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('processos_id');
        $this->dbforge->add_key('prazos_id');
        $this->dbforge->add_key('status');
        $this->dbforge->create_table('pecas_geradas', true);

        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_processos`
            FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_prazos`
            FOREIGN KEY (`prazos_id`) REFERENCES `prazos` (`idPrazos`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_audiencias`
            FOREIGN KEY (`audiencias_id`) REFERENCES `audiencias` (`idAudiencias`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_contratos`
            FOREIGN KEY (`contratos_id`) REFERENCES `contratos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_modelos`
            FOREIGN KEY (`modelos_pecas_id`) REFERENCES `modelos_pecas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_usuarios_gerador`
            FOREIGN KEY (`usuarios_gerador_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_usuarios_aprovador`
            FOREIGN KEY (`usuarios_aprovador_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_geradas`
            ADD CONSTRAINT `fk_pecas_geradas_clientes`
            FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE SET NULL ON UPDATE CASCADE');

        // 3. Tabela pecas_versoes
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pecas_geradas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'numero_versao' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'conteudo' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'origem' => [
                'type' => "ENUM('ia', 'editado_manual', 'aprovado')",
                'null' => false,
            ],
            'diff_anterior' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON diff em relação à versão anterior',
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('pecas_geradas_id');
        $this->dbforge->create_table('pecas_versoes', true);

        $this->db->query('ALTER TABLE `pecas_versoes`
            ADD CONSTRAINT `fk_pecas_versoes_pecas`
            FOREIGN KEY (`pecas_geradas_id`) REFERENCES `pecas_geradas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `pecas_versoes`
            ADD CONSTRAINT `fk_pecas_versoes_usuarios`
            FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');

        // 4. Tabela logs_geracao_pecas
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pecas_geradas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'pecas_versoes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'prompt' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'contexto_ids' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: processo_id, anexos_ids, etc.',
            ],
            'resposta_llm' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'modelo_llm' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'chamada_local' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1=local, 0=cloud',
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('pecas_geradas_id');
        $this->dbforge->create_table('logs_geracao_pecas', true);

        $this->db->query('ALTER TABLE `logs_geracao_pecas`
            ADD CONSTRAINT `fk_logs_geracao_pecas_geradas`
            FOREIGN KEY (`pecas_geradas_id`) REFERENCES `pecas_geradas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `logs_geracao_pecas`
            ADD CONSTRAINT `fk_logs_geracao_pecas_versoes`
            FOREIGN KEY (`pecas_versoes_id`) REFERENCES `pecas_versoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `logs_geracao_pecas`
            ADD CONSTRAINT `fk_logs_geracao_pecas_usuarios`
            FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');

        // 5. Tabela checklist_revisao
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pecas_geradas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'item' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'marcado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('pecas_geradas_id');
        $this->dbforge->create_table('checklist_revisao', true);

        $this->db->query('ALTER TABLE `checklist_revisao`
            ADD CONSTRAINT `fk_checklist_pecas`
            FOREIGN KEY (`pecas_geradas_id`) REFERENCES `pecas_geradas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `checklist_revisao`
            ADD CONSTRAINT `fk_checklist_usuarios`
            FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE');

        // 6. Tabela jurisprudencia_base (opcional RAG)
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tribunal' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'numero_processo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'data' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'trecho' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'area' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'assunto' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'dataCadastro' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('area');
        $this->dbforge->add_key('assunto');
        $this->dbforge->create_table('jurisprudencia_base', true);
    }

    public function down()
    {
        $this->dbforge->drop_table('jurisprudencia_base', true);
        $this->dbforge->drop_table('checklist_revisao', true);
        $this->dbforge->drop_table('logs_geracao_pecas', true);
        $this->dbforge->drop_table('pecas_versoes', true);
        $this->dbforge->drop_table('pecas_geradas', true);
        $this->dbforge->drop_table('modelos_pecas', true);
    }
}
