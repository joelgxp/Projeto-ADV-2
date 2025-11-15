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
                    <i class="fas fa-gavel"></i>
                </span>
                <h5>Editar Processo</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formProcesso" method="post" class="form-horizontal">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="idProcessos" value="<?= $result->idProcessos ?>">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="numeroProcesso" class="control-label">Número de Processo<span class="required">*</span></label>
                            <div class="controls">
                                <input id="numeroProcesso" type="text" name="numeroProcesso" 
                                    placeholder="0000123-45.2023.8.13.0139 ou 00001234520238130139" 
                                    value="<?php echo set_value('numeroProcesso', isset($result->numeroProcessoFormatado) ? $result->numeroProcessoFormatado : ($result->numeroProcesso ?? '')); ?>" />
                                <small class="help-inline">Aceita formato CNJ ou número limpo</small>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="classe" class="control-label">Classe Processual</label>
                            <div class="controls">
                                <input id="classe" type="text" name="classe" value="<?php echo set_value('classe', $result->classe ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="assunto" class="control-label">Assunto</label>
                            <div class="controls">
                                <input id="assunto" type="text" name="assunto" value="<?php echo set_value('assunto', $result->assunto ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo_processo" class="control-label">Tipo de Processo</label>
                            <div class="controls">
                                <select id="tipo_processo" name="tipo_processo">
                                    <option value="">Selecione...</option>
                                    <option value="civel" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'civel' ? 'selected' : '' ?>>Cível</option>
                                    <option value="trabalhista" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'trabalhista' ? 'selected' : '' ?>>Trabalhista</option>
                                    <option value="tributario" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'tributario' ? 'selected' : '' ?>>Tributário</option>
                                    <option value="criminal" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'criminal' ? 'selected' : '' ?>>Criminal</option>
                                    <option value="familia" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'familia' ? 'selected' : '' ?>>Família</option>
                                    <option value="consumidor" <?= set_value('tipo_processo', $result->tipo_processo ?? '') == 'consumidor' ? 'selected' : '' ?>>Consumidor</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="vara" class="control-label">Vara</label>
                            <div class="controls">
                                <input id="vara" type="text" name="vara" value="<?php echo set_value('vara', $result->vara ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="comarca" class="control-label">Comarca</label>
                            <div class="controls">
                                <input id="comarca" type="text" name="comarca" value="<?php echo set_value('comarca', $result->comarca ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tribunal" class="control-label">Tribunal</label>
                            <div class="controls">
                                <input id="tribunal" type="text" name="tribunal" value="<?php echo set_value('tribunal', $result->tribunal ?? ''); ?>" />
                                <small class="help-inline">Será preenchido automaticamente pelo número do processo</small>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="segmento" class="control-label">Segmento</label>
                            <div class="controls">
                                <select id="segmento" name="segmento">
                                    <option value="">Selecione...</option>
                                    <option value="estadual" <?= set_value('segmento', $result->segmento ?? '') == 'estadual' ? 'selected' : '' ?>>Estadual</option>
                                    <option value="federal" <?= set_value('segmento', $result->segmento ?? '') == 'federal' ? 'selected' : '' ?>>Federal</option>
                                    <option value="trabalho" <?= set_value('segmento', $result->segmento ?? '') == 'trabalho' ? 'selected' : '' ?>>Trabalho</option>
                                    <option value="eleitoral" <?= set_value('segmento', $result->segmento ?? '') == 'eleitoral' ? 'selected' : '' ?>>Eleitoral</option>
                                    <option value="militar" <?= set_value('segmento', $result->segmento ?? '') == 'militar' ? 'selected' : '' ?>>Militar</option>
                                </select>
                                <small class="help-inline">Será preenchido automaticamente pelo número do processo</small>
                            </div>
                        </div>
                    </div>

                    <div class="span6">
                        <div class="control-group">
                            <label for="status" class="control-label">Status<span class="required">*</span></label>
                            <div class="controls">
                                <select id="status" name="status">
                                    <option value="em_andamento" <?= set_value('status', $result->status ?? 'em_andamento') == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                    <option value="suspenso" <?= set_value('status', $result->status ?? '') == 'suspenso' ? 'selected' : '' ?>>Suspenso</option>
                                    <option value="arquivado" <?= set_value('status', $result->status ?? '') == 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                                    <option value="finalizado" <?= set_value('status', $result->status ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="valorCausa" class="control-label">Valor da Causa</label>
                            <div class="controls">
                                <input id="valorCausa" type="text" name="valorCausa" 
                                    placeholder="0,00" 
                                    value="<?php echo set_value('valorCausa', $result->valorCausa ? number_format($result->valorCausa, 2, ',', '.') : ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataDistribuicao" class="control-label">Data de Distribuição</label>
                            <div class="controls">
                                <input id="dataDistribuicao" type="date" name="dataDistribuicao" value="<?php echo set_value('dataDistribuicao', $result->dataDistribuicao ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="clientes_id" class="control-label">Cliente</label>
                            <div class="controls">
                                <select id="clientes_id" name="clientes_id">
                                    <option value="">Selecione um cliente...</option>
                                    <?php
                                    if (isset($clientes) && $clientes) {
                                        $clientes_id_col = null;
                                        $clientes_nome_col = null;
                                        if (!empty($clientes)) {
                                            $first = reset($clientes);
                                            $clientes_id_col = isset($first->idClientes) ? 'idClientes' : (isset($first->id) ? 'id' : null);
                                            $clientes_nome_col = isset($first->nomeCliente) ? 'nomeCliente' : (isset($first->nome) ? 'nome' : null);
                                        }
                                        foreach ($clientes as $cliente) {
                                            $cliente_id = $clientes_id_col ? $cliente->$clientes_id_col : null;
                                            $cliente_nome = $clientes_nome_col ? $cliente->$clientes_nome_col : 'Cliente';
                                            $selected = set_value('clientes_id', $result->clientes_id ?? '') == $cliente_id ? 'selected' : '';
                                            echo '<option value="' . $cliente_id . '" ' . $selected . '>' . $cliente_nome . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="usuarios_id" class="control-label">Advogado Responsável</label>
                            <div class="controls">
                                <select id="usuarios_id" name="usuarios_id">
                                    <option value="">Selecione um advogado...</option>
                                    <?php
                                    if (isset($usuarios) && $usuarios) {
                                        $usuarios_id_col = null;
                                        $usuarios_nome_col = null;
                                        if (!empty($usuarios)) {
                                            $first = reset($usuarios);
                                            $usuarios_id_col = isset($first->idUsuarios) ? 'idUsuarios' : (isset($first->id) ? 'id' : null);
                                            $usuarios_nome_col = isset($first->nome) ? 'nome' : null;
                                        }
                                        foreach ($usuarios as $usuario) {
                                            $usuario_id = $usuarios_id_col ? $usuario->$usuarios_id_col : null;
                                            $usuario_nome = $usuarios_nome_col ? $usuario->$usuarios_nome_col : 'Usuário';
                                            $selected = set_value('usuarios_id', $result->usuarios_id ?? '') == $usuario_id ? 'selected' : '';
                                            echo '<option value="' . $usuario_id . '" ' . $selected . '>' . $usuario_nome . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="5" class="span12"><?php echo set_value('observacoes', $result->observacoes ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-success"><span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Salvar Alterações</span></button>
                            <a href="<?= base_url() ?>index.php/processos" class="button btn btn-warning"><span class="button__icon"><i class='bx bx-arrow-back'></i></span><span class="button__text2">Cancelar</span></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Máscara para número de processo (aceita formatado ou limpo)
        $('#numeroProcesso').on('blur', function() {
            var numero = $(this).val().replace(/[^0-9]/g, '');
            if (numero.length == 20) {
                // Formatar automaticamente se tiver 20 dígitos
                var formatado = numero.substring(0, 7) + '-' + 
                               numero.substring(7, 9) + '.' + 
                               numero.substring(9, 13) + '.' + 
                               numero.substring(13, 14) + '.' + 
                               numero.substring(14, 16) + '.' + 
                               numero.substring(16, 20);
                $(this).val(formatado);
            }
        });

        // Máscara para valor da causa
        $('#valorCausa').mask('#.##0,00', {reverse: true});
    });
</script>

