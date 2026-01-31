<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <h5>Fatura <?= $result->numero ?></h5>
            </div>
            <div class="widget-content">
                <div class="span12" style="margin-left: 0">
                    <div class="span6">
                        <h4>Informações da Fatura</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>Número</th>
                                <td><?= $result->numero ?></td>
                            </tr>
                            <tr>
                                <th>Cliente</th>
                                <td><?= htmlspecialchars($result->nomeCliente) ?></td>
                            </tr>
                            <tr>
                                <th>Data de Emissão</th>
                                <td><?= date('d/m/Y', strtotime($result->data_emissao)) ?></td>
                            </tr>
                            <tr>
                                <th>Data de Vencimento</th>
                                <td><?= date('d/m/Y', strtotime($result->data_vencimento)) ?></td>
                            </tr>
                            <tr>
                                <th>Valor Total</th>
                                <td><strong>R$ <?= number_format($result->valor_total, 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Valor Pago</th>
                                <td>R$ <?= number_format($result->valor_pago, 2, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <th>Saldo Restante</th>
                                <td><strong>R$ <?= number_format($result->saldo_restante, 2, ',', '.') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status</th>
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
                                    $badge = $badges[$result->status] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $result->status)) ?></span>
                                </td>
                            </tr>
                            <?php if ($result->observacoes) { ?>
                            <tr>
                                <th>Observações</th>
                                <td><?= nl2br(htmlspecialchars($result->observacoes)) ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>

                <div class="span12" style="margin-left: 0; margin-top: 20px;">
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
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="8">Nenhum item cadastrado.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" style="text-align: right;">Total:</th>
                                <th>R$ <?= number_format($result->valor_total, 2, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="span12" style="margin-left: 0; margin-top: 20px;">
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
                        <tfoot>
                            <tr>
                                <th colspan="1" style="text-align: right;">Total Pago:</th>
                                <th>R$ <?= number_format($result->valor_pago, 2, ',', '.') ?></th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <a href="<?= base_url() ?>index.php/faturas/gerarPdf/<?= $result->id ?>" target="_blank" class="button btn btn-info">
                        <i class="bx bx-file"></i> Gerar PDF
                    </a>
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eFatura')) { ?>
                        <a href="<?= base_url() ?>index.php/faturas/editar/<?= $result->id ?>" class="button btn btn-warning">Editar</a>
                    <?php } ?>
                    <a href="<?= base_url() ?>index.php/faturas" class="button btn">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>

