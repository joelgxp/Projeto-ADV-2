<!DOCTYPE html>
<html>

<head>
    <title>Adv - Relatório de Prazos Vencidos</title>
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
                                    <th style="font-size: 15px">Processo</th>
                                    <th style="font-size: 15px">Tipo</th>
                                    <th style="font-size: 15px">Descrição</th>
                                    <th style="font-size: 15px">Data Prazo</th>
                                    <th style="font-size: 15px">Data Vencimento</th>
                                    <th style="font-size: 15px">Status</th>
                                    <th style="font-size: 15px">Prioridade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($prazos) && !empty($prazos)) : ?>
                                    <?php foreach ($prazos as $pz) : ?>
                                        <?php 
                                        $dataPrazo = isset($pz->dataPrazo) ? date('d/m/Y', strtotime($pz->dataPrazo)) : '-';
                                        $dataVenc = isset($pz->dataVencimento) ? date('d/m/Y', strtotime($pz->dataVencimento)) : '-';
                                        ?>
                                        <tr>
                                            <td><?= isset($pz->numeroProcesso) ? $pz->numeroProcesso : '-' ?></td>
                                            <td><?= isset($pz->tipo) ? $pz->tipo : '-' ?></td>
                                            <td><?= isset($pz->descricao) ? $pz->descricao : '-' ?></td>
                                            <td align="center"><?= $dataPrazo ?></td>
                                            <td align="center"><?= $dataVenc ?></td>
                                            <td><?= isset($pz->status) ? ucfirst($pz->status) : '-' ?></td>
                                            <td><?= isset($pz->prioridade) ? ucfirst($pz->prioridade) : '-' ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7" align="center">Nenhum prazo vencido encontrado</td>
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

