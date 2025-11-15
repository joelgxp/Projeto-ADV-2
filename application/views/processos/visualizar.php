<div class="widget-box">
    <div class="widget-title" style="margin: 0;font-size: 1.1em">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab1">Dados do Processo</a></li>
            <li><a data-toggle="tab" href="#tab2">Movimentações</a></li>
            <li><a data-toggle="tab" href="#tab3">Prazos</a></li>
            <li><a data-toggle="tab" href="#tab4">Audiências</a></li>
            <li><a data-toggle="tab" href="#tab5">Documentos</a></li>
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
                                        <?php echo isset($result->numeroProcessoFormatado) ? $result->numeroProcessoFormatado : ($result->numeroProcesso ?? '-') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Classe Processual</strong></td>
                                    <td>
                                        <?php echo $result->classe ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Assunto</strong></td>
                                    <td>
                                        <?php echo $result->assunto ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Tipo de Processo</strong></td>
                                    <td>
                                        <?php 
                                        $tipos = [
                                            'civel' => 'Cível',
                                            'trabalhista' => 'Trabalhista',
                                            'tributario' => 'Tributário',
                                            'criminal' => 'Criminal',
                                            'familia' => 'Família',
                                            'consumidor' => 'Consumidor',
                                        ];
                                        echo $tipos[$result->tipo_processo ?? ''] ?? ($result->tipo_processo ?? '-');
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Status</strong></td>
                                    <td>
                                        <?php 
                                        $status_labels = [
                                            'em_andamento' => ['label' => 'Em Andamento', 'class' => 'label-info'],
                                            'suspenso' => ['label' => 'Suspenso', 'class' => 'label-warning'],
                                            'arquivado' => ['label' => 'Arquivado', 'class' => 'label-default'],
                                            'finalizado' => ['label' => 'Finalizado', 'class' => 'label-success'],
                                        ];
                                        $status = $result->status ?? 'em_andamento';
                                        $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'class' => 'label-default'];
                                        echo '<span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span>';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Valor da Causa</strong></td>
                                    <td>
                                        <?php echo $result->valorCausa ? 'R$ ' . number_format($result->valorCausa, 2, ',', '.') : '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Data de Distribuição</strong></td>
                                    <td>
                                        <?php echo $result->dataDistribuicao ? date('d/m/Y', strtotime($result->dataDistribuicao)) : '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Data de Cadastro</strong></td>
                                    <td>
                                        <?php echo $result->dataCadastro ? date('d/m/Y H:i', strtotime($result->dataCadastro)) : '-' ?>
                                    </td>
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
                                    <td>
                                        <?php echo $result->vara ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Comarca</strong></td>
                                    <td>
                                        <?php echo $result->comarca ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Tribunal</strong></td>
                                    <td>
                                        <?php echo $result->tribunal ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Segmento</strong></td>
                                    <td>
                                        <?php 
                                        $segmentos = [
                                            'estadual' => 'Estadual',
                                            'federal' => 'Federal',
                                            'trabalho' => 'Trabalho',
                                            'eleitoral' => 'Eleitoral',
                                            'militar' => 'Militar',
                                        ];
                                        echo $segmentos[$result->segmento ?? ''] ?? ($result->segmento ?? '-');
                                        ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGThree" data-toggle="collapse">
                                <span><i class='bx bx-user icon-cli' ></i></span>
                                <h5 style="padding-left: 28px">Partes e Responsáveis</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGThree">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Cliente</strong></td>
                                    <td>
                                        <?php 
                                        if (isset($result->nomeCliente) && $result->nomeCliente) {
                                            echo '<a href="' . base_url() . 'index.php/clientes/visualizar/' . ($result->clientes_id ?? '') . '">' . $result->nomeCliente . '</a>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Advogado Responsável</strong></td>
                                    <td>
                                        <?php echo isset($result->nomeAdvogado) && $result->nomeAdvogado ? $result->nomeAdvogado : '-' ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php if ($result->observacoes) { ?>
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGFour" data-toggle="collapse">
                                <span><i class='bx bx-note icon-cli' ></i></span>
                                <h5 style="padding-left: 28px">Observações</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGFour">
                        <div class="widget-content">
                            <p><?php echo nl2br($result->observacoes) ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <!--Tab 2 - Movimentações-->
        <div id="tab2" class="tab-pane" style="min-height: 300px">
            <?php if (!$movimentacoes) { ?>
                <table class="table table-bordered ">
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
                <?php
            } else { ?>
                <table class="table table-bordered ">
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
                        $dataMov = date('d/m/Y H:i', strtotime($m->dataMovimentacao));
                        echo '<tr>';
                        echo '<td>' . $dataMov . '</td>';
                        echo '<td>' . $m->titulo . '</td>';
                        echo '<td>' . ($m->descricao ? substr($m->descricao, 0, 100) . '...' : '-') . '</td>';
                        echo '<td><span class="label ' . ($m->origem == 'api_cnj' ? 'label-info' : 'label-default') . '">' . ($m->origem == 'api_cnj' ? 'API CNJ' : 'Manual') . '</span></td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <!--Tab 3 - Prazos-->
        <div id="tab3" class="tab-pane" style="min-height: 300px">
            <?php if (!$prazos) { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data do Prazo</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5">Nenhum Prazo Cadastrado</td>
                    </tr>
                    </tbody>
                </table>
                <?php
            } else { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data do Prazo</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($prazos as $p) {
                        $dataPrazo = date('d/m/Y', strtotime($p->dataPrazo));
                        $dataVenc = date('d/m/Y', strtotime($p->dataVencimento));
                        $hoje = date('Y-m-d');
                        $vencido = $p->dataVencimento < $hoje && $p->status != 'concluido';
                        
                        echo '<tr' . ($vencido ? ' style="background-color: #ffebee;"' : '') . '>';
                        echo '<td>' . ucfirst($p->tipo) . '</td>';
                        echo '<td>' . $p->descricao . '</td>';
                        echo '<td>' . $dataPrazo . '</td>';
                        echo '<td>' . $dataVenc . '</td>';
                        $status_labels = [
                            'pendente' => ['label' => 'Pendente', 'class' => 'label-warning'],
                            'vencido' => ['label' => 'Vencido', 'class' => 'label-danger'],
                            'concluido' => ['label' => 'Concluído', 'class' => 'label-success'],
                        ];
                        $status_info = $status_labels[$p->status] ?? ['label' => ucfirst($p->status), 'class' => 'label-default'];
                        echo '<td><span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span></td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <!--Tab 4 - Audiências-->
        <div id="tab4" class="tab-pane" style="min-height: 300px">
            <?php if (!$audiencias) { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4">Nenhuma Audiência Cadastrada</td>
                    </tr>
                    </tbody>
                </table>
                <?php
            } else { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($audiencias as $a) {
                        $dataHora = date('d/m/Y H:i', strtotime($a->dataHora));
                        echo '<tr>';
                        echo '<td>' . ucfirst($a->tipo) . '</td>';
                        echo '<td>' . $dataHora . '</td>';
                        echo '<td>' . ($a->local ?? '-') . '</td>';
                        $status_labels = [
                            'agendada' => ['label' => 'Agendada', 'class' => 'label-info'],
                            'realizada' => ['label' => 'Realizada', 'class' => 'label-success'],
                            'cancelada' => ['label' => 'Cancelada', 'class' => 'label-danger'],
                        ];
                        $status_info = $status_labels[$a->status] ?? ['label' => ucfirst($a->status), 'class' => 'label-default'];
                        echo '<td><span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span></td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <!--Tab 5 - Documentos-->
        <div id="tab5" class="tab-pane" style="min-height: 300px">
            <?php if (!$documentos) { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Data Upload</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4">Nenhum Documento Cadastrado</td>
                    </tr>
                    </tbody>
                </table>
                <?php
            } else { ?>
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Data Upload</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($documentos as $d) {
                        $dataUpload = date('d/m/Y H:i', strtotime($d->dataUpload));
                        echo '<tr>';
                        echo '<td>' . $d->titulo . '</td>';
                        echo '<td>' . ($d->tipo_documento ?? '-') . '</td>';
                        echo '<td>' . $dataUpload . '</td>';
                        echo '<td>';
                        if (file_exists(FCPATH . 'assets/documentos/' . $d->arquivo)) {
                            echo '<a href="' . base_url() . 'assets/documentos/' . $d->arquivo . '" target="_blank" class="btn btn-mini btn-info" title="Download"><i class="bx bx-download"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
    <div class="form-actions" style="padding: 20px;">
        <div class="span12">
            <div class="span6 offset3" style="display:flex;justify-content: center">
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProcesso')) { ?>
                    <a href="<?= base_url() ?>index.php/processos/editar/<?= $result->idProcessos ?>" class="button btn btn-success"><span class="button__icon"><i class='bx bx-edit'></i></span><span class="button__text2">Editar Processo</span></a>
                <?php } ?>
                <a href="<?= base_url() ?>index.php/processos" class="button btn btn-warning"><span class="button__icon"><i class='bx bx-arrow-back'></i></span><span class="button__text2">Voltar</span></a>
            </div>
        </div>
    </div>
</div>

