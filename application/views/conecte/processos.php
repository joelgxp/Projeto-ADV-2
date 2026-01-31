<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-file-alt"></i>
        </span>
        <h5>Meus Processos</h5>
    </div>
    
    <!-- Filtros -->
    <div class="widget-content" style="padding: 15px; border-bottom: 1px solid #ddd;">
        <form method="get" action="<?= base_url() ?>index.php/mine/processos" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
            <div style="flex: 1; min-width: 150px;">
                <label>Tipo de Demanda</label>
                <select name="tipo_processo" class="span12">
                    <option value="">Todos</option>
                    <option value="civel" <?= $this->input->get('tipo_processo') === 'civel' ? 'selected' : '' ?>>Cível</option>
                    <option value="trabalhista" <?= $this->input->get('tipo_processo') === 'trabalhista' ? 'selected' : '' ?>>Trabalhista</option>
                    <option value="tributario" <?= $this->input->get('tipo_processo') === 'tributario' ? 'selected' : '' ?>>Tributário</option>
                    <option value="criminal" <?= $this->input->get('tipo_processo') === 'criminal' ? 'selected' : '' ?>>Criminal</option>
                    <option value="administrativo" <?= $this->input->get('tipo_processo') === 'administrativo' ? 'selected' : '' ?>>Administrativo</option>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label>Situação</label>
                <select name="status" class="span12">
                    <option value="">Todas</option>
                    <option value="em_andamento" <?= $this->input->get('status') === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="suspenso" <?= $this->input->get('status') === 'suspenso' ? 'selected' : '' ?>>Suspenso</option>
                    <option value="arquivado" <?= $this->input->get('status') === 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                    <option value="finalizado" <?= $this->input->get('status') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label>Comarca/Tribunal</label>
                <input type="text" name="comarca" class="span12" value="<?= $this->input->get('comarca') ?>" placeholder="Buscar comarca...">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label>Advogado Responsável</label>
                <select name="usuarios_id" class="span12">
                    <option value="">Todos</option>
                    <?php
                    // Fase 6 - Correção Bug: Advogados carregados no controller
                    if (isset($advogados) && $advogados) {
                        foreach ($advogados as $adv) {
                            $selected = ($this->input->get('usuarios_id') == $adv['idUsuarios']) ? 'selected' : '';
                            echo '<option value="' . $adv['idUsuarios'] . '" ' . $selected . '>' . htmlspecialchars($adv['nome']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div style="flex: 0 0 auto;">
                <button type="submit" class="button btn btn-primary btn-sm">
                    <span class="button__icon"><i class='bx bx-filter-alt'></i></span>
                    <span class="button__text2">Filtrar</span>
                </button>
                <a href="<?= base_url() ?>index.php/mine/processos" class="button btn btn-default btn-sm" style="margin-left: 5px;">
                    <span class="button__icon"><i class='bx bx-x'></i></span>
                    <span class="button__text2">Limpar</span>
                </a>
            </div>
        </form>
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

