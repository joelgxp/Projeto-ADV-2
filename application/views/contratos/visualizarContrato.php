<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-contract"></i></span>
                <h5>Visualizar Contrato #<?= $result->id ?></h5>
            </div>
            <div class="widget-content">
                <div class="span12" style="margin-left: 0">
                    <div class="span6">
                        <h4>Informações do Contrato</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>Cliente</th>
                                <td><?= htmlspecialchars($result->nomeCliente) ?></td>
                            </tr>
                            <tr>
                                <th>Tipo</th>
                                <td><?= ucfirst($result->tipo) ?></td>
                            </tr>
                            <tr>
                                <th>Data de Início</th>
                                <td><?= date('d/m/Y', strtotime($result->data_inicio)) ?></td>
                            </tr>
                            <tr>
                                <th>Data de Fim</th>
                                <td><?= $result->data_fim ? date('d/m/Y', strtotime($result->data_fim)) : 'Indeterminado' ?></td>
                            </tr>
                            <?php if ($result->valor_fixo) { ?>
                            <tr>
                                <th>Valor Fixo</th>
                                <td>R$ <?= number_format($result->valor_fixo, 2, ',', '.') ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($result->percentual_sucumbencia) { ?>
                            <tr>
                                <th>Percentual Sucumbência</th>
                                <td><?= number_format($result->percentual_sucumbencia, 2, ',', '.') ?>%</td>
                            </tr>
                            <?php } ?>
                            <?php if ($result->percentual_exito) { ?>
                            <tr>
                                <th>Percentual Êxito</th>
                                <td><?= number_format($result->percentual_exito, 2, ',', '.') ?>%</td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($result->ativo) { ?>
                                        <span class="badge badge-success">Ativo</span>
                                    <?php } else { ?>
                                        <span class="badge badge-secondary">Inativo</span>
                                    <?php } ?>
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

                <?php if ($faturas) { ?>
                <div class="span12" style="margin-left: 0; margin-top: 20px;">
                    <h4>Faturas Vinculadas</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Data Emissão</th>
                                <th>Vencimento</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faturas as $fat) { ?>
                                <tr>
                                    <td><?= $fat->numero ?></td>
                                    <td><?= date('d/m/Y', strtotime($fat->data_emissao)) ?></td>
                                    <td><?= date('d/m/Y', strtotime($fat->data_vencimento)) ?></td>
                                    <td>R$ <?= number_format($fat->valor_total, 2, ',', '.') ?></td>
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
                                        $badge = $badges[$fat->status] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $fat->status)) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url() ?>index.php/faturas/visualizar/<?= $fat->id ?>" class="button btn btn-mini btn-info">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>

                <div class="form-actions" style="margin-top: 20px;">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eContrato')) { ?>
                        <a href="<?= base_url() ?>index.php/contratos/editar/<?= $result->id ?>" class="button btn btn-warning">Editar</a>
                    <?php } ?>
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'gPeticaoIA')) { ?>
                        <a href="<?= site_url('pecas-geradas/gerar?contratos_id=' . $result->id . '&clientes_id=' . ($result->clientes_id ?? '')) ?>" class="button btn btn-primary">
                            <span class="button__icon"><i class='bx bx-magic-wand'></i></span>
                            Gerar adendo/notificação com IA
                        </a>
                    <?php } ?>
                    <a href="<?= base_url() ?>index.php/contratos" class="button btn">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>

