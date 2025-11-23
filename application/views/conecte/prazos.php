<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-calendar-check"></i>
        </span>
        <h5>Meus Prazos</h5>
    </div>
    <div class="widget-content nopadding tab-content">
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
                if (!$results) {
                    echo '<tr><td colspan="6">Nenhum prazo encontrado.</td></tr>';
                } else {
                    foreach ($results as $pz) {
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
                }
                ?>
            </tbody>
        </table>
        <?php if (isset($pagination) && $pagination) {
            echo $pagination;
        } ?>
    </div>
</div>

