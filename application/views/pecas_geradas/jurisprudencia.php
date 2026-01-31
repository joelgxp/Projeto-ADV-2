<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-balance-scale"></i></span>
        <h5>Base de Jurisprudencia (RAG)</h5>
    </div>
    <p class="muted">Decisoes verificadas usadas pela IA ao gerar peticoes. Nunca inventa precedentes.</p>
    <div class="span12" style="margin-left: 0">
        <a href="<?= site_url('pecas-geradas/adicionar-jurisprudencia') ?>" class="button btn btn-mini btn-success">Adicionar Jurisprudencia</a>
        <a href="<?= site_url('pecas-geradas/listar') ?>" class="button btn btn-mini">Voltar as Peticoes</a>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tribunal</th>
                        <th>Processo</th>
                        <th>Area</th>
                        <th>Assunto</th>
                        <th>Trecho</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="7">Nenhuma jurisprudencia cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= $r->id ?></td>
                                <td><?= htmlspecialchars($r->tribunal ?? '-') ?></td>
                                <td><?= htmlspecialchars($r->numero_processo ?? '-') ?></td>
                                <td><?= htmlspecialchars($r->area ?? '-') ?></td>
                                <td><?= htmlspecialchars(mb_substr($r->assunto ?? '', 0, 50)) ?></td>
                                <td><?= htmlspecialchars(mb_substr($r->trecho ?? '', 0, 100)) ?>...</td>
                                <td><?= $r->data ? date('d/m/Y', strtotime($r->data)) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
