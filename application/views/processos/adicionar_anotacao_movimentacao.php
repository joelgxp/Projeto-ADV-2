<!-- Modal para adicionar anotação a movimentação -->
<div id="modal-anotacao-movimentacao" class="modal hide fade" tabindex="-1" role="dialog">
    <form id="form-anotacao-movimentacao" method="post" action="<?= site_url('processos/adicionar_anotacao_movimentacao') ?>">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5>Adicionar Anotação à Movimentação</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="movimentacao_id_anotacao" name="movimentacao_id" value="" />
            <input type="hidden" name="processo_id" value="<?= isset($result->idProcessos) ? $result->idProcessos : ($result->id ?? 0) ?>" />
            
            <div class="control-group">
                <label for="anotacao_texto" class="control-label">Anotação</label>
                <div class="controls">
                    <textarea id="anotacao_texto" name="anotacao" class="span12" rows="5" placeholder="Adicione uma anotação sobre esta movimentação..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar Anotação</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Abrir modal de anotação
    $(document).on('click', '.btn-anotacao-movimentacao', function() {
        var movimentacaoId = $(this).data('id');
        $('#movimentacao_id_anotacao').val(movimentacaoId);
        $('#modal-anotacao-movimentacao').modal('show');
    });
    
    // Salvar anotação
    $('#form-anotacao-movimentacao').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Anotação salva com sucesso!');
                    $('#modal-anotacao-movimentacao').modal('hide');
                    location.reload();
                } else {
                    alert('Erro ao salvar anotação: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function() {
                alert('Erro ao salvar anotação.');
            }
        });
    });
});
</script>

