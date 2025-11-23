<?php
/**
 * Script de diagnóstico do sistema
 * 
 * USO: Acesse via navegador: https://seudominio.com/diagnostico.php
 * 
 * IMPORTANTE: Remover este arquivo após o diagnóstico por segurança!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico do Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .ok { color: green; }
        .erro { color: red; }
        .aviso { color: orange; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .erro-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        .ok-box { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Diagnóstico do Sistema</h1>
    
    <?php
    // 1. Verificar .env
    echo "<h2>1. Arquivo .env</h2>";
    $envFile = __DIR__ . '/application/.env';
    if (file_exists($envFile)) {
        echo "<div class='ok-box'>✅ Arquivo existe</div>";
        $perms = substr(sprintf('%o', fileperms($envFile)), -4);
        echo "<p>Permissões: <strong>$perms</strong> (deve ser 0644)</p>";
        
        require_once 'application/vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
        $dotenv->load();
        
        $required = [
            'APP_ENVIRONMENT' => 'Ambiente',
            'APP_ENCRYPTION_KEY' => 'Chave de Criptografia',
            'DB_HOSTNAME' => 'Host do Banco',
            'DB_USERNAME' => 'Usuário do Banco',
            'DB_PASSWORD' => 'Senha do Banco',
            'DB_DATABASE' => 'Nome do Banco'
        ];
        
        echo "<ul>";
        foreach ($required as $var => $label) {
            if (isset($_ENV[$var]) && !empty($_ENV[$var])) {
                $value = ($var === 'DB_PASSWORD' || $var === 'APP_ENCRYPTION_KEY') ? '***' : $_ENV[$var];
                echo "<li class='ok'>✅ $label ($var): $value</li>";
            } else {
                echo "<li class='erro'>❌ $label ($var): NÃO CONFIGURADO</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<div class='erro-box'>❌ Arquivo não existe em: $envFile</div>";
    }
    
    // 2. Verificar PHP
    echo "<h2>2. PHP e Extensões</h2>";
    echo "<p>Versão PHP: <strong>" . PHP_VERSION . "</strong></p>";
    $extensions = [
        'mysqli' => 'MySQLi',
        'pdo_mysql' => 'PDO MySQL',
        'mbstring' => 'Multibyte String',
        'json' => 'JSON',
        'curl' => 'cURL',
        'openssl' => 'OpenSSL',
        'fileinfo' => 'FileInfo'
    ];
    echo "<ul>";
    foreach ($extensions as $ext => $name) {
        if (extension_loaded($ext)) {
            echo "<li class='ok'>✅ $name ($ext)</li>";
        } else {
            echo "<li class='erro'>❌ $name ($ext) - NÃO CARREGADO</li>";
        }
    }
    echo "</ul>";
    
    // 3. Verificar Banco
    echo "<h2>3. Conexão com Banco de Dados</h2>";
    if (isset($_ENV['DB_HOSTNAME'])) {
        $conn = @mysqli_connect(
            $_ENV['DB_HOSTNAME'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_DATABASE']
        );
        if ($conn) {
            echo "<div class='ok-box'>✅ Conexão estabelecida com sucesso!</div>";
            
            // Verificar tabelas
            echo "<h3>Tabelas Principais:</h3>";
            $tables = ['usuarios', 'clientes', 'processos', 'configuracoes', 'ci_sessions'];
            echo "<ul>";
            foreach ($tables as $table) {
                $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                if ($result && mysqli_num_rows($result) > 0) {
                    $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM $table");
                    $row = mysqli_fetch_assoc($count);
                    echo "<li class='ok'>✅ Tabela '$table' existe ({$row['total']} registros)</li>";
                } else {
                    echo "<li class='aviso'>⚠️ Tabela '$table' não encontrada</li>";
                }
            }
            echo "</ul>";
            
            mysqli_close($conn);
        } else {
            echo "<div class='erro-box'>❌ Erro de conexão: " . mysqli_connect_error() . "</div>";
            echo "<p>Código: " . mysqli_connect_errno() . "</p>";
        }
    } else {
        echo "<div class='erro-box'>❌ Variáveis de banco não configuradas</div>";
    }
    
    // 4. Verificar Diretórios
    echo "<h2>4. Diretórios e Permissões</h2>";
    $dirs = [
        'application/logs' => 'Logs',
        'assets/documentos_processuais' => 'Documentos Processuais',
        'assets/uploads' => 'Uploads',
        'assets/userImage' => 'Imagens de Usuário'
    ];
    echo "<ul>";
    foreach ($dirs as $dir => $label) {
        if (is_dir($dir)) {
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            $writable = is_writable($dir);
            $status = $writable ? '✅' : '⚠️';
            $class = $writable ? 'ok' : 'aviso';
            echo "<li class='$class'>$status $label: existe (perms: $perms, " . ($writable ? 'gravável' : 'NÃO gravável') . ")</li>";
        } else {
            echo "<li class='erro'>❌ $label: não existe</li>";
        }
    }
    echo "</ul>";
    
    // 5. Verificar Logs
    echo "<h2>5. Logs do Sistema</h2>";
    $logFile = 'application/logs/log-' . date('Y-m-d') . '.php';
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        echo "<div class='ok-box'>✅ Arquivo de log existe</div>";
        echo "<p>Tamanho: " . number_format($size / 1024, 2) . " KB</p>";
        
        // Mostrar últimas 5 linhas (sem informações sensíveis)
        $lines = file($logFile);
        $lastLines = array_slice($lines, -5);
        echo "<h3>Últimas 5 linhas do log:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<div class='info'>⚠️ Arquivo de log não existe (pode ser normal se não houve erros hoje)</div>";
    }
    
    // 6. Verificar Base URL
    echo "<h2>6. Configuração de URL</h2>";
    if (isset($_ENV['APP_BASEURL'])) {
        $configured = $_ENV['APP_BASEURL'];
        $current = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
        
        echo "<p>Configurado no .env: <strong>$configured</strong></p>";
        echo "<p>URL atual: <strong>$current</strong></p>";
        
        $configuredClean = rtrim($configured, '/');
        $currentClean = rtrim($current, '/');
        
        if ($configuredClean === $currentClean) {
            echo "<div class='ok-box'>✅ Base URL está correto</div>";
        } else {
            echo "<div class='erro-box'>⚠️ Base URL pode estar incorreto</div>";
            echo "<p>Verifique se o APP_BASEURL no .env corresponde à URL atual do servidor.</p>";
        }
    } else {
        echo "<div class='erro-box'>❌ APP_BASEURL não configurado</div>";
    }
    
    // 7. Verificar Ambiente
    echo "<h2>7. Ambiente</h2>";
    if (isset($_ENV['APP_ENVIRONMENT'])) {
        $env = $_ENV['APP_ENVIRONMENT'];
        if ($env === 'production') {
            echo "<div class='ok-box'>✅ Ambiente: PRODUÇÃO</div>";
        } else {
            echo "<div class='info'>Ambiente: $env</div>";
        }
        
        if (isset($_ENV['WHOOPS_ERROR_PAGE_ENABLED'])) {
            $whoops = filter_var($_ENV['WHOOPS_ERROR_PAGE_ENABLED'], FILTER_VALIDATE_BOOLEAN);
            if (!$whoops) {
                echo "<div class='ok-box'>✅ Whoops desabilitado (correto para produção)</div>";
            } else {
                echo "<div class='erro-box'>⚠️ Whoops habilitado (desabilitar em produção)</div>";
            }
        }
    }
    
    // 8. Verificar Segurança
    echo "<h2>8. Configurações de Segurança</h2>";
    $security = [];
    
    if (isset($_ENV['APP_COOKIE_SECURE'])) {
        $secure = filter_var($_ENV['APP_COOKIE_SECURE'], FILTER_VALIDATE_BOOLEAN);
        $security['Cookie Secure'] = $secure ? '✅ Habilitado' : '⚠️ Desabilitado';
    }
    
    if (isset($_ENV['APP_COOKIE_HTTPONLY'])) {
        $httponly = filter_var($_ENV['APP_COOKIE_HTTPONLY'], FILTER_VALIDATE_BOOLEAN);
        $security['Cookie HttpOnly'] = $httponly ? '✅ Habilitado' : '⚠️ Desabilitado';
    }
    
    if (isset($_ENV['APP_CSRF_PROTECTION'])) {
        $csrf = filter_var($_ENV['APP_CSRF_PROTECTION'], FILTER_VALIDATE_BOOLEAN);
        $security['CSRF Protection'] = $csrf ? '✅ Habilitado' : '❌ Desabilitado';
    }
    
    if (empty($security)) {
        echo "<div class='info'>Nenhuma configuração de segurança verificada</div>";
    } else {
        echo "<ul>";
        foreach ($security as $key => $value) {
            echo "<li>$key: $value</li>";
        }
        echo "</ul>";
    }
    ?>
    
    <hr>
    <div class="info">
        <strong>⚠️ IMPORTANTE:</strong> Remova este arquivo (diagnostico.php) após o diagnóstico por segurança!
    </div>
</div>
</body>
</html>

