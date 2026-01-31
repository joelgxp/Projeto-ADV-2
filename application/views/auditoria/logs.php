<style>
  select {
    width: 70px;
  }
  .filtros-auditoria {
    background: #f5f5f5;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
  }
  .filtros-auditoria .control-group {
    margin-bottom: 10px;
  }
  .badge-acao {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
  }
  .badge-create { background: #28a745; color: white; }
  .badge-update { background: #ffc107; color: #000; }
  .badge-delete { background: #dc3545; color: white; }
  .badge-view { background: #17a2b8; color: white; }
  .badge-sensivel {
    background: #dc3545;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
  }
</style>
<div class="new122" style="margin-top: 0; min-height: 100vh">
<div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-clock"></i>
        </span>
        <h5>Logs de Auditoria</h5>
</div>

<!-- Filtros -->
<div class="filtros-auditoria">
    <form method="get" action="<?= site_url('auditoria') ?>" class="form-horizontal">
        <div class="row-fluid">
            <div class="span3">
                <label>Usuário</label>
                <select name="usuario" class="span12">
                    <option value="">Todos</option>
                    <?php if (isset($usuarios) && $usuarios): ?>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= htmlspecialchars($u->usuario) ?>" <?= (isset($filtros['usuario']) && $filtros['usuario'] == $u->usuario) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u->usuario) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="span3">
                <label>Ação</label>
                <select name="acao" class="span12">
                    <option value="">Todas</option>
                    <?php if (isset($acoes) && $acoes): ?>
                        <?php foreach ($acoes as $a): ?>
                            <option value="<?= htmlspecialchars($a->acao) ?>" <?= (isset($filtros['acao']) && $filtros['acao'] == $a->acao) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($a->acao)) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="span3">
                <label>Módulo</label>
                <select name="modulo" class="span12">
                    <option value="">Todos</option>
                    <?php if (isset($modulos) && $modulos): ?>
                        <?php foreach ($modulos as $m): ?>
                            <option value="<?= htmlspecialchars($m->modulo) ?>" <?= (isset($filtros['modulo']) && $filtros['modulo'] == $m->modulo) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($m->modulo)) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="span3">
                <label>Dados Sensíveis</label>
                <select name="dados_sensiveis" class="span12">
                    <option value="">Todos</option>
                    <option value="1" <?= (isset($filtros['dados_sensiveis']) && $filtros['dados_sensiveis'] == '1') ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= (isset($filtros['dados_sensiveis']) && $filtros['dados_sensiveis'] == '0') ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
        <div class="row-fluid" style="margin-top: 10px;">
            <div class="span3">
                <label>Data Início</label>
                <input type="date" name="data_inicio" class="span12" value="<?= isset($filtros['data_inicio']) ? htmlspecialchars($filtros['data_inicio']) : '' ?>">
            </div>
            <div class="span3">
                <label>Data Fim</label>
                <input type="date" name="data_fim" class="span12" value="<?= isset($filtros['data_fim']) ? htmlspecialchars($filtros['data_fim']) : '' ?>">
            </div>
            <div class="span4">
                <label>Busca</label>
                <input type="text" name="pesquisa" class="span12" placeholder="Buscar por tarefa, usuário ou IP..." value="<?= isset($filtros['pesquisa']) ? htmlspecialchars($filtros['pesquisa']) : '' ?>">
            </div>
            <div class="span2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary span12">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </div>
        <?php if (!empty($filtros)): ?>
        <div class="row-fluid" style="margin-top: 10px;">
            <div class="span12">
                <a href="<?= site_url('auditoria') ?>" class="btn btn-default">
                    <i class="fas fa-times"></i> Limpar Filtros
                </a>
                <a href="<?= site_url('auditoria/exportar?' . http_build_query($filtros)) ?>" class="btn btn-success">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>

  <a href="#modal-excluir" role="button" data-toggle="modal" class="button btn btn-danger tip-top" style="max-width: 250px" title="Excluir Logs">
  <span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Remover Logs - 30 dias ou mais</span></a>

<div class="widget-box">
    <h5 style="padding: 3px 0"></h5>
    <div class="widget-content nopadding tab-content">
        <table id="tabela" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Data/Hora</th>
                    <th>IP</th>
                    <th>Ação</th>
                    <th>Módulo</th>
                    <th>Tarefa</th>
                    <th>Sensível</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): ?>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= $r->idLogs ?></td>
                            <td><?= htmlspecialchars($r->usuario) ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($r->data)) ?><br>
                                <small><?= $r->hora ?></small>
                            </td>
                            <td><?= htmlspecialchars($r->ip) ?></td>
                            <td>
                                <?php if ($r->acao): ?>
                                    <?php
                                    $badge_class = 'badge-acao ';
                                    switch(strtolower($r->acao)) {
                                        case 'create': $badge_class .= 'badge-create'; break;
                                        case 'update': $badge_class .= 'badge-update'; break;
                                        case 'delete': $badge_class .= 'badge-delete'; break;
                                        case 'view': $badge_class .= 'badge-view'; break;
                                        default: $badge_class .= 'badge-view';
                                    }
                                    ?>
                                    <span class="<?= $badge_class ?>"><?= strtoupper($r->acao) ?></span>
                                <?php else: ?>
                                    <span class="badge-acao badge-view">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r->entidade_tipo): ?>
                                    <?= htmlspecialchars(ucfirst($r->entidade_tipo)) ?>
                                    <?php if ($r->entidade_id): ?>
                                        <br><small>ID: <?= $r->entidade_id ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r->tarefa) ?></td>
                            <td>
                                <?php if (isset($r->dados_sensiveis) && $r->dados_sensiveis): ?>
                                    <span class="badge-sensivel">SIM</span>
                                <?php else: ?>
                                    <span style="color: #999;">Não</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('auditoria/visualizar/' . $r->idLogs) ?>" class="btn btn-mini btn-info" title="Visualizar Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Nenhum registro encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Excluir Logs</h3>
    </div>
    <div class="modal-body">
        <p>Deseja realmente excluir todos os logs com mais de 30 dias?</p>
        <p><strong>Esta ação não pode ser desfeita!</strong></p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
        <a href="<?= site_url('auditoria/clean') ?>" class="btn btn-danger">Excluir</a>
    </div>
</div>
