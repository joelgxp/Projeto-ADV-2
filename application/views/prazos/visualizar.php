<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-calendar-check"></i>
                </span>
                <h5>Visualizar Prazo Processual</h5>
            </div>
            <div class="widget-content nopadding">
                <?php if ($result) { ?>
                    <div class="span12" style="padding: 20px;">
                        <div class="span6">
                            <h4>Informações do Prazo</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td><?= $result->idPrazos ?></td>
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
                                    <td><strong>Descrição:</strong></td>
                                    <td><?= $result->descricao ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Data do Prazo:</strong></td>
                                    <td><?= isset($result->dataPrazo) ? date('d/m/Y', strtotime($result->dataPrazo)) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Data de Vencimento:</strong></td>
                                    <td>
                                        <?php
                                        if (isset($result->dataVencimento)) {
                                            $dataVenc = date('d/m/Y', strtotime($result->dataVencimento));
                                            $vencido = strtotime($result->dataVencimento) < strtotime('today') && ($result->status ?? '') == 'Pendente';
                                            if ($vencido) {
                                                echo '<span class="label label-important">' . $dataVenc . ' (Vencido)</span>';
                                            } else {
                                                echo $dataVenc;
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $status_labels = [
                                            'pendente' => ['label' => 'Pendente', 'class' => 'label-warning'],
                                            'proximo_vencer' => ['label' => 'Próximo a Vencer', 'class' => 'label-info'],
                                            'vencendo_hoje' => ['label' => 'Vencendo Hoje', 'class' => 'label-important'],
                                            'vencido' => ['label' => 'Vencido', 'class' => 'label-important'],
                                            'cumprido' => ['label' => 'Cumprido', 'class' => 'label-success'],
                                            'prorrogado' => ['label' => 'Prorrogado', 'class' => 'label-info'],
                                            'cancelado' => ['label' => 'Cancelado', 'class' => 'label-default'],
                                        ];
                                        $status = strtolower($result->status ?? 'pendente');
                                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'class' => 'label-default'];
                                        echo '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Prioridade:</strong></td>
                                    <td>
                                        <?php
                                        $prioridade_labels = [
                                            'Baixa' => 'label-default',
                                            'Normal' => 'label-info',
                                            'Alta' => 'label-warning',
                                            'Urgente' => 'label-important',
                                        ];
                                        $prioridade = $result->prioridade ?? 'Normal';
                                        $class = $prioridade_labels[$prioridade] ?? 'label-default';
                                        echo '<span class="label ' . $class . '">' . $prioridade . '</span>';
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
                        </div>
                    </div>
                    <div class="form-actions" style="padding: 20px;">
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo')) { ?>
                            <a href="<?= base_url() ?>index.php/prazos/editar/<?= $result->idPrazos ?>" class="button btn btn-mini btn-info">
                                <span class="button__icon"><i class='bx bx-edit'></i></span>
                                <span class="button__text2">Editar Prazo</span>
                            </a>
                            <?php
                            if (isset($numeroProrrogacoes) && $numeroProrrogacoes < 3 && isset($result->dataVencimento) && strtotime($result->dataVencimento) >= strtotime('-30 days')) {
                                ?>
                                <a href="<?= base_url() ?>index.php/prazos/prorrogar/<?= $result->idPrazos ?>" class="button btn btn-mini btn-warning">
                                    <span class="button__icon"><i class='bx bx-calendar-plus'></i></span>
                                    <span class="button__text2">Prorrogar Prazo</span>
                                </a>
                            <?php } ?>
                        <?php } ?>
                        <a href="<?= base_url() ?>index.php/prazos" class="button btn btn-mini btn-warning">
                            <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                            <span class="button__text2">Voltar</span>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="span12" style="padding: 20px;">
                        <div class="alert alert-error">Prazo não encontrado.</div>
                        <a href="<?= base_url() ?>index.php/prazos" class="button btn btn-mini btn-warning">
                            <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                            <span class="button__text2">Voltar</span>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

