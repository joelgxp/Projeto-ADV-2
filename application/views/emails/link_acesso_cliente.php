<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link de Acesso ao Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #436eee;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-box {
            background-color: #fff;
            border: 2px solid #436eee;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .link-display {
            font-size: 14px;
            color: #436eee;
            word-break: break-all;
            margin: 10px 0;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 3px;
        }
        .button {
            display: inline-block;
            background-color: #436eee;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #2d4fc7;
        }
        .expiration-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .instructions {
            background-color: #e7f3ff;
            border-left: 4px solid #436eee;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo isset($emitente->nome) ? $emitente->nome : $this->config->item('app_name'); ?></h2>
        <p>Link de Acesso ao Portal do Cliente</p>
    </div>
    
    <div class="content">
        <p>Olá <strong><?php echo isset($cliente->nomeCliente) ? htmlspecialchars($cliente->nomeCliente) : 'Cliente'; ?></strong>,</p>
        
        <p>Foi gerado um link de acesso único para você acessar o portal do cliente. Este link é válido por <strong>365 dias</strong> e pode ser renovado quando necessário.</p>
        
        <div class="info-box">
            <p style="margin: 0 0 15px 0; font-weight: bold;">Clique no botão abaixo para acessar o portal:</p>
            <a href="<?php echo isset($link_acesso) ? $link_acesso : base_url('index.php/mine'); ?>" class="button">Acessar Portal do Cliente</a>
            
            <p style="margin-top: 20px; font-size: 12px; color: #666;">Ou copie e cole este link no seu navegador:</p>
            <div class="link-display">
                <?php echo isset($link_acesso) ? htmlspecialchars($link_acesso) : 'Link não disponível'; ?>
            </div>
        </div>
        
        <div class="expiration-info">
            <p style="margin: 0;"><strong>⏰ Data de Expiração:</strong> <?php echo isset($data_expiracao_formatada) ? $data_expiracao_formatada : 'Não definida'; ?></p>
            <p style="margin: 5px 0 0 0; font-size: 12px;">Este link permanece válido por 365 dias. Após esse período, será necessário gerar um novo link.</p>
        </div>
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #436eee;">📋 Instruções de Uso:</h3>
            <ol style="margin: 0; padding-left: 20px;">
                <li>Clique no botão "Acessar Portal do Cliente" acima ou copie o link</li>
                <li>O link irá redirecionar você automaticamente para o portal</li>
                <li>Você poderá visualizar seus processos, prazos e documentos</li>
                <li>Mantenha este link seguro e não compartilhe com terceiros</li>
            </ol>
        </div>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            <strong>🔒 Segurança:</strong> Este link é único e pessoal. Se você suspeitar que ele foi comprometido, entre em contato conosco imediatamente.
        </p>
        
        <p style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>❓ Precisa de ajuda?</strong> Entre em contato com nosso escritório através dos canais de atendimento.
        </p>
    </div>
    
    <div class="footer">
        <p><?php echo date('Y'); ?> &copy; <?php echo isset($emitente->nome) ? htmlspecialchars($emitente->nome) : $this->config->item('app_name'); ?></p>
        <p>Este é um email automático, por favor não responda.</p>
        <?php if (isset($emitente->telefone) && $emitente->telefone) : ?>
        <p>Telefone: <?php echo htmlspecialchars($emitente->telefone); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>

