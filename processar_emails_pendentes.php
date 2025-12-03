<?php
/**
 * Script para processar emails pendentes na fila
 * 
 * IMPORTANTE: Este script processa emails pendentes manualmente
 */

// Carregar CodeIgniter
define('BASEPATH', true);
define('ENVIRONMENT', 'development');

require_once 'system/core/CodeIgniter.php';

// Não é necessário mais, pois o CodeIgniter já foi carregado
// Mas vamos verificar se há emails pendentes primeiro
$CI =& get_instance();
$CI->load->database();

// Verificar emails pendentes
$pendentes = $CI->db->where('status', 'pending')->get('email_queue')->result();

echo "========================================\n";
echo "PROCESSAR EMAILS PENDENTES\n";
echo "========================================\n\n";

if (empty($pendentes)) {
    echo "✅ Nenhum email pendente na fila.\n\n";
    
    // Verificar últimos emails
    $ultimos = $CI->db->order_by('id', 'DESC')->limit(5)->get('email_queue')->result();
    if (!empty($ultimos)) {
        echo "Últimos 5 emails:\n";
        foreach ($ultimos as $email) {
            echo "- ID: {$email->id} | Para: {$email->to} | Status: {$email->status} | Criado: {$email->created_at}\n";
        }
    }
} else {
    echo "📧 Encontrados " . count($pendentes) . " email(s) pendente(s).\n\n";
    echo "Listando emails pendentes:\n";
    foreach ($pendentes as $email) {
        echo "- ID: {$email->id} | Para: {$email->to} | Assunto: {$email->subject}\n";
    }
    
    echo "\n⚠️ Para processar os emails, você precisa:\n";
    echo "1. Acessar a área administrativa\n";
    echo "2. Ir em: Configurações > Emails\n";
    echo "3. Clicar no botão 'Processar E-mails'\n\n";
    echo "OU configurar um cron job para processar automaticamente.\n";
}

echo "\n========================================\n";
echo "NOTA: Os emails são processados através da biblioteca MY_Email\n";
echo "que usa as configurações SMTP do arquivo .env\n";
echo "Verifique se as configurações de email estão corretas!\n";

