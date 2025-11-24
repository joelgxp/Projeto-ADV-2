<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-wrench"></i>
                </span>
                <h5>Cadastro de Serviço Jurídico</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" id="formServico" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label for="nome" class="control-label">Nome<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nome" type="text" name="nome" value="<?php echo set_value('nome'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="preco" class="control-label"><span class="required">Preço*</span></label>
                        <div class="controls">
                            <input id="preco" class="money" data-affixes-stay="true" data-thousands="" data-decimal="." type="text" name="preco" value="<?php echo set_value('preco'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="tipo_servico" class="control-label">Tipo de Serviço</label>
                        <div class="controls">
                            <select id="tipo_servico" name="tipo_servico">
                                <option value="">Selecione...</option>
                                <option value="Consultoria" <?= set_value('tipo_servico') == 'Consultoria' ? 'selected' : '' ?>>Consultoria</option>
                                <option value="Petição" <?= set_value('tipo_servico') == 'Petição' ? 'selected' : '' ?>>Petição</option>
                                <option value="Contestação" <?= set_value('tipo_servico') == 'Contestação' ? 'selected' : '' ?>>Contestação</option>
                                <option value="Recurso" <?= set_value('tipo_servico') == 'Recurso' ? 'selected' : '' ?>>Recurso</option>
                                <option value="Audiência" <?= set_value('tipo_servico') == 'Audiência' ? 'selected' : '' ?>>Audiência</option>
                                <option value="Análise" <?= set_value('tipo_servico') == 'Análise' ? 'selected' : '' ?>>Análise Jurídica</option>
                                <option value="Outros" <?= set_value('tipo_servico') == 'Outros' ? 'selected' : '' ?>>Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="tempo_estimado" class="control-label">Tempo Estimado (horas)</label>
                        <div class="controls">
                            <input id="tempo_estimado" type="number" name="tempo_estimado" min="0" step="0.5" value="<?php echo set_value('tempo_estimado'); ?>" placeholder="Ex: 2.5" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label">Descrição</label>
                        <div class="controls">
                            <textarea id="descricao" name="descricao" rows="3"><?php echo set_value('descricao'); ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;max-width: 160px">
                                  <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar</span></button>
                                <a href="<?php echo base_url() ?>index.php/servicos" id="btnAdicionar" class="button btn btn-mini btn-warning" style="display:inline-flex;max-width: 160px">
                                  <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".money").maskMoney();
        $('#formServico').validate({
            rules: {
                nome: {
                    required: true
                },
                preco: {
                    required: true
                }
            },
            messages: {
                nome: {
                    required: 'Campo Requerido.'
                },
                preco: {
                    required: 'Campo Requerido.'
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
