<?php
/**
 * Script para simular e verificar o fluxo de reset de senha
 */

echo "========================================\n";
echo "GUIA: Como testar reset de senha\n";
echo "========================================\n\n";

echo "PASSO 1: Solicite o reset novamente\n";
echo "----------------------------------------\n";
echo "1. Acesse: http://localhost/mapos/index.php/mine/resetarSenha\n";
echo "2. Digite: joelvieirasouza@gmail.com\n";
echo "3. Clique em 'Enviar'\n\n";

echo "PASSO 2: Verifique se o email foi adicionado à fila\n";
echo "----------------------------------------\n";
echo "Execute: php verificar_fila_emails.php\n\n";

echo "PASSO 3: Processe os emails\n";
echo "----------------------------------------\n";
echo "1. Faça login como administrador\n";
echo "2. Acesse: http://localhost/mapos/index.php/adv/emails\n";
echo "3. Clique no botão 'Processar E-mails'\n\n";

echo "PASSO 4: Verifique os logs\n";
echo "----------------------------------------\n";
echo "Verifique o arquivo: application/logs/log-" . date('Y-m-d') . ".php\n";
echo "Procure por linhas com:\n";
echo "  - '=== INÍCIO gerarTokenResetarSenha ==='\n";
echo "  - 'Email de recuperação adicionado à fila'\n";
echo "  - 'Emitente:'\n\n";

echo "========================================\n";
echo "NOTA: Os emails ficam na fila até serem processados.\n";
echo "Eles NÃO são enviados automaticamente!\n";
echo "========================================\n";

