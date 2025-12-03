<?php
/**
 * Script para corrigir foreign keys da tabela advogados_processo
 * Executa após criar a tabela se as foreign keys falharem
 */

// Configurações padrão
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'advocacia'; // Ajuste se necessário

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

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Corrigir Foreign Keys</title></head><body><pre>";
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco de dados '$db_name' estabelecida.\n\n";
    
    // Verificar se tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'advogados_processo'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela 'advogados_processo' não existe. Execute primeiro criar_tabela_advogados_processo.php\n";
        exit(1);
    }
    
    // Verificar tipos de dados das tabelas referenciadas
    $stmt = $pdo->query("SHOW COLUMNS FROM processos WHERE Field = 'idProcessos'");
    $col_processos = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_processos_id = $col_processos['Type'] ?? 'INT(11)';
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios WHERE Field = 'idUsuarios'");
    $col_usuarios = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_usuarios_id = $col_usuarios['Type'] ?? 'INT(11)';
    
    echo "ℹ️ Tipo de idProcessos: $tipo_processos_id\n";
    echo "ℹ️ Tipo de idUsuarios: $tipo_usuarios_id\n\n";
    
    // Verificar tipos na tabela advogados_processo
    $stmt = $pdo->query("SHOW COLUMNS FROM advogados_processo WHERE Field = 'processos_id'");
    $col_adv_processos = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_adv_processos = $col_adv_processos['Type'] ?? '';
    
    $stmt = $pdo->query("SHOW COLUMNS FROM advogados_processo WHERE Field = 'usuarios_id'");
    $col_adv_usuarios = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_adv_usuarios = $col_adv_usuarios['Type'] ?? '';
    
    echo "ℹ️ Tipo atual de processos_id na advogados_processo: $tipo_adv_processos\n";
    echo "ℹ️ Tipo atual de usuarios_id na advogados_processo: $tipo_adv_usuarios\n\n";
    
    // Se os tipos não correspondem, alterar
    if ($tipo_adv_processos !== $tipo_processos_id) {
        echo "🔄 Ajustando tipo de processos_id de '$tipo_adv_processos' para '$tipo_processos_id'...\n";
        $pdo->exec("ALTER TABLE `advogados_processo` MODIFY `processos_id` $tipo_processos_id NOT NULL");
        echo "✅ Tipo de processos_id ajustado.\n";
    }
    
    if ($tipo_adv_usuarios !== $tipo_usuarios_id) {
        echo "🔄 Ajustando tipo de usuarios_id de '$tipo_adv_usuarios' para '$tipo_usuarios_id'...\n";
        $pdo->exec("ALTER TABLE `advogados_processo` MODIFY `usuarios_id` $tipo_usuarios_id NOT NULL");
        echo "✅ Tipo de usuarios_id ajustado.\n";
    }
    
    // Verificar foreign keys existentes
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '$db_name' 
        AND TABLE_NAME = 'advogados_processo' 
        AND CONSTRAINT_NAME LIKE 'fk_%'
    ");
    $fks_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fks_existentes[] = $row['CONSTRAINT_NAME'];
    }
    
    // Remover foreign keys existentes se houver
    foreach ($fks_existentes as $fk) {
        echo "🔄 Removendo foreign key existente: $fk\n";
        try {
            $pdo->exec("ALTER TABLE `advogados_processo` DROP FOREIGN KEY `$fk`");
            echo "✅ Foreign key $fk removida.\n";
        } catch (PDOException $e) {
            echo "⚠️ Erro ao remover $fk: " . $e->getMessage() . "\n";
        }
    }
    
    // Criar foreign keys corretas
    echo "\n🔄 Criando foreign keys...\n";
    
    try {
        $pdo->exec("ALTER TABLE `advogados_processo` 
            ADD CONSTRAINT `fk_advogados_processo_processos` 
            FOREIGN KEY (`processos_id`) 
            REFERENCES `processos` (`idProcessos`) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE");
        echo "✅ Foreign key para processos criada com sucesso!\n";
    } catch (PDOException $e) {
        echo "❌ Erro ao criar foreign key para processos: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE `advogados_processo` 
            ADD CONSTRAINT `fk_advogados_processo_usuarios` 
            FOREIGN KEY (`usuarios_id`) 
            REFERENCES `usuarios` (`idUsuarios`) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE");
        echo "✅ Foreign key para usuarios criada com sucesso!\n";
    } catch (PDOException $e) {
        echo "❌ Erro ao criar foreign key para usuarios: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Operação concluída!\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

if (php_sapi_name() !== 'cli') {
    echo "</pre></body></html>";
}

