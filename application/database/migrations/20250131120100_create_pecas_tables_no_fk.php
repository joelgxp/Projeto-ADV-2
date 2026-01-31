<?php

defined('BASEPATH') or exit('No script access allowed');

/**
 * Migration de fallback: cria tabelas do módulo Petições IA SEM foreign keys.
 * Use quando a migration principal falhar com erro 150 (FK incorrectly formed).
 * As tabelas funcionam normalmente; apenas não há constraints de integridade referencial.
 */
class Migration_Create_pecas_tables_no_fk extends CI_Migration
{
    public function up()
    {
        $sql = [];

        // modelos_pecas
        $sql[] = "CREATE TABLE IF NOT EXISTS `modelos_pecas` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nome` VARCHAR(255) NOT NULL,
            `area` VARCHAR(50) NULL,
            `tipo_peca` VARCHAR(50) NOT NULL,
            `corpo` LONGTEXT NULL,
            `versao` INT(11) DEFAULT 1,
            `ativo` TINYINT(1) DEFAULT 1,
            `usuarios_id` INT(11) NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `tipo_peca` (`tipo_peca`),
            KEY `area` (`area`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // pecas_geradas
        $sql[] = "CREATE TABLE IF NOT EXISTS `pecas_geradas` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `processos_id` INT(11) NULL,
            `prazos_id` INT(11) NULL,
            `audiencias_id` INT(11) NULL,
            `contratos_id` INT(11) NULL,
            `tipo_peca` VARCHAR(50) NOT NULL,
            `status` ENUM('rascunho_ia','em_revisao','aprovado','reprovado') DEFAULT 'rascunho_ia',
            `modelos_pecas_id` INT(11) NULL,
            `usuarios_gerador_id` INT(11) NULL,
            `usuarios_aprovador_id` INT(11) NULL,
            `data_aprovacao` DATETIME NULL,
            `tese_principal` TEXT NULL,
            `pontos_enfatizar` TEXT NULL,
            `tom` VARCHAR(50) NULL,
            `clientes_id` INT(11) NULL,
            `contexto_manual` TEXT NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `processos_id` (`processos_id`),
            KEY `prazos_id` (`prazos_id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // pecas_versoes
        $sql[] = "CREATE TABLE IF NOT EXISTS `pecas_versoes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `pecas_geradas_id` INT(11) NOT NULL,
            `numero_versao` INT(11) NOT NULL,
            `conteudo` LONGTEXT NULL,
            `origem` ENUM('ia','editado_manual','aprovado') NOT NULL,
            `diff_anterior` TEXT NULL,
            `usuarios_id` INT(11) NULL,
            `ip` VARCHAR(45) NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `pecas_geradas_id` (`pecas_geradas_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // logs_geracao_pecas
        $sql[] = "CREATE TABLE IF NOT EXISTS `logs_geracao_pecas` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `pecas_geradas_id` INT(11) NULL,
            `pecas_versoes_id` INT(11) NULL,
            `prompt` LONGTEXT NULL,
            `contexto_ids` TEXT NULL,
            `resposta_llm` LONGTEXT NULL,
            `modelo_llm` VARCHAR(100) NULL,
            `chamada_local` TINYINT(1) DEFAULT 0,
            `usuarios_id` INT(11) NULL,
            `ip` VARCHAR(45) NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `pecas_geradas_id` (`pecas_geradas_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // checklist_revisao
        $sql[] = "CREATE TABLE IF NOT EXISTS `checklist_revisao` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `pecas_geradas_id` INT(11) NOT NULL,
            `item` VARCHAR(100) NOT NULL,
            `marcado` TINYINT(1) DEFAULT 0,
            `usuarios_id` INT(11) NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `pecas_geradas_id` (`pecas_geradas_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // jurisprudencia_base
        $sql[] = "CREATE TABLE IF NOT EXISTS `jurisprudencia_base` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `tribunal` VARCHAR(100) NULL,
            `numero_processo` VARCHAR(50) NULL,
            `data` DATE NULL,
            `trecho` TEXT NULL,
            `link` VARCHAR(500) NULL,
            `area` VARCHAR(50) NULL,
            `assunto` VARCHAR(255) NULL,
            `dataCadastro` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `area` (`area`),
            KEY `assunto` (`assunto`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        foreach ($sql as $q) {
            $this->db->query($q);
        }
    }

    public function down()
    {
        $tables = ['jurisprudencia_base', 'checklist_revisao', 'logs_geracao_pecas', 'pecas_versoes', 'pecas_geradas', 'modelos_pecas'];
        foreach ($tables as $t) {
            $this->dbforge->drop_table($t, true);
        }
    }
}
