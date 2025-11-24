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
                    <i class="fas fa-calendar-check"></i>
                </span>
                <h5>Cadastro de Prazo Processual</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formPrazo" method="post" class="form-horizontal">
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
                                            echo '<option value="' . $p->idProcessos . '">' . ($p->numeroProcesso ?? 'N/A') . ' - ' . ($p->classe ?? 'N/A') . '</option>';
                                        }
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo" class="control-label">Tipo de Prazo<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Resposta">Resposta</option>
                                    <option value="Recurso">Recurso</option>
                                    <option value="Impugnação">Impugnação</option>
                                    <option value="Contestação">Contestação</option>
                                    <option value="Alegação">Alegação</option>
                                    <option value="Recurso Especial">Recurso Especial</option>
                                    <option value="Agravo">Agravo</option>
                                    <option value="Embargos">Embargos</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="descricao" class="control-label">Descrição<span class="required">*</span></label>
                            <div class="controls">
                                <textarea id="descricao" name="descricao" rows="3" required><?php echo set_value('descricao'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label for="dataPrazo" class="control-label">Data do Prazo</label>
                            <div class="controls">
                                <input id="dataPrazo" type="date" name="dataPrazo" value="<?php echo set_value('dataPrazo', date('Y-m-d')); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataVencimento" class="control-label">Data de Vencimento<span class="required">*</span></label>
                            <div class="controls">
                                <input id="dataVencimento" type="date" name="dataVencimento" value="<?php echo set_value('dataVencimento'); ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <select id="status" name="status">
                                    <option value="Pendente" <?= set_value('status', 'Pendente') == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="Cumprido" <?= set_value('status') == 'Cumprido' ? 'selected' : '' ?>>Cumprido</option>
                                    <option value="Vencido" <?= set_value('status') == 'Vencido' ? 'selected' : '' ?>>Vencido</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="prioridade" class="control-label">Prioridade</label>
                            <div class="controls">
                                <select id="prioridade" name="prioridade">
                                    <option value="Baixa" <?= set_value('prioridade', 'Normal') == 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                                    <option value="Normal" <?= set_value('prioridade', 'Normal') == 'Normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="Alta" <?= set_value('prioridade') == 'Alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="Urgente" <?= set_value('prioridade') == 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span>
                                    <span class="button__text2">Salvar Prazo</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/prazos" class="button btn btn-mini btn-warning" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-x'></i></span>
                                    <span class="button__text2">Cancelar</span>
                                </a>
                            </div>
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
        $('#formPrazo').validate({
            rules: {
                processos_id: {
                    required: true
                },
                tipo: {
                    required: true
                },
                descricao: {
                    required: true
                },
                dataVencimento: {
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
                descricao: {
                    required: 'Campo obrigatório.'
                },
                dataVencimento: {
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

