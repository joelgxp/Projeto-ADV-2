<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<style>
    .control-group.error .help-inline {
        display: flex;
    }

    .form-horizontal .control-group {
        border-bottom: 1px solid #ffffff;
    }

    .form-horizontal .controls {
        margin-left: 20px;
        padding-bottom: 8px 0;
    }

    .form-horizontal .control-label {
        text-align: left;
        padding-top: 15px;
    }

    .nopadding {
        padding: 0 20px !important;
        margin-right: 20px;
    }

    .widget-title h5 {
        padding-bottom: 30px;
        text-align-last: left;
        font-size: 2em;
        font-weight: 500;
    }
</style>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-calendar-event"></i>
                </span>
                <h5>Editar Audiência</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formAudiencia" method="post" class="form-horizontal">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="processos_id" class="control-label">Processo<span class="required">*</span></label>
                            <div class="controls">
                                <select id="processos_id" name="processos_id" required>
                                    <option value="">Selecione um processo...</option>
                                    <?php if ($processos) {
                                        foreach ($processos as $p) {
                                            $selected = (isset($result->processos_id) && $result->processos_id == $p->idProcessos) ? 'selected' : '';
                                            echo '<option value="' . $p->idProcessos . '" ' . $selected . '>' . ($p->numeroProcesso ?? 'N/A') . ' - ' . ($p->classe ?? 'N/A') . '</option>';
                                        }
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo" class="control-label">Tipo de Audiência<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $tipos = ['Audiência Inicial', 'Audiência de Instrução', 'Audiência de Conciliação', 'Audiência de Julgamento', 'Audiência de Sentença', 'Audiência de Justificação', 'Outras'];
                                    foreach ($tipos as $t) {
                                        $selected = (isset($result->tipo) && $result->tipo == $t) ? 'selected' : '';
                                        echo '<option value="' . $t . '" ' . $selected . '>' . $t . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataHora" class="control-label">Data<span class="required">*</span></label>
                            <div class="controls">
                                <?php
                                $dataHora = isset($result->dataHora) ? date('Y-m-d', strtotime($result->dataHora)) : set_value('dataHora');
                                $hora = isset($result->dataHora) ? date('H:i', strtotime($result->dataHora)) : set_value('hora', '09:00');
                                ?>
                                <input id="dataHora" type="date" name="dataHora" value="<?= $dataHora ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="hora" class="control-label">Hora</label>
                            <div class="controls">
                                <input id="hora" type="time" name="hora" value="<?= $hora ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label for="local" class="control-label">Local</label>
                            <div class="controls">
                                <input id="local" type="text" name="local" value="<?php echo isset($result->local) ? $result->local : set_value('local'); ?>" placeholder="Ex: Fórum, Sala, Vara..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <select id="status" name="status">
                                    <?php
                                    $statuses = ['Agendada', 'Realizada', 'Cancelada', 'Adiada'];
                                    foreach ($statuses as $s) {
                                        $selected = (isset($result->status) && $result->status == $s) ? 'selected' : '';
                                        echo '<option value="' . $s . '" ' . $selected . '>' . $s . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="4"><?php echo isset($result->observacoes) ? $result->observacoes : set_value('observacoes'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <button type="submit" class="button btn btn-mini btn-success">
                                <span class="button__icon"><i class='bx bx-save'></i></span>
                                <span class="button__text2">Salvar Alterações</span>
                            </button>
                            <a href="<?= base_url() ?>index.php/audiencias" class="button btn btn-mini btn-warning">
                                <span class="button__icon"><i class='bx bx-x'></i></span>
                                <span class="button__text2">Cancelar</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#formAudiencia').validate({
            rules: {
                processos_id: {
                    required: true
                },
                tipo: {
                    required: true
                },
                dataHora: {
                    required: true,
                    date: true
                }
            },
            messages: {
                processos_id: {
                    required: 'Campo obrigatório.'
                },
                tipo: {
                    required: 'Campo obrigatório.'
                },
                dataHora: {
                    required: 'Campo obrigatório.',
                    date: 'Data inválida.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>

