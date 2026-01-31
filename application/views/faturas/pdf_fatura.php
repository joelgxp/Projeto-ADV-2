<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fatura <?= $fatura->numero ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .info-box { margin-bottom: 20px; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 5px; }
        .info-box td:first-child { font-weight: bold; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($emitente) { ?>
            <h1><?= htmlspecialchars($emitente->nome) ?></h1>
            <?php if ($emitente->documento) { ?><p>CNPJ: <?= htmlspecialchars($emitente->documento) ?></p><?php } ?>
            <?php if ($emitente->telefone) { ?><p>Telefone: <?= htmlspecialchars($emitente->telefone) ?></p><?php } ?>
            <?php if ($emitente->email) { ?><p>E-mail: <?= htmlspecialchars($emitente->email) ?></p><?php } ?>
        <?php } else { ?>
            <h1>Fatura</h1>
        <?php } ?>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td>Número da Fatura:</td>
                <td><?= $fatura->numero ?></td>
            </tr>
            <tr>
                <td>Cliente:</td>
                <td><?= htmlspecialchars($fatura->nomeCliente) ?></td>
            </tr>
            <tr>
                <td>Data de Emissão:</td>
                <td><?= date('d/m/Y', strtotime($fatura->data_emissao)) ?></td>
            </tr>
            <tr>
                <td>Data de Vencimento:</td>
                <td><?= date('d/m/Y', strtotime($fatura->data_vencimento)) ?></td>
            </tr>
            <tr>
                <td>Status:</td>
                <td><?= ucfirst(str_replace('_', ' ', $fatura->status)) ?></td>
            </tr>
        </table>
    </div>

    <h3>Itens da Fatura</h3>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Processo</th>
                <th class="text-right">Valor Unitário</th>
                <th class="text-right">Quantidade</th>
                <th class="text-right">IPI %</th>
                <th class="text-right">ISS %</th>
                <th class="text-right">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itens) { ?>
                <?php foreach ($itens as $item) { ?>
                    <tr>
                        <td><?= htmlspecialchars($item->descricao) ?></td>
                        <td><?= $item->numeroProcesso ?: '-' ?></td>
                        <td class="text-right">R$ <?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($item->quantidade, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($item->ipi, 2, ',', '.') ?>%</td>
                        <td class="text-right"><?= number_format($item->iss, 2, ',', '.') ?>%</td>
                        <td class="text-right">R$ <?= number_format($item->valor_total, 2, ',', '.') ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-right">R$ <?= number_format($fatura->valor_total, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right">Valor Pago:</td>
                <td class="text-right">R$ <?= number_format($fatura->valor_pago, 2, ',', '.') ?></td>
            </tr>
            <tr class="total">
                <td colspan="6" class="text-right"><strong>Saldo Restante:</strong></td>
                <td class="text-right">R$ <?= number_format($fatura->saldo_restante, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if ($fatura->observacoes) { ?>
        <div style="margin-top: 20px;">
            <strong>Observações:</strong><br>
            <?= nl2br(htmlspecialchars($fatura->observacoes)) ?>
        </div>
    <?php } ?>

    <?php if ($pagamentos) { ?>
        <h3>Pagamentos</h3>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Método</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pag) { ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($pag->data_pagamento)) ?></td>
                        <td class="text-right">R$ <?= number_format($pag->valor, 2, ',', '.') ?></td>
                        <td><?= ucfirst($pag->metodo_pagamento) ?></td>
                        <td><?= htmlspecialchars($pag->observacoes) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <div class="footer">
        <p>Gerado em <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html>

