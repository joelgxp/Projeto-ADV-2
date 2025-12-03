<?php
/**
 * Script para testar envio de email diretamente
 */

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . '/application/.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

echo "========================================\n";
echo "TESTE DE ENVIO DE EMAIL\n";
echo "========================================\n\n";

// Carregar CodeIgniter
define('BASEPATH', true);
define('ENVIRONMENT', 'development');

// Carregar apenas o que é necessário
require_once 'system/core/CodeIgniter.php';

$CI =& get_instance();
$CI->load->library('email');

// Configurações
$smtp_host = $_ENV['EMAIL_SMTP_HOST'] ?? 'smtp.gmail.com';
$smtp_user = $_ENV['EMAIL_SMTP_USER'] ?? '';
$smtp_pass = $_ENV['EMAIL_SMTP_PASS'] ?? '';
$smtp_port = $_ENV['EMAIL_SMTP_PORT'] ?? 587;
$smtp_crypto = $_ENV['EMAIL_SMTP_CRYPTO'] ?? 'tls';

echo "Configurações SMTP:\n";
echo "- Host: $smtp_host\n";
echo "- Port: $smtp_port\n";
echo "- Crypto: $smtp_crypto\n";
echo "- User: $smtp_user\n";
echo "- Pass: " . (!empty($smtp_pass) ? 'configurado' : 'não configurado') . "\n\n";

// Configurar email
$config = [
    'protocol' => 'smtp',
    'smtp_host' => $smtp_host,
    'smtp_port' => $smtp_port,
    'smtp_crypto' => $smtp_crypto,
    'smtp_user' => $smtp_user,
    'smtp_pass' => $smtp_pass,
    'mailtype' => 'html',
    'charset' => 'utf-8',
    'newline' => "\r\n",
];

$CI->email->initialize($config);

// Tentar enviar email de teste
$email_teste = 'joelvieirasouza@gmail.com';
$CI->email->from($smtp_user, 'Teste de Email');
$CI->email->to($email_teste);
$CI->email->subject('Teste de Envio - Sistema');
$CI->email->message('<html><body><h1>Teste de Email</h1><p>Este é um email de teste enviado pelo sistema.</p></body></html>');

echo "Tentando enviar email para: $email_teste\n\n";

if ($CI->email->send()) {
    echo "✅ Email enviado com sucesso!\n\n";
    echo "Verifique:\n";
    echo "1. A caixa de entrada de $email_teste\n";
    echo "2. A pasta de SPAM/Lixo Eletrônico\n";
    echo "3. Aguarde alguns minutos (pode haver delay)\n";
} else {
    echo "❌ ERRO ao enviar email!\n\n";
    echo "Detalhes do erro:\n";
    echo $CI->email->print_debugger() . "\n\n";
    
    echo "Possíveis causas:\n";
    echo "1. Credenciais SMTP incorretas\n";
    echo "2. Servidor SMTP bloqueando conexão\n";
    echo "3. Firewall bloqueando porta SMTP\n";
    echo "4. Autenticação de dois fatores ativada (precisa usar senha de app)\n";
    echo "5. Porta/crypto incorretos\n";
}

echo "\n========================================\n";

