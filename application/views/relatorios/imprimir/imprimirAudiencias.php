<!DOCTYPE html>
<html>

<head>
    <title>Adv - Relatório de Audiências</title>
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
                                    <th style="font-size: 15px">Data/Hora</th>
                                    <th style="font-size: 15px">Local</th>
                                    <th style="font-size: 15px">Status</th>
                                    <th style="font-size: 15px">Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($audiencias) && !empty($audiencias)) : ?>
                                    <?php foreach ($audiencias as $a) : ?>
                                        <?php 
                                        $dataHora = isset($a['dataHora']) ? date('d/m/Y H:i', strtotime($a['dataHora'])) : '-';
                                        ?>
                                        <tr>
                                            <td><?= isset($a['numeroProcesso']) ? $a['numeroProcesso'] : '-' ?></td>
                                            <td><?= isset($a['tipo']) ? $a['tipo'] : '-' ?></td>
                                            <td align="center"><?= $dataHora ?></td>
                                            <td><?= isset($a['local']) ? $a['local'] : '-' ?></td>
                                            <td><?= isset($a['status']) ? ucfirst($a['status']) : '-' ?></td>
                                            <td><?= isset($a['observacoes']) ? substr($a['observacoes'], 0, 50) . '...' : '-' ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" align="center">Nenhuma audiência encontrada</td>
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

