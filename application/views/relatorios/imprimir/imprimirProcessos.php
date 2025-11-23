<!DOCTYPE html>
<html>

<head>
    <title>Adv - Relatório de Processos</title>
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
                                    <th style="font-size: 15px">Nº Processo</th>
                                    <th style="font-size: 15px">Classe</th>
                                    <th style="font-size: 15px">Assunto</th>
                                    <th style="font-size: 15px">Tipo</th>
                                    <th style="font-size: 15px">Status</th>
                                    <th style="font-size: 15px">Vara</th>
                                    <th style="font-size: 15px">Data Distribuição</th>
                                    <th style="font-size: 15px">Valor Causa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($processos) && !empty($processos)) : ?>
                                    <?php foreach ($processos as $p) : ?>
                                        <?php 
                                        $dataDist = isset($p['dataDistribuicao']) ? date('d/m/Y', strtotime($p['dataDistribuicao'])) : '-';
                                        $valorCausa = isset($p['valorCausa']) && $p['valorCausa'] > 0 ? 'R$ ' . number_format($p['valorCausa'], 2, ',', '.') : '-';
                                        ?>
                                        <tr>
                                            <td><?= isset($p['numeroProcesso']) ? $p['numeroProcesso'] : '-' ?></td>
                                            <td><?= isset($p['classe']) ? $p['classe'] : '-' ?></td>
                                            <td><?= isset($p['assunto']) ? $p['assunto'] : '-' ?></td>
                                            <td><?= isset($p['tipo_processo']) ? ucfirst($p['tipo_processo']) : '-' ?></td>
                                            <td><?= isset($p['status']) ? ucfirst(str_replace('_', ' ', $p['status'])) : '-' ?></td>
                                            <td><?= isset($p['vara']) ? $p['vara'] : '-' ?></td>
                                            <td align="center"><?= $dataDist ?></td>
                                            <td align="right"><?= $valorCausa ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="8" align="center">Nenhum processo encontrado</td>
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

