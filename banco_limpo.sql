-- ============================================================
-- BANCO DE DADOS LIMPO - SISTEMA JURÍDICO
-- ============================================================
-- Este script cria um banco de dados limpo apenas com as
-- tabelas necessárias para o sistema jurídico.
-- 
-- IMPORTANTE: Este script DROP todas as tabelas existentes!
-- Use apenas em ambiente de desenvolvimento ou após backup.
-- ============================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- ============================================================
-- REMOVER TODAS AS TABELAS EXISTENTES (CUIDADO!)
-- ============================================================
-- Descomente as linhas abaixo se quiser remover todas as tabelas
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `processos_cache`;
-- DROP TABLE IF EXISTS `partes_processo`;
-- DROP TABLE IF EXISTS `documentos_processuais`;
-- DROP TABLE IF EXISTS `audiencias`;
-- DROP TABLE IF EXISTS `prazos`;
-- DROP TABLE IF EXISTS `movimentacoes_processuais`;
-- DROP TABLE IF EXISTS `processos`;
-- DROP TABLE IF EXISTS `planos`;
-- DROP TABLE IF EXISTS `servicos_juridicos`;
-- DROP TABLE IF EXISTS `cobrancas`;
-- DROP TABLE IF EXISTS `lancamentos`;
-- DROP TABLE IF EXISTS `categorias`;
-- DROP TABLE IF EXISTS `contas`;
-- DROP TABLE IF EXISTS `logs_auditoria`;
-- DROP TABLE IF EXISTS `email_queue`;
-- DROP TABLE IF EXISTS `configuracoes`;
-- DROP TABLE IF EXISTS `emitente`;
-- DROP TABLE IF EXISTS `resets_de_senha`;
-- DROP TABLE IF EXISTS `clientes`;
-- DROP TABLE IF EXISTS `permissoes`;
-- DROP TABLE IF EXISTS `usuarios`;
-- DROP TABLE IF EXISTS `ci_sessions`;
-- DROP TABLE IF EXISTS `migrations`;
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TABELAS DO SISTEMA
-- ============================================================

-- Tabela de sessões (CodeIgniter)
CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id` VARCHAR(128) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `timestamp` INT(10) UNSIGNED DEFAULT 0 NOT NULL,
    `data` BLOB NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de migrations
CREATE TABLE IF NOT EXISTS `migrations` (
    `version` BIGINT(20) NOT NULL,
    PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE USUÁRIOS E PERMISSÕES
-- ============================================================

-- Tabela de permissões (DEVE SER CRIADA PRIMEIRO)
CREATE TABLE IF NOT EXISTS `permissoes` (
    `idPermissao` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(80) NOT NULL,
    `permissoes` TEXT NULL,
    `situacao` TINYINT(1) NULL DEFAULT 1,
    `data` DATE NULL,
    PRIMARY KEY (`idPermissao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de usuários (DEPOIS de permissoes, pois tem FK)
CREATE TABLE IF NOT EXISTS `usuarios` (
    `idUsuarios` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL,
    `rg` VARCHAR(20) NULL DEFAULT NULL,
    `cpf` VARCHAR(20) NOT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `estado` VARCHAR(20) NULL DEFAULT NULL,
    `telefone` VARCHAR(20) NULL DEFAULT NULL,
    `celular` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `senha` VARCHAR(200) NOT NULL,
    `situacao` TINYINT(1) NOT NULL DEFAULT 1,
    `dataExpiracao` DATE NULL DEFAULT NULL,
    `permissoes_id` INT(11) NULL DEFAULT NULL COMMENT 'ID da permissão (FK para permissoes.idPermissao)',
    `dataCadastro` DATETIME NOT NULL,
    `url_image_user` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`idUsuarios`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `cpf` (`cpf`),
    INDEX `idx_permissoes_id` (`permissoes_id`),
    CONSTRAINT `fk_usuarios_permissoes` FOREIGN KEY (`permissoes_id`) 
        REFERENCES `permissoes` (`idPermissao`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE PLANOS (DEVE SER CRIADA ANTES DE clientes)
-- ============================================================

-- Tabela de planos (DEVE SER CRIADA ANTES DE clientes)
CREATE TABLE IF NOT EXISTS `planos` (
    `idPlanos` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL,
    `descricao` TEXT NULL DEFAULT NULL,
    `valor_mensal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `limite_processos` INT(11) NULL DEFAULT NULL COMMENT 'NULL = ilimitado',
    `limite_prazos` INT(11) NULL DEFAULT NULL COMMENT 'NULL = ilimitado',
    `limite_audiencias` INT(11) NULL DEFAULT NULL COMMENT 'NULL = ilimitado',
    `limite_documentos` INT(11) NULL DEFAULT NULL COMMENT 'NULL = ilimitado',
    `acesso_portal` TINYINT(1) NOT NULL DEFAULT 1,
    `acesso_api` TINYINT(1) NOT NULL DEFAULT 0,
    `suporte_prioritario` TINYINT(1) NOT NULL DEFAULT 0,
    `relatorios_avancados` TINYINT(1) NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idPlanos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE CLIENTES
-- ============================================================

-- Tabela de clientes (adaptada para contexto jurídico)
CREATE TABLE IF NOT EXISTS `clientes` (
    `idClientes` INT(11) NOT NULL AUTO_INCREMENT,
    `asaas_id` VARCHAR(255) DEFAULT NULL,
    `nomeCliente` VARCHAR(255) NOT NULL,
    `sexo` VARCHAR(20) NULL,
    `pessoa_fisica` BOOLEAN NOT NULL DEFAULT 1,
    `tipo_cliente` VARCHAR(20) NULL DEFAULT 'fisica' COMMENT 'Tipo: fisica, juridica, advogado',
    `documento` VARCHAR(20) NOT NULL,
    `oab` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Número OAB (se for advogado)',
    `telefone` VARCHAR(20) NOT NULL,
    `celular` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `senha` VARCHAR(200) NOT NULL,
    `dataCadastro` DATE NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `estado` VARCHAR(20) NULL DEFAULT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `contato` VARCHAR(45) DEFAULT NULL,
    `complemento` VARCHAR(45) DEFAULT NULL,
    `fornecedor` BOOLEAN NOT NULL DEFAULT 0,
    `ramo_atividade` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ramo de atividade (para PJ)',
    `observacoes_juridicas` TEXT NULL DEFAULT NULL COMMENT 'Observações específicas do contexto jurídico',
    `planos_id` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`idClientes`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `documento` (`documento`),
    INDEX `idx_planos_id` (`planos_id`),
    CONSTRAINT `fk_clientes_planos` FOREIGN KEY (`planos_id`) 
        REFERENCES `planos` (`idPlanos`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de reset de senha
CREATE TABLE IF NOT EXISTS `resets_de_senha` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(200) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `data_expiracao` DATETIME NOT NULL,
    `token_utilizado` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE CONFIGURAÇÃO
-- ============================================================

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `idConfig` INT(11) NOT NULL AUTO_INCREMENT,
    `config` VARCHAR(100) NOT NULL,
    `valor` TEXT NULL,
    PRIMARY KEY (`idConfig`),
    UNIQUE KEY `config` (`config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de emitente (dados do escritório)
CREATE TABLE IF NOT EXISTS `emitente` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL,
    `cnpj` VARCHAR(20) NOT NULL,
    `ie` VARCHAR(20) NULL DEFAULT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `uf` VARCHAR(2) NULL DEFAULT NULL,
    `telefone` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NULL DEFAULT NULL,
    `logo` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS FINANCEIRAS
-- ============================================================

-- Tabela de contas bancárias
CREATE TABLE IF NOT EXISTS `contas` (
    `idContas` INT(11) NOT NULL AUTO_INCREMENT,
    `conta` VARCHAR(45) NULL,
    `banco` VARCHAR(45) NULL,
    `numero` VARCHAR(45) NULL,
    `saldo` DECIMAL(10,2) NULL DEFAULT 0.00,
    `cadastro` DATE NULL,
    `status` TINYINT(1) NULL DEFAULT 1,
    `tipo` VARCHAR(80) NULL,
    PRIMARY KEY (`idContas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS `categorias` (
    `idCategorias` INT(11) NOT NULL AUTO_INCREMENT,
    `categoria` VARCHAR(80) NULL,
    `cadastro` DATE NULL,
    `status` TINYINT(1) NULL DEFAULT 1,
    `tipo` VARCHAR(15) NULL,
    PRIMARY KEY (`idCategorias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de lançamentos financeiros
CREATE TABLE IF NOT EXISTS `lancamentos` (
    `idLancamentos` INT(11) NOT NULL AUTO_INCREMENT,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `data_pagamento` DATE NULL DEFAULT NULL,
    `baixado` TINYINT(1) NOT NULL DEFAULT 0,
    `cliente_fornecedor` VARCHAR(255) NULL DEFAULT NULL,
    `forma_pgto` VARCHAR(20) NULL DEFAULT NULL,
    `tipo` VARCHAR(20) NOT NULL COMMENT 'receita ou despesa',
    `observacoes` TEXT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL,
    `categorias_id` INT(11) NULL DEFAULT NULL,
    `contas_id` INT(11) NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idLancamentos`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_categorias_id` (`categorias_id`),
    INDEX `idx_contas_id` (`contas_id`),
    INDEX `idx_data_vencimento` (`data_vencimento`),
    INDEX `idx_tipo` (`tipo`),
    CONSTRAINT `fk_lancamentos_usuarios` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_lancamentos_categorias` FOREIGN KEY (`categorias_id`) 
        REFERENCES `categorias` (`idCategorias`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_lancamentos_contas` FOREIGN KEY (`contas_id`) 
        REFERENCES `contas` (`idContas`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cobranças
CREATE TABLE IF NOT EXISTS `cobrancas` (
    `idCobrancas` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `data_pagamento` DATE NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente, pago, cancelado',
    `forma_pgto` VARCHAR(20) NULL DEFAULT NULL,
    `observacoes` TEXT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idCobrancas`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_data_vencimento` (`data_vencimento`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_cobrancas_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cobrancas_usuarios` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS JURÍDICAS
-- ============================================================
-- NOTA: A tabela planos já foi criada anteriormente (antes de clientes)

-- Tabela de serviços jurídicos
CREATE TABLE IF NOT EXISTS `servicos_juridicos` (
    `idServicos` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(45) NOT NULL,
    `descricao` TEXT NULL DEFAULT NULL,
    `preco` DECIMAL(10,2) NOT NULL,
    `tipo_servico` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de serviço jurídico',
    `modelo_peca_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do modelo de peça relacionado',
    `valor_base` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Valor base do serviço',
    `tempo_estimado` INT(11) NULL DEFAULT NULL COMMENT 'Tempo estimado em horas',
    PRIMARY KEY (`idServicos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de processos
CREATE TABLE IF NOT EXISTS `processos` (
    `idProcessos` INT(11) NOT NULL AUTO_INCREMENT,
    `numeroProcesso` VARCHAR(50) NOT NULL COMMENT 'Número do processo (CNJ) - aceita formatado ou limpo',
    `classe` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Classe processual',
    `assunto` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Assunto do processo',
    `tipo_processo` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Tipo: civel, trabalhista, tributario, criminal, etc.',
    `vara` VARCHAR(255) NULL DEFAULT NULL,
    `comarca` VARCHAR(255) NULL DEFAULT NULL,
    `tribunal` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tribunal responsável',
    `segmento` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Segmento: estadual, federal, trabalho, eleitoral, militar',
    `status` VARCHAR(50) NOT NULL DEFAULT 'em_andamento' COMMENT 'Status: em_andamento, suspenso, arquivado, finalizado',
    `valorCausa` DECIMAL(10,2) NULL DEFAULT 0.00,
    `dataDistribuicao` DATE NULL DEFAULT NULL,
    `dataUltimaMovimentacao` DATETIME NULL DEFAULT NULL,
    `clientes_id` INT(11) NULL DEFAULT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Advogado responsável',
    `observacoes` TEXT NULL DEFAULT NULL,
    `ultimaConsultaAPI` DATETIME NULL DEFAULT NULL COMMENT 'Data da última consulta na API CNJ',
    `proximaConsultaAPI` DATETIME NULL DEFAULT NULL COMMENT 'Data da próxima consulta agendada na API',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idProcessos`),
    UNIQUE KEY `numeroProcesso` (`numeroProcesso`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_processos_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_processos_usuarios` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de movimentações processuais
CREATE TABLE IF NOT EXISTS `movimentacoes_processuais` (
    `idMovimentacoes` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NOT NULL,
    `data` DATETIME NOT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo de movimentação',
    `descricao` TEXT NOT NULL,
    `usuario_id` INT(11) NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idMovimentacoes`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_data` (`data`),
    CONSTRAINT `fk_movimentacoes_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de prazos
CREATE TABLE IF NOT EXISTS `prazos` (
    `idPrazos` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NULL DEFAULT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: intimacao, audiencia, recurso, etc.',
    `descricao` TEXT NOT NULL,
    `dataPrazo` DATE NOT NULL COMMENT 'Data do prazo',
    `dataVencimento` DATE NOT NULL COMMENT 'Data de vencimento do prazo',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'Status: pendente, vencido, concluido',
    `alertado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se já foi enviado alerta',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário responsável',
    `prioridade` VARCHAR(20) NULL DEFAULT 'normal' COMMENT 'Prioridade: baixa, normal, alta, critica',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idPrazos`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_dataVencimento` (`dataVencimento`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_prazos_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de audiências
CREATE TABLE IF NOT EXISTS `audiencias` (
    `idAudiencias` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NULL DEFAULT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: audiencia, conciliacao, depoimento, etc.',
    `dataHora` DATETIME NOT NULL,
    `local` VARCHAR(255) NULL DEFAULT NULL,
    `observacoes` TEXT NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'agendada' COMMENT 'Status: agendada, realizada, cancelada',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário responsável',
    `alertado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se já foi enviado alerta',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idAudiencias`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_dataHora` (`dataHora`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_audiencias_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de documentos gerais
CREATE TABLE IF NOT EXISTS `documentos` (
    `idDocumentos` INT(11) NOT NULL AUTO_INCREMENT,
    `documento` VARCHAR(70) NULL DEFAULT NULL,
    `descricao` TEXT NULL DEFAULT NULL,
    `file` VARCHAR(100) NULL DEFAULT NULL,
    `path` VARCHAR(300) NULL DEFAULT NULL,
    `url` VARCHAR(300) NULL DEFAULT NULL,
    `cadastro` DATE NULL DEFAULT NULL,
    `categoria` VARCHAR(80) NULL DEFAULT NULL,
    `tipo` VARCHAR(15) NULL DEFAULT NULL,
    `tamanho` VARCHAR(45) NULL DEFAULT NULL,
    PRIMARY KEY (`idDocumentos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de documentos processuais
CREATE TABLE IF NOT EXISTS `documentos_processuais` (
    `idDocumentos` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `tipo` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Tipo de documento',
    `descricao` TEXT NULL DEFAULT NULL,
    `arquivo` VARCHAR(255) NOT NULL,
    `tamanho` INT(11) NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`idDocumentos`),
    INDEX `idx_processos_id` (`processos_id`),
    CONSTRAINT `fk_documentos_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de partes do processo
CREATE TABLE IF NOT EXISTS `partes_processo` (
    `idPartes` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NOT NULL,
    `nome` VARCHAR(255) NOT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: autor, reu, terceiro, etc.',
    `documento` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NULL DEFAULT NULL,
    `telefone` VARCHAR(20) NULL DEFAULT NULL,
    `endereco` TEXT NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idPartes`),
    INDEX `idx_processos_id` (`processos_id`),
    CONSTRAINT `fk_partes_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cache de processos (consultas API)
CREATE TABLE IF NOT EXISTS `processos_cache` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `numero_processo` VARCHAR(50) NOT NULL,
    `dados` LONGTEXT NOT NULL COMMENT 'JSON com dados do processo',
    `hash_dados` VARCHAR(64) NOT NULL COMMENT 'Hash dos dados para detectar mudanças',
    `data_consulta` DATETIME NOT NULL,
    `proxima_consulta` DATETIME NULL DEFAULT NULL,
    `ttl` INT(11) NOT NULL DEFAULT 86400 COMMENT 'Time to live em segundos',
    PRIMARY KEY (`id`),
    UNIQUE KEY `numero_processo` (`numero_processo`),
    INDEX `idx_proxima_consulta` (`proxima_consulta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE SISTEMA
-- ============================================================

-- Tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NULL DEFAULT NULL,
    `acao` VARCHAR(100) NOT NULL,
    `tabela` VARCHAR(100) NULL DEFAULT NULL,
    `registro_id` INT(11) NULL DEFAULT NULL,
    `dados_anteriores` TEXT NULL DEFAULT NULL,
    `dados_novos` TEXT NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `data` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_tabela` (`tabela`),
    INDEX `idx_data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de fila de emails
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `to` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, sent, failed',
    `attempts` INT(11) NOT NULL DEFAULT 0,
    `last_attempt` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Criar permissão de Administrador
INSERT IGNORE INTO `permissoes` (`idPermissao`, `nome`, `permissoes`, `situacao`, `data`) VALUES
(1, 'Administrador', 'a:45:{s:8:"aCliente";s:1:"1";s:8:"eCliente";s:1:"1";s:8:"dCliente";s:1:"1";s:8:"vCliente";s:1:"1";s:8:"aServico";s:1:"1";s:8:"eServico";s:1:"1";s:8:"dServico";s:1:"1";s:8:"vServico";s:1:"1";s:9:"aProcesso";s:1:"1";s:9:"eProcesso";s:1:"1";s:9:"dProcesso";s:1:"1";s:9:"vProcesso";s:1:"1";s:9:"sProcesso";s:1:"1";s:6:"aPrazo";s:1:"1";s:6:"ePrazo";s:1:"1";s:6:"dPrazo";s:1:"1";s:6:"vPrazo";s:1:"1";s:10:"aAudiencia";s:1:"1";s:10:"eAudiencia";s:1:"1";s:10:"dAudiencia";s:1:"1";s:10:"vAudiencia";s:1:"1";s:18:"cConsultaProcessual";s:1:"1";s:8:"aArquivo";s:1:"1";s:8:"eArquivo";s:1:"1";s:8:"dArquivo";s:1:"1";s:8:"vArquivo";s:1:"1";s:11:"aLancamento";s:1:"1";s:11:"eLancamento";s:1:"1";s:11:"dLancamento";s:1:"1";s:11:"vLancamento";s:1:"1";s:8:"cUsuario";s:1:"1";s:9:"cEmitente";s:1:"1";s:10:"cPermissao";s:1:"1";s:7:"cBackup";s:1:"1";s:10:"cAuditoria";s:1:"1";s:6:"cEmail";s:1:"1";s:8:"cSistema";s:1:"1";s:8:"rCliente";s:1:"1";s:8:"rServico";s:1:"1";s:9:"rProcesso";s:1:"1";s:6:"rPrazo";s:1:"1";s:10:"rAudiencia";s:1:"1";s:11:"rFinanceiro";s:1:"1";s:9:"aCobranca";s:1:"1";s:9:"eCobranca";s:1:"1";s:9:"dCobranca";s:1:"1";s:9:"vCobranca";s:1:"1";}', 1, CURDATE());

-- Criar usuário Administrador
-- Email: admin@admin.com
-- Senha: 123456 (hash: $2y$10$lAW0AXb0JLZxR0yDdfcBcu3BN9c2AXKKjKTdug7Or0pr6cSGtgyGO)
INSERT IGNORE INTO `usuarios` (
    `idUsuarios`,
    `nome`,
    `rg`,
    `cpf`,
    `cep`,
    `rua`,
    `numero`,
    `bairro`,
    `cidade`,
    `estado`,
    `email`,
    `senha`,
    `telefone`,
    `celular`,
    `situacao`,
    `dataCadastro`,
    `permissoes_id`,
    `dataExpiracao`
) VALUES (
    1,
    'Administrador',
    'MG-00.000.000',
    '000.000.000-00',
    '00000-000',
    'Rua Exemplo',
    '0',
    'Centro',
    'Cidade',
    'MG',
    'admin@admin.com',
    '$2y$10$lAW0AXb0JLZxR0yDdfcBcu3BN9c2AXKKjKTdug7Or0pr6cSGtgyGO',
    '0000-0000',
    '',
    1,
    NOW(),
    1,
    '2030-01-01'
);

-- Criar planos padrão
INSERT IGNORE INTO `planos` (`idPlanos`, `nome`, `descricao`, `valor_mensal`, `limite_processos`, `limite_prazos`, `limite_audiencias`, `limite_documentos`, `acesso_portal`, `acesso_api`, `suporte_prioritario`, `relatorios_avancados`, `status`, `dataCadastro`) VALUES
(1, 'Básico', 'Plano básico com funcionalidades essenciais', 0.00, 10, 50, 20, 100, 1, 0, 0, 0, 1, NOW()),
(2, 'Profissional', 'Plano profissional com recursos avançados', 99.90, 50, 200, 100, 500, 1, 1, 1, 1, 1, NOW()),
(3, 'Enterprise', 'Plano enterprise com recursos ilimitados', 299.90, 0, 0, 0, 0, 1, 1, 1, 1, 1, NOW());

-- ============================================================
-- RESTAURAR CONFIGURAÇÕES
-- ============================================================

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET SQL_MODE=@OLD_SQL_MODE;

-- ============================================================
-- VERIFICAÇÃO FINAL
-- ============================================================

-- Verificar se usuário admin foi criado
SELECT 
    '✅ Usuário Admin criado!' as Status,
    idUsuarios as ID,
    nome as Nome,
    email as Email,
    situacao as Situacao
FROM usuarios 
WHERE email = 'admin@admin.com';

-- Verificar se permissão admin foi criada
SELECT 
    '✅ Permissão Admin criada!' as Status,
    idPermissao as ID,
    nome as Nome,
    situacao as Situacao
FROM permissoes 
WHERE idPermissao = 1;

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================
-- 
-- CREDENCIAIS DE ACESSO:
-- Email: admin@admin.com
-- Senha: 123456
-- 
-- ⚠️  IMPORTANTE: Altere a senha após o primeiro login!
-- ============================================================

