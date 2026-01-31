<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script>
var itemCounter = 0;
function adicionarItem() {
    itemCounter++;
    var html = '<tr class="item-row" data-index="' + itemCounter + '">' +
        '<td><select name="itens[' + itemCounter + '][tipo_item]" class="span12" required>' +
        '<option value="honorario">Honorário</option>' +
        '<option value="custas">Custas</option>' +
        '<option value="diligencia">Diligência</option>' +
        '<option value="despesa">Despesa</option>' +
        '<option value="repasse">Repasse</option>' +
        '</select></td>' +
        '<td><input type="text" name="itens[' + itemCounter + '][descricao]" class="span12" required></td>' +
        '<td><select name="itens[' + itemCounter + '][processos_id]" class="span12"><option value="">Nenhum</option>' +
        '<?php if (isset($processos) && $processos) { foreach($processos as $p) { $id = is_object($p) ? $p->idProcessos : $p["idProcessos"]; $num = is_object($p) ? $p->numeroProcesso : $p["numeroProcesso"]; ?>' +
        '<option value="<?= $id ?>"><?= htmlspecialchars($num) ?></option>' +
        '<?php } } ?>' +
        '</select></td>' +
        '<td><input type="text" name="itens[' + itemCounter + '][valor_unitario]" class="span12 money" placeholder="0,00" required></td>' +
        '<td><input type="number" name="itens[' + itemCounter + '][quantidade]" class="span12" value="1" step="0.01" min="0.01" required></td>' +
        '<td><input type="number" name="itens[' + itemCounter + '][ipi]" class="span12" value="0" step="0.01" min="0" max="100" placeholder="%"></td>' +
        '<td><input type="number" name="itens[' + itemCounter + '][iss]" class="span12" value="0" step="0.01" min="0" max="100" placeholder="%"></td>' +
        '<td><button type="button" class="btn btn-danger btn-mini" onclick="removerItem(this)"><i class="bx bx-trash"></i></button></td>' +
        '</tr>';
    $('#itensTable tbody').append(html);
    $('.money').maskMoney({thousands: '.', decimal: ',', allowZero: true});
}

function removerItem(btn) {
    $(btn).closest('tr').remove();
}
</script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <h5>Adicionar Fatura</h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" method="post" class="form-horizontal">
                    <div class="span6">
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
                                        <option value="<?= $id ?>"><?= htmlspecialchars($nome) ?></option>
                                    <?php 
                                        }
                                    } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Contrato</label>
                        <div class="controls">
                            <select name="contratos_id" id="contratos_id" class="span12">
                                <option value="">Selecione um contrato</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group" id="processos_container" style="display:none;">
                        <label class="control-label">Processos do Cliente</label>
                        <div class="controls">
                            <select id="processos_selector" class="span12" multiple style="height: 100px;">
                                <!-- Preenchido via AJAX -->
                            </select>
                        </div>
                    </div>

                        <div class="control-group">
                            <label class="control-label">Data de Emissão*</label>
                            <div class="controls">
                                <input type="date" name="data_emissao" value="<?= date('Y-m-d') ?>" class="span12" required>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">Data de Vencimento*</label>
                            <div class="controls">
                                <input type="date" name="data_vencimento" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" class="span12" required>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">Status</label>
                            <div class="controls">
                                <select name="status" class="span12">
                                    <option value="rascunho">Rascunho</option>
                                    <option value="emitida" selected>Emitida</option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">Observações</label>
                            <div class="controls">
                                <textarea name="observacoes" class="span12" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="span12" style="margin-left: 0; margin-top: 20px;">
                        <h4>Itens da Fatura</h4>
                        <table class="table table-bordered" id="itensTable">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Processo</th>
                                    <th>Valor Unitário</th>
                                    <th>Quantidade</th>
                                    <th>IPI %</th>
                                    <th>ISS %</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Itens serão adicionados dinamicamente aqui -->
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success" onclick="adicionarItem()">
                            <i class="bx bx-plus"></i> Adicionar Item
                        </button>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="button btn btn-success">Salvar Fatura</button>
                        <a href="<?= base_url() ?>index.php/faturas" class="button btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Carregar contratos e processos quando cliente for selecionado
    $('#clientes_id').change(function() {
        var cliente_id = $(this).val();
        if (cliente_id) {
            // Carregar contratos
            $.ajax({
                url: '<?= base_url() ?>index.php/contratos/getByCliente/' + cliente_id,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#contratos_id').html('<option value="">Selecione um contrato</option>');
                    if (data.success && data.contratos) {
                        $.each(data.contratos, function(i, contrato) {
                            $('#contratos_id').append('<option value="' + contrato.id + '">' + contrato.tipo + ' - ' + contrato.data_inicio + '</option>');
                        });
                    }
                }
            });

            // Carregar processos
            $.ajax({
                url: '<?= base_url() ?>processos',
                type: 'GET',
                data: {cliente: cliente_id},
                dataType: 'json',
                success: function(data) {
                    processos = data.processos || [];
                    $('#processos_selector').html('');
                    if (processos.length > 0) {
                        $('#processos_container').show();
                        processos.forEach(function(p) {
                            var id = p.idProcessos || p['idProcessos'] || p.id;
                            var num = p.numeroProcesso || p['numeroProcesso'] || '';
                            $('#processos_selector').append('<option value="' + id + '">' + num + '</option>');
                        });
                    } else {
                        $('#processos_container').hide();
                    }
                },
                error: function() {
                    // Se não retornar JSON, tentar buscar via listagem normal
                    processos = [];
                }
            });
        } else {
            $('#contratos_id').html('<option value="">Selecione um contrato</option>');
            $('#processos_container').hide();
            processos = [];
        }
    });
    
    // Adicionar primeiro item ao carregar
    adicionarItem();
});
</script>

