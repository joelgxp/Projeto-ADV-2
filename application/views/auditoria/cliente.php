<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-shield-alt"></i>
                </span>
                <h5>Auditoria - Cliente: <?php echo htmlspecialchars($cliente->nomeCliente ?? 'N/A'); ?></h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <div class="span12" style="margin-left: 0; margin-top: 20px;">
                    <a href="<?php echo site_url('clientes/visualizar/' . $cliente->idClientes); ?>" class="button btn btn-mini btn-warning">
                        <span class="button__icon"><i class='bx bx-arrow-back'></i></span>
                        <span class="button__text2">Voltar para Cliente</span>
                    </a>
                </div>

                <table id="tabela" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>IP</th>
                            <th>Ação</th>
                            <th>Campo Alterado</th>
                            <th>Valor Anterior</th>
                            <th>Valor Novo</th>
                            <th>Dados Sensíveis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!$results) {
                            echo '<tr><td colspan="8">Nenhum log de auditoria encontrado.</td></tr>';
                        } else {
                            foreach ($results as $r) {
                                echo '<tr>';
                                echo '<td>' . date('d/m/Y H:i:s', strtotime($r->data . ' ' . $r->hora)) . '</td>';
                                echo '<td>' . htmlspecialchars($r->usuario ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($r->ip ?? 'N/A') . '</td>';
                                echo '<td><span class="label label-info">' . ucfirst($r->acao ?? 'N/A') . '</span></td>';
                                echo '<td>' . htmlspecialchars($r->campo_alterado ?? '-') . '</td>';
                                echo '<td>' . htmlspecialchars(substr($r->valor_anterior ?? '-', 0, 50)) . (strlen($r->valor_anterior ?? '') > 50 ? '...' : '') . '</td>';
                                echo '<td>' . htmlspecialchars(substr($r->valor_novo ?? '-', 0, 50)) . (strlen($r->valor_novo ?? '') > 50 ? '...' : '') . '</td>';
                                echo '<td>';
                                if (isset($r->dados_sensiveis) && $r->dados_sensiveis == 1) {
                                    echo '<span class="label label-danger">Sim</span>';
                                } else {
                                    echo '<span class="label label-success">Não</span>';
                                }
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
    </div>
</div>

