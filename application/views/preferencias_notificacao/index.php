<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-cog"></i></span>
                <h5>Preferências de Notificação</h5>
            </div>
            <div class="widget-content nopadding">
                <div style="padding: 20px;">
                    <form method="post" action="<?= site_url('preferencias_notificacao') ?>" class="form-horizontal">
                        <h4>Notificações por E-mail</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Habilitado</th>
                                    <th>Dias Antes (Prazos)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categorias = ['movimentacao', 'prazo', 'fatura', 'interacao', 'sistema'];
                                foreach ($categorias as $categoria):
                                    $pref = null;
                                    foreach ($preferencias as $p) {
                                        if ($p->tipo_notificacao == 'email' && $p->categoria == $categoria) {
                                            $pref = $p;
                                            break;
                                        }
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= ucfirst(str_replace('_', ' ', $categoria)) ?></strong>
                                        <input type="hidden" name="preferencias[<?= $categoria ?>][tipo_notificacao]" value="email">
                                        <input type="hidden" name="preferencias[<?= $categoria ?>][categoria]" value="<?= $categoria ?>">
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               name="preferencias[<?= $categoria ?>][habilitado]" 
                                               value="1" 
                                               <?= (!$pref || $pref->habilitado) ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <?php if ($categoria == 'prazo'): ?>
                                            <select name="preferencias[<?= $categoria ?>][dias_antes_prazo]" class="span12">
                                                <option value="">Padrão (7, 5, 2, 1)</option>
                                                <option value="7" <?= ($pref && $pref->dias_antes_prazo == 7) ? 'selected' : '' ?>>7 dias antes</option>
                                                <option value="5" <?= ($pref && $pref->dias_antes_prazo == 5) ? 'selected' : '' ?>>5 dias antes</option>
                                                <option value="2" <?= ($pref && $pref->dias_antes_prazo == 2) ? 'selected' : '' ?>>2 dias antes</option>
                                                <option value="1" <?= ($pref && $pref->dias_antes_prazo == 1) ? 'selected' : '' ?>>1 dia antes</option>
                                            </select>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Preferências
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Salvar preferências via AJAX (opcional)
    $('form').on('submit', function(e) {
        // Form submit normal - deixar processar normalmente
    });
});
</script>

