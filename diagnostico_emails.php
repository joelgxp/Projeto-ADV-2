<?php
/**
 * Script de diagnóstico de emails
 */

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

$hostname = $_ENV['DB_HOSTNAME'] ?? 'localhost';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? '';

$conn = @mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}

echo "========================================\n";
echo "DIAGNÓSTICO DE EMAILS - RESET DE SENHA\n";
echo "========================================\n\n";

// Verificar emails de reset de senha
$result = mysqli_query($conn, "
    SELECT * FROM email_queue 
    WHERE subject LIKE '%Recuperar%' OR subject LIKE '%reset%' OR subject LIKE '%senha%'
    ORDER BY id DESC LIMIT 5
");

echo "📧 Emails de reset de senha:\n";
echo str_repeat("=", 80) . "\n";

$encontrados = 0;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $encontrados++;
        echo "ID: {$row['id']}\n";
        echo "Para: {$row['to']}\n";
        echo "Assunto: {$row['subject']}\n";
        echo "Status: {$row['status']}\n";
        echo "Criado: {$row['created_at']}\n";
        if (!empty($row['last_attempt'])) {
            echo "Última tentativa: {$row['last_attempt']}\n";
        }
        echo str_repeat("-", 80) . "\n";
    }
} else {
    echo "❌ Nenhum email de reset de senha encontrado na fila!\n";
    echo "Isso significa que você ainda não tentou solicitar reset de senha.\n\n";
}

// Verificar emails pendentes
$pendentes = mysqli_query($conn, "SELECT COUNT(*) as total FROM email_queue WHERE status = 'pending'");
$row = mysqli_fetch_assoc($pendentes);
$total_pendentes = $row['total'];

if ($total_pendentes > 0) {
    echo "\n⚠️ IMPORTANTE: Há $total_pendentes email(s) PENDENTE(S) aguardando processamento!\n\n";
    echo "Para processar os emails, você precisa:\n";
    echo "1. Fazer login como administrador\n";
    echo "2. Acessar: Configurações > Emails\n";
    echo "3. Clicar no botão 'Processar E-mails'\n\n";
} else {
    echo "\n✅ Não há emails pendentes.\n";
}

// Verificar configurações de email
echo "\n========================================\n";
echo "CONFIGURAÇÕES DE EMAIL\n";
echo "========================================\n\n";

$smtp_host = $_ENV['EMAIL_SMTP_HOST'] ?? 'não configurado';
$smtp_user = $_ENV['EMAIL_SMTP_USER'] ?? 'não configurado';
$smtp_pass = !empty($_ENV['EMAIL_SMTP_PASS']) ? 'configurado' : 'não configurado';

echo "SMTP Host: $smtp_host\n";
echo "SMTP User: $smtp_user\n";
echo "SMTP Pass: $smtp_pass\n\n";

if ($smtp_host === 'não configurado' || $smtp_user === 'não configurado' || $smtp_pass === 'não configurado') {
    echo "❌ PROBLEMA ENCONTRADO: Configurações de email não estão completas!\n\n";
    echo "Para configurar, edite o arquivo application/.env e defina:\n";
    echo "- EMAIL_SMTP_HOST (ex: smtp.gmail.com)\n";
    echo "- EMAIL_SMTP_USER (seu email)\n";
    echo "- EMAIL_SMTP_PASS (sua senha ou senha de app)\n";
    echo "- EMAIL_SMTP_PORT (587 para TLS ou 465 para SSL)\n\n";
}

echo "========================================\n";
echo "PRÓXIMOS PASSOS:\n";
echo "========================================\n\n";

if ($encontrados == 0) {
    echo "1. Teste novamente o reset de senha:\n";
    echo "   http://localhost/mapos/index.php/mine/resetarSenha\n";
    echo "   Digite o email: joelvieirasouza@gmail.com\n\n";
}

echo "2. Se houver emails pendentes, processe-os:\n";
echo "   http://localhost/mapos/index.php/adv/emails\n";
echo "   (Clique em 'Processar E-mails')\n\n";

echo "3. Verifique se as configurações SMTP estão corretas no .env\n\n";

mysqli_close($conn);

