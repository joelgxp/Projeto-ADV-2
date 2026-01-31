<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                <h5>Editar Pagamento - Fatura <?= $fatura->numero ?></h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                
                <form action="<?php echo current_url(); ?>" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">Data de Pagamento*</label>
                        <div class="controls">
                            <input type="date" name="data_pagamento" value="<?= $result->data_pagamento ?>" class="span12" required>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Valor (R$)*</label>
                        <div class="controls">
                            <input type="text" name="valor" id="valor" 
                                value="<?= number_format($result->valor, 2, ',', '.') ?>" 
                                class="span12 money" placeholder="0,00" required>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Método de Pagamento*</label>
                        <div class="controls">
                            <select name="metodo_pagamento" class="span12" required>
                                <option value="dinheiro" <?= $result->metodo_pagamento == 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="pix" <?= $result->metodo_pagamento == 'pix' ? 'selected' : '' ?>>PIX</option>
                                <option value="boleto" <?= $result->metodo_pagamento == 'boleto' ? 'selected' : '' ?>>Boleto</option>
                                <option value="transferencia" <?= $result->metodo_pagamento == 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                                <option value="cartao" <?= $result->metodo_pagamento == 'cartao' ? 'selected' : '' ?>>Cartão</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Observações</label>
                        <div class="controls">
                            <textarea name="observacoes" class="span12" rows="3"><?= htmlspecialchars($result->observacoes) ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button btn btn-success">Salvar Alterações</button>
                        <a href="<?= base_url() ?>faturas/visualizar/<?= $result->faturas_id ?>" class="button btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    $('.money').maskMoney({
        thousands: '.',
        decimal: ',',
        allowZero: true
    });
});
</script>

