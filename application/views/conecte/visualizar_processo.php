<div class="widget-box">
    <div class="widget-title" style="margin: 0;font-size: 1.1em">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab1">Dados do Processo</a></li>
            <li><a data-toggle="tab" href="#tab2">Movimentações</a></li>
            <li><a data-toggle="tab" href="#tab3">Prazos</a></li>
            <li><a data-toggle="tab" href="#tab4">Audiências</a></li>
        </ul>
    </div>
    <div class="widget-content tab-content">
        <div id="tab1" class="tab-pane active" style="min-height: 300px">
            <div class="accordion" id="collapse-group">
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                                <span><i class='bx bx-file icon-cli' ></i></span>
                                <h5 style="padding-left: 28px">Dados do Processo</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse in accordion-body" id="collapseGOne">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Número de Processo</strong></td>
                                    <td>
                                        <?php 
                                        $this->load->model('processos_model');
                                        echo $this->processos_model->formatarNumeroProcesso($result->numeroProcesso ?? '');
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Classe Processual</strong></td>
                                    <td><?php echo $result->classe ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Assunto</strong></td>
                                    <td><?php echo $result->assunto ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Status</strong></td>
                                    <td>
                                        <?php 
                                        $status_labels = [
                                            'em_andamento' => ['label' => 'Em Andamento', 'cor' => '#436eee'],
                                            'suspenso' => ['label' => 'Suspenso', 'cor' => '#FF7F00'],
                                            'arquivado' => ['label' => 'Arquivado', 'cor' => '#808080'],
                                            'finalizado' => ['label' => 'Finalizado', 'cor' => '#256'],
                                        ];
                                        $status = $result->status ?? 'em_andamento';
                                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                                        echo '<span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php /* Fase 6 - Sprint 3: Ocultar informações financeiras do cliente */ ?>
                                <!-- Valor da Causa oculto para cliente (informação financeira sensível) -->
                                <tr>
                                    <td style="text-align: right"><strong>Data de Distribuição</strong></td>
                                    <td><?php echo $result->dataDistribuicao ? date('d/m/Y', strtotime($result->dataDistribuicao)) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Última Movimentação</strong></td>
                                    <td><?php echo $result->dataUltimaMovimentacao ? date('d/m/Y H:i', strtotime($result->dataUltimaMovimentacao)) : '-' ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGTwo" data-toggle="collapse">
                                <span><i class='bx bx-building icon-cli'></i></span>
                                <h5 style="padding-left: 28px">Tribunal e Comarca</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGTwo">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Vara</strong></td>
                                    <td><?php echo $result->vara ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Comarca</strong></td>
                                    <td><?php echo $result->comarca ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Tribunal</strong></td>
                                    <td><?php echo $result->tribunal ?? '-' ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php /* Fase 6 - Sprint 3: Ocultar observações/comentários internos do cliente */ ?>
                <!-- Observações ocultas para cliente (podem conter comentários internos) -->
            </div>
        </div>
        <!--Tab 2 - Movimentações-->
        <div id="tab2" class="tab-pane" style="min-height: 300px">
            <?php if (empty($movimentacoes)) { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Data</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Origem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4">Nenhuma Movimentação Cadastrada</td>
                    </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Data</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Origem</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($movimentacoes as $m) {
                        $dataMov = date('d/m/Y H:i', strtotime($m->data ?? $m->dataMovimentacao ?? date('Y-m-d H:i:s')));
                        echo '<tr>';
                        echo '<td>' . $dataMov . '</td>';
                        echo '<td>' . ($m->tipo ?? '-') . '</td>';
                        echo '<td>' . ($m->descricao ? substr($m->descricao, 0, 200) . (strlen($m->descricao) > 200 ? '...' : '') : '-') . '</td>';
                        $origem_label = ($m->origem == 'API' || $m->importado_api == 1) ? 'API CNJ' : 'Manual';
                        $origem_class = ($m->origem == 'API' || $m->importado_api == 1) ? 'label-info' : 'label-default';
                        echo '<td><span class="label ' . $origem_class . '">' . $origem_label . '</span></td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <!--Tab 3 - Prazos-->
        <div id="tab3" class="tab-pane" style="min-height: 300px">
            <?php if (empty($prazos)) { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data Vencimento</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5">Nenhum Prazo Cadastrado</td>
                    </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data Vencimento</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($prazos as $p) {
                        $dataVenc = strtotime($p->dataVencimento);
                        $hoje = strtotime(date('Y-m-d'));
                        $diasRestantes = floor(($dataVenc - $hoje) / 86400);
                        $vencido = $diasRestantes < 0 && $p->status != 'concluido';
                        
                        $cor = '#436eee';
                        if ($diasRestantes < 0) {
                            $cor = '#CD0000';
                        } elseif ($diasRestantes <= 2) {
                            $cor = '#FF7F00';
                        } elseif ($diasRestantes <= 5) {
                            $cor = '#AEB404';
                        }
                        
                        echo '<tr' . ($vencido ? ' style="background-color: #ffebee;"' : '') . '>';
                        echo '<td>' . ($p->tipo ?? '-') . '</td>';
                        echo '<td>' . ($p->descricao ?? '-') . '</td>';
                        echo '<td><span style="color: ' . $cor . '; font-weight: bold;">' . date('d/m/Y', strtotime($p->dataVencimento)) . '</span></td>';
                        
                        $status_labels = [
                            'pendente' => ['label' => 'Pendente', 'cor' => '#FF7F00'],
                            'concluido' => ['label' => 'Concluído', 'cor' => '#4d9c79'],
                            'cancelado' => ['label' => 'Cancelado', 'cor' => '#808080'],
                        ];
                        $status = $p->status ?? 'pendente';
                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                        echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                        
                        $prioridade_labels = [
                            'baixa' => ['label' => 'Baixa', 'cor' => '#808080'],
                            'normal' => ['label' => 'Normal', 'cor' => '#436eee'],
                            'alta' => ['label' => 'Alta', 'cor' => '#FF7F00'],
                            'urgente' => ['label' => 'Urgente', 'cor' => '#CD0000'],
                        ];
                        $prioridade = $p->prioridade ?? 'normal';
                        $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => ucfirst($prioridade), 'cor' => '#E0E4CC'];
                        echo '<td><span class="badge" style="background-color: ' . $prioridade_info['cor'] . '; border-color: ' . $prioridade_info['cor'] . '">' . $prioridade_info['label'] . '</span></td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <!--Tab 4 - Audiências-->
        <div id="tab4" class="tab-pane" style="min-height: 300px">
            <?php if (empty($audiencias)) { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Observações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5">Nenhuma Audiência Cadastrada</td>
                    </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Observações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($audiencias as $a) {
                        $dataHora = date('d/m/Y H:i', strtotime($a->dataHora));
                        echo '<tr>';
                        echo '<td>' . ($a->tipo ?? '-') . '</td>';
                        echo '<td>' . $dataHora . '</td>';
                        echo '<td>' . ($a->local ?? '-') . '</td>';
                        
                        $status_labels = [
                            'agendada' => ['label' => 'Agendada', 'cor' => '#436eee'],
                            'realizada' => ['label' => 'Realizada', 'cor' => '#4d9c79'],
                            'cancelada' => ['label' => 'Cancelada', 'cor' => '#808080'],
                        ];
                        $status = $a->status ?? 'agendada';
                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                        echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                        echo '<td>-</td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</div>

