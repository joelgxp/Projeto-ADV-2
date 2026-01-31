<div class="widget-box">
    <div class="widget-title" style="margin: 0;font-size: 1.1em">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab1">Meus Tickets</a></li>
        </ul>
    </div>
    <div class="widget-content tab-content">
        <div id="tab1" class="tab-pane active" style="min-height: 300px">
            <div style="margin-bottom: 20px; text-align: right;">
                <a href="<?php echo base_url() ?>index.php/tickets/abrir" class="btn btn-success">
                    <i class='bx bx-plus'></i> Abrir Novo Ticket
                </a>
            </div>

            <!-- Filtros -->
            <div class="dashboard-filters" style="margin-bottom: 20px;">
                <h5>Filtros</h5>
                <form method="get" action="<?php echo base_url() ?>index.php/tickets">
                    <div class="filter-group">
                        <div class="filter-item">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="aberto" <?php echo (isset($_GET['status']) && $_GET['status'] == 'aberto') ? 'selected' : ''; ?>>Aberto</option>
                                <option value="em_andamento" <?php echo (isset($_GET['status']) && $_GET['status'] == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="respondido" <?php echo (isset($_GET['status']) && $_GET['status'] == 'respondido') ? 'selected' : ''; ?>>Respondido</option>
                                <option value="fechado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'fechado') ? 'selected' : ''; ?>>Fechado</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-filter-primary">Filtrar</button>
                        <a href="<?php echo base_url() ?>index.php/tickets" class="btn-filter btn-filter-secondary">Limpar</a>
                    </div>
                </form>
            </div>

            <!-- Lista de Tickets -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Assunto</th>
                        <th>Processo</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th>Data Abertura</th>
                        <th>Última Resposta</th>
                        <th style="text-align:center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($tickets) && $tickets && count($tickets) > 0) : ?>
                        <?php foreach ($tickets as $ticket) : 
                            $status_labels = [
                                'aberto' => ['label' => 'Aberto', 'cor' => '#436eee'],
                                'em_andamento' => ['label' => 'Em Andamento', 'cor' => '#FF7F00'],
                                'respondido' => ['label' => 'Respondido', 'cor' => '#4d9c79'],
                                'fechado' => ['label' => 'Fechado', 'cor' => '#808080'],
                            ];
                            $status = $ticket->status ?? 'aberto';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];

                            $prioridade_labels = [
                                'baixa' => ['label' => 'Baixa', 'cor' => '#808080'],
                                'normal' => ['label' => 'Normal', 'cor' => '#436eee'],
                                'alta' => ['label' => 'Alta', 'cor' => '#FF7F00'],
                                'urgente' => ['label' => 'Urgente', 'cor' => '#CD0000'],
                            ];
                            $prioridade = $ticket->prioridade ?? 'normal';
                            $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => ucfirst($prioridade), 'cor' => '#E0E4CC'];
                        ?>
                        <tr>
                            <td>#<?php echo $ticket->id; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ticket->assunto); ?></strong>
                                <?php if (!$ticket->lido_cliente && $ticket->status == 'respondido') : ?>
                                    <span class="badge badge-danger" style="margin-left: 5px;">Novo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($ticket->numeroProcesso)) : ?>
                                    <a href="<?php echo base_url() ?>index.php/mine/visualizarProcesso/<?php echo $ticket->processos_id; ?>">
                                        <?php echo htmlspecialchars($ticket->numeroProcesso); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?php echo $status_info['cor']; ?>; border-color: <?php echo $status_info['cor']; ?>">
                                    <?php echo $status_info['label']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?php echo $prioridade_info['cor']; ?>; border-color: <?php echo $prioridade_info['cor']; ?>">
                                    <?php echo $prioridade_info['label']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket->data_abertura)); ?></td>
                            <td>
                                <?php if ($ticket->data_resposta) : ?>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket->data_resposta)); ?>
                                <?php else : ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center">
                                <a href="<?php echo base_url() ?>index.php/tickets/visualizar/<?php echo $ticket->id; ?>" class="btn btn-info btn-mini" title="Visualizar">
                                    <i class='bx bx-eye'></i>
                                </a>
                                <?php if ($ticket->status != 'fechado') : ?>
                                    <a href="<?php echo base_url() ?>index.php/tickets/fechar/<?php echo $ticket->id; ?>" class="btn btn-warning btn-mini" title="Fechar" onclick="return confirm('Deseja realmente fechar este ticket?');">
                                        <i class='bx bx-x'></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="bx bx-message"></i></div>
                                    <div class="empty-state-message">Nenhum ticket encontrado.</div>
                                    <a href="<?php echo base_url() ?>index.php/tickets/abrir" class="btn btn-success" style="margin-top: 15px;">
                                        <i class='bx bx-plus'></i> Abrir Primeiro Ticket
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

