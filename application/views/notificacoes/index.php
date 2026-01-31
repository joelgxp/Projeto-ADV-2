<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-bell"></i></span>
                <h5>Notificações</h5>
                <div style="float: right; margin-top: 5px;">
                    <button id="marcar-todas-lidas" class="btn btn-info btn-mini">
                        <i class="fas fa-check-double"></i> Marcar Todas como Lidas
                    </button>
                </div>
            </div>
            <div class="widget-content nopadding">
                <div style="padding: 20px;">
                    <?php if (empty($notificacoes)): ?>
                        <p class="text-center" style="padding: 20px; color: #999;">
                            <i class="fas fa-bell-slash" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                            Nenhuma notificação encontrada.
                        </p>
                    <?php else: ?>
                        <div class="notificacoes-list">
                            <?php foreach ($notificacoes as $notif): ?>
                                <div class="notificacao-item <?= $notif->lida ? 'lida' : 'nao-lida' ?>" data-id="<?= $notif->id ?>">
                                    <div class="notificacao-header">
                                        <h6 style="margin: 0;">
                                            <?php if (!$notif->lida): ?>
                                                <span class="badge badge-info" style="margin-right: 5px;">Nova</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($notif->titulo) ?>
                                        </h6>
                                        <small style="color: #999;">
                                            <?= date('d/m/Y H:i', strtotime($notif->created_at)) ?>
                                            <?php if ($notif->categoria): ?>
                                                | <span class="badge badge-secondary"><?= ucfirst($notif->categoria) ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="notificacao-body">
                                        <p><?= nl2br(htmlspecialchars($notif->mensagem)) ?></p>
                                        <?php if ($notif->url): ?>
                                            <a href="<?= $notif->url ?>" class="btn btn-mini btn-primary">
                                                <i class="fas fa-external-link-alt"></i> Ver Detalhes
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!$notif->lida): ?>
                                            <button class="btn btn-mini btn-success marcar-lida" data-id="<?= $notif->id ?>">
                                                <i class="fas fa-check"></i> Marcar como Lida
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notificacao-item {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
    background: #fff;
}
.notificacao-item.nao-lida {
    border-left: 4px solid #2196F3;
    background: #f0f8ff;
}
.notificacao-item.lida {
    opacity: 0.7;
}
.notificacao-header {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
.notificacao-body p {
    margin: 10px 0;
}
</style>

<script>
$(document).ready(function() {
    $('.marcar-lida').click(function() {
        var id = $(this).data('id');
        var btn = $(this);
        
        $.ajax({
            url: '<?= site_url("notificacoes/marcar_lida") ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    btn.closest('.notificacao-item').removeClass('nao-lida').addClass('lida');
                    btn.remove();
                    location.reload(); // Recarregar para atualizar contador
                }
            }
        });
    });
    
    $('#marcar-todas-lidas').click(function() {
        if (!confirm('Deseja marcar todas as notificações como lidas?')) {
            return;
        }
        
        $.ajax({
            url: '<?= site_url("notificacoes/marcar_todas_lidas") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Todas as notificações foram marcadas como lidas.');
                    location.reload();
                }
            }
        });
    });
});
</script>

