<!DOCTYPE html>
<html>

<head>
    <title>Adv - Relatório de Honorários</title>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/main.css" />
</head>

<body style="background-color: transparent">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <?= $topo ?>
                    <div class="widget-title">
                        <h4 style="text-align: center; font-size: 1.1em; padding: 5px;">
                            <?= ucfirst($title) ?>
                        </h4>
                    </div>
                    <div class="widget-content nopadding tab-content">
                        <table width="100%" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="font-size: 15px">Descrição</th>
                                    <th style="font-size: 15px">Cliente</th>
                                    <th style="font-size: 15px">Valor</th>
                                    <th style="font-size: 15px">Data Vencimento</th>
                                    <th style="font-size: 15px">Data Pagamento</th>
                                    <th style="font-size: 15px">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($lancamentos) && !empty($lancamentos)) : ?>
                                    <?php 
                                    $total = 0;
                                    foreach ($lancamentos as $l) : 
                                        $valor = isset($l['valor']) ? floatval($l['valor']) : 0;
                                        $total += $valor;
                                        $dataVenc = isset($l['data_vencimento']) ? date('d/m/Y', strtotime($l['data_vencimento'])) : '-';
                                        $dataPag = isset($l['data_pagamento']) && $l['data_pagamento'] ? date('d/m/Y', strtotime($l['data_pagamento'])) : '-';
                                        $status = isset($l['baixado']) && $l['baixado'] == 1 ? 'Pago' : 'Pendente';
                                    ?>
                                        <tr>
                                            <td><?= isset($l['descricao']) ? $l['descricao'] : '-' ?></td>
                                            <td><?= isset($l['cliente_fornecedor']) ? $l['cliente_fornecedor'] : '-' ?></td>
                                            <td align="right">R$ <?= number_format($valor, 2, ',', '.') ?></td>
                                            <td align="center"><?= $dataVenc ?></td>
                                            <td align="center"><?= $dataPag ?></td>
                                            <td><?= $status ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    <tr style="font-weight: bold;">
                                        <td colspan="2" align="right">Total:</td>
                                        <td align="right">R$ <?= number_format($total, 2, ',', '.') ?></td>
                                        <td colspan="3"></td>
                                    </tr>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" align="center">Nenhum lançamento de honorário encontrado</td>
                                    </tr>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <h5 style="text-align: right; font-size: 0.8em; padding: 5px;">Data do Relatório:
                    <?php echo date('d/m/Y'); ?>
                </h5>
            </div>
        </div>
    </div>
</body>

</html>

