<link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
<link href='https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css' rel='stylesheet'>

<style>
/* Cards Informativos - Fase 6 Sprint 2 */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.dashboard-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.dashboard-card:hover::before {
    top: -30%;
    right: -30%;
}

.dashboard-card.card-processos {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.dashboard-card.card-prazos {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.dashboard-card.card-audiencias {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.dashboard-card.card-financeiro {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.dashboard-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.dashboard-card-icon {
    font-size: 40px;
    opacity: 0.9;
}

.dashboard-card-title {
    font-size: 14px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.dashboard-card-value {
    font-size: 36px;
    font-weight: bold;
    margin: 10px 0;
}

.dashboard-card-label {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 5px;
}

.dashboard-card-link {
    display: inline-block;
    margin-top: 15px;
    color: white;
    text-decoration: none;
    font-size: 12px;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.dashboard-card-link:hover {
    opacity: 1;
    color: white;
    text-decoration: underline;
}

.dashboard-card-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.dashboard-card-stat {
    text-align: center;
    flex: 1;
}

.dashboard-card-stat-value {
    font-size: 20px;
    font-weight: bold;
    display: block;
}

.dashboard-card-stat-label {
    font-size: 11px;
    opacity: 0.8;
    margin-top: 5px;
}

/* Filtros - Fase 6 Sprint 2 */
.dashboard-filters {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-filters h5 {
    margin-bottom: 15px;
    color: #333;
}

.filter-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-item {
    flex: 1;
    min-width: 150px;
}

.filter-item label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    color: #666;
    font-weight: 600;
}

.filter-item select,
.filter-item input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-filter {
    padding: 8px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-filter-primary {
    background: #436eee;
    color: white;
}

.btn-filter-primary:hover {
    background: #2d4fc7;
}

.btn-filter-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-filter-secondary:hover {
    background: #e0e0e0;
}

/* Loading State */
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
    border-radius: 4px;
    height: 20px;
    margin: 5px 0;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state-message {
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
    
    .filter-group {
        flex-direction: column;
    }
    
    .filter-item {
        width: 100%;
    }
}
</style>

<!-- Cards Informativos - Fase 6 Sprint 2 -->
<div class="dashboard-cards">
    <!-- Card de Processos -->
    <div class="dashboard-card card-processos">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-card-title">Processos</div>
            </div>
            <div class="dashboard-card-icon">
                <i class='bx bx-file-blank'></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php echo isset($estatisticas_processos) ? $estatisticas_processos->total : 0; ?>
        </div>
        <div class="dashboard-card-label">Total de Processos</div>
        <div class="dashboard-card-stats">
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value"><?php echo isset($estatisticas_processos) ? $estatisticas_processos->ativos : 0; ?></span>
                <div class="dashboard-card-stat-label">Ativos</div>
            </div>
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value"><?php echo isset($estatisticas_processos) ? $estatisticas_processos->aguardando_acao : 0; ?></span>
                <div class="dashboard-card-stat-label">Aguardando</div>
            </div>
        </div>
        <a href="<?php echo base_url() ?>index.php/mine/processos" class="dashboard-card-link">
            Ver todos os processos <i class='bx bx-arrow-right'></i>
        </a>
    </div>

    <!-- Card de Prazos -->
    <div class="dashboard-card card-prazos">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-card-title">Prazos</div>
            </div>
            <div class="dashboard-card-icon">
                <i class='bx bx-calendar-check'></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php echo isset($estatisticas_prazos) ? $estatisticas_prazos->total : 0; ?>
        </div>
        <div class="dashboard-card-label">Total de Prazos</div>
        <div class="dashboard-card-stats">
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value" style="color: <?php echo (isset($estatisticas_prazos) && $estatisticas_prazos->vencendo_hoje > 0) ? '#ffeb3b' : 'white'; ?>">
                    <?php echo isset($estatisticas_prazos) ? $estatisticas_prazos->vencendo_hoje : 0; ?>
                </span>
                <div class="dashboard-card-stat-label">Vencendo Hoje</div>
            </div>
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value">
                    <?php echo isset($estatisticas_prazos) ? $estatisticas_prazos->proximos_7dias : 0; ?>
                </span>
                <div class="dashboard-card-stat-label">Próximos 7 dias</div>
            </div>
        </div>
        <a href="<?php echo base_url() ?>index.php/mine/prazos" class="dashboard-card-link">
            Ver todos os prazos <i class='bx bx-arrow-right'></i>
        </a>
    </div>

    <!-- Card de Audiências -->
    <div class="dashboard-card card-audiencias">
        <div class="dashboard-card-header">
            <div>
                <div class="dashboard-card-title">Audiências</div>
            </div>
            <div class="dashboard-card-icon">
                <i class='bx bx-calendar-event'></i>
            </div>
        </div>
        <div class="dashboard-card-value">
            <?php echo isset($estatisticas_audiencias) ? $estatisticas_audiencias->total_agendadas : 0; ?>
        </div>
        <div class="dashboard-card-label">Audiências Agendadas</div>
        <?php if (isset($estatisticas_audiencias) && $estatisticas_audiencias->proxima) : ?>
        <div class="dashboard-card-label" style="margin-top: 10px;">
            <strong>Próxima:</strong> <?php echo date('d/m/Y H:i', strtotime($estatisticas_audiencias->proxima->dataHora)); ?>
        </div>
        <?php endif; ?>
        <div class="dashboard-card-stats">
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value"><?php echo isset($estatisticas_audiencias) ? $estatisticas_audiencias->esta_semana : 0; ?></span>
                <div class="dashboard-card-stat-label">Esta Semana</div>
            </div>
            <div class="dashboard-card-stat">
                <span class="dashboard-card-stat-value"><?php echo isset($estatisticas_audiencias) ? $estatisticas_audiencias->este_mes : 0; ?></span>
                <div class="dashboard-card-stat-label">Este Mês</div>
            </div>
        </div>
        <a href="<?php echo base_url() ?>index.php/mine/audiencias" class="dashboard-card-link">
            Ver todas as audiências <i class='bx bx-arrow-right'></i>
        </a>
    </div>
</div>

<!-- Menu Rápido -->
<div class="quick-actions_homepage">
    <ul class="cardBox">
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/processos">
                <div class="lord-icon04">
                    <i class='bx bx-file-blank iconBx04'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/processos">
                <div style="font-size: 1.2em" class="numbers">Processos</div>
            </a>
        </li>

        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/prazos">
                <div class="lord-icon05">
                    <i class='bx bx-calendar-check iconBx05'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/prazos">
                <div style="font-size: 1.2em" class="numbers">Prazos</div>
            </a>
        </li>
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/cobrancas">
                <div class="lord-icon05">
                    <i class='bx bx-credit-card-front iconBx05'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/cobrancas">
                <div style="font-size: 1.2em" class="numbers">Cobranças&nbsp;&nbsp;&nbsp;&nbsp;</div>
            </a>
        </li>
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/conta">
                <div class="lord-icon07">
                    <i class='bx bx-user-circle iconBx07'></i></span>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/conta">
                <div style="font-size: 1.2em" class="numbers">Minha Conta</div>
            </a>
        </li>
    </ul>
</div>

<!-- Listas de Processos e Prazos -->
<div class="span12" style="margin-left: 0">
    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Meus Processos</h5>
        </div>
        <div class="widget-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nº Processo</th>
                        <th>Classe</th>
                        <th>Assunto</th>
                        <th>Advogado</th>
                        <th>Status</th>
                        <th>Última Movimentação</th>
                        <th style="text-align:right">Visualizar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($processos) && $processos != null && count($processos) > 0) {
                        $this->load->model('processos_model');
                        foreach ($processos as $p) {
                            $numeroFormatado = $this->processos_model->formatarNumeroProcesso($p->numeroProcesso);
                            
                            $status_labels = [
                                'em_andamento' => ['label' => 'Em Andamento', 'cor' => '#436eee'],
                                'suspenso' => ['label' => 'Suspenso', 'cor' => '#FF7F00'],
                                'arquivado' => ['label' => 'Arquivado', 'cor' => '#808080'],
                                'finalizado' => ['label' => 'Finalizado', 'cor' => '#256'],
                            ];
                            $status = $p->status ?? 'em_andamento';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];

                            echo '<tr>';
                            echo '<td><a href="' . base_url() . 'index.php/mine/visualizarProcesso/' . $p->idProcessos . '">' . $numeroFormatado . '</a></td>';
                            echo '<td>' . ($p->classe ?? '-') . '</td>';
                            echo '<td>' . ($p->assunto ?? '-') . '</td>';
                            echo '<td>' . (isset($p->nomeAdvogado) ? $p->nomeAdvogado : '-') . '</td>';
                            echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                            echo '<td>' . (isset($p->dataUltimaMovimentacao) ? date('d/m/Y', strtotime($p->dataUltimaMovimentacao)) : '-') . '</td>';
                            echo '<td style="text-align:right">';
                            echo '<a href="' . base_url() . 'index.php/mine/visualizarProcesso/' . $p->idProcessos . '" class="btn"> <i class="fas fa-eye" ></i></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon"><i class="bx bx-file"></i></div><div class="empty-state-message">Nenhum processo encontrado.</div></div></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon"><i class="fas fa-calendar-check"></i></span>
            <h5>Prazos Próximos</h5>
        </div>
        <div class="widget-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Processo</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th>Data Vencimento</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($prazos) && $prazos != null && count($prazos) > 0) {
                        foreach ($prazos as $pz) {
                            $dataVenc = strtotime($pz->dataVencimento);
                            $hoje = strtotime(date('Y-m-d'));
                            $diasRestantes = floor(($dataVenc - $hoje) / 86400);
                            
                            $cor = '#436eee'; // Normal
                            if ($diasRestantes < 0) {
                                $cor = '#CD0000'; // Vencido
                            } elseif ($diasRestantes <= 2) {
                                $cor = '#FF7F00'; // Urgente
                            } elseif ($diasRestantes <= 5) {
                                $cor = '#AEB404'; // Atenção
                            }
                            
                            $status_labels = [
                                'pendente' => ['label' => 'Pendente', 'cor' => '#FF7F00'],
                                'concluido' => ['label' => 'Concluído', 'cor' => '#4d9c79'],
                                'cancelado' => ['label' => 'Cancelado', 'cor' => '#808080'],
                                'proximo_vencer' => ['label' => 'Próximo', 'cor' => '#AEB404'],
                                'vencendo_hoje' => ['label' => 'Vencendo Hoje', 'cor' => '#FF7F00'],
                                'vencido' => ['label' => 'Vencido', 'cor' => '#CD0000'],
                            ];
                            $status = $pz->status ?? 'pendente';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                            
                            $prioridade_labels = [
                                'baixa' => ['label' => 'Baixa', 'cor' => '#808080'],
                                'normal' => ['label' => 'Normal', 'cor' => '#436eee'],
                                'alta' => ['label' => 'Alta', 'cor' => '#FF7F00'],
                                'urgente' => ['label' => 'Urgente', 'cor' => '#CD0000'],
                            ];
                            $prioridade = $pz->prioridade ?? 'normal';
                            $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => ucfirst($prioridade), 'cor' => '#E0E4CC'];

                            echo '<tr>';
                            echo '<td>' . (isset($pz->numeroProcesso) ? $pz->numeroProcesso : '-') . '</td>';
                            echo '<td>' . ($pz->descricao ?? '-') . '</td>';
                            echo '<td>' . ($pz->tipo ?? '-') . '</td>';
                            echo '<td><span style="color: ' . $cor . '; font-weight: bold;">' . date('d/m/Y', strtotime($pz->dataVencimento)) . '</span></td>';
                            echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                            echo '<td><span class="badge" style="background-color: ' . $prioridade_info['cor'] . '; border-color: ' . $prioridade_info['cor'] . '">' . $prioridade_info['label'] . '</span></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon"><i class="bx bx-calendar"></i></div><div class="empty-state-message">Nenhum prazo próximo encontrado.</div></div></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
