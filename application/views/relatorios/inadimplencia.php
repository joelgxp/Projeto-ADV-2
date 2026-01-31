<div class="new122">
    <div class="widget-title">
        <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
        <h5>Relatório - Inadimplência</h5>
    </div>

    <?php if (isset($estatisticas) && $estatisticas) { ?>
    <div class="widget-box" style="margin-bottom: 15px;">
        <div class="widget-content">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div><strong>Faturas Atrasadas:</strong> <?= $estatisticas->total_atrasadas ?></div>
                <div><strong>Valor Total Atrasado:</strong> R$ <?= number_format($estatisticas->saldo_restante, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Vencimento</th>
                        <th>Dias em Atraso</th>
                        <th>Valor Total</th>
                        <th>Valor Pago</th>
                        <th>Saldo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($faturas) && $faturas) { ?>
                        <?php foreach ($faturas as $f) { 
                            $dias_atraso = (strtotime(date('Y-m-d')) - strtotime($f->data_vencimento)) / 86400;
                        ?>
                            <tr>
                                <td><?= $f->numero ?></td>
                                <td><?= htmlspecialchars($f->nomeCliente) ?></td>
                                <td><?= date('d/m/Y', strtotime($f->data_vencimento)) ?></td>
                                <td>
                                    <span class="badge badge-danger"><?= ceil($dias_atraso) ?> dias</span>
                                </td>
                                <td>R$ <?= number_format($f->valor_total, 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($f->valor_pago, 2, ',', '.') ?></td>
                                <td><strong>R$ <?= number_format($f->saldo_restante, 2, ',', '.') ?></strong></td>
                                <td>
                                    <a href="<?= base_url() ?>faturas/visualizar/<?= $f->id ?>" class="button btn btn-mini btn-info">
                                        <i class='bx bx-show'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8">Nenhuma fatura em atraso encontrada.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

