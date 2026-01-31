<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script>
var itemCounter = <?= count($itens) ?>;
function adicionarItem() {
    itemCounter++;
    $.ajax({
        url: '<?= base_url() ?>index.php/faturas/adicionarItem',
        type: 'POST',
        data: {
            faturas_id: <?= $result->id ?>,
            processos_id: '',
            tipo_item: 'honorario',
            descricao: '',
            valor_unitario: '0,00',
            quantidade: '1',
            ipi: '0',
            iss: '0',
            <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Erro ao adicionar item');
            }
        }
    });
}

function removerItem(item_id) {
    if (confirm('Deseja realmente remover este item?')) {
        $.ajax({
            url: '<?= base_url() ?>index.php/faturas/removerItem',
            type: 'POST',
            data: {
                item_id: item_id,
                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Erro ao remover item');
                }
            }
        });
    }
}
</script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <h5>Editar Fatura - <?= $result->numero ?></h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tab1">Dados da Fatura</a></li>
                    <li><a data-toggle="tab" href="#tab2">Itens</a></li>
                    <li><a data-toggle="tab" href="#tab3">Pagamentos</a></li>
                </ul>

                <div class="tab-content">
                    <!-- Tab 1: Dados da Fatura -->
                    <div id="tab1" class="tab-pane active">
                        <form action="<?php echo current_url(); ?>" method="post" class="form-horizontal" style="padding: 20px;">
                            <div class="span6">
                                <div class="control-group">
                                    <label class="control-label">Número</label>
                                    <div class="controls">
                                        <input type="text" value="<?= $result->numero ?>" class="span12" disabled>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Cliente</label>
                                    <div class="controls">
                                        <input type="text" value="<?= htmlspecialchars($result->nomeCliente) ?>" class="span12" disabled>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Contrato</label>
                                    <div class="controls">
                                        <select name="contratos_id" class="span12">
                                            <option value="">Nenhum</option>
                                            <?php if ($contratos) { ?>
                                                <?php foreach ($contratos as $c) { ?>
                                                    <option value="<?= $c->id ?>" <?= $result->contratos_id == $c->id ? 'selected' : '' ?>>
                                                        <?= ucfirst($c->tipo) ?> - <?= date('d/m/Y', strtotime($c->data_inicio)) ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Data de Emissão*</label>
                                    <div class="controls">
                                        <input type="date" name="data_emissao" value="<?= $result->data_emissao ?>" class="span12" required>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Data de Vencimento*</label>
                                    <div class="controls">
                                        <input type="date" name="data_vencimento" value="<?= $result->data_vencimento ?>" class="span12" required>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Status</label>
                                    <div class="controls">
                                        <select name="status" class="span12">
                                            <option value="rascunho" <?= $result->status == 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                            <option value="emitida" <?= $result->status == 'emitida' ? 'selected' : '' ?>>Emitida</option>
                                            <option value="paga" <?= $result->status == 'paga' ? 'selected' : '' ?> disabled>Paga</option>
                                            <option value="parcialmente_paga" <?= $result->status == 'parcialmente_paga' ? 'selected' : '' ?>>Parcialmente Paga</option>
                                            <option value="atrasada" <?= $result->status == 'atrasada' ? 'selected' : '' ?>>Atrasada</option>
                                            <option value="cancelada" <?= $result->status == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Valor Total</label>
                                    <div class="controls">
                                        <input type="text" value="R$ <?= number_format($result->valor_total, 2, ',', '.') ?>" class="span12" disabled>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Valor Pago</label>
                                    <div class="controls">
                                        <input type="text" value="R$ <?= number_format($result->valor_pago, 2, ',', '.') ?>" class="span12" disabled>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Saldo Restante</label>
                                    <div class="controls">
                                        <input type="text" value="R$ <?= number_format($result->saldo_restante, 2, ',', '.') ?>" class="span12" disabled>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Observações</label>
                                    <div class="controls">
                                        <textarea name="observacoes" class="span12" rows="3"><?= htmlspecialchars($result->observacoes) ?></textarea>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="button btn btn-success">Salvar Alterações</button>
                                    <a href="<?= base_url() ?>index.php/faturas" class="button btn">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Itens -->
                    <div id="tab2" class="tab-pane">
                        <div style="padding: 20px;">
                            <h4>Itens da Fatura</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descrição</th>
                                        <th>Processo</th>
                                        <th>Valor Unitário</th>
                                        <th>Quantidade</th>
                                        <th>IPI %</th>
                                        <th>ISS %</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($itens) { ?>
                                        <?php foreach ($itens as $item) { ?>
                                            <tr>
                                                <td><?= ucfirst($item->tipo_item) ?></td>
                                                <td><?= htmlspecialchars($item->descricao) ?></td>
                                                <td><?= $item->numeroProcesso ?: '-' ?></td>
                                                <td>R$ <?= number_format($item->valor_unitario, 2, ',', '.') ?></td>
                                                <td><?= number_format($item->quantidade, 2, ',', '.') ?></td>
                                                <td><?= number_format($item->ipi, 2, ',', '.') ?>%</td>
                                                <td><?= number_format($item->iss, 2, ',', '.') ?>%</td>
                                                <td>R$ <?= number_format($item->valor_total, 2, ',', '.') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-mini" onclick="removerItem(<?= $item->id ?>)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="9">Nenhum item cadastrado.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success" onclick="adicionarItem()">
                                <i class="bx bx-plus"></i> Adicionar Item
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Pagamentos -->
                    <div id="tab3" class="tab-pane">
                        <div style="padding: 20px;">
                            <h4>Pagamentos</h4>
                            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aPagamento')) { ?>
                                <div style="margin-bottom: 15px;">
                                    <a href="<?= base_url() ?>pagamentos/adicionar/<?= $result->id ?>" class="button btn btn-success">
                                        <i class="bx bx-plus"></i> Adicionar Pagamento
                                    </a>
                                </div>
                            <?php } ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Método</th>
                                        <th>Observações</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pagamentos) { ?>
                                        <?php foreach ($pagamentos as $pag) { ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($pag->data_pagamento)) ?></td>
                                                <td>R$ <?= number_format($pag->valor, 2, ',', '.') ?></td>
                                                <td><?= ucfirst($pag->metodo_pagamento) ?></td>
                                                <td><?= htmlspecialchars($pag->observacoes) ?></td>
                                                <td>
                                                    <a href="<?= base_url() ?>pagamentos/gerarRecibo/<?= $pag->id ?>" 
                                                        class="button btn btn-mini btn-info" title="Gerar Recibo">
                                                        <i class='bx bx-file'></i>
                                                    </a>
                                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePagamento')) { ?>
                                                        <a href="<?= base_url() ?>pagamentos/editar/<?= $pag->id ?>" 
                                                            class="button btn btn-mini btn-warning" title="Editar">
                                                            <i class='bx bx-edit'></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dPagamento')) { ?>
                                                        <a href="<?= base_url() ?>pagamentos/excluir/<?= $pag->id ?>" 
                                                            class="button btn btn-mini btn-danger" 
                                                            onclick="return confirm('Deseja realmente excluir este pagamento?')" title="Excluir">
                                                            <i class='bx bx-trash'></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr>
                                            <td colspan="5">Nenhum pagamento registrado.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

