<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-tags"></i>
                </span>
                <h5>Cadastro de Plano</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formPlano" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="nome" class="control-label">Nome do Plano <span class="required">*</span></label>
                            <div class="controls">
                                <input id="nome" type="text" name="nome" value="<?php echo set_value('nome'); ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="descricao" class="control-label">Descrição</label>
                            <div class="controls">
                                <textarea id="descricao" name="descricao" rows="3"><?php echo set_value('descricao'); ?></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="valor_mensal" class="control-label">Valor Mensal (R$)</label>
                            <div class="controls">
                                <input id="valor_mensal" type="text" name="valor_mensal" value="<?php echo set_value('valor_mensal', '0.00'); ?>" class="money" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="limite_processos" class="control-label">Limite de Processos</label>
                            <div class="controls">
                                <input id="limite_processos" type="number" name="limite_processos" value="<?php echo set_value('limite_processos', '0'); ?>" min="0" />
                                <span class="help-inline">0 = Ilimitado</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="limite_prazos" class="control-label">Limite de Prazos</label>
                            <div class="controls">
                                <input id="limite_prazos" type="number" name="limite_prazos" value="<?php echo set_value('limite_prazos', '0'); ?>" min="0" />
                                <span class="help-inline">0 = Ilimitado</span>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label for="limite_audiencias" class="control-label">Limite de Audiências</label>
                            <div class="controls">
                                <input id="limite_audiencias" type="number" name="limite_audiencias" value="<?php echo set_value('limite_audiencias', '0'); ?>" min="0" />
                                <span class="help-inline">0 = Ilimitado</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="limite_documentos" class="control-label">Limite de Documentos</label>
                            <div class="controls">
                                <input id="limite_documentos" type="number" name="limite_documentos" value="<?php echo set_value('limite_documentos', '0'); ?>" min="0" />
                                <span class="help-inline">0 = Ilimitado</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Funcionalidades</label>
                            <div class="controls">
                                <label class="checkbox">
                                    <input type="checkbox" name="acesso_portal" value="1" <?php echo set_checkbox('acesso_portal', '1', true); ?> />
                                    Acesso ao Portal do Cliente
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="suporte_prioritario" value="1" <?php echo set_checkbox('suporte_prioritario', '1'); ?> />
                                    Suporte Prioritário
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" name="relatorios_avancados" value="1" <?php echo set_checkbox('relatorios_avancados', '1'); ?> />
                                    Relatórios Avançados
                                </label>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Status</label>
                            <div class="controls">
                                <label class="checkbox">
                                    <input type="checkbox" name="status" value="1" <?php echo set_checkbox('status', '1', true); ?> />
                                    Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions" style="padding: 20px;">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Salvar</span>
                                </button>
                                <a title="Cancelar" class="button btn btn-warning" href="<?php echo site_url() ?>/planos" style="display:inline-flex;"><span class="button__icon"><i class="bx bx-x"></i></span> <span class="button__text2">Cancelar</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.money').mask('000.000.000.000.000,00', {reverse: true});
    });
</script>

