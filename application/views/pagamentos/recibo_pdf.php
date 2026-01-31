<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?= $pagamento->id ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header h2 { margin: 10px 0; font-size: 18px; color: #666; }
        .info-box { margin: 20px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 8px; }
        .info-box td:first-child { font-weight: bold; width: 200px; }
        .amount { font-size: 24px; font-weight: bold; text-align: center; margin: 30px 0; padding: 20px; background: #f5f5f5; }
        .signature { margin-top: 80px; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 60px auto 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($emitente) { ?>
            <h1><?= htmlspecialchars($emitente->nome) ?></h1>
            <?php if ($emitente->documento) { ?><p>CNPJ: <?= htmlspecialchars($emitente->documento) ?></p><?php } ?>
        <?php } else { ?>
            <h1>Recibo de Pagamento</h1>
        <?php } ?>
        <h2>RECIBO</h2>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td>Recebi de:</td>
                <td><?= htmlspecialchars($fatura->nomeCliente) ?></td>
            </tr>
            <tr>
                <td>CPF/CNPJ:</td>
                <td><?= htmlspecialchars($fatura->cpf_cnpj) ?></td>
            </tr>
            <tr>
                <td>Referente a:</td>
                <td>Pagamento da Fatura <?= $fatura->numero ?></td>
            </tr>
            <tr>
                <td>Data do Pagamento:</td>
                <td><?= date('d/m/Y', strtotime($pagamento->data_pagamento)) ?></td>
            </tr>
            <tr>
                <td>Método de Pagamento:</td>
                <td><?= ucfirst($pagamento->metodo_pagamento) ?></td>
            </tr>
            <?php if ($pagamento->observacoes) { ?>
            <tr>
                <td>Observações:</td>
                <td><?= htmlspecialchars($pagamento->observacoes) ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <div class="amount">
        Valor: R$ <?= number_format($pagamento->valor, 2, ',', '.') ?>
    </div>

    <div class="signature">
        <div style="text-align: center; margin-top: 40px;">
            <div class="signature-line"></div>
            <p style="margin-top: 5px;"><?= $emitente ? htmlspecialchars($emitente->nome) : 'Assinatura' ?></p>
        </div>
    </div>

    <div class="footer">
        <p>Recibo gerado em <?= date('d/m/Y H:i:s') ?> - ID: <?= $pagamento->id ?></p>
    </div>
</body>
</html>

