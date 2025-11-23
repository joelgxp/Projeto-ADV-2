<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Email</title>
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
        .email-display {
            font-size: 18px;
            font-weight: bold;
            color: #436eee;
            margin: 10px 0;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #436eee;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo isset($emitente->nome) ? $emitente->nome : $this->config->item('app_name'); ?></h2>
        <p>Recuperação de Email Cadastrado</p>
    </div>
    
    <div class="content">
        <p>Olá <strong><?php echo isset($cliente->nomeCliente) ? $cliente->nomeCliente : 'Cliente'; ?></strong>,</p>
        
        <p>Você solicitou a recuperação do seu email cadastrado no sistema.</p>
        
        <div class="info-box">
            <p style="margin: 0 0 10px 0;">Seu email cadastrado é:</p>
            <div class="email-display"><?php echo isset($email_cadastrado) ? $email_cadastrado : 'N/A'; ?></div>
        </div>
        
        <p>Agora que você tem seu email, você pode:</p>
        <ul>
            <li>Acessar sua conta usando este email</li>
            <li>Recuperar sua senha caso tenha esquecido</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="<?php echo base_url(); ?>index.php/mine" class="button">Acessar Sistema</a>
        </div>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            <strong>Importante:</strong> Se você não solicitou esta recuperação, ignore este email.
        </p>
    </div>
    
    <div class="footer">
        <p><?php echo date('Y'); ?> &copy; <?php echo isset($emitente->nome) ? $emitente->nome : $this->config->item('app_name'); ?></p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>

