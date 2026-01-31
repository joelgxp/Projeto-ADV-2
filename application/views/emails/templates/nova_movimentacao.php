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
            background-color: #2196F3;
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
            border-left: 4px solid #2196F3;
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
            background-color: #2196F3;
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
            <h2>📋 Nova Movimentação Processual</h2>
        </div>
        
        <div class="content">
            <p>Olá <?= isset($destinatario->nome) ? htmlspecialchars($destinatario->nome) : (isset($destinatario->nomeCliente) ? htmlspecialchars($destinatario->nomeCliente) : 'Usuário') ?>,</p>
            
            <p>Uma nova movimentação foi registrada no processo:</p>
            
            <div class="info-box">
                <strong>Processo:</strong> <?= isset($processo->numeroProcesso) ? htmlspecialchars($processo->numeroProcesso) : 'N/A' ?><br>
                <?php if (isset($processo->classe)): ?>
                <strong>Classe:</strong> <?= htmlspecialchars($processo->classe) ?><br>
                <?php endif; ?>
                <?php if (isset($processo->assunto)): ?>
                <strong>Assunto:</strong> <?= htmlspecialchars($processo->assunto) ?><br>
                <?php endif; ?>
                <?php if (isset($movimentacao->data)): ?>
                <strong>Data da Movimentação:</strong> <?= date('d/m/Y', strtotime($movimentacao->data)) ?><br>
                <?php endif; ?>
                <?php if (isset($movimentacao->descricao)): ?>
                <strong>Descrição:</strong><br>
                <?= nl2br(htmlspecialchars($movimentacao->descricao)) ?>
                <?php endif; ?>
            </div>
            
            <?php if (isset($url_processo)): ?>
            <p style="text-align: center;">
                <a href="<?= $url_processo ?>" class="btn">
                    Ver Detalhes do Processo
                </a>
            </p>
            <?php endif; ?>
            
            <p><strong>Atenção:</strong> Este é um alerta automático do sistema. Por favor, verifique a movimentação e tome as providências necessárias.</p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>Sistema de Gestão Jurídica - <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>

