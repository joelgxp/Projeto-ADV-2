SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `ci_sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ci_sessions` (
        `id` varchar(128) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
        `data` blob NOT NULL,
        KEY `ci_sessions_timestamp` (`timestamp`)
);


-- -----------------------------------------------------
-- Table `clientes` - Adaptado para contexto jurídico
-- -----------------------------------------------------
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
  `contato` varchar(45) DEFAULT NULL,
  `complemento` varchar(45) DEFAULT NULL,
  `fornecedor` BOOLEAN NOT NULL DEFAULT 0,
  `ramo_atividade` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ramo de atividade (para PJ)',
  `observacoes_juridicas` TEXT NULL DEFAULT NULL COMMENT 'Observações específicas do contexto jurídico',
  PRIMARY KEY (`idClientes`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `resets_de_senha` ( 
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(200) NOT NULL , 
  `token` VARCHAR(255) NOT NULL , 
  `data_expiracao` DATETIME NOT NULL, 
  `token_utilizado` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `categorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
  `idCategorias` INT NOT NULL AUTO_INCREMENT,
  `categoria` VARCHAR(80) NULL,
  `cadastro` DATE NULL,
  `status` TINYINT(1) NULL,
  `tipo` VARCHAR(15) NULL,
  PRIMARY KEY (`idCategorias`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `contas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `contas` (
  `idContas` INT NOT NULL AUTO_INCREMENT,
  `conta` VARCHAR(45) NULL,
  `banco` VARCHAR(45) NULL,
  `numero` VARCHAR(45) NULL,
  `saldo` DECIMAL(10,2) NULL,
  `cadastro` DATE NULL,
  `status` TINYINT(1) NULL,
  `tipo` VARCHAR(80) NULL,
  PRIMARY KEY (`idContas`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `permissoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissoes` (
  `idPermissao` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `permissoes` TEXT NULL,
  `situacao` TINYINT(1) NULL,
  `data` DATE NULL,
  PRIMARY KEY (`idPermissao`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuarios` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `rg` VARCHAR(20) NULL DEFAULT NULL,
  `cpf` VARCHAR(20) NOT NULL,
  `cep` VARCHAR(9) NOT NULL,
  `rua` VARCHAR(70) NULL DEFAULT NULL,
  `numero` VARCHAR(15) NULL DEFAULT NULL,
  `bairro` VARCHAR(45) NULL DEFAULT NULL,
  `cidade` VARCHAR(45) NULL DEFAULT NULL,
  `estado` VARCHAR(20) NULL DEFAULT NULL,
  `email` VARCHAR(80) NOT NULL,
  `senha` VARCHAR(200) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `celular` VARCHAR(20) NULL DEFAULT NULL,
  `situacao` TINYINT(1) NOT NULL,
  `dataCadastro` DATE NOT NULL,
  `permissoes_id` INT NOT NULL,
  `dataExpiracao` date DEFAULT NULL,
  `url_image_user` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`idUsuarios`),
  INDEX `fk_usuarios_permissoes1_idx` (`permissoes_id` ASC),
  CONSTRAINT `fk_usuarios_permissoes1`
    FOREIGN KEY (`permissoes_id`)
    REFERENCES `permissoes` (`idPermissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;



-- -----------------------------------------------------
-- Table `lancamentos` - Adaptado para honorários
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lancamentos` (
  `idLancamentos` INT(11) NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(255) NULL DEFAULT NULL,
  `valor` DECIMAL(10, 2) NULL DEFAULT 0,
  `desconto` DECIMAL(10, 2) NULL DEFAULT 0,
  `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0,
  `tipo_desconto` varchar(8) NULL DEFAULT NULL,
  `data_vencimento` DATE NOT NULL,
  `data_pagamento` DATE NULL DEFAULT NULL,
  `baixado` TINYINT(1) NULL DEFAULT 0,
  `cliente_fornecedor` VARCHAR(255) NULL DEFAULT NULL,
  `forma_pgto` VARCHAR(100) NULL DEFAULT NULL,
  `tipo` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Tipo: honorario, custa, despesa, receita',
  `anexo` VARCHAR(250) NULL,
  `observacoes` TEXT NULL,
  `clientes_id` INT(11) NULL DEFAULT NULL,
  `categorias_id` INT NULL,
  `contas_id` INT NULL,
  `processos_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do processo relacionado',
  `contratos_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do contrato relacionado',
  `usuarios_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`idLancamentos`),
  INDEX `fk_lancamentos_clientes1` (`clientes_id` ASC),
  INDEX `fk_lancamentos_categorias1_idx` (`categorias_id` ASC),
  INDEX `fk_lancamentos_contas1_idx` (`contas_id` ASC),
  INDEX `fk_lancamentos_usuarios1` (`usuarios_id` ASC),
  INDEX `fk_lancamentos_processos1` (`processos_id` ASC),
  CONSTRAINT `fk_lancamentos_clientes1`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_categorias1`
    FOREIGN KEY (`categorias_id`)
    REFERENCES `categorias` (`idCategorias`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_contas1`
    FOREIGN KEY (`contas_id`)
    REFERENCES `contas` (`idContas`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_lancamentos_usuarios1`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `servicos_juridicos` - Renomeado de servicos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `servicos_juridicos` (
  `idServicos` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `tipo_servico` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de serviço jurídico',
  `modelo_peca_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do modelo de peça relacionado',
  `valor_base` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Valor base do serviço',
  `tempo_estimado` INT(11) NULL DEFAULT NULL COMMENT 'Tempo estimado em horas',
  PRIMARY KEY (`idServicos`))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `processos` - Nova tabela para processos jurídicos
-- -----------------------------------------------------
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
  INDEX `idx_numeroProcesso` (`numeroProcesso`),
  INDEX `idx_clientes_id` (`clientes_id`),
  INDEX `idx_usuarios_id` (`usuarios_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_processos_clientes`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_processos_usuarios`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `movimentacoes_processuais` - Nova tabela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `movimentacoes_processuais` (
  `idMovimentacoes` INT(11) NOT NULL AUTO_INCREMENT,
  `processos_id` INT(11) NOT NULL,
  `dataMovimentacao` DATETIME NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `tipo` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo de movimentação',
  `origem` VARCHAR(20) NOT NULL DEFAULT 'manual' COMMENT 'Origem: manual ou api_cnj',
  `dados_api` TEXT NULL DEFAULT NULL COMMENT 'Dados JSON da API CNJ',
  `importado_api` TINYINT(1) NOT NULL DEFAULT 0,
  `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário que cadastrou (se manual)',
  `dataCadastro` DATETIME NOT NULL,
  PRIMARY KEY (`idMovimentacoes`),
  INDEX `idx_processos_id` (`processos_id`),
  INDEX `idx_dataMovimentacao` (`dataMovimentacao`),
  CONSTRAINT `fk_movimentacoes_processos`
    FOREIGN KEY (`processos_id`)
    REFERENCES `processos` (`idProcessos`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `prazos` - Nova tabela
-- -----------------------------------------------------
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
  CONSTRAINT `fk_prazos_processos`
    FOREIGN KEY (`processos_id`)
    REFERENCES `processos` (`idProcessos`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `audiencias` - Nova tabela
-- -----------------------------------------------------
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
  CONSTRAINT `fk_audiencias_processos`
    FOREIGN KEY (`processos_id`)
    REFERENCES `processos` (`idProcessos`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `documentos_processuais` - Nova tabela
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `documentos_processuais` (
  `idDocumentos` INT(11) NOT NULL AUTO_INCREMENT,
  `processos_id` INT(11) NULL DEFAULT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome do arquivo',
  `tipo_documento` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo: peticao, sentenca, documento, etc.',
  `dataUpload` DATETIME NOT NULL,
  `usuarios_id` INT(11) NULL DEFAULT NULL COMMENT 'Usuário que fez upload',
  `tamanho` BIGINT(20) NULL DEFAULT NULL COMMENT 'Tamanho do arquivo em bytes',
  `mime_type` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Tipo MIME do arquivo',
  PRIMARY KEY (`idDocumentos`),
  INDEX `idx_processos_id` (`processos_id`),
  INDEX `idx_tipo_documento` (`tipo_documento`),
  CONSTRAINT `fk_documentos_processos`
    FOREIGN KEY (`processos_id`)
    REFERENCES `processos` (`idProcessos`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `cobrancas` - Adaptado (removidas referências a os_id e vendas_id)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cobrancas` (
  `idCobranca` INT(11) NOT NULL AUTO_INCREMENT,
  `charge_id` varchar(255) DEFAULT NULL,
  `conditional_discount_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `custom_id` int(11) DEFAULT NULL,
  `expire_at` date NOT NULL,
  `message` varchar(255) NOT NULL,
  `payment_method` varchar(11) DEFAULT NULL,
  `payment_url` varchar(255) DEFAULT NULL,
  `request_delivery_address` varchar(64) DEFAULT NULL,
  `status` varchar(36) NOT NULL,
  `total` varchar(15) DEFAULT NULL,
  `barcode` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `payment_gateway` varchar(255) NULL DEFAULT NULL,
  `payment` varchar(64) NOT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `processos_id` int(11) DEFAULT NULL COMMENT 'ID do processo relacionado',
  `clientes_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`idCobranca`),
  INDEX `fk_cobrancas_processos1` (`processos_id` ASC),
  CONSTRAINT `fk_cobrancas_processos1` FOREIGN KEY (`processos_id`) REFERENCES `processos` (`idProcessos`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  INDEX `fk_cobrancas_clientes1` (`clientes_id` ASC),
  CONSTRAINT `fk_cobrancas_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`idClientes`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `documentos` - Mantida para documentos gerais
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `documentos` (
  `idDocumentos` INT NOT NULL AUTO_INCREMENT,
  `documento` VARCHAR(70) NULL,
  `descricao` TEXT NULL,
  `file` VARCHAR(100) NULL,
  `path` VARCHAR(300) NULL,
  `url` VARCHAR(300) NULL,
  `cadastro` DATE NULL,
  `categoria` VARCHAR(80) NULL,
  `tipo` VARCHAR(15) NULL,
  `tamanho` VARCHAR(45) NULL,
  PRIMARY KEY (`idDocumentos`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;


-- -----------------------------------------------------
-- Table `logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs` (
  `idLogs` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(80) NULL,
  `tarefa` VARCHAR(100) NULL,
  `data` DATE NULL,
  `hora` TIME NULL,
  `ip` VARCHAR(45) NULL,
  PRIMARY KEY (`idLogs`))
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `emitente`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `emitente` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `nome` VARCHAR(255) NULL ,
  `cnpj` VARCHAR(45) NULL ,
  `ie` VARCHAR(50) NULL ,
  `rua` VARCHAR(70) NULL ,
  `numero` VARCHAR(15) NULL ,
  `bairro` VARCHAR(45) NULL ,
  `cidade` VARCHAR(45) NULL ,
  `uf` VARCHAR(20) NULL ,
  `telefone` VARCHAR(20) NULL ,
  `email` VARCHAR(255) NULL ,
  `url_logo` VARCHAR(225) NULL ,
  `cep` VARCHAR(20) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `email_queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sending','sent','failed') DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `headers` text,
  PRIMARY KEY (`id`)
)ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `configuracoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracoes` ( 
  `idConfig` INT NOT NULL AUTO_INCREMENT , `config` VARCHAR(20) NOT NULL UNIQUE, `valor` TEXT NULL , PRIMARY KEY (`idConfig`)
  ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table `migrations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `version` BIGINT(20) NOT NULL
);

-- -----------------------------------------------------
-- Dados Iniciais - Configurações
-- -----------------------------------------------------
INSERT IGNORE INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES
(2, 'app_name', 'Adv'),
(3, 'app_theme', 'white'),
(4, 'per_page', '10'),
(5, 'email_automatico', '1'),
(6, 'control_datatable', '1'),
(7, 'pix_key', ''),
(8, 'control_2vias', '0');

-- -----------------------------------------------------
-- Dados Iniciais - Permissões (Adaptadas para contexto jurídico)
-- -----------------------------------------------------
INSERT IGNORE INTO `permissoes` (`idPermissao`, `nome`, `permissoes`, `situacao`, `data`) VALUES
(1, 'Administrador', 'a:45:{s:8:"aCliente";s:1:"1";s:8:"eCliente";s:1:"1";s:8:"dCliente";s:1:"1";s:8:"vCliente";s:1:"1";s:8:"aServico";s:1:"1";s:8:"eServico";s:1:"1";s:8:"dServico";s:1:"1";s:8:"vServico";s:1:"1";s:9:"aProcesso";s:1:"1";s:9:"eProcesso";s:1:"1";s:9:"dProcesso";s:1:"1";s:9:"vProcesso";s:1:"1";s:9:"sProcesso";s:1:"1";s:6:"aPrazo";s:1:"1";s:6:"ePrazo";s:1:"1";s:6:"dPrazo";s:1:"1";s:6:"vPrazo";s:1:"1";s:10:"aAudiencia";s:1:"1";s:10:"eAudiencia";s:1:"1";s:10:"dAudiencia";s:1:"1";s:10:"vAudiencia";s:1:"1";s:18:"cConsultaProcessual";s:1:"1";s:8:"aArquivo";s:1:"1";s:8:"eArquivo";s:1:"1";s:8:"dArquivo";s:1:"1";s:8:"vArquivo";s:1:"1";s:11:"aLancamento";s:1:"1";s:11:"eLancamento";s:1:"1";s:11:"dLancamento";s:1:"1";s:11:"vLancamento";s:1:"1";s:8:"cUsuario";s:1:"1";s:9:"cEmitente";s:1:"1";s:10:"cPermissao";s:1:"1";s:7:"cBackup";s:1:"1";s:10:"cAuditoria";s:1:"1";s:6:"cEmail";s:1:"1";s:8:"cSistema";s:1:"1";s:8:"rCliente";s:1:"1";s:8:"rServico";s:1:"1";s:9:"rProcesso";s:1:"1";s:6:"rPrazo";s:1:"1";s:10:"rAudiencia";s:1:"1";s:11:"rFinanceiro";s:1:"1";s:9:"aCobranca";s:1:"1";s:9:"eCobranca";s:1:"1";s:9:"dCobranca";s:1:"1";s:9:"vCobranca";s:1:"1";}', 1, CURDATE());

-- -----------------------------------------------------
-- Dados Iniciais - Usuário Admin
-- -----------------------------------------------------
INSERT IGNORE INTO `usuarios` (`idUsuarios`, `nome`, `rg`, `cpf`, `cep`, `rua`, `numero`, `bairro`, `cidade`, `estado`, `email`, `senha`, `telefone`, `celular`, `situacao`, `dataCadastro`, `permissoes_id`,`dataExpiracao`) VALUES
(1, 'Administrador', 'MG-00.000.000', '000.000.000-00', '00000-000', 'Rua Exemplo', '0', 'Centro', 'Cidade', 'MG', 'admin@adv.com', '$2y$10$O.wf7J2XVeSSXp.XZwX.EeO1RHAICqKwy/h6QW0.4.S3qCWzsgR2G', '000000-0000', '', 1, CURDATE(), 1, '3000-01-01');

-- -----------------------------------------------------
-- Dados Iniciais - Migrations
-- -----------------------------------------------------
INSERT IGNORE INTO `migrations`(`version`) VALUES ('20251115061241');

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
