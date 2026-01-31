<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-file"></i></span>
        <h5>Modelos de Peças</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/adicionar-modelo') ?>" class="button btn btn-mini btn-success" style="max-width: 180px">
                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                <span class="button__text2">Novo Modelo</span>
            </a>
        </div>
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/listar') ?>" class="button btn btn-mini">Voltar às Petições</a>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Área</th>
                        <th>Autor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="6">Nenhum modelo cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= $r->id ?></td>
                                <td><?= htmlspecialchars($r->nome ?? '') ?></td>
                                <td><?= $tipos_peca[$r->tipo_peca] ?? $r->tipo_peca ?></td>
                                <td><?= $areas[$r->area] ?? $r->area ?? '-' ?></td>
                                <td><?= $r->nomeAutor ?? '-' ?></td>
                                <td>
                                    <a href="<?= site_url('pecas-geradas/editar-modelo/' . $r->id) ?>" class="btn btn-mini btn-info"><i class="bx bx-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
