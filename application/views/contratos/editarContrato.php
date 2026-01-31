<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-contract"></i></span>
                <h5>Editar Contrato</h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">Cliente*</label>
                        <div class="controls">
                            <select name="clientes_id" id="clientes_id" class="span12" required>
                                <option value="">Selecione um cliente</option>
                                <?php if (isset($clientes) && $clientes) { ?>
                                    <?php foreach ($clientes as $cliente) { 
                                        $id = is_array($cliente) ? $cliente['idClientes'] : (isset($cliente->idClientes) ? $cliente->idClientes : null);
                                        $nome = is_array($cliente) ? $cliente['nomeCliente'] : (isset($cliente->nomeCliente) ? $cliente->nomeCliente : '');
                                        if ($id) {
                                    ?>
                                        <option value="<?= $id ?>" 
                                            <?= $result->clientes_id == $id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nome) ?>
                                        </option>
                                    <?php 
                                        }
                                    } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Tipo de Contrato*</label>
                        <div class="controls">
                            <select name="tipo" id="tipo" class="span12" required>
                                <option value="fixo" <?= $result->tipo == 'fixo' ? 'selected' : '' ?>>Honorário Fixo</option>
                                <option value="variavel" <?= $result->tipo == 'variavel' ? 'selected' : '' ?>>Honorário Variável</option>
                                <option value="sucumbencia" <?= $result->tipo == 'sucumbencia' ? 'selected' : '' ?>>Sucumbência</option>
                                <option value="misto" <?= $result->tipo == 'misto' ? 'selected' : '' ?>>Misto</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Data de Início*</label>
                        <div class="controls">
                            <input type="date" name="data_inicio" value="<?= $result->data_inicio ?>" class="span12" required>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Data de Fim</label>
                        <div class="controls">
                            <input type="date" name="data_fim" value="<?= $result->data_fim ?>" class="span12">
                        </div>
                    </div>

                    <div class="control-group" id="valor_fixo_group">
                        <label class="control-label">Valor Fixo (R$)</label>
                        <div class="controls">
                            <input type="text" name="valor_fixo" id="valor_fixo" 
                                value="<?= $result->valor_fixo ? number_format($result->valor_fixo, 2, ',', '.') : '' ?>" 
                                class="span12 money" placeholder="0,00">
                        </div>
                    </div>

                    <div class="control-group" id="percentual_sucumbencia_group" style="display:none;">
                        <label class="control-label">Percentual de Sucumbência (%)</label>
                        <div class="controls">
                            <input type="number" name="percentual_sucumbencia" id="percentual_sucumbencia" 
                                value="<?= $result->percentual_sucumbencia ?>" 
                                class="span12" step="0.01" min="0" max="100">
                        </div>
                    </div>

                    <div class="control-group" id="percentual_exito_group" style="display:none;">
                        <label class="control-label">Percentual de Êxito (%)</label>
                        <div class="controls">
                            <input type="number" name="percentual_exito" id="percentual_exito" 
                                value="<?= $result->percentual_exito ?>" 
                                class="span12" step="0.01" min="0" max="100">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Ativar Contrato</label>
                        <div class="controls">
                            <label>
                                <input type="checkbox" name="ativo" value="1" <?= $result->ativo ? 'checked' : '' ?>>
                                Ativar este contrato (desativará outros do mesmo cliente)
                            </label>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Observações</label>
                        <div class="controls">
                            <textarea name="observacoes" class="span12" rows="4"><?= htmlspecialchars($result->observacoes) ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button btn btn-success">Salvar Alterações</button>
                        <a href="<?= base_url() ?>index.php/contratos" class="button btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar campos conforme tipo atual
    var tipo = $('#tipo').val();
    toggleCampos(tipo);

    // Mostrar/ocultar campos conforme tipo
    $('#tipo').change(function() {
        toggleCampos($(this).val());
    });

    function toggleCampos(tipo) {
        $('#valor_fixo_group').hide();
        $('#percentual_sucumbencia_group').hide();
        $('#percentual_exito_group').hide();

        if (tipo === 'fixo' || tipo === 'misto') {
            $('#valor_fixo_group').show();
        }
        if (tipo === 'variavel' || tipo === 'misto') {
            $('#percentual_exito_group').show();
        }
        if (tipo === 'sucumbencia' || tipo === 'misto') {
            $('#percentual_sucumbencia_group').show();
        }
    }

    // Formatação monetária
    $('.money').maskMoney({
        thousands: '.',
        decimal: ',',
        allowZero: true
    });
});
</script>

