<div class="new122">
    <div class="widget-title">
        <span class="icon"><i class="fas fa-chart-line"></i></span>
        <h5>Relatório - Fluxo de Caixa</h5>
    </div>

    <form method="get" action="<?= base_url() ?>relatorios/fluxoCaixa" style="margin-bottom: 15px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div>
                <select name="mes" class="span12">
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" 
                            <?= $this->input->get('mes', date('m')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <select name="ano" class="span12">
                    <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++) { ?>
                        <option value="<?= $i ?>" <?= $this->input->get('ano', date('Y')) == $i ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <button type="submit" class="button btn btn-mini btn-warning">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                </button>
            </div>
        </div>
    </form>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <h4>Receitas (Pagamentos Recebidos)</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Total Recebido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_receitas = 0;
                    if (isset($receitas) && $receitas) { 
                        foreach ($receitas as $r) {
                            $total_receitas += $r->total;
                    ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r->data)) ?></td>
                            <td>R$ <?= number_format($r->total, 2, ',', '.') ?></td>
                        </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="2">Nenhum pagamento encontrado neste período.</td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total de Receitas:</th>
                        <th>R$ <?= number_format($total_receitas, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="widget-box" style="margin-top: 20px;">
        <div class="widget-content nopadding">
            <h4>Faturas Emitidas</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Total Emitido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_emitidas = 0;
                    if (isset($faturas_emitidas) && $faturas_emitidas) { 
                        foreach ($faturas_emitidas as $f) {
                            $total_emitidas += $f->total;
                    ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($f->data)) ?></td>
                            <td>R$ <?= number_format($f->total, 2, ',', '.') ?></td>
                        </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="2">Nenhuma fatura emitida neste período.</td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total Emitido:</th>
                        <th>R$ <?= number_format($total_emitidas, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

