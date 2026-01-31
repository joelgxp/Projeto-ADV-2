<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #4CAF50;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Bem-vindo ao Sistema de Gestão Jurídica!</h2>
        </div>
        
        <div class="content">
            <p>Olá <?= isset($cliente->nomeCliente) ? htmlspecialchars($cliente->nomeCliente) : 'Cliente' ?>,</p>
            
            <p>É um prazer tê-lo(a) como nosso cliente! Seu cadastro foi realizado com sucesso em nosso sistema.</p>
            
            <div class="info-box">
                <strong>Seus dados de acesso:</strong><br>
                <?php if (isset($cliente->email)): ?>
                <strong>E-mail:</strong> <?= htmlspecialchars($cliente->email) ?><br>
                <?php endif; ?>
                <?php if (isset($link_acesso)): ?>
                <strong>Link de Acesso:</strong> <a href="<?= $link_acesso ?>">Acessar Portal do Cliente</a>
                <?php endif; ?>
            </div>
            
            <p>No portal do cliente, você poderá:</p>
            <ul>
                <li>Visualizar seus processos e andamentos</li>
                <li>Acompanhar prazos importantes</li>
                <li>Consultar faturas e pagamentos</li>
                <li>Comunicar-se conosco através de tickets</li>
            </ul>
            
            <?php if (isset($link_acesso)): ?>
            <p style="text-align: center;">
                <a href="<?= $link_acesso ?>" class="btn">
                    Acessar Portal do Cliente
                </a>
            </p>
            <?php endif; ?>
            
            <p>Se tiver alguma dúvida, não hesite em entrar em contato conosco.</p>
            
            <p>Atenciosamente,<br>
            <strong>Equipe Jurídica</strong></p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>Sistema de Gestão Jurídica - <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>

