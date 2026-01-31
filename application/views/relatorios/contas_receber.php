<div class="new122">
    <div class="widget-title">
        <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
        <h5>Relatório - Contas a Receber</h5>
    </div>

    <form method="get" action="<?= base_url() ?>relatorios/contasReceber" style="margin-bottom: 15px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div>
                <select name="status" class="span12">
                    <option value="">Todos os status</option>
                    <option value="emitida" <?= $this->input->get('status') == 'emitida' ? 'selected' : '' ?>>Emitida</option>
                    <option value="parcialmente_paga" <?= $this->input->get('status') == 'parcialmente_paga' ? 'selected' : '' ?>>Parcialmente Paga</option>
                    <option value="atrasada" <?= $this->input->get('status') == 'atrasada' ? 'selected' : '' ?>>Atrasada</option>
                </select>
            </div>
            <div>
                <input type="text" name="vencimento_de" placeholder="Vencimento de" 
                    value="<?= $this->input->get('vencimento_de') ?>" class="span12 datepicker">
            </div>
            <div>
                <input type="text" name="vencimento_ate" placeholder="Vencimento até" 
                    value="<?= $this->input->get('vencimento_ate') ?>" class="span12 datepicker">
            </div>
            <div>
                <button type="submit" class="button btn btn-mini btn-warning">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                </button>
            </div>
        </div>
    </form>

    <?php if (isset($estatisticas) && $estatisticas) { ?>
    <div class="widget-box" style="margin-bottom: 15px;">
        <div class="widget-content">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div><strong>Total de Faturas:</strong> <?= $estatisticas->total_faturas ?></div>
                <div><strong>Valor Total:</strong> R$ <?= number_format($estatisticas->valor_total, 2, ',', '.') ?></div>
                <div><strong>Valor Pago:</strong> R$ <?= number_format($estatisticas->valor_pago, 2, ',', '.') ?></div>
                <div><strong>Saldo a Receber:</strong> R$ <?= number_format($estatisticas->saldo_restante, 2, ',', '.') ?></div>
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
                        <th>Emissão</th>
                        <th>Vencimento</th>
                        <th>Valor Total</th>
                        <th>Valor Pago</th>
                        <th>Saldo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($faturas) && $faturas) { ?>
                        <?php foreach ($faturas as $f) { ?>
                            <tr>
                                <td><?= $f->numero ?></td>
                                <td><?= htmlspecialchars($f->nomeCliente) ?></td>
                                <td><?= date('d/m/Y', strtotime($f->data_emissao)) ?></td>
                                <td><?= date('d/m/Y', strtotime($f->data_vencimento)) ?></td>
                                <td>R$ <?= number_format($f->valor_total, 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($f->valor_pago, 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($f->saldo_restante, 2, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $badges = ['emitida' => 'badge-info', 'parcialmente_paga' => 'badge-warning', 'atrasada' => 'badge-danger'];
                                    $badge = $badges[$f->status] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $f->status)) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url() ?>faturas/visualizar/<?= $f->id ?>" class="button btn btn-mini btn-info">
                                        <i class='bx bx-show'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9">Nenhuma fatura encontrada.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
    });
});
</script>

