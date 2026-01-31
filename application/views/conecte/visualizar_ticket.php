<div class="widget-box">
    <div class="widget-title">
        <h5>
            <i class='bx bx-message-detail'></i> Ticket #<?php echo $ticket->id; ?> - <?php echo htmlspecialchars($ticket->assunto); ?>
        </h5>
    </div>
    <div class="widget-content">
        <!-- Informações do Ticket -->
        <div class="widget-box" style="margin-bottom: 20px;">
            <div class="widget-title">
                <h5>Informações do Ticket</h5>
            </div>
            <div class="widget-content">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 30%;"><strong>Status:</strong></td>
                        <td>
                            <?php
                            $status_labels = [
                                'aberto' => ['label' => 'Aberto', 'cor' => '#436eee'],
                                'em_andamento' => ['label' => 'Em Andamento', 'cor' => '#FF7F00'],
                                'respondido' => ['label' => 'Respondido', 'cor' => '#4d9c79'],
                                'fechado' => ['label' => 'Fechado', 'cor' => '#808080'],
                            ];
                            $status = $ticket->status ?? 'aberto';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                            ?>
                            <span class="badge" style="background-color: <?php echo $status_info['cor']; ?>; border-color: <?php echo $status_info['cor']; ?>">
                                <?php echo $status_info['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Prioridade:</strong></td>
                        <td>
                            <?php
                            $prioridade_labels = [
                                'baixa' => ['label' => 'Baixa', 'cor' => '#808080'],
                                'normal' => ['label' => 'Normal', 'cor' => '#436eee'],
                                'alta' => ['label' => 'Alta', 'cor' => '#FF7F00'],
                                'urgente' => ['label' => 'Urgente', 'cor' => '#CD0000'],
                            ];
                            $prioridade = $ticket->prioridade ?? 'normal';
                            $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => ucfirst($prioridade), 'cor' => '#E0E4CC'];
                            ?>
                            <span class="badge" style="background-color: <?php echo $prioridade_info['cor']; ?>; border-color: <?php echo $prioridade_info['cor']; ?>">
                                <?php echo $prioridade_info['label']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php if (isset($processo) && $processo) : ?>
                    <tr>
                        <td><strong>Processo Vinculado:</strong></td>
                        <td>
                            <a href="<?php echo base_url() ?>index.php/mine/visualizarProcesso/<?php echo $ticket->processos_id; ?>">
                                <?php 
                                $this->load->model('processos_model');
                                echo $this->processos_model->formatarNumeroProcesso($processo->numeroProcesso);
                                ?>
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Data de Abertura:</strong></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($ticket->data_abertura)); ?></td>
                    </tr>
                    <?php if ($ticket->data_resposta) : ?>
                    <tr>
                        <td><strong>Última Resposta:</strong></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($ticket->data_resposta)); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Mensagem Inicial -->
        <div class="widget-box" style="margin-bottom: 20px;">
            <div class="widget-title">
                <h5>Mensagem Inicial</h5>
            </div>
            <div class="widget-content">
                <div style="padding: 15px; background: #f9f9f9; border-radius: 5px;">
                    <p style="margin: 0; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($ticket->mensagem)); ?></p>
                </div>
            </div>
        </div>

        <!-- Respostas -->
        <div class="widget-box" style="margin-bottom: 20px;">
            <div class="widget-title">
                <h5>Respostas (<?php echo count($respostas ?? []); ?>)</h5>
            </div>
            <div class="widget-content">
                <?php if (isset($respostas) && $respostas && count($respostas) > 0) : ?>
                    <?php foreach ($respostas as $resposta) : 
                        $is_advogado = !empty($resposta->usuarios_id);
                    ?>
                        <div style="margin-bottom: 20px; padding: 15px; border-left: 4px solid <?php echo $is_advogado ? '#436eee' : '#4d9c79'; ?>; background: #f9f9f9;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong>
                                    <?php if ($is_advogado) : ?>
                                        <i class='bx bx-user'></i> <?php echo htmlspecialchars($resposta->nomeUsuario ?? 'Advogado'); ?>
                                    <?php else : ?>
                                        <i class='bx bx-user-circle'></i> Você
                                    <?php endif; ?>
                                </strong>
                                <span style="color: #666; font-size: 12px;">
                                    <?php echo date('d/m/Y H:i:s', strtotime($resposta->data_resposta)); ?>
                                </span>
                            </div>
                            <div style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($resposta->mensagem)); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bx bx-message"></i></div>
                        <div class="empty-state-message">Ainda não há respostas neste ticket.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulário de Resposta -->
        <?php if ($ticket->status != 'fechado') : ?>
        <div class="widget-box">
            <div class="widget-title">
                <h5>Responder Ticket</h5>
            </div>
            <div class="widget-content">
                <form method="post" action="<?php echo base_url() ?>index.php/tickets/responder/<?php echo $ticket->id; ?>">
                    <div class="form-group">
                        <label for="mensagem"><strong>Sua Resposta *</strong></label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="6" required 
                                  placeholder="Digite sua resposta..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class='bx bx-send'></i> Enviar Resposta
                        </button>
                        <a href="<?php echo base_url() ?>index.php/tickets" class="btn btn-default">
                            <i class='bx bx-arrow-back'></i> Voltar
                        </a>
                        <?php if ($ticket->status != 'fechado') : ?>
                            <a href="<?php echo base_url() ?>index.php/tickets/fechar/<?php echo $ticket->id; ?>" 
                               class="btn btn-warning" onclick="return confirm('Deseja realmente fechar este ticket?');">
                                <i class='bx bx-x'></i> Fechar Ticket
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php else : ?>
            <div class="alert alert-info">
                <strong>Ticket Fechado</strong>
                <p>Este ticket foi fechado em <?php echo $ticket->data_fechamento ? date('d/m/Y H:i:s', strtotime($ticket->data_fechamento)) : '-'; ?>.</p>
                <a href="<?php echo base_url() ?>index.php/tickets" class="btn btn-default">
                    <i class='bx bx-arrow-back'></i> Voltar para Tickets
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

