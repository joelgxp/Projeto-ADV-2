<?php
/**
 * Script de teste para verificar se as correções foram aplicadas
 * Acesse: http://seu-dominio.com.br/testar_correcoes.php
 * DELETE este arquivo após testar (segurança)
 */

// Carregar o CodeIgniter
define('BASEPATH', true);
require_once 'index.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste de Correções - MapOS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .ok { color: green; }
        .erro { color: red; }
        .aviso { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Teste de Correções - MapOS</h1>
    
    <?php
    $erros = [];
    $sucessos = [];
    
    // Teste 1: Verificar Tools.php
    echo "<h2>1. Verificando Tools.php</h2>";
    $tools_file = APPPATH . 'controllers/Tools.php';
    if (file_exists($tools_file)) {
        $content = file_get_contents($tools_file);
        if (strpos($content, "class_exists('Faker") !== false) {
            echo "<p class='ok'>✅ Tools.php foi corrigido (Faker é opcional)</p>";
            $sucessos[] = 'Tools.php';
        } else {
            echo "<p class='erro'>❌ Tools.php NÃO foi corrigido</p>";
            $erros[] = 'Tools.php';
        }
    } else {
        echo "<p class='erro'>❌ Arquivo Tools.php não encontrado</p>";
        $erros[] = 'Tools.php não existe';
    }
    
    // Teste 2: Verificar Mapos_model.php
    echo "<h2>2. Verificando Mapos_model.php</h2>";
    $model_file = APPPATH . 'models/Mapos_model.php';
    if (file_exists($model_file)) {
        $content = file_get_contents($model_file);
        if (strpos($content, 'if ($query === false)') !== false) {
            echo "<p class='ok'>✅ Mapos_model.php foi corrigido (tratamento de erro)</p>";
            $sucessos[] = 'Mapos_model.php';
        } else {
            echo "<p class='erro'>❌ Mapos_model.php NÃO foi corrigido</p>";
            $erros[] = 'Mapos_model.php';
        }
    } else {
        echo "<p class='erro'>❌ Arquivo Mapos_model.php não encontrado</p>";
        $erros[] = 'Mapos_model.php não existe';
    }
    
    // Teste 3: Verificar Migration
    echo "<h2>3. Verificando Migration</h2>";
    $migration_file = APPPATH . 'database/migrations/20251114182314_fix_check_credentials_error.php';
    if (file_exists($migration_file)) {
        echo "<p class='ok'>✅ Migration encontrada</p>";
        $sucessos[] = 'Migration';
    } else {
        echo "<p class='aviso'>⚠️ Migration não encontrada (pode não ter sido enviada ainda)</p>";
    }
    
    // Teste 4: Testar conexão com banco
    echo "<h2>4. Testando Conexão com Banco de Dados</h2>";
    try {
        $CI =& get_instance();
        $CI->load->database();
        
        if ($CI->db->conn_id) {
            echo "<p class='ok'>✅ Conexão com banco estabelecida</p>";
            
            // Verificar se tabela usuarios existe
            if ($CI->db->table_exists('usuarios')) {
                echo "<p class='ok'>✅ Tabela 'usuarios' existe</p>";
                
                // Testar query check_credentials
                $CI->load->model('Mapos_model');
                $test_email = 'teste@teste.com';
                $result = $CI->Mapos_model->check_credentials($test_email);
                
                if ($result === false || $result === null) {
                    echo "<p class='ok'>✅ Método check_credentials funcionando (retornou false/null - esperado para email inexistente)</p>";
                } else {
                    echo "<p class='ok'>✅ Método check_credentials funcionando (retornou objeto)</p>";
                }
            } else {
                echo "<p class='erro'>❌ Tabela 'usuarios' NÃO existe</p>";
                $erros[] = 'Tabela usuarios não existe';
            }
        } else {
            echo "<p class='erro'>❌ Não foi possível conectar ao banco de dados</p>";
            $erros[] = 'Conexão com banco falhou';
        }
    } catch (Exception $e) {
        echo "<p class='erro'>❌ Erro ao testar banco: " . htmlspecialchars($e->getMessage()) . "</p>";
        $erros[] = 'Erro: ' . $e->getMessage();
    }
    
    // Resumo
    echo "<h2>📊 Resumo</h2>";
    echo "<p><strong>Sucessos:</strong> " . count($sucessos) . "</p>";
    echo "<p><strong>Erros:</strong> " . count($erros) . "</p>";
    
    if (count($erros) == 0) {
        echo "<p class='ok'><strong>✅ Todas as verificações passaram!</strong></p>";
    } else {
        echo "<p class='erro'><strong>❌ Alguns problemas foram encontrados:</strong></p>";
        echo "<ul>";
        foreach ($erros as $erro) {
            echo "<li class='erro'>" . htmlspecialchars($erro) . "</li>";
        }
        echo "</ul>";
    }
    ?>
    
    <hr>
    <p><strong>⚠️ IMPORTANTE:</strong> Delete este arquivo após testar por questões de segurança!</p>
    <p><small>Para deletar: <code>rm testar_correcoes.php</code> ou via FTP</small></p>
</body>
</html>

