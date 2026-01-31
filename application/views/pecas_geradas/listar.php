<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-file-alt"></i></span>
        <h5>Petições Geradas com IA</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/gerar') ?>" class="button btn btn-mini btn-success" style="max-width: 200px">
                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                <span class="button__text2">Gerar Petição com IA</span>
            </a>
        </div>
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/modelos') ?>" class="button btn btn-mini btn-info" style="max-width: 180px">
                <span class="button__icon"><i class='bx bx-file'></i></span>
                <span class="button__text2">Modelos de Peças</span>
            </a>
        </div>
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/jurisprudencia') ?>" class="button btn btn-mini" style="max-width: 180px">
                <span class="button__icon"><i class='bx bx-balance-scale'></i></span>
                <span class="button__text2">Jurisprudencia</span>
            </a>
        </div>
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/dashboard') ?>" class="button btn btn-mini btn-warning" style="max-width: 180px">
                <span class="button__icon"><i class='bx bx-bar-chart'></i></span>
                <span class="button__text2">Metricas</span>
            </a>
        </div>
        <div class="span3">
            <a href="<?= site_url('pecas-geradas/diagnostico') ?>" class="button btn btn-mini" style="max-width: 180px" title="Diagnóstico do ambiente (produção vs local)">
                <span class="button__icon"><i class='bx bx-cog'></i></span>
                <span class="button__text2">Diagnóstico</span>
            </a>
        </div>
        <form class="span6" method="get" action="<?= site_url('pecas-geradas/listar') ?>" style="display: flex; justify-content: flex-end;">
            <div class="span2">
                <select name="tipo" class="span12">
                    <option value="">Todos os Tipos</option>
                    <?php foreach ($tipos_peca ?? [] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($filtros['tipo'] ?? '') == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="span2">
                <select name="status" class="span12">
                    <option value="">Todos os Status</option>
                    <option value="rascunho_ia" <?= ($filtros['status'] ?? '') == 'rascunho_ia' ? 'selected' : '' ?>>Rascunho IA</option>
                    <option value="em_revisao" <?= ($filtros['status'] ?? '') == 'em_revisao' ? 'selected' : '' ?>>Em Revisão</option>
                    <option value="aprovado" <?= ($filtros['status'] ?? '') == 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="reprovado" <?= ($filtros['status'] ?? '') == 'reprovado' ? 'selected' : '' ?>>Reprovado</option>
                </select>
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" type="submit"><i class='bx bx-search-alt'></i></button>
            </div>
        </form>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Processo / Cliente</th>
                        <th>Status</th>
                        <th>Gerador</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="7">Nenhuma petição gerada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                            <?php
                            $status_labels = [
                                'rascunho_ia' => ['label' => 'Rascunho IA', 'class' => 'label-warning'],
                                'em_revisao' => ['label' => 'Em Revisão', 'class' => 'label-info'],
                                'aprovado' => ['label' => 'Aprovado', 'class' => 'label-success'],
                                'reprovado' => ['label' => 'Reprovado', 'class' => 'label-danger'],
                            ];
                            $st = $r->status ?? 'rascunho_ia';
                            $st_info = $status_labels[$st] ?? ['label' => ucfirst($st), 'class' => 'label-default'];
                            $vinculo = $r->numeroProcesso ? 'Processo ' . $r->numeroProcesso : ($r->nomeCliente ?? '-');
                            ?>
                            <tr>
                                <td><?= $r->id ?></td>
                                <td><?= $tipos_peca[$r->tipo_peca] ?? $r->tipo_peca ?></td>
                                <td><?= htmlspecialchars($vinculo) ?></td>
                                <td><span class="label <?= $st_info['class'] ?>"><?= $st_info['label'] ?></span></td>
                                <td><?= $r->nomeGerador ?? '-' ?></td>
                                <td><?= $r->dataCadastro ? date('d/m/Y H:i', strtotime($r->dataCadastro)) : '-' ?></td>
                                <td>
                                    <a href="<?= site_url('pecas-geradas/visualizar/' . $r->id) ?>" class="btn btn-mini btn-info" title="Visualizar/Editar"><i class="bx bx-show"></i></a>
                                    <?php if ($r->status === 'aprovado'): ?>
                                        <a href="<?= site_url('pecas-geradas/exportar/' . $r->id) ?>?formato=txt" class="btn btn-mini btn-success" title="Exportar"><i class="bx bx-download"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (isset($configuration) && !empty($configuration['total_rows'])): ?>
        <div class="pagination alternate"><?= $this->pagination->create_links() ?></div>
    <?php endif; ?>
</div>
