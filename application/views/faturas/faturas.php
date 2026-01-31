<div class="new122">
    <div class="widget-title">
        <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
        <h5>Faturas</h5>
    </div>
    
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aFatura')) { ?>
        <div style="margin-bottom: 15px;">
            <a href="<?= base_url() ?>index.php/faturas/adicionar" class="button btn btn-mini btn-success">
                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                <span class="button__text2">Nova Fatura</span>
            </a>
        </div>
    <?php } ?>

    <?php if (isset($estatisticas) && $estatisticas) { ?>
    <div class="widget-box" style="margin-bottom: 15px;">
        <div class="widget-content">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div><strong>Total de Faturas:</strong> <?= $estatisticas->total_faturas ?></div>
                <div><strong>Valor Total:</strong> R$ <?= number_format($estatisticas->valor_total, 2, ',', '.') ?></div>
                <div><strong>Valor Pago:</strong> R$ <?= number_format($estatisticas->valor_pago, 2, ',', '.') ?></div>
                <div><strong>Saldo Restante:</strong> R$ <?= number_format($estatisticas->saldo_restante, 2, ',', '.') ?></div>
                <div><strong>Pagas:</strong> <?= $estatisticas->total_pagas ?></div>
                <div><strong>Atrasadas:</strong> <?= $estatisticas->total_atrasadas ?></div>
            </div>
        </div>
    </div>
    <?php } ?>

    <form method="get" action="<?= base_url() ?>index.php/faturas" style="margin-bottom: 15px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div>
                <input type="text" name="cliente" placeholder="Buscar por cliente..." 
                    value="<?= $this->input->get('cliente') ?>" class="span12">
            </div>
            <div>
                <select name="status" class="span12">
                    <option value="">Todos os status</option>
                    <option value="rascunho" <?= $this->input->get('status') == 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="emitida" <?= $this->input->get('status') == 'emitida' ? 'selected' : '' ?>>Emitida</option>
                    <option value="paga" <?= $this->input->get('status') == 'paga' ? 'selected' : '' ?>>Paga</option>
                    <option value="parcialmente_paga" <?= $this->input->get('status') == 'parcialmente_paga' ? 'selected' : '' ?>>Parcialmente Paga</option>
                    <option value="atrasada" <?= $this->input->get('status') == 'atrasada' ? 'selected' : '' ?>>Atrasada</option>
                    <option value="cancelada" <?= $this->input->get('status') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
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
                    <?php if ($results) { ?>
                        <?php foreach ($results as $r) { ?>
                            <tr>
                                <td><?= $r->numero ?></td>
                                <td><?= htmlspecialchars($r->nomeCliente) ?></td>
                                <td><?= date('d/m/Y', strtotime($r->data_emissao)) ?></td>
                                <td><?= date('d/m/Y', strtotime($r->data_vencimento)) ?></td>
                                <td>R$ <?= number_format($r->valor_total, 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($r->valor_pago, 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($r->saldo_restante, 2, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'rascunho' => 'badge-secondary',
                                        'emitida' => 'badge-info',
                                        'paga' => 'badge-success',
                                        'parcialmente_paga' => 'badge-warning',
                                        'atrasada' => 'badge-danger',
                                        'cancelada' => 'badge-dark'
                                    ];
                                    $badge = $badges[$r->status] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $r->status)) ?></span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <?php 
                                    // Garantir que temos um ID válido
                                    $fatura_id = $r->id ?? $r->idFaturas ?? null;
                                    if ($fatura_id) { ?>
                                        <a href="<?= base_url() ?>index.php/faturas/visualizar/<?= $fatura_id ?>" 
                                            class="button btn btn-mini btn-info" title="Visualizar" style="display: inline-block; margin-right: 5px;">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) { ?>
                                            <a href="<?= base_url() ?>index.php/faturas/editar/<?= $fatura_id ?>" 
                                                class="button btn btn-mini btn-warning" title="Editar" style="display: inline-block; margin-right: 5px;">
                                                <i class='bx bx-edit'></i>
                                            </a>
                                            <?php if ($r->status != 'paga' && $r->status != 'cancelada') { ?>
                                                <a href="<?= base_url() ?>index.php/faturas/cancelar/<?= $fatura_id ?>" 
                                                    class="button btn btn-mini btn-danger" 
                                                    onclick="return confirm('Deseja cancelar esta fatura?')" title="Cancelar" style="display: inline-block; margin-right: 5px;">
                                                    <i class='bx bx-x'></i>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
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
            
            <?php if (isset($pagination)) { echo $pagination; } ?>
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

