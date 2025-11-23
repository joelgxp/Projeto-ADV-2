<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-calendar-event"></i>
                </span>
                <h5>Visualizar Audiência</h5>
            </div>
            <div class="widget-content nopadding">
                <?php if ($result) { ?>
                    <div class="span12" style="padding: 20px;">
                        <div class="span6">
                            <h4>Informações da Audiência</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td><?= $result->idAudiencias ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Processo:</strong></td>
                                    <td>
                                        <?php if (isset($result->processos_id)) { ?>
                                            <a href="<?= base_url() ?>index.php/processos/visualizar/<?= $result->processos_id ?>">
                                                <?= isset($result->numeroProcesso) ? $result->numeroProcesso : 'N/A' ?>
                                            </a>
                                        <?php } else { ?>
                                            N/A
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td><?= $result->tipo ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Data e Hora:</strong></td>
                                    <td>
                                        <?php
                                        if (isset($result->dataHora)) {
                                            echo date('d/m/Y H:i', strtotime($result->dataHora));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Local:</strong></td>
                                    <td><?= $result->local ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $status_labels = [
                                            'Agendada' => 'label-info',
                                            'Realizada' => 'label-success',
                                            'Cancelada' => 'label-important',
                                            'Adiada' => 'label-warning',
                                        ];
                                        $status = $result->status ?? 'Agendada';
                                        $class = $status_labels[$status] ?? 'label-default';
                                        echo '<span class="label ' . $class . '">' . $status . '</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php if (isset($result->nomeResponsavel)) { ?>
                                <tr>
                                    <td><strong>Responsável:</strong></td>
                                    <td><?= $result->nomeResponsavel ?></td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                        <div class="span6">
                            <?php if (isset($result->idProcessos)) { ?>
                                <h4>Informações do Processo</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>Número:</strong></td>
                                        <td><?= isset($result->numeroProcesso) ? $result->numeroProcesso : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Classe:</strong></td>
                                        <td><?= isset($result->classe) ? $result->classe : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Assunto:</strong></td>
                                        <td><?= isset($result->assunto) ? $result->assunto : '-' ?></td>
                                    </tr>
                                    <?php if (isset($result->nomeCliente)) { ?>
                                    <tr>
                                        <td><strong>Cliente:</strong></td>
                                        <td><?= $result->nomeCliente ?></td>
                                    </tr>
                                    <?php } ?>
                                </table>
                            <?php } ?>
                            <?php if (isset($result->observacoes) && !empty($result->observacoes)) { ?>
                                <h4>Observações</h4>
                                <div style="padding: 10px; background: #f5f5f5; border-radius: 4px;">
                                    <?= nl2br($result->observacoes) ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-actions" style="padding: 20px;">
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) { ?>
                            <a href="<?= base_url() ?>index.php/audiencias/editar/<?= $result->idAudiencias ?>" class="button btn btn-mini btn-info">
                                <span class="button__icon"><i class='bx bx-edit'></i></span>
                                <span class="button__text2">Editar Audiência</span>
                            </a>
                        <?php } ?>
                        <a href="<?= base_url() ?>index.php/audiencias" class="button btn btn-mini btn-warning">
                            <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                            <span class="button__text2">Voltar</span>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="span12" style="padding: 20px;">
                        <div class="alert alert-error">Audiência não encontrada.</div>
                        <a href="<?= base_url() ?>index.php/audiencias" class="button btn btn-mini btn-warning">
                            <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                            <span class="button__text2">Voltar</span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

