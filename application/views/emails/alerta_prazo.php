<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f44336;
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
            border-left: 4px solid #f44336;
        }
        .info-box strong {
            color: #f44336;
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
            background-color: #f44336;
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
            <h2>⚠️ Alerta de Prazo Processual</h2>
        </div>
        
        <div class="content">
            <p>Olá <?= isset($usuario->nome) ? $usuario->nome : 'Advogado' ?>,</p>
            
            <p><?= $descricao ?></p>
            
            <div class="info-box">
                <strong>Tipo de Prazo:</strong> <?= isset($prazo->tipo) ? $prazo->tipo : 'N/A' ?><br>
                <strong>Descrição:</strong> <?= isset($prazo->descricao) ? $prazo->descricao : 'N/A' ?><br>
                <strong>Data de Vencimento:</strong> <?= $data_vencimento ?><br>
                <?php if ($processo && isset($processo->numeroProcesso)): ?>
                <strong>Processo:</strong> <?= $processo->numeroProcesso ?><br>
                <?php endif; ?>
                <?php if (isset($prazo->prioridade)): ?>
                <strong>Prioridade:</strong> <?= ucfirst($prazo->prioridade) ?><br>
                <?php endif; ?>
            </div>
            
            <p><strong>Atenção:</strong> Este é um alerta automático do sistema. Por favor, verifique o prazo e tome as providências necessárias.</p>
            
            <p>
                <a href="<?= base_url() ?>index.php/prazos/visualizar/<?= $prazo->idPrazos ?>" class="btn">
                    Ver Detalhes do Prazo
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
            <p>Sistema de Gestão Jurídica - <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>

