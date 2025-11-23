<?php
/**
 * Script para verificar erro específico de migrations
 * 
 * USO: Acesse via navegador: https://seudominio.com/verificar_erro_migration.php
 * 
 * IMPORTANTE: Remover este arquivo após o uso!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificação de Erro - Migrations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .ok { color: green; }
        .erro { color: red; }
        .aviso { color: orange; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .erro-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        .ok-box { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Verificação de Erro - Migrations</h1>
    
    <?php
    require_once 'application/vendor/autoload.php';
    
    $envFile = __DIR__ . '/application/.env';
    if (file_exists($envFile)) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
        $dotenv->load();
    } else {
        echo "<div class='erro-box'>❌ Arquivo .env não encontrado!</div>";
        exit;
    }
    
    // 1. Verificar conexão
    echo "<h2>1. Conexão com Banco</h2>";
    $hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
    $username = $_ENV['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $database = $_ENV['DB_DATABASE'] ?? '';
    
    $conn = @mysqli_connect($hostname, $username, $password, $database);
    if ($conn) {
        echo "<div class='ok-box'>✅ Conexão OK</div>";
    } else {
        echo "<div class='erro-box'>❌ Erro: " . mysqli_connect_error() . "</div>";
        exit;
    }
    
    // 2. Verificar tabela migrations
    echo "<h2>2. Tabela migrations</h2>";
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'migrations'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='ok-box'>✅ Tabela existe</div>";
        
        $result = mysqli_query($conn, "SELECT * FROM migrations ORDER BY version DESC LIMIT 5");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<p>Últimas migrations:</p><ul>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<li>" . $row['version'] . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<div class='aviso'>⚠️ Tabela não existe. Tentando criar...</div>";
        if (mysqli_query($conn, "CREATE TABLE IF NOT EXISTS migrations (version BIGINT(20) NOT NULL)")) {
            echo "<div class='ok-box'>✅ Tabela criada com sucesso!</div>";
        } else {
            echo "<div class='erro-box'>❌ Erro ao criar: " . mysqli_error($conn) . "</div>";
        }
    }
    
    // 3. Verificar diretório
    echo "<h2>3. Diretório de Migrations</h2>";
    $migrationPath = __DIR__ . '/application/database/migrations/';
    if (is_dir($migrationPath)) {
        echo "<div class='ok-box'>✅ Diretório existe</div>";
        echo "<p>Caminho: <code>$migrationPath</code></p>";
        
        if (is_readable($migrationPath)) {
            echo "<div class='ok-box'>✅ Permissão de leitura OK</div>";
        } else {
            echo "<div class='erro-box'>❌ Sem permissão de leitura!</div>";
        }
        
        $files = glob($migrationPath . '*.php');
        echo "<p>Arquivos encontrados: <strong>" . count($files) . "</strong></p>";
    } else {
        echo "<div class='erro-box'>❌ Diretório não existe: $migrationPath</div>";
    }
    
    // 4. Verificar sintaxe de migrations importantes
    echo "<h2>4. Verificação de Sintaxe</h2>";
    $importantFiles = [
        '20251115061242_criar_tabela_processos.php',
        '20251122150000_create_processos_cache.php',
        '20251122160000_adaptar_notificacoes_juridicas.php'
    ];
    
    $errosSintaxe = [];
    foreach ($importantFiles as $file) {
        $fullPath = $migrationPath . $file;
        if (file_exists($fullPath)) {
            $output = [];
            $return = 0;
            exec("php -l " . escapeshellarg($fullPath) . " 2>&1", $output, $return);
            if ($return === 0) {
                echo "<div class='ok'>✅ $file - Sintaxe OK</div>";
            } else {
                echo "<div class='erro'>❌ $file - Erro de sintaxe:</div>";
                echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
                $errosSintaxe[] = $file;
            }
        }
    }
    
    // 5. Tentar carregar CodeIgniter
    echo "<h2>5. Teste de Carregamento</h2>";
    try {
        // Definir constantes básicas
        if (!defined('ENVIRONMENT')) {
            define('ENVIRONMENT', $_ENV['APP_ENVIRONMENT'] ?? 'production');
        }
        if (!defined('BASEPATH')) {
            define('BASEPATH', __DIR__ . '/application/vendor/codeigniter/framework/system/');
        }
        if (!defined('APPPATH')) {
            define('APPPATH', __DIR__ . '/application/');
        }
        
        // Verificar se arquivos existem
        $systemPath = BASEPATH . 'core/CodeIgniter.php';
        if (file_exists($systemPath)) {
            echo "<div class='ok-box'>✅ Arquivos do CodeIgniter encontrados</div>";
        } else {
            echo "<div class='erro-box'>❌ CodeIgniter não encontrado em: $systemPath</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='erro-box'>❌ Exceção: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // 6. Verificar logs recentes
    echo "<h2>6. Logs Recentes</h2>";
    $logFile = __DIR__ . '/application/logs/log-' . date('Y-m-d') . '.php';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        echo "<p>Últimas 20 linhas do log de hoje:</p>";
        echo "<pre style='max-height: 400px; overflow-y: auto;'>";
        foreach ($lastLines as $line) {
            if (stripos($line, 'migration') !== false || stripos($line, 'error') !== false) {
                echo "<strong>" . htmlspecialchars($line) . "</strong>";
            } else {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
    } else {
        echo "<div class='info'>⚠️ Arquivo de log não existe ainda</div>";
    }
    
    mysqli_close($conn);
    ?>
    
    <hr>
    <div class="info">
        <strong>💡 PRÓXIMOS PASSOS:</strong><br>
        1. Se houver erros acima, corrija-os primeiro<br>
        2. Tente executar via CLI: <code>php index.php tools migrate</code><br>
        3. Verifique os logs em <code>application/logs/</code><br>
        4. <strong>Remova este arquivo após o diagnóstico!</strong>
    </div>
</div>
</body>
</html>

