<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-calendar-plus"></i>
                </span>
                <h5>Prorrogar Prazo Processual</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <?php if ($result) { ?>
                <div class="widget-content nopadding" style="padding: 20px;">
                    <div class="alert alert-info">
                        <strong>⚠️ Atenção:</strong> Você pode prorrogar este prazo até 3 vezes. 
                        Prorrogações realizadas: <strong><?= $numeroProrrogacoes ?></strong> de 3.
                    </div>

                    <h4>Informações do Prazo Atual</h4>
                    <table class="table table-bordered" style="margin-bottom: 20px;">
                        <tr>
                            <td style="width: 200px;"><strong>Tipo:</strong></td>
                            <td><?= $result->tipo ?? '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Descrição:</strong></td>
                            <td><?= $result->descricao ?? '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Data de Vencimento Atual:</strong></td>
                            <td>
                                <?php
                                if (isset($result->dataVencimento)) {
                                    echo '<strong>' . date('d/m/Y', strtotime($result->dataVencimento)) . '</strong>';
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
                                    'pendente' => ['label' => 'Pendente', 'class' => 'label-info'],
                                    'proximo_vencer' => ['label' => 'Próximo a Vencer', 'class' => 'label-warning'],
                                    'vencendo_hoje' => ['label' => 'Vencendo Hoje', 'class' => 'label-warning'],
                                    'vencido' => ['label' => 'Vencido', 'class' => 'label-important'],
                                    'prorrogado' => ['label' => 'Prorrogado', 'class' => 'label-inverse'],
                                ];
                                $status_atual = strtolower($result->status ?? 'pendente');
                                $status_info = $status_labels[$status_atual] ?? ['label' => ucfirst($status_atual), 'class' => 'label-default'];
                                echo '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
                                ?>
                            </td>
                        </tr>
                    </table>

                    <?php if ($numeroProrrogacoes < 3): ?>
                        <form action="<?php echo current_url(); ?>" id="formProrrogar" method="post" class="form-horizontal">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                            
                            <h4>Dados da Prorrogação</h4>
                            
                            <div class="control-group">
                                <label for="novos_dias_uteis" class="control-label">Novos Dias Úteis<span class="required">*</span></label>
                                <div class="controls">
                                    <input id="novos_dias_uteis" type="number" name="novos_dias_uteis" min="1" max="60" value="15" required />
                                    <span class="help-block" style="margin-top: 5px; font-size: 11px; color: #666;">
                                        Número de dias úteis a adicionar ao prazo (máximo 60 dias)
                                    </span>
                                </div>
                            </div>

                            <div class="control-group">
                                <label for="motivo" class="control-label">Motivo da Prorrogação</label>
                                <div class="controls">
                                    <textarea id="motivo" name="motivo" rows="3" placeholder="Informe o motivo da prorrogação (opcional)"><?php echo set_value('motivo'); ?></textarea>
                                </div>
                            </div>

                            <div class="form-actions" style="margin-top: 20px;">
                                <button type="submit" class="button btn btn-mini btn-success">
                                    <span class="button__icon"><i class='bx bx-calendar-check'></i></span>
                                    <span class="button__text2">Confirmar Prorrogação</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/prazos/visualizar/<?= $result->idPrazos ?>" class="button btn btn-mini btn-warning">
                                    <span class="button__icon"><i class='bx bx-x'></i></span>
                                    <span class="button__text2">Cancelar</span>
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-error">
                            <strong>Limite Atingido:</strong> Este prazo já possui o máximo de 3 prorrogações permitidas.
                        </div>
                        <a href="<?= base_url() ?>index.php/prazos/visualizar/<?= $result->idPrazos ?>" class="button btn btn-mini btn-warning">
                            <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                            <span class="button__text2">Voltar</span>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($historicoProrrogacoes) && count($historicoProrrogacoes) > 1): ?>
                        <hr style="margin: 30px 0;">
                        <h4>Histórico de Prorrogações</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data de Vencimento</th>
                                    <th>Status</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historicoProrrogacoes as $h): ?>
                                    <tr>
                                        <td>
                                            <?php if (isset($h->numero_prorrogacao) && $h->numero_prorrogacao > 0): ?>
                                                Prorrogação <?= $h->numero_prorrogacao ?>
                                            <?php else: ?>
                                                <strong>Original</strong>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= isset($h->dataVencimento) ? date('d/m/Y', strtotime($h->dataVencimento)) : '-' ?></td>
                                        <td>
                                            <?php
                                            $status_atual = strtolower($h->status ?? 'pendente');
                                            $status_info = $status_labels[$status_atual] ?? ['label' => ucfirst($status_atual), 'class' => 'label-default'];
                                            echo '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
                                            ?>
                                        </td>
                                        <td><?= $h->descricao ?? '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php } else { ?>
                <div class="widget-content nopadding" style="padding: 20px;">
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

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#formProrrogar').validate({
            rules: {
                novos_dias_uteis: {
                    required: true,
                    min: 1,
                    max: 60
                }
            },
            messages: {
                novos_dias_uteis: {
                    required: 'Campo obrigatório.',
                    min: 'Mínimo 1 dia útil.',
                    max: 'Máximo 60 dias úteis.'
                }
            }
        });
    });
</script>

