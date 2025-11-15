<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-file-alt"></i>
        </span>
        <h5>Meus Processos</h5>
    </div>
    <div class="widget-content nopadding tab-content">
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
                if (!$results) {
                    echo '<tr><td colspan="7">Nenhum processo encontrado.</td></tr>';
                } else {
                    $this->load->model('processos_model');
                    foreach ($results as $r) {
                        $numeroFormatado = $this->processos_model->formatarNumeroProcesso($r->numeroProcesso);
                        
                        $status_labels = [
                            'em_andamento' => ['label' => 'Em Andamento', 'cor' => '#436eee'],
                            'suspenso' => ['label' => 'Suspenso', 'cor' => '#FF7F00'],
                            'arquivado' => ['label' => 'Arquivado', 'cor' => '#808080'],
                            'finalizado' => ['label' => 'Finalizado', 'cor' => '#256'],
                        ];
                        $status = $r->status ?? 'em_andamento';
                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];

                        echo '<tr>';
                        echo '<td><a href="' . base_url() . 'index.php/mine/visualizarProcesso/' . $r->idProcessos . '">' . $numeroFormatado . '</a></td>';
                        echo '<td>' . ($r->classe ?? '-') . '</td>';
                        echo '<td>' . ($r->assunto ?? '-') . '</td>';
                        echo '<td>' . (isset($r->nomeAdvogado) ? $r->nomeAdvogado : '-') . '</td>';
                        echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                        echo '<td>' . (isset($r->dataUltimaMovimentacao) ? date('d/m/Y', strtotime($r->dataUltimaMovimentacao)) : '-') . '</td>';
                        echo '<td style="text-align:right">';
                        echo '<a href="' . base_url() . 'index.php/mine/visualizarProcesso/' . $r->idProcessos . '" class="btn"> <i class="fas fa-eye" ></i></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
        <?php if (isset($pagination)) {
            echo $pagination;
        } ?>
    </div>
</div>

