<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                <h5>Adicionar Pagamento - Fatura <?= $fatura->numero ?></h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                
                <div style="padding: 20px;">
                    <div class="alert alert-info">
                        <strong>Fatura:</strong> <?= $fatura->numero ?><br>
                        <strong>Cliente:</strong> <?= htmlspecialchars($fatura->nomeCliente) ?><br>
                        <strong>Valor Total:</strong> R$ <?= number_format($fatura->valor_total, 2, ',', '.') ?><br>
                        <strong>Valor Pago:</strong> R$ <?= number_format($fatura->valor_pago, 2, ',', '.') ?><br>
                        <strong>Saldo Restante:</strong> R$ <?= number_format($fatura->saldo_restante, 2, ',', '.') ?>
                    </div>
                </div>

                <form action="<?php echo current_url(); ?>" method="post" class="form-horizontal">
                    <input type="hidden" name="faturas_id" value="<?= $fatura->id ?>">

                    <div class="control-group">
                        <label class="control-label">Data de Pagamento*</label>
                        <div class="controls">
                            <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>" class="span12" required>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Valor (R$)*</label>
                        <div class="controls">
                            <input type="text" name="valor" id="valor" 
                                value="<?= number_format($fatura->saldo_restante, 2, ',', '.') ?>" 
                                class="span12 money" placeholder="0,00" required>
                            <span class="help-block">Máximo: R$ <?= number_format($fatura->saldo_restante, 2, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Método de Pagamento*</label>
                        <div class="controls">
                            <select name="metodo_pagamento" class="span12" required>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferência</option>
                                <option value="cartao">Cartão</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Observações</label>
                        <div class="controls">
                            <textarea name="observacoes" class="span12" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button btn btn-success">Registrar Pagamento</button>
                        <a href="<?= base_url() ?>faturas/visualizar/<?= $fatura->id ?>" class="button btn">Cancelar</a>
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

    // Validar valor máximo
    $('form').submit(function(e) {
        var valor = parseFloat($('#valor').maskMoney('unmasked')[0]);
        var maximo = <?= $fatura->saldo_restante ?>;
        
        if (valor > maximo) {
            e.preventDefault();
            alert('O valor do pagamento não pode ser maior que o saldo restante (R$ ' + maximo.toFixed(2).replace('.', ',') + ')');
            return false;
        }
    });
});
</script>

