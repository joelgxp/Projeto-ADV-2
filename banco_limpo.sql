-- ============================================================
-- BANCO DE DADOS LIMPO - SISTEMA JURÍDICO
-- ============================================================
-- Este script cria um banco de dados limpo com TODAS as
-- tabelas e colunas necessárias para o sistema jurídico,
-- incluindo todas as funcionalidades das Fases 4-11.
-- 
-- ✅ INCLUI TODAS AS FASES IMPLEMENTADAS:
-- - Fase 4: Prazos (campos adicionais, feriados, alertas)
-- - Fase 5: Agenda e Compromissos (múltiplos tipos)
-- - Fase 6: Portal do Cliente (acessos, tickets)
-- - Fase 7: Gestão Financeira (contratos, faturas, pagamentos)
-- - Fase 8: Auditoria e Segurança (LGPD, histórico)
-- - Fase 9: Notificações (sistema completo)
-- - Fase 10: Integração CNJ (sincronização, movimentações)
-- - Fase 11: Validações Cross-Cutting (soft delete, rate limit, backups)
-- 
-- IMPORTANTE: Este script DROP todas as tabelas existentes!
-- Use apenas em ambiente de desenvolvimento ou após backup.
-- 
-- ⚠️  NÃO É MAIS NECESSÁRIO executar scripts de docs/implementacao/
-- Este arquivo já contém TUDO consolidado!
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
    `oab` VARCHAR(20) NULL DEFAULT NULL,
    `cpf` VARCHAR(20) NOT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `estado` VARCHAR(20) NULL DEFAULT NULL,
    `celular` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `senha` VARCHAR(200) NOT NULL,
    `situacao` TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `dataExpiracao` DATE NULL DEFAULT NULL,
    `permissoes_id` INT(11) NULL DEFAULT NULL COMMENT 'ID da permissão (FK para permissoes.idPermissao)',
    `email_confirmado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se o e-mail foi confirmado (RN 1.1)',
    `dataCadastro` DATETIME NOT NULL,
    `url_image_user` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`idUsuarios`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `cpf` (`cpf`),
    INDEX `idx_permissoes_id` (`permissoes_id`),
    INDEX `idx_email_confirmado` (`email_confirmado`),
    INDEX `idx_deleted_at` (`deleted_at`),
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
    `nomeCliente` VARCHAR(255) NOT NULL,
    `sexo` VARCHAR(20) NULL,
    `pessoa_fisica` BOOLEAN NOT NULL DEFAULT 1,
    `tipo_cliente` VARCHAR(20) NULL DEFAULT 'fisica' COMMENT 'Tipo: fisica, juridica, advogado',
    `documento` VARCHAR(20) NOT NULL,
    `rg` VARCHAR(20) NULL DEFAULT NULL,
    `data_nascimento` DATE NULL DEFAULT NULL,
    `estado_civil` VARCHAR(20) NULL DEFAULT NULL,
    `nacionalidade` VARCHAR(100) NULL DEFAULT NULL,
    `profissao` VARCHAR(100) NULL DEFAULT NULL,
    `nome_mae` VARCHAR(255) NULL DEFAULT NULL,
    `nome_pai` VARCHAR(255) NULL DEFAULT NULL,
    `dependentes` TEXT NULL DEFAULT NULL,
    `razao_social` VARCHAR(255) NULL DEFAULT NULL,
    `nome_fantasia` VARCHAR(255) NULL DEFAULT NULL,
    `inscricao_estadual` VARCHAR(50) NULL DEFAULT NULL,
    `inscricao_municipal` VARCHAR(50) NULL DEFAULT NULL,
    `data_constituicao` DATE NULL DEFAULT NULL,
    `cnae` VARCHAR(20) NULL DEFAULT NULL,
    `representantes_legais` TEXT NULL DEFAULT NULL,
    `socios` TEXT NULL DEFAULT NULL,
    `telefone` VARCHAR(20) NOT NULL,
    `celular` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL,
    `senha` VARCHAR(200) NOT NULL,
    `redes_sociais` TEXT NULL DEFAULT NULL,
    `dataCadastro` DATE NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `estado` VARCHAR(20) NULL DEFAULT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `contato` VARCHAR(45) DEFAULT NULL,
    `complemento` VARCHAR(45) DEFAULT NULL,
    `ramo_atividade` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ramo de atividade (para PJ)',
    `observacoes` TEXT NULL DEFAULT NULL,
    `observacoes_juridicas` TEXT NULL DEFAULT NULL COMMENT 'Observações específicas do contexto jurídico',
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `planos_id` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`idClientes`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `documento` (`documento`),
    INDEX `idx_planos_id` (`planos_id`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_clientes_planos` FOREIGN KEY (`planos_id`) 
        REFERENCES `planos` (`idPlanos`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contatos do cliente (RN 2.4)
CREATE TABLE IF NOT EXISTS `contatos_cliente` (
    `idContato` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo` ENUM('email', 'telefone', 'celular') NOT NULL,
    `valor` VARCHAR(255) NOT NULL,
    `observacoes` VARCHAR(255) NULL DEFAULT NULL,
    `principal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = contato principal',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idContato`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_principal` (`clientes_id`, `tipo`, `principal`),
    CONSTRAINT `fk_contatos_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de endereços do cliente (RN 2.5)
CREATE TABLE IF NOT EXISTS `enderecos_cliente` (
    `idEndereco` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo` ENUM('Residencial', 'Comercial', 'Correspondencia', 'Judicial') NOT NULL DEFAULT 'Residencial',
    `rua` VARCHAR(70) NOT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `complemento` VARCHAR(45) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NOT NULL,
    `estado` VARCHAR(20) NOT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `principal` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = endereço principal',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idEndereco`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_principal` (`clientes_id`, `principal`),
    CONSTRAINT `fk_enderecos_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de interações do cliente (RN 2.3)
CREATE TABLE IF NOT EXISTS `interacoes_cliente` (
    `idInteracao` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo` ENUM('reuniao', 'telefonema', 'email', 'nota', 'outro') NOT NULL,
    `data_hora` DATETIME NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descricao` TEXT NULL DEFAULT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário que registrou a interação',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idInteracao`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_data_hora` (`data_hora`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    CONSTRAINT `fk_interacoes_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_interacoes_usuario` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
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

-- Tabela de confirmação de e-mail para novos usuários (RN 1.1)
CREATE TABLE IF NOT EXISTS `confirmacoes_email` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuarios_id` INT(11) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `data_expiracao` DATETIME NOT NULL,
    `token_utilizado` TINYINT(1) NOT NULL DEFAULT 0,
    `data_cadastro` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_token` (`token`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    CONSTRAINT `fk_confirmacoes_usuarios` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tentativas de login (RN 1.4)
CREATE TABLE IF NOT EXISTS `tentativas_login` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(200) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL DEFAULT NULL,
    `sucesso` TINYINT(1) NOT NULL DEFAULT 0,
    `data_hora` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_data_hora` (`data_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de bloqueios de conta (RN 1.4)
CREATE TABLE IF NOT EXISTS `bloqueios_conta` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(200) NOT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `tentativas_falhadas` INT(11) NOT NULL DEFAULT 0,
    `bloqueado_ate` DATETIME NOT NULL,
    `data_bloqueio` DATETIME NOT NULL,
    `desbloqueado` TINYINT(1) NOT NULL DEFAULT 0,
    `desbloqueado_por` INT(11) NULL DEFAULT NULL COMMENT 'ID do admin que desbloqueou',
    `data_desbloqueio` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_bloqueado_ate` (`bloqueado_ate`),
    INDEX `idx_desbloqueado` (`desbloqueado`)
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
    `cnpj` VARCHAR(20) NULL DEFAULT NULL,
    `ie` VARCHAR(20) NULL DEFAULT NULL,
    `cep` VARCHAR(20) NULL DEFAULT NULL,
    `rua` VARCHAR(70) NULL DEFAULT NULL,
    `numero` VARCHAR(15) NULL DEFAULT NULL,
    `bairro` VARCHAR(45) NULL DEFAULT NULL,
    `cidade` VARCHAR(45) NULL DEFAULT NULL,
    `uf` VARCHAR(2) NULL DEFAULT NULL,
    `telefone` VARCHAR(20) NULL DEFAULT NULL,
    `celular` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NULL DEFAULT NULL,
    `site` VARCHAR(255) NULL DEFAULT NULL,
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
    `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tipo_desconto` VARCHAR(20) NULL DEFAULT NULL COMMENT 'real ou porcento',
    `valor_desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
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
    `ultimaConsultaAPI` DATETIME NULL DEFAULT NULL COMMENT 'Data da última consulta na API CNJ (Fase 10)',
    `proximaConsultaAPI` DATETIME NULL DEFAULT NULL COMMENT 'Data da próxima consulta agendada na API (Fase 10)',
    `sincronizado_parcialmente` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indica se dados foram sincronizados parcialmente (Fase 10)',
    `tribunal` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nome do tribunal (Fase 10)',
    `juiz` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nome do juiz responsável (Fase 10)',
    `anotacao` TEXT NULL DEFAULT NULL COMMENT 'Anotações adicionais (Fase 10)',
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idProcessos`),
    UNIQUE KEY `numeroProcesso` (`numeroProcesso`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_ultimaConsultaAPI` (`ultimaConsultaAPI`),
    INDEX `idx_proximaConsultaAPI` (`proximaConsultaAPI`),
    INDEX `idx_sincronizado_parcialmente` (`sincronizado_parcialmente`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_processos_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_processos_usuarios` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cobranças (DEPOIS de processos, pois tem FK para processos)
CREATE TABLE IF NOT EXISTS `cobrancas` (
    `idCobranca` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `processos_id` INT(11) NULL DEFAULT NULL,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `expire_at` DATE NULL DEFAULT NULL COMMENT 'Data de expiração (para gateways de pagamento)',
    `data_pagamento` DATE NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'pendente, pago, cancelado',
    `payment_method` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Método de pagamento',
    `charge_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ID externo do gateway',
    `barcode` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Código de barras (boleto)',
    `link` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Link de pagamento',
    `pdf` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Link do PDF do boleto',
    `forma_pgto` VARCHAR(20) NULL DEFAULT NULL,
    `observacoes` TEXT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL,
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idCobranca`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_data_vencimento` (`data_vencimento`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_cobrancas_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cobrancas_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cobrancas_usuarios` FOREIGN KEY (`usuarios_id`) 
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
    `data_atualizacao` DATETIME NULL DEFAULT NULL COMMENT 'Data de atualização no sistema (Fase 10)',
    `tribunal` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Tribunal da movimentação (Fase 10)',
    `juiz` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Juiz responsável (Fase 10)',
    `anotacao` TEXT NULL DEFAULT NULL COMMENT 'Anotação do advogado (Fase 10)',
    `origem` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Origem: manual, api_cnj',
    `dados_api` TEXT NULL DEFAULT NULL COMMENT 'Dados JSON da API CNJ',
    `importado_api` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se foi importado da API',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idMovimentacoes`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_data` (`data`),
    INDEX `idx_data_atualizacao` (`data_atualizacao`),
    INDEX `idx_tribunal` (`tribunal`),
    INDEX `idx_origem` (`origem`),
    CONSTRAINT `fk_movimentacoes_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de prazos
CREATE TABLE IF NOT EXISTS `prazos` (
    `idPrazos` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NULL DEFAULT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: intimacao, audiencia, recurso, etc.',
    `descricao` TEXT NOT NULL,
    `dataPrazo` DATE NOT NULL COMMENT 'Data do prazo (início/intimação)',
    `dataVencimento` DATE NOT NULL COMMENT 'Data de vencimento do prazo',
    `diasUteis` INT(11) NULL DEFAULT NULL COMMENT 'Número de dias úteis do prazo',
    `legislacao` VARCHAR(20) NULL DEFAULT 'CPC' COMMENT 'Legislação: CPC, CLT, tributario',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'Status: pendente, proximo_vencer, vencendo_hoje, vencido, cumprido, prorrogado, cancelado',
    `alertado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se já foi enviado alerta',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário responsável',
    `prioridade` VARCHAR(20) NULL DEFAULT 'normal' COMMENT 'Prioridade: baixa, normal, alta, critica',
    `prazo_original_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do prazo original (se for prorrogação)',
    `numero_prorrogacao` INT(11) NOT NULL DEFAULT 0 COMMENT 'Número da prorrogação (0 = original, 1-3 = prorrogações)',
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idPrazos`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_dataVencimento` (`dataVencimento`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_prazos_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de feriados (nacionais e municipais)
CREATE TABLE IF NOT EXISTS `feriados` (
    `idFeriados` INT(11) NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do feriado',
    `data` DATE NOT NULL COMMENT 'Data do feriado',
    `tipo` VARCHAR(20) NOT NULL DEFAULT 'nacional' COMMENT 'Tipo: nacional, municipal',
    `municipio_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do município (se tipo = municipal)',
    `recorrente` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = recorrente (ex: Natal sempre 25/12), 0 = data fixa',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idFeriados`),
    INDEX `idx_data` (`data`),
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_recorrente` (`recorrente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de alertas de prazos (RN 4.4)
CREATE TABLE IF NOT EXISTS `alertas_prazos` (
    `idAlertasPrazos` INT(11) NOT NULL AUTO_INCREMENT,
    `prazos_id` INT(11) NOT NULL COMMENT 'ID do prazo',
    `tipo_alerta` VARCHAR(10) NOT NULL COMMENT 'Tipo: 7d, 2d, 1d, hoje',
    `data_envio_previsto` DATETIME NOT NULL COMMENT 'Data/hora prevista para envio',
    `data_envio` DATETIME NULL DEFAULT NULL COMMENT 'Data/hora do envio efetivo',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pendente' COMMENT 'Status: pendente, enviado, falhou',
    `canal` VARCHAR(20) NOT NULL DEFAULT 'email' COMMENT 'Canal: email, push, sms',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário responsável',
    `observacoes` TEXT NULL DEFAULT NULL COMMENT 'Observações/erro em caso de falha',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idAlertasPrazos`),
    INDEX `idx_prazos_id` (`prazos_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_data_envio_previsto` (`data_envio_previsto`),
    INDEX `idx_tipo_alerta` (`tipo_alerta`),
    CONSTRAINT `fk_alertas_prazos` FOREIGN KEY (`prazos_id`) 
        REFERENCES `prazos` (`idPrazos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de audiências (Fase 5: Expandida para múltiplos tipos de compromissos)
CREATE TABLE IF NOT EXISTS `audiencias` (
    `idAudiencias` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NULL DEFAULT NULL,
    `tipo` VARCHAR(50) NOT NULL COMMENT 'Tipo: audiencia, conciliacao, depoimento, etc.',
    `tipo_compromisso` ENUM('audiencia', 'reuniao', 'diligencia', 'prazo', 'evento') NOT NULL DEFAULT 'audiencia' COMMENT 'Tipo de compromisso (Fase 5)',
    `tribunal` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tribunal (para audiências - Fase 5)',
    `juiz` VARCHAR(200) NULL DEFAULT NULL COMMENT 'Juiz (para audiências - Fase 5)',
    `tipo_audiencia` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de audiência (Fase 5)',
    `tipo_diligencia` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de diligência (Fase 5)',
    `prazos_id` INT(11) NULL DEFAULT NULL COMMENT 'FK para prazos (para compromissos tipo prazo - Fase 5)',
    `tipo_evento` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de evento (Fase 5)',
    `abrangencia` ENUM('pessoal', 'equipe', 'escritorio') NULL DEFAULT NULL COMMENT 'Abrangência (para eventos - Fase 5)',
    `dataHora` DATETIME NOT NULL,
    `duracao_estimada` INT(11) NULL DEFAULT NULL COMMENT 'Duração em minutos (Fase 5)',
    `local` VARCHAR(255) NULL DEFAULT NULL,
    `participantes` TEXT NULL DEFAULT NULL COMMENT 'JSON com lista de participantes (para reuniões - Fase 5)',
    `observacoes` TEXT NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'agendada' COMMENT 'Status: agendada, realizada, cancelada, adiada',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário responsável',
    `visibilidade` ENUM('privado', 'publico', 'equipe') NOT NULL DEFAULT 'publico' COMMENT 'Visibilidade do compromisso (Fase 5)',
    `alertado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se já foi enviado alerta',
    `google_calendar_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ID do evento no Google Calendar (Fase 5)',
    `google_calendar_sincronizado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se foi sincronizado com Google Calendar (Fase 5)',
    `lembretes_habilitados` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Se lembretes estão habilitados (Fase 5)',
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `dataCadastro` DATETIME NOT NULL,
    PRIMARY KEY (`idAudiencias`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_dataHora` (`dataHora`),
    INDEX `idx_status` (`status`),
    INDEX `idx_tipo_compromisso` (`tipo_compromisso`),
    INDEX `idx_visibilidade` (`visibilidade`),
    INDEX `idx_prazos_id` (`prazos_id`),
    INDEX `idx_duracao_estimada` (`duracao_estimada`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_audiencias_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_audiencias_prazos` FOREIGN KEY (`prazos_id`) 
        REFERENCES `prazos` (`idPrazos`) ON DELETE SET NULL ON UPDATE CASCADE
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

-- Tabela de advogados responsáveis por processo (suporta múltiplos advogados com papéis)
CREATE TABLE IF NOT EXISTS `advogados_processo` (
    `idAdvogadoProcesso` INT(11) NOT NULL AUTO_INCREMENT,
    `processos_id` INT(11) NOT NULL COMMENT 'ID do processo',
    `usuarios_id` INT(11) NOT NULL COMMENT 'ID do usuário (advogado)',
    `papel` VARCHAR(50) NOT NULL DEFAULT 'coadjuvante' COMMENT 'Papel: principal, coadjuvante, estagiario',
    `data_atribuicao` DATETIME NOT NULL COMMENT 'Data/hora da atribuição do advogado ao processo',
    `data_remocao` DATETIME NULL DEFAULT NULL COMMENT 'Data/hora da remoção (soft delete)',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=removido',
    `notificado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=notificado por email, 0=não notificado',
    `data_notificacao` DATETIME NULL DEFAULT NULL COMMENT 'Data/hora da notificação por email',
    `observacoes` TEXT NULL DEFAULT NULL COMMENT 'Observações sobre a atribuição',
    PRIMARY KEY (`idAdvogadoProcesso`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_ativo` (`ativo`),
    INDEX `idx_processo_usuario_ativo` (`processos_id`, `usuarios_id`, `ativo`),
    CONSTRAINT `fk_advogados_processo_processos` 
        FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_advogados_processo_usuarios` 
        FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cache de processos (consultas API)
CREATE TABLE IF NOT EXISTS `processos_cache` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `numeroProcesso` VARCHAR(50) NOT NULL COMMENT 'Número do processo (limpo, sem formatação)',
    `payload` LONGTEXT NULL DEFAULT NULL COMMENT 'JSON com dados do processo (nome alternativo para compatibilidade)',
    `dados` LONGTEXT NULL DEFAULT NULL COMMENT 'JSON com dados do processo',
    `hash_payload` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Hash dos dados (nome alternativo)',
    `hash_dados` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Hash dos dados para detectar mudanças',
    `ultimo_fetch` DATETIME NULL DEFAULT NULL COMMENT 'Data do último fetch',
    `data_consulta` DATETIME NULL DEFAULT NULL COMMENT 'Data da consulta',
    `ultima_atualizacao` DATETIME NULL DEFAULT NULL COMMENT 'Data da última atualização',
    `proxima_consulta` DATETIME NULL DEFAULT NULL COMMENT 'Data da próxima consulta agendada',
    `updated_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `ttl` INT(11) NOT NULL DEFAULT 86400 COMMENT 'Time to live em segundos',
    PRIMARY KEY (`id`),
    UNIQUE KEY `numeroProcesso` (`numeroProcesso`),
    INDEX `idx_proxima_consulta` (`proxima_consulta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELAS DE SISTEMA
-- ============================================================

-- Tabela de logs do sistema
CREATE TABLE IF NOT EXISTS `logs` (
    `idLogs` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario` VARCHAR(100) NOT NULL,
    `ip` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Navegador e informações do cliente (RN 1.4)',
    `tarefa` VARCHAR(255) NOT NULL,
    `data` DATE NOT NULL,
    `hora` TIME NOT NULL,
    PRIMARY KEY (`idLogs`),
    INDEX `idx_data` (`data`),
    INDEX `idx_usuario` (`usuario`),
    INDEX `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Tabela de fila de emails (Fase 9: Expandida com campos adicionais)
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `to` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, sent, failed',
    `prioridade` ENUM('baixa', 'normal', 'alta', 'critica') NOT NULL DEFAULT 'normal' COMMENT 'Prioridade do email (Fase 9)',
    `template` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Template usado (Fase 9)',
    `dados_template` TEXT NULL DEFAULT NULL COMMENT 'JSON com dados para template (Fase 9)',
    `attempts` INT(11) NOT NULL DEFAULT 0,
    `tentativas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Número de tentativas (Fase 9)',
    `max_tentativas` INT(11) NOT NULL DEFAULT 3 COMMENT 'Máximo de tentativas (Fase 9)',
    `erro` TEXT NULL DEFAULT NULL COMMENT 'Mensagem de erro (Fase 9)',
    `last_attempt` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de atualização (Fase 9)',
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_prioridade` (`prioridade`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FASE 6: PORTAL DO CLIENTE
-- ============================================================

-- Tabela de acessos do cliente (Fase 6 - Sprint 1)
CREATE TABLE IF NOT EXISTS `acessos_cliente` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `token_acesso` VARCHAR(255) NOT NULL COMMENT 'Token único para link de acesso',
    `data_criacao` DATETIME NOT NULL,
    `data_expiracao` DATETIME NOT NULL COMMENT 'Data de expiração do link (365 dias após criação)',
    `data_renovacao` DATETIME NULL DEFAULT NULL COMMENT 'Data da última renovação do link',
    `ativo` TINYINT(1) DEFAULT 1 COMMENT 'Se o acesso está ativo (1) ou desativado (0)',
    `ip_criacao` VARCHAR(45) NULL DEFAULT NULL,
    `ultimo_acesso` DATETIME NULL DEFAULT NULL COMMENT 'Data e hora do último acesso usando este token',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_token_acesso` (`token_acesso`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_data_expiracao` (`data_expiracao`),
    INDEX `idx_ativo` (`ativo`),
    CONSTRAINT `fk_acessos_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tickets do cliente (Fase 6 - Sprint 4)
CREATE TABLE IF NOT EXISTS `tickets_cliente` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `processos_id` INT(11) NULL DEFAULT NULL COMMENT 'Ticket pode estar vinculado a um processo',
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Advogado responsável',
    `assunto` VARCHAR(255) NOT NULL,
    `mensagem` TEXT NOT NULL,
    `status` ENUM('aberto', 'em_andamento', 'respondido', 'fechado') DEFAULT 'aberto' COMMENT 'Status do ticket',
    `prioridade` ENUM('baixa', 'normal', 'alta', 'urgente') DEFAULT 'normal' COMMENT 'Prioridade do ticket',
    `data_abertura` DATETIME NOT NULL,
    `data_resposta` DATETIME NULL DEFAULT NULL,
    `data_fechamento` DATETIME NULL DEFAULT NULL,
    `lido_cliente` TINYINT(1) DEFAULT 0,
    `lido_advogado` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_processos_id` (`processos_id`),
    INDEX `idx_usuarios_id` (`usuarios_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_tickets_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_processo` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_usuario` FOREIGN KEY (`usuarios_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de respostas de tickets (Fase 6 - Sprint 4)
CREATE TABLE IF NOT EXISTS `tickets_respostas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tickets_id` INT(11) NOT NULL,
    `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'NULL se for resposta do cliente',
    `clientes_id` INT(11) NULL DEFAULT NULL COMMENT 'NULL se for resposta do advogado',
    `mensagem` TEXT NOT NULL,
    `data_resposta` DATETIME NOT NULL,
    `anexos` TEXT NULL DEFAULT NULL COMMENT 'JSON com lista de arquivos anexados',
    PRIMARY KEY (`id`),
    INDEX `idx_tickets_id` (`tickets_id`),
    INDEX `idx_data_resposta` (`data_resposta`),
    CONSTRAINT `fk_respostas_ticket` FOREIGN KEY (`tickets_id`) 
        REFERENCES `tickets_cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FASE 7: GESTÃO FINANCEIRA
-- ============================================================

-- Tabela de contratos (Fase 7)
CREATE TABLE IF NOT EXISTS `contratos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo` ENUM('fixo', 'variavel', 'sucumbencia', 'misto') NOT NULL DEFAULT 'fixo',
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE NULL DEFAULT NULL,
    `valor_fixo` DECIMAL(10,2) NULL DEFAULT NULL,
    `percentual_sucumbencia` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Percentual sobre valor da causa em caso de sucumbência',
    `percentual_exito` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Percentual sobre valor da causa em caso de vitória',
    `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Apenas 1 ativo por cliente',
    `observacoes` TEXT NULL DEFAULT NULL,
    `arquivo_pdf` VARCHAR(255) NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL DEFAULT NULL,
    `updated_by` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_ativo` (`ativo`),
    INDEX `idx_clientes_ativo` (`clientes_id`, `ativo`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_contratos_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de faturas (Fase 7)
CREATE TABLE IF NOT EXISTS `faturas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `numero` VARCHAR(20) NOT NULL COMMENT 'FAT-YYYY-NNN',
    `clientes_id` INT(11) NOT NULL,
    `contratos_id` INT(11) NULL DEFAULT NULL,
    `data_emissao` DATE NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `valor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_pago` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `saldo_restante` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('rascunho', 'emitida', 'paga', 'parcialmente_paga', 'atrasada', 'cancelada') NOT NULL DEFAULT 'rascunho',
    `observacoes` TEXT NULL DEFAULT NULL,
    `arquivo_pdf` VARCHAR(255) NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de exclusão (soft delete - Fase 11)',
    `deleted_by` INT(11) NULL DEFAULT NULL COMMENT 'ID do usuário que deletou (soft delete - Fase 11)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL DEFAULT NULL,
    `updated_by` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_numero` (`numero`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_contratos_id` (`contratos_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_data_vencimento` (`data_vencimento`),
    INDEX `idx_faturas_status_vencimento` (`status`, `data_vencimento`),
    INDEX `idx_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_faturas_clientes` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_faturas_contratos` FOREIGN KEY (`contratos_id`) 
        REFERENCES `contratos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens de fatura (Fase 7)
CREATE TABLE IF NOT EXISTS `faturas_itens` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `faturas_id` INT(11) NOT NULL,
    `processos_id` INT(11) NULL DEFAULT NULL COMMENT 'Vincular a processo específico',
    `tipo_item` ENUM('honorario', 'custas', 'diligencia', 'despesa', 'repasse') NOT NULL DEFAULT 'honorario',
    `descricao` VARCHAR(255) NOT NULL,
    `valor_unitario` DECIMAL(10,2) NOT NULL,
    `quantidade` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    `valor_total` DECIMAL(10,2) NOT NULL,
    `ipi` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentual IPI',
    `iss` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentual ISS',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_faturas_id` (`faturas_id`),
    INDEX `idx_processos_id` (`processos_id`),
    CONSTRAINT `fk_faturas_itens_faturas` FOREIGN KEY (`faturas_id`) 
        REFERENCES `faturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_faturas_itens_processos` FOREIGN KEY (`processos_id`) 
        REFERENCES `processos` (`idProcessos`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pagamentos (Fase 7)
CREATE TABLE IF NOT EXISTS `pagamentos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `faturas_id` INT(11) NOT NULL,
    `data_pagamento` DATE NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `metodo_pagamento` ENUM('boleto', 'pix', 'transferencia', 'dinheiro', 'cartao') NOT NULL DEFAULT 'dinheiro',
    `observacoes` TEXT NULL DEFAULT NULL,
    `arquivo_comprovante` VARCHAR(255) NULL DEFAULT NULL,
    `recibo_gerado` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL DEFAULT NULL,
    `updated_by` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_faturas_id` (`faturas_id`),
    INDEX `idx_data_pagamento` (`data_pagamento`),
    CONSTRAINT `fk_pagamentos_faturas` FOREIGN KEY (`faturas_id`) 
        REFERENCES `faturas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FASE 8: AUDITORIA E SEGURANÇA
-- ============================================================

-- Tabela de histórico de alterações (Fase 8 - Sprint 5)
CREATE TABLE IF NOT EXISTS `historico_alteracoes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tabela` VARCHAR(100) NOT NULL COMMENT 'Nome da tabela alterada',
    `registro_id` INT(11) NOT NULL COMMENT 'ID do registro alterado',
    `campo` VARCHAR(100) NULL COMMENT 'Campo específico alterado (NULL = múltiplos campos)',
    `valor_anterior` TEXT NULL COMMENT 'Valor anterior (JSON se múltiplos campos)',
    `valor_novo` TEXT NULL COMMENT 'Valor novo (JSON se múltiplos campos)',
    `usuario_id` INT(11) NULL COMMENT 'ID do usuário que fez a alteração',
    `usuario_nome` VARCHAR(255) NULL COMMENT 'Nome do usuário (backup)',
    `acao` ENUM('create', 'update', 'delete') NOT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `data_alteracao` DATETIME NOT NULL,
    `observacoes` TEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_tabela_registro` (`tabela`, `registro_id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_acao` (`acao`),
    INDEX `idx_data_alteracao` (`data_alteracao`),
    CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico detalhado de alterações em registros';

-- Tabela de consentimentos LGPD (Fase 8 - Sprint 3)
CREATE TABLE IF NOT EXISTS `consentimentos_lgpd` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo_consentimento` ENUM('tratamento_dados', 'comunicacao', 'marketing') NOT NULL,
    `consentido` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = consentido, 0 = não consentido',
    `data_consentimento` DATETIME NULL,
    `data_revogacao` DATETIME NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_tipo_consentimento` (`tipo_consentimento`),
    INDEX `idx_consentido` (`consentido`),
    CONSTRAINT `fk_consentimentos_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Consentimentos LGPD dos clientes';

-- Tabela de solicitações LGPD (Fase 8 - Sprint 3)
CREATE TABLE IF NOT EXISTS `solicitacoes_lgpd` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `clientes_id` INT(11) NOT NULL,
    `tipo_solicitacao` ENUM('esquecimento', 'portabilidade', 'acesso', 'retificacao', 'revogacao') NOT NULL,
    `status` ENUM('pendente', 'em_andamento', 'concluida', 'rejeitada') NOT NULL DEFAULT 'pendente',
    `descricao` TEXT NULL,
    `dados_solicitados` TEXT NULL COMMENT 'JSON com dados solicitados para portabilidade',
    `data_solicitacao` DATETIME NOT NULL,
    `data_conclusao` DATETIME NULL,
    `observacoes` TEXT NULL,
    `usuario_responsavel` INT(11) NULL,
    `ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_clientes_id` (`clientes_id`),
    INDEX `idx_tipo_solicitacao` (`tipo_solicitacao`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_solicitacoes_cliente` FOREIGN KEY (`clientes_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitacoes_usuario` FOREIGN KEY (`usuario_responsavel`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitações LGPD dos clientes';

-- ============================================================
-- FASE 9: NOTIFICAÇÕES E COMUNICAÇÃO
-- ============================================================

-- Tabela de notificações (Fase 9)
CREATE TABLE IF NOT EXISTS `notificacoes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NULL COMMENT 'NULL = notificação para cliente',
    `cliente_id` INT(11) NULL COMMENT 'NULL = notificação para usuário interno',
    `tipo` ENUM('email', 'push', 'sms') NOT NULL DEFAULT 'email',
    `categoria` ENUM('movimentacao', 'prazo', 'fatura', 'interacao', 'sistema') NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `mensagem` TEXT NOT NULL,
    `url` VARCHAR(500) NULL COMMENT 'Link relacionado à notificação',
    `lida` TINYINT(1) NOT NULL DEFAULT 0,
    `data_envio` DATETIME NULL,
    `data_leitura` DATETIME NULL,
    `enviada` TINYINT(1) NOT NULL DEFAULT 0,
    `erro_envio` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_cliente_id` (`cliente_id`),
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_lida` (`lida`),
    INDEX `idx_enviada` (`enviada`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_notificacoes_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notificacoes_cliente` FOREIGN KEY (`cliente_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notificações do sistema';

-- Tabela de preferências de notificação (Fase 9)
CREATE TABLE IF NOT EXISTS `preferencias_notificacao` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NULL,
    `cliente_id` INT(11) NULL,
    `tipo_notificacao` ENUM('email', 'push', 'sms') NOT NULL,
    `categoria` ENUM('movimentacao', 'prazo', 'fatura', 'interacao', 'sistema') NOT NULL,
    `habilitado` TINYINT(1) NOT NULL DEFAULT 1,
    `dias_antes_prazo` INT(11) NULL COMMENT 'Para prazos: 7, 5, 2, 1 dias antes',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_usuario_tipo_categoria` (`usuario_id`, `tipo_notificacao`, `categoria`),
    UNIQUE KEY `idx_cliente_tipo_categoria` (`cliente_id`, `tipo_notificacao`, `categoria`),
    CONSTRAINT `fk_pref_notif_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `usuarios` (`idUsuarios`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pref_notif_cliente` FOREIGN KEY (`cliente_id`) 
        REFERENCES `clientes` (`idClientes`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Preferências de notificação dos usuários';

-- ============================================================
-- FASE 11: VALIDAÇÕES E REGRAS CROSS-CUTTING
-- ============================================================

-- Tabela de backups (Fase 11 - RN 12.4)
CREATE TABLE IF NOT EXISTS `backups` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tipo` ENUM('diario', 'semanal', 'mensal') NOT NULL DEFAULT 'diario' COMMENT 'Tipo de backup',
    `arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo de backup',
    `tamanho` BIGINT(20) NOT NULL COMMENT 'Tamanho do arquivo em bytes',
    `data_backup` DATETIME NOT NULL COMMENT 'Data e hora do backup',
    `status` ENUM('sucesso', 'erro', 'em_andamento') NOT NULL DEFAULT 'em_andamento' COMMENT 'Status do backup',
    `mensagem` TEXT NULL COMMENT 'Mensagem de status ou erro',
    `criptografado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se backup está criptografado',
    `local_armazenamento` VARCHAR(255) NULL COMMENT 'Caminho do arquivo',
    PRIMARY KEY (`id`),
    KEY `idx_tipo_data` (`tipo`, `data_backup`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de backups do sistema';

-- Tabela de rate limits (Fase 11 - RN 12.3)
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `identificador` VARCHAR(64) NOT NULL COMMENT 'Identificador único (IP + user_id)',
    `timestamp` INT(11) NOT NULL COMMENT 'Timestamp da requisição',
    `bloqueado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se requisição foi bloqueada',
    PRIMARY KEY (`id`),
    KEY `idx_identificador_timestamp` (`identificador`, `timestamp`),
    KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Controle de rate limiting por usuário/IP';

-- ============================================================
-- TRIGGERS PARA INTEGRIDADE (FASE 7)
-- ============================================================

-- Trigger: Atualizar saldo da fatura quando pagamento é adicionado
DELIMITER //
DROP TRIGGER IF EXISTS `trg_atualizar_saldo_fatura_insert`//
CREATE TRIGGER `trg_atualizar_saldo_fatura_insert` 
AFTER INSERT ON `pagamentos`
FOR EACH ROW
BEGIN
    UPDATE `faturas` 
    SET `valor_pago` = (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = NEW.`faturas_id`
    ),
    `saldo_restante` = `valor_total` - (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = NEW.`faturas_id`
    ),
    `status` = CASE
        WHEN `valor_total` - (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = NEW.`faturas_id`) <= 0 THEN 'paga'
        WHEN (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = NEW.`faturas_id`) > 0 THEN 'parcialmente_paga'
        ELSE `status`
    END
    WHERE `id` = NEW.`faturas_id`;
END//
DELIMITER ;

-- Trigger: Atualizar saldo quando pagamento é atualizado
DELIMITER //
DROP TRIGGER IF EXISTS `trg_atualizar_saldo_fatura_update`//
CREATE TRIGGER `trg_atualizar_saldo_fatura_update` 
AFTER UPDATE ON `pagamentos`
FOR EACH ROW
BEGIN
    UPDATE `faturas` 
    SET `valor_pago` = (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = NEW.`faturas_id`
    ),
    `saldo_restante` = `valor_total` - (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = NEW.`faturas_id`
    ),
    `status` = CASE
        WHEN `valor_total` - (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = NEW.`faturas_id`) <= 0 THEN 'paga'
        WHEN (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = NEW.`faturas_id`) > 0 THEN 'parcialmente_paga'
        ELSE `status`
    END
    WHERE `id` = NEW.`faturas_id`;
END//
DELIMITER ;

-- Trigger: Atualizar saldo quando pagamento é deletado
DELIMITER //
DROP TRIGGER IF EXISTS `trg_atualizar_saldo_fatura_delete`//
CREATE TRIGGER `trg_atualizar_saldo_fatura_delete` 
AFTER DELETE ON `pagamentos`
FOR EACH ROW
BEGIN
    UPDATE `faturas` 
    SET `valor_pago` = (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = OLD.`faturas_id`
    ),
    `saldo_restante` = `valor_total` - (
        SELECT COALESCE(SUM(`valor`), 0) 
        FROM `pagamentos` 
        WHERE `faturas_id` = OLD.`faturas_id`
    ),
    `status` = CASE
        WHEN (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = OLD.`faturas_id`) = 0 THEN 'emitida'
        WHEN `valor_total` - (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = OLD.`faturas_id`) <= 0 THEN 'paga'
        WHEN (SELECT COALESCE(SUM(`valor`), 0) FROM `pagamentos` WHERE `faturas_id` = OLD.`faturas_id`) > 0 THEN 'parcialmente_paga'
        ELSE `status`
    END
    WHERE `id` = OLD.`faturas_id`;
END//
DELIMITER ;

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
    `oab`,
    `cpf`,
    `cep`,
    `rua`,
    `numero`,
    `bairro`,
    `cidade`,
    `estado`,
    `celular`,
    `email`,
    `senha`,
    `situacao`,
    `dataExpiracao`,
    `permissoes_id`,
    `email_confirmado`,
    `dataCadastro`
) VALUES (
    1,
    'Administrador',
    NULL,
    '000.000.000-00',
    '00000-000',
    'Rua Exemplo',
    '0',
    'Centro',
    'Cidade',
    'MG',
    '',
    'admin@admin.com',
    '$2y$10$lAW0AXb0JLZxR0yDdfcBcu3BN9c2AXKKjKTdug7Or0pr6cSGtgyGO',
    1,
    '2030-01-01',
    1,
    1,
    NOW()
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
-- ✅ BANCO DE DADOS COMPLETO
-- Este script cria TODAS as tabelas e colunas necessárias
-- para o sistema jurídico, incluindo Fases 4-11.
-- 
-- CREDENCIAIS DE ACESSO:
-- Email: admin@admin.com
-- Senha: 123456
-- 
-- ⚠️  IMPORTANTE: Altere a senha após o primeiro login!
-- 
-- 📋 TABELAS CRIADAS:
-- - Sistema: ci_sessions, migrations, configuracoes, emitente
-- - Usuários: usuarios, permissoes, resets_de_senha, confirmacoes_email, tentativas_login, bloqueios_conta
-- - Clientes: clientes, contatos_cliente, enderecos_cliente, interacoes_cliente
-- - Planos: planos
-- - Financeiro: contas, categorias, lancamentos, cobrancas
-- - Jurídico: servicos_juridicos, processos, movimentacoes_processuais, prazos, feriados, alertas_prazos, audiencias
-- - Documentos: documentos, documentos_processuais, partes_processo, advogados_processo, processos_cache
-- - Fase 6: acessos_cliente, tickets_cliente, tickets_respostas
-- - Fase 7: contratos, faturas, faturas_itens, pagamentos
-- - Fase 8: historico_alteracoes, consentimentos_lgpd, solicitacoes_lgpd
-- - Fase 9: notificacoes, preferencias_notificacao
-- - Fase 11: backups, rate_limits
-- - Sistema: logs, logs_auditoria, email_queue
-- 
-- ✅ TOTAL: ~40+ tabelas criadas com todas as funcionalidades!
-- ============================================================

