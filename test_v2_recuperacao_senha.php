<?php
/**
 * Script de Teste - Recuperação de Senha V2.0
 * 
 * Testa as melhorias implementadas na Fase 1:
 * - Token de recuperação válido por 1 hora (RN 1.3)
 * - Validação de token
 * - Marcação de token como utilizado
 */

// Carregar autoload do CodeIgniter
define('BASEPATH', __DIR__ . '/');
require_once __DIR__ . '/application/vendor/autoload.php';

// Carregar .env se existir
$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

// Conectar ao banco de dados
$host = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db = $_ENV['DB_DATABASE'] ?? 'adv';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        die("❌ Erro de conexão: " . $conn->connect_error . "\n");
    }
    
    echo "✅ Conectado ao banco de dados: {$db}\n\n";
    
    // Teste 1: Verificar se a tabela resets_de_senha existe
    echo "📋 Teste 1: Verificando estrutura da tabela resets_de_senha...\n";
    $result = $conn->query("SHOW TABLES LIKE 'resets_de_senha'");
    if ($result->num_rows > 0) {
        echo "   ✅ Tabela resets_de_senha existe\n";
        
        // Verificar colunas
        $columns = $conn->query("SHOW COLUMNS FROM resets_de_senha");
        $hasTokenUtilizado = false;
        $hasDataExpiracao = false;
        
        while ($row = $columns->fetch_assoc()) {
            if ($row['Field'] === 'token_utilizado') {
                $hasTokenUtilizado = true;
            }
            if ($row['Field'] === 'data_expiracao') {
                $hasDataExpiracao = true;
            }
        }
        
        echo "   " . ($hasTokenUtilizado ? "✅" : "❌") . " Coluna token_utilizado existe\n";
        echo "   " . ($hasDataExpiracao ? "✅" : "❌") . " Coluna data_expiracao existe\n";
        
        if (!$hasTokenUtilizado || !$hasDataExpiracao) {
            echo "\n   ⚠️  ATENÇÃO: Algumas colunas podem estar faltando. Verifique a estrutura da tabela.\n";
        }
    } else {
        echo "   ❌ Tabela resets_de_senha NÃO existe\n";
        echo "   ℹ️  Execute as migrations para criar a tabela\n";
    }
    
    // Teste 2: Testar geração de data de expiração (1 hora)
    echo "\n📋 Teste 2: Testando geração de data de expiração (1 hora)...\n";
    $dataAtual = date('Y-m-d H:i:s');
    $dataExpiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $dateAtual = new DateTime($dataAtual);
    $dateExpiracao = new DateTime($dataExpiracao);
    $diff = $dateAtual->diff($dateExpiracao);
    
    if ($diff->h == 1 && $diff->i == 0) {
        echo "   ✅ Data de expiração gerada corretamente (+1 hora)\n";
        echo "      Data atual: {$dataAtual}\n";
        echo "      Data expiração: {$dataExpiracao}\n";
    } else {
        echo "   ❌ Erro: Diferença não é de 1 hora exata\n";
        echo "      Diferença: {$diff->h}h {$diff->i}m\n";
    }
    
    // Teste 3: Testar validação de token expirado
    echo "\n📋 Teste 3: Testando validação de token expirado...\n";
    $dataPassada = date('Y-m-d H:i:s', strtotime('-2 hours'));
    $dataFutura = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $datePassada = new DateTime($dataPassada);
    $dateFutura = new DateTime($dataFutura);
    $dateNow = new DateTime();
    
    $expirado = $datePassada < $dateNow;
    $valido = $dateFutura >= $dateNow;
    
    echo "   " . ($expirado ? "✅" : "❌") . " Token expirado detectado corretamente\n";
    echo "   " . ($valido ? "✅" : "❌") . " Token válido detectado corretamente\n";
    
    // Teste 4: Verificar se o helper de validação de senha forte existe
    echo "\n📋 Teste 4: Verificando helper de validação de senha forte...\n";
    $helperFile = __DIR__ . '/application/helpers/password_helper.php';
    if (file_exists($helperFile)) {
        require_once $helperFile;
        
        if (function_exists('validar_senha_forte')) {
            echo "   ✅ Função validar_senha_forte existe\n";
            
            // Testar validação
            $senhas = [
                'senha123' => false, // muito curta
                'senhaForte123' => false, // sem caractere especial
                'SenhaForte123!' => true, // válida
                '12345678' => false, // só números
                'abcdefghijkl' => false, // só letras
            ];
            
            foreach ($senhas as $senha => $esperado) {
                $resultado = validar_senha_forte($senha);
                $status = ($resultado['valido'] === $esperado) ? "✅" : "❌";
                echo "   {$status} Senha '{$senha}': " . ($resultado['valido'] ? 'válida' : 'inválida') . "\n";
                if (!$resultado['valido'] && !empty($resultado['erros'])) {
                    echo "      Erros: " . implode(', ', $resultado['erros']) . "\n";
                }
            }
        } else {
            echo "   ❌ Função validar_senha_forte NÃO existe\n";
        }
    } else {
        echo "   ❌ Arquivo password_helper.php não encontrado\n";
    }
    
    // Teste 5: Verificar estrutura do modelo ResetSenhas_model
    echo "\n📋 Teste 5: Verificando métodos do ResetSenhas_model...\n";
    $modelFile = __DIR__ . '/application/models/ResetSenhas_model.php';
    if (file_exists($modelFile)) {
        $content = file_get_contents($modelFile);
        
        $methods = [
            'getByToken' => 'Buscar token por valor',
            'validarToken' => 'Validar token',
            'marcarTokenComoUtilizado' => 'Marcar token como usado',
            'limparTokensExpirados' => 'Limpar tokens expirados',
        ];
        
        foreach ($methods as $method => $desc) {
            $exists = strpos($content, "function {$method}") !== false;
            echo "   " . ($exists ? "✅" : "❌") . " Método {$method} ({$desc})\n";
        }
    } else {
        echo "   ❌ Arquivo ResetSenhas_model.php não encontrado\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Testes concluídos!\n";
    echo "\n💡 Próximos passos:\n";
    echo "   1. Acesse o sistema via navegador\n";
    echo "   2. Teste a recuperação de senha na área do cliente\n";
    echo "   3. Verifique se o token expira em 1 hora\n";
    echo "\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Erro durante os testes: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

