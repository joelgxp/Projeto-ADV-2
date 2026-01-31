<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-info-circle"></i></span>
                <h5>Detalhes do Log de Auditoria #<?= $log->idLogs ?></h5>
            </div>
            <div class="widget-content nopadding">
                <div style="padding: 20px;">
                    <div class="row-fluid">
                        <div class="span6">
                            <h4>Informações Básicas</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">ID</th>
                                    <td><?= $log->idLogs ?></td>
                                </tr>
                                <tr>
                                    <th>Usuário</th>
                                    <td><?= htmlspecialchars($log->usuario) ?></td>
                                </tr>
                                <tr>
                                    <th>Data/Hora</th>
                                    <td>
                                        <?= date('d/m/Y', strtotime($log->data)) ?>
                                        <?= $log->hora ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>IP</th>
                                    <td><?= htmlspecialchars($log->ip) ?></td>
                                </tr>
                                <tr>
                                    <th>User Agent</th>
                                    <td><small><?= htmlspecialchars($log->user_agent ?? 'N/A') ?></small></td>
                                </tr>
                            </table>
                        </div>
                        <div class="span6">
                            <h4>Ação Realizada</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Ação</th>
                                    <td>
                                        <?php if ($log->acao): ?>
                                            <span class="badge-acao badge-<?= strtolower($log->acao) ?>">
                                                <?= strtoupper($log->acao) ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Módulo</th>
                                    <td><?= $log->entidade_tipo ? htmlspecialchars(ucfirst($log->entidade_tipo)) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Registro ID</th>
                                    <td><?= $log->entidade_id ? $log->entidade_id : '-' ?></td>
                                </tr>
                                <tr>
                                    <th>Tarefa</th>
                                    <td><?= htmlspecialchars($log->tarefa) ?></td>
                                </tr>
                                <tr>
                                    <th>Dados Sensíveis</th>
                                    <td>
                                        <?php if (isset($log->dados_sensiveis) && $log->dados_sensiveis): ?>
                                            <span class="badge-sensivel">SIM</span>
                                        <?php else: ?>
                                            <span style="color: #999;">Não</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if (!empty($dados_anteriores) || !empty($dados_novos)): ?>
                    <div class="row-fluid" style="margin-top: 20px;">
                        <div class="span12">
                            <h4>Dados da Alteração</h4>
                            
                            <?php if (!empty($dados_anteriores)): ?>
                            <div class="span6">
                                <h5 style="color: #dc3545;">Dados Anteriores</h5>
                                <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;"><?= htmlspecialchars(json_encode($dados_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($dados_novos)): ?>
                            <div class="span6">
                                <h5 style="color: #28a745;">Dados Novos</h5>
                                <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;"><?= htmlspecialchars(json_encode($dados_novos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row-fluid" style="margin-top: 20px;">
                        <div class="span12">
                            <a href="<?= site_url('auditoria') ?>" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

