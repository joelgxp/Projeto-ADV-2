<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <?= $topo ?>
    
    <h2 style="text-align: center;"><?= $title ?></h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data Vencimento</th>
                <th>Data Pagamento</th>
                <th>Status</th>
                <th>Cliente</th>
                <th>Forma Pagamento</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            if ($lancamentos) {
                foreach ($lancamentos as $lancamento) {
                    $total += floatval($lancamento['valor']);
                    ?>
                    <tr>
                        <td><?= $lancamento['idLancamentos'] ?></td>
                        <td><?= htmlspecialchars($lancamento['descricao']) ?></td>
                        <td>R$ <?= number_format($lancamento['valor'], 2, ',', '.') ?></td>
                        <td><?= $lancamento['data_vencimento'] ? date('d/m/Y', strtotime($lancamento['data_vencimento'])) : '-' ?></td>
                        <td><?= $lancamento['data_pagamento'] ? date('d/m/Y', strtotime($lancamento['data_pagamento'])) : '-' ?></td>
                        <td><?= $lancamento['baixado'] == 1 ? 'Pago' : 'Pendente' ?></td>
                        <td><?= htmlspecialchars($lancamento['cliente_fornecedor']) ?></td>
                        <td><?= htmlspecialchars($lancamento['forma_pgto'] ?? '-') ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Nenhuma custa encontrada no período.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="total">Total:</td>
                <td class="total">R$ <?= number_format($total, 2, ',', '.') ?></td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

