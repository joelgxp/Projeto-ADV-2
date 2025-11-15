<link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

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
                    if (isset($processos) && $processos != null) {
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
                        echo '<tr><td colspan="7">Nenhum processo encontrado.</td></tr>';
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
                    if (isset($prazos) && $prazos != null) {
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
                        echo '<tr><td colspan="6">Nenhum prazo próximo encontrado.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
