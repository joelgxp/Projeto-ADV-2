<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-chart-bar"></i></span>
        <h5>Dashboard - Metricas Peticoes IA</h5>
    </div>

    <form method="get" action="<?= site_url('pecas-geradas/dashboard') ?>" class="form-inline" style="margin: 15px 0;">
        <label>Periodo:</label>
        <input type="date" name="inicio" value="<?= $periodo_inicio ?? '' ?>" class="span2">
        <input type="date" name="fim" value="<?= $periodo_fim ?? '' ?>" class="span2">
        <select name="tipo" class="span3">
            <option value="">Todos os tipos</option>
            <?php foreach ($tipos_peca ?? [] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($tipo ?? '') == $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="advogado" class="span3">
            <option value="">Todos os advogados</option>
            <?php foreach ($usuarios ?? [] as $u): ?>
                <option value="<?= $u->idUsuarios ?? $u->id ?>" <?= ($advogado_id ?? '') == ($u->idUsuarios ?? $u->id) ? 'selected' : '' ?>><?= htmlspecialchars($u->nome ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-info">Filtrar</button>
    </form>

    <div class="row-fluid">
        <div class="span3">
            <div class="widget-box">
                <div class="widget-content">
                    <h4>Pecas Geradas</h4>
                    <p style="font-size: 2em; font-weight: bold;"><?= $total_geradas ?? 0 ?></p>
                    <small>no periodo</small>
                </div>
            </div>
        </div>
        <div class="span3">
            <div class="widget-box">
                <div class="widget-content">
                    <h4>Pecas Aprovadas</h4>
                    <p style="font-size: 2em; font-weight: bold;"><?= $total_aprovadas ?? 0 ?></p>
                    <small><?= $percentual_aprovadas ?? 0 ?>% do total</small>
                </div>
            </div>
        </div>
        <div class="span3">
            <div class="widget-box">
                <div class="widget-content">
                    <h4>Tempo Medio (geracao a aprovacao)</h4>
                    <p style="font-size: 2em; font-weight: bold;"><?= $tempo_medio_horas ?? 0 ?>h</p>
                    <small>em horas</small>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 15px;">
        <a href="<?= site_url('pecas-geradas/listar') ?>" class="btn">Voltar</a>
    </div>
</div>
