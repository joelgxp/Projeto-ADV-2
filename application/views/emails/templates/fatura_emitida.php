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
            background-color: #FF9800;
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
            border-left: 4px solid #FF9800;
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
            background-color: #FF9800;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .valor {
            font-size: 24px;
            font-weight: bold;
            color: #FF9800;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>💰 Fatura Emitida</h2>
        </div>
        
        <div class="content">
            <p>Olá <?= isset($cliente->nomeCliente) ? htmlspecialchars($cliente->nomeCliente) : 'Cliente' ?>,</p>
            
            <p>Uma nova fatura foi emitida para você:</p>
            
            <div class="info-box">
                <strong>Número da Fatura:</strong> <?= isset($fatura->numero) ? htmlspecialchars($fatura->numero) : 'N/A' ?><br>
                <?php if (isset($fatura->data_emissao)): ?>
                <strong>Data de Emissão:</strong> <?= date('d/m/Y', strtotime($fatura->data_emissao)) ?><br>
                <?php endif; ?>
                <?php if (isset($fatura->data_vencimento)): ?>
                <strong>Data de Vencimento:</strong> <?= date('d/m/Y', strtotime($fatura->data_vencimento)) ?><br>
                <?php endif; ?>
                <?php if (isset($fatura->valor_total)): ?>
                <strong>Valor Total:</strong> <span class="valor">R$ <?= number_format($fatura->valor_total, 2, ',', '.') ?></span><br>
                <?php endif; ?>
                <?php if (isset($fatura->status)): ?>
                <strong>Status:</strong> <?= htmlspecialchars(ucfirst($fatura->status)) ?><br>
                <?php endif; ?>
            </div>
            
            <?php if (isset($url_fatura)): ?>
            <p style="text-align: center;">
                <a href="<?= $url_fatura ?>" class="btn">
                    Ver Detalhes da Fatura
                </a>
            </p>
            <?php endif; ?>
            
            <p>Por favor, verifique os detalhes da fatura e realize o pagamento até a data de vencimento.</p>
            
            <p>Se tiver alguma dúvida sobre esta fatura, entre em contato conosco.</p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>Sistema de Gestão Jurídica - <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>

