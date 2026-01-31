<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-file-alt"></i></span>
        <h5>Petição Gerada - <?= $tipos_peca[$peca->tipo_peca] ?? $peca->tipo_peca ?> (ID: <?= $peca->id ?>)</h5>
    </div>

    <div class="alert alert-info">
        <strong>Aviso:</strong> Conteúdo gerado com auxílio de IA. Revisão e responsabilidade final são exclusivas do advogado responsável.
    </div>

    <?php
    $status_labels = [
        'rascunho_ia' => ['label' => 'Rascunho IA', 'class' => 'label-warning'],
        'em_revisao' => ['label' => 'Em Revisão', 'class' => 'label-info'],
        'aprovado' => ['label' => 'Aprovado', 'class' => 'label-success'],
        'reprovado' => ['label' => 'Reprovado', 'class' => 'label-danger'],
    ];
    $st = $peca->status ?? 'rascunho_ia';
    $st_info = $status_labels[$st] ?? ['label' => ucfirst($st), 'class' => 'label-default'];
    ?>
    <p>Status: <span class="label <?= $st_info['class'] ?>"><?= $st_info['label'] ?></span></p>

    <?php if ($peca->status !== 'aprovado'): ?>
    <div class="widget-box">
        <div class="widget-title"><h5>Editor</h5></div>
        <div class="widget-content">
            <form method="post" action="<?= site_url('pecas-geradas/salvar-edicao/' . $peca->id) ?>">
                <textarea name="conteudo" rows="20" class="span12" style="font-family: monospace;"><?= htmlspecialchars($conteudo_atual ?? '') ?></textarea>
                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">Salvar edição</button>
                    <button type="button" class="btn btn-info" id="btnRefinar">Refinar com IA</button>
                </div>
            </form>
        </div>
    </div>

    <div class="widget-box" id="refinarBox" style="display:none;">
        <div class="widget-title"><h5>Refinar com IA</h5></div>
        <div class="widget-content">
            <select id="refinarInstrucao" class="span6">
                <option value="Simplifique a linguagem">Simplificar linguagem</option>
                <option value="Deixe mais objetivo">Deixar mais objetivo</option>
                <option value="Aumente a fundamentação jurídica">Aumentar fundamentação</option>
                <option value="Reforce o argumento principal">Reforçar argumento principal</option>
            </select>
            <button type="button" class="btn btn-success" id="btnExecutarRefinar">Executar</button>
            <button type="button" class="btn" id="btnCancelarRefinar">Cancelar</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="widget-box">
        <div class="widget-title"><h5>Checklist de Revisão</h5></div>
        <div class="widget-content">
            <?php if ($peca->status !== 'aprovado'): ?>
            <form method="post" action="<?= site_url('pecas-geradas/salvar-checklist/' . $peca->id) ?>">
                <?php foreach ($itens_checklist ?? [] as $key => $label): ?>
                    <label class="checkbox">
                        <input type="checkbox" name="checklist[<?= $key ?>]" value="1" <?= !empty($checklist_marcados[$key]) ? 'checked' : '' ?>>
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-mini">Salvar checklist</button>
                </div>
            </form>
            <?php if ($pode_aprovar && $checklist_completo): ?>
            <form method="post" action="<?= site_url('pecas-geradas/aprovar/' . $peca->id) ?>" style="margin-top: 15px;">
                <button type="submit" class="btn btn-success">Aprovar peça</button>
            </form>
            <?php elseif ($pode_aprovar && !$checklist_completo): ?>
            <p class="text-warning">Marque todos os itens do checklist antes de aprovar.</p>
            <?php endif; ?>
            <?php else: ?>
            <p class="text-success">Checklist concluído. Peça aprovada em <?= $peca->data_aprovacao ? date('d/m/Y H:i', strtotime($peca->data_aprovacao)) : '-' ?> por <?= $peca->nomeAprovador ?? '-' ?>.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($peca->status === 'aprovado'): ?>
    <div class="widget-box">
        <a href="<?= site_url('pecas-geradas/exportar/' . $peca->id) ?>?formato=txt" class="btn btn-success">Exportar (TXT)</a>
    </div>
    <?php endif; ?>

    <div class="widget-box">
        <div class="widget-title"><h5>Versões</h5></div>
        <div class="widget-content">
            <?php if ($versao_ia): ?>
            <p><strong>Versão IA (original):</strong></p>
            <pre style="max-height: 300px; overflow: auto; white-space: pre-wrap;"><?= htmlspecialchars($versao_ia->conteudo ?? '') ?></pre>
            <?php endif; ?>
            <?php if ($versao_aprovada && $peca->status === 'aprovado'): ?>
            <p><strong>Versão final aprovada:</strong></p>
            <pre style="max-height: 300px; overflow: auto; white-space: pre-wrap;"><?= htmlspecialchars($versao_aprovada->conteudo ?? '') ?></pre>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 15px;">
        <a href="<?= site_url('pecas-geradas/listar') ?>" class="btn">Voltar</a>
    </div>
</div>

<?php if ($peca->status !== 'aprovado'): ?>
<script>
document.getElementById('btnRefinar').onclick = function() {
    document.getElementById('refinarBox').style.display = 'block';
};
document.getElementById('btnCancelarRefinar').onclick = function() {
    document.getElementById('refinarBox').style.display = 'none';
};
document.getElementById('btnExecutarRefinar').onclick = function() {
    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Processando...';
    var conteudo = document.querySelector('textarea[name="conteudo"]').value;
    var instrucao = document.getElementById('refinarInstrucao').value;
    $.ajax({
        url: '<?= site_url('pecas-geradas/refinar') ?>',
        type: 'POST',
        data: { id: <?= (int)$peca->id ?>, instrucao: instrucao, conteudo: conteudo },
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .done(function(data) {
        if (data.sucesso) {
            document.querySelector('textarea[name="conteudo"]').value = data.conteudo;
            document.getElementById('refinarBox').style.display = 'none';
            alert('Texto refinado com sucesso.');
        } else {
            alert('Erro: ' + (data.erro || 'Erro ao refinar.'));
        }
    })
    .fail(function() {
        alert('Erro de conexão.');
    })
    .always(function() {
        btn.disabled = false;
        btn.textContent = 'Executar';
    });
};
</script>
<?php endif; ?>
