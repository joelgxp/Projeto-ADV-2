<?php
/**
 * Script standalone para criar tabela advogados_processo
 * Suporta múltiplos advogados por processo com papéis diferentes
 * 
 * USO: Acesse via navegador: http://localhost/mapos/criar_tabela_advogados_processo.php
 */

// Configurações padrão (ajuste se necessário)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = ''; // Será detectado automaticamente

// Tentar ler do .env se existir
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if ($key === 'DB_HOSTNAME') $db_host = $value;
            if ($key === 'DB_USERNAME') $db_user = $value;
            if ($key === 'DB_PASSWORD') $db_pass = $value;
            if ($key === 'DB_DATABASE') $db_name = $value;
        }
    }
}

// Se executado via navegador, mostrar HTML
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Criar Tabela Advogados Processo</title></head><body><pre>";
}

try {
    // Primeiro conectar sem especificar banco para listar bancos disponíveis
    $pdo_temp = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Se não especificou banco, tentar detectar ou listar
    if (empty($db_name)) {
        // Tentar encontrar banco que tenha tabela 'processos'
        $stmt = $pdo_temp->query("SHOW DATABASES");
        $bancos = [];
        $bancos_com_processos = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $banco = $row[0];
            // Ignorar bancos do sistema
            if (!in_array($banco, ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
                $bancos[] = $banco;
                
                // Verificar se tem tabela processos
                try {
                    $check = $pdo_temp->query("SELECT 1 FROM information_schema.tables WHERE table_schema = '$banco' AND table_name = 'processos' LIMIT 1");
                    if ($check->rowCount() > 0) {
                        $bancos_com_processos[] = $banco;
                    }
                } catch (PDOException $e) {
                    // Ignorar erros
                }
            }
        }
        
        if (!empty($bancos_com_processos)) {
            // Usar o primeiro banco que tem tabela processos
            $db_name = $bancos_com_processos[0];
            echo "✅ Banco de dados detectado automaticamente: '$db_name'\n\n";
        } elseif (count($bancos) == 1) {
            // Se só tem um banco, usar ele
            $db_name = $bancos[0];
            echo "✅ Banco de dados detectado: '$db_name'\n\n";
        } else {
            // Múltiplos bancos, pedir para escolher
            echo "⚠️ Múltiplos bancos encontrados. Por favor, informe o nome do banco:\n";
            echo "Bancos disponíveis: " . implode(', ', $bancos) . "\n";
            if (php_sapi_name() === 'cli') {
                echo "Digite o nome do banco: ";
                $db_name = trim(fgets(STDIN));
            } else {
                echo "Por favor, edite o script e defina \$db_name manualmente.\n";
                exit(1);
            }
        }
    }
    
    // Agora conectar ao banco específico
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco de dados '$db_name' estabelecida.\n\n";
    
    // Verificar se tabela já existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'advogados_processo'");
    if ($stmt->rowCount() > 0) {
        echo "⚠️ Tabela 'advogados_processo' já existe.\n";
        echo "Recriando tabela...\n";
        $pdo->exec("DROP TABLE IF EXISTS `advogados_processo`");
        echo "✅ Tabela antiga removida.\n\n";
    }
    
    // Verificar tipos de dados das tabelas referenciadas
    $stmt = $pdo->query("SHOW COLUMNS FROM processos WHERE Field = 'idProcessos'");
    $col_processos = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_processos_id = $col_processos['Type'] ?? 'INT(11)';
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios WHERE Field = 'idUsuarios'");
    $col_usuarios = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_usuarios_id = $col_usuarios['Type'] ?? 'INT(11)';
    
    // Extrair apenas o tipo base (sem UNSIGNED se houver)
    $tipo_processos_id = preg_replace('/\s+UNSIGNED/i', '', $tipo_processos_id);
    $tipo_usuarios_id = preg_replace('/\s+UNSIGNED/i', '', $tipo_usuarios_id);
    
    echo "ℹ️ Tipo de idProcessos: $tipo_processos_id\n";
    echo "ℹ️ Tipo de idUsuarios: $tipo_usuarios_id\n\n";
    
    // Criar tabela
    $sql = "CREATE TABLE IF NOT EXISTS `advogados_processo` (
        `idAdvogadoProcesso` INT(11) NOT NULL AUTO_INCREMENT,
        `processos_id` $tipo_processos_id NOT NULL COMMENT 'ID do processo',
        `usuarios_id` $tipo_usuarios_id NOT NULL COMMENT 'ID do usuário (advogado)',
        `papel` VARCHAR(50) NOT NULL DEFAULT 'coadjuvante' COMMENT 'Papel: principal, coadjuvante, estagiario',
        `data_atribuicao` DATETIME NOT NULL COMMENT 'Data/hora da atribuição',
        `data_remocao` DATETIME NULL DEFAULT NULL COMMENT 'Data/hora da remoção (soft delete)',
        `ativo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=removido',
        `notificado` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=notificado por email, 0=não notificado',
        `data_notificacao` DATETIME NULL DEFAULT NULL COMMENT 'Data/hora da notificação',
        `observacoes` TEXT NULL DEFAULT NULL COMMENT 'Observações',
        PRIMARY KEY (`idAdvogadoProcesso`),
        INDEX `idx_processos_id` (`processos_id`),
        INDEX `idx_usuarios_id` (`usuarios_id`),
        INDEX `idx_ativo` (`ativo`),
        INDEX `idx_processo_usuario_ativo` (`processos_id`, `usuarios_id`, `ativo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Tabela 'advogados_processo' criada com sucesso!\n\n";
    
    // Verificar se tabelas relacionadas existem antes de criar foreign keys
    $stmt = $pdo->query("SHOW TABLES LIKE 'processos'");
    $processos_existe = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $usuarios_existe = $stmt->rowCount() > 0;
    
    // Verificar se foreign keys já existem e remover se necessário
    try {
        $pdo->exec("ALTER TABLE `advogados_processo` DROP FOREIGN KEY IF EXISTS `fk_advogados_processo_processos`");
    } catch (PDOException $e) {
        // Ignorar se não existir
    }
    
    try {
        $pdo->exec("ALTER TABLE `advogados_processo` DROP FOREIGN KEY IF EXISTS `fk_advogados_processo_usuarios`");
    } catch (PDOException $e) {
        // Ignorar se não existir
    }
    
    if ($processos_existe) {
        try {
            $pdo->exec("ALTER TABLE `advogados_processo` 
                ADD CONSTRAINT `fk_advogados_processo_processos` 
                FOREIGN KEY (`processos_id`) 
                REFERENCES `processos` (`idProcessos`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE");
            echo "✅ Foreign key para processos criada.\n";
        } catch (PDOException $e) {
            echo "⚠️ Aviso ao criar foreign key para processos: " . $e->getMessage() . "\n";
            echo "   Verifique se os tipos de dados correspondem.\n";
        }
    } else {
        echo "⚠️ Tabela 'processos' não encontrada. Foreign key não criada.\n";
    }
    
    if ($usuarios_existe) {
        try {
            $pdo->exec("ALTER TABLE `advogados_processo` 
                ADD CONSTRAINT `fk_advogados_processo_usuarios` 
                FOREIGN KEY (`usuarios_id`) 
                REFERENCES `usuarios` (`idUsuarios`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE");
            echo "✅ Foreign key para usuarios criada.\n";
        } catch (PDOException $e) {
            echo "⚠️ Aviso ao criar foreign key para usuarios: " . $e->getMessage() . "\n";
            echo "   Verifique se os tipos de dados correspondem.\n";
        }
    } else {
        echo "⚠️ Tabela 'usuarios' não encontrada. Foreign key não criada.\n";
    }
    
    // Migrar dados existentes (se houver processos com usuarios_id)
    if ($processos_existe) {
        echo "\n🔄 Migrando dados existentes...\n";
        $stmt = $pdo->query("
            SELECT DISTINCT idProcessos, usuarios_id 
            FROM processos 
            WHERE usuarios_id IS NOT NULL AND usuarios_id > 0
        ");
        
        $migrados = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            try {
                // Verificar se já existe
                $check = $pdo->prepare("
                    SELECT id FROM advogados_processo 
                    WHERE processos_id = ? AND usuarios_id = ? AND ativo = 1
                ");
                $check->execute([$row['idProcessos'], $row['usuarios_id']]);
                
                if ($check->rowCount() == 0) {
                    $insert = $pdo->prepare("
                        INSERT INTO advogados_processo 
                        (processos_id, usuarios_id, papel, data_atribuicao, ativo, notificado) 
                        VALUES (?, ?, 'principal', NOW(), 1, 0)
                    ");
                    $insert->execute([$row['idProcessos'], $row['usuarios_id']]);
                    $migrados++;
                }
            } catch (PDOException $e) {
                echo "⚠️ Erro ao migrar processo ID {$row['idProcessos']}: " . $e->getMessage() . "\n";
            }
        }
        
        if ($migrados > 0) {
            echo "✅ $migrados processo(s) migrado(s) com advogado principal.\n";
        } else {
            echo "ℹ️ Nenhum processo com advogado responsável encontrado para migrar.\n";
        }
    }
    
    echo "\n✅ Operação concluída com sucesso!\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// Se executado via navegador, fechar HTML
if (php_sapi_name() !== 'cli') {
    echo "</pre></body></html>";
}
