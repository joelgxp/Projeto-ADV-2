<div class="widget-box">
    <div class="widget-title" style="margin: 0;font-size: 1.1em">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab1">Dados do Cliente</a></li>
            <?php if (isset($can_view_processos) && $can_view_processos) { ?>
            <li><a data-toggle="tab" href="#tab2">Processos</a></li>
            <?php } ?>
            <?php if (isset($can_view_documentos) && $can_view_documentos) { ?>
            <li><a data-toggle="tab" href="#tab3">Prazos</a></li>
            <li><a data-toggle="tab" href="#tab4">Audiências</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) { ?>
            <li><a data-toggle="tab" href="#tab5">Auditoria</a></li>
            <?php } ?>
        </ul>
    </div>
    <div class="widget-content tab-content">
        <div id="tab1" class="tab-pane active" style="min-height: 300px">
            <div class="accordion" id="collapse-group">
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                                <span><i class='bx bx-user icon-cli' ></i></span>
                                <h5 style="padding-left: 28px">Dados Pessoais</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse in accordion-body" id="collapseGOne">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Nome</strong></td>
                                    <td>
                                        <?php echo $result->nomeCliente ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Documento</strong></td>
                                    <td>
                                        <?php echo $result->documento ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Data de Cadastro</strong></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($result->dataCadastro)) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Tipo do Cliente</strong></td>
                                    <td>
                                        <?php echo $result->fornecedor == true ? 'Fornecedor' : 'Cliente'; ?>
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
                                <span><i class='bx bx-phone icon-cli'></i></span>
                                <h5 style="padding-left: 28px">Contatos</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGTwo">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Contato:</strong></td>
                                    <td>
                                        <?php echo $result->contato ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Telefone</strong></td>
                                    <td>
                                        <?php echo $result->telefone ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Celular</strong></td>
                                    <td>
                                        <?php echo $result->celular ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Email</strong></td>
                                    <td>
                                        <?php echo $result->email ?>
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
                                <span><i class='bx bx-map-alt icon-cli' ></i></span>
                                <h5 style="padding-left: 28px">Endereço</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGThree">
                        <div class="widget-content">
                            <table class="table table-bordered th" style="border: 1px solid #ddd;border-left: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%;"><strong>Rua</strong></td>
                                    <td>
                                        <?php echo $result->rua ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Número</strong></td>
                                    <td>
                                        <?php echo $result->numero ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Complemento</strong></td>
                                    <td>
                                        <?php echo $result->complemento ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Bairro</strong></td>
                                    <td>
                                        <?php echo $result->bairro ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Cidade</strong></td>
                                    <td>
                                        <?php echo $result->cidade ?> -
                                        <?php echo $result->estado ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>CEP</strong></td>
                                    <td>
                                        <?php echo $result->cep ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Tab 2 - Processos-->
        <?php if (isset($can_view_processos) && $can_view_processos) { ?>
        <div id="tab2" class="tab-pane" style="min-height: 300px">
            <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                <form method="get" action="<?php echo site_url('clientes/visualizar/' . $result->idClientes); ?>" class="form-inline">
                    <div class="span3">
                        <label>Tipo de Demanda:</label>
                        <select name="tipo_processo" class="span12">
                            <option value="">Todos</option>
                            <option value="civel" <?php echo $this->input->get('tipo_processo') == 'civel' ? 'selected' : ''; ?>>Cível</option>
                            <option value="trabalhista" <?php echo $this->input->get('tipo_processo') == 'trabalhista' ? 'selected' : ''; ?>>Trabalhista</option>
                            <option value="tributario" <?php echo $this->input->get('tipo_processo') == 'tributario' ? 'selected' : ''; ?>>Tributário</option>
                            <option value="criminal" <?php echo $this->input->get('tipo_processo') == 'criminal' ? 'selected' : ''; ?>>Criminal</option>
                            <option value="familia" <?php echo $this->input->get('tipo_processo') == 'familia' ? 'selected' : ''; ?>>Família</option>
                            <option value="consumidor" <?php echo $this->input->get('tipo_processo') == 'consumidor' ? 'selected' : ''; ?>>Consumidor</option>
                        </select>
                    </div>
                    <div class="span3">
                        <label>Situação:</label>
                        <select name="status" class="span12">
                            <option value="">Todas</option>
                            <option value="em_andamento" <?php echo $this->input->get('status') == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="suspenso" <?php echo $this->input->get('status') == 'suspenso' ? 'selected' : ''; ?>>Suspenso</option>
                            <option value="arquivado" <?php echo $this->input->get('status') == 'arquivado' ? 'selected' : ''; ?>>Arquivado</option>
                            <option value="finalizado" <?php echo $this->input->get('status') == 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                        </select>
                    </div>
                    <div class="span3">
                        <label>Comarca:</label>
                        <input type="text" name="comarca" class="span12" value="<?php echo $this->input->get('comarca'); ?>" placeholder="Buscar comarca...">
                    </div>
                    <div class="span3">
                        <label>&nbsp;</label>
                        <button type="submit" class="button btn btn-mini btn-success span12">
                            <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                            <span class="button__text2">Filtrar</span>
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (!$processos) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nº Processo</th>
                            <th>Classe</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Advogado</th>
                            <th>Última Movimentação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7">Nenhum processo encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nº Processo</th>
                            <th>Classe</th>
                            <th>Assunto</th>
                            <th>Status</th>
                            <th>Advogado</th>
                            <th>Última Movimentação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
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
                            echo '<td><a href="' . base_url() . 'index.php/processos/visualizar/' . $p->idProcessos . '">' . $numeroFormatado . '</a></td>';
                            echo '<td>' . ($p->classe ?? '-') . '</td>';
                            echo '<td>' . ($p->assunto ?? '-') . '</td>';
                            echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                            echo '<td>' . (isset($p->nomeAdvogado) ? $p->nomeAdvogado : '-') . '</td>';
                            echo '<td>' . (isset($p->dataUltimaMovimentacao) ? date('d/m/Y', strtotime($p->dataUltimaMovimentacao)) : '-') . '</td>';
                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) {
                                echo '<a href="' . base_url() . 'index.php/processos/visualizar/' . $p->idProcessos . '" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <?php if (isset($pagination)) {
                    echo $pagination;
                } ?>
            <?php } ?>
        </div>
        <?php } ?>
        
        <!--Tab 3 - Prazos-->
        <?php if (isset($can_view_documentos) && $can_view_documentos) { ?>
        <div id="tab3" class="tab-pane" style="min-height: 300px">
            <?php if (!$prazos) { ?>
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
                            <td colspan="5">Nenhum prazo encontrado.</td>
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
                        foreach ($prazos as $pz) {
                            $dataVenc = strtotime($pz->dataVencimento);
                            $hoje = strtotime(date('Y-m-d'));
                            $diasRestantes = floor(($dataVenc - $hoje) / 86400);
                            
                            $cor = '#436eee';
                            if ($diasRestantes < 0) {
                                $cor = '#CD0000';
                            } elseif ($diasRestantes <= 2) {
                                $cor = '#FF7F00';
                            } elseif ($diasRestantes <= 5) {
                                $cor = '#AEB404';
                            }
                            
                            $status_labels = [
                                'pendente' => ['label' => 'Pendente', 'cor' => '#FF7F00'],
                                'concluido' => ['label' => 'Concluído', 'cor' => '#4d9c79'],
                                'cancelado' => ['label' => 'Cancelado', 'cor' => '#808080'],
                            ];
                            $status = $pz->status ?? 'pendente';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                            
                            echo '<tr>';
                            echo '<td>' . ($pz->tipo ?? '-') . '</td>';
                            echo '<td>' . ($pz->descricao ?? '-') . '</td>';
                            echo '<td><span style="color: ' . $cor . '; font-weight: bold;">' . date('d/m/Y', strtotime($pz->dataVencimento)) . '</span></td>';
                            echo '<td><span class="badge" style="background-color: ' . $status_info['cor'] . '; border-color: ' . $status_info['cor'] . '">' . $status_info['label'] . '</span></td>';
                            echo '<td>' . ucfirst($pz->prioridade ?? 'normal') . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        
        <!--Tab 4 - Audiências-->
        <div id="tab4" class="tab-pane" style="min-height: 300px">
            <?php if (!$audiencias) { ?>
                <table class="table table-bordered">
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
                            <td colspan="4">Nenhuma audiência encontrada.</td>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($audiencias as $a) {
                            $dataHora = date('d/m/Y H:i', strtotime($a->dataHora));
                            echo '<tr>';
                            echo '<td>' . ucfirst($a->tipo ?? '-') . '</td>';
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
                        }
                        ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <?php } ?>
        
        <!--Tab 5 - Auditoria-->
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) { ?>
        <div id="tab5" class="tab-pane" style="min-height: 300px">
            <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                <a href="<?php echo site_url('auditoria/cliente/' . $result->idClientes); ?>" class="button btn btn-mini btn-info">
                    <span class="button__icon"><i class='bx bx-shield-alt-2'></i></span>
                    <span class="button__text2">Ver Auditoria Completa</span>
                </a>
            </div>
            
            <?php if (!$logs_auditoria) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Dados Sensíveis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4">Nenhum log de auditoria encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            <?php } else { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Dados Sensíveis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($logs_auditoria as $log) {
                            echo '<tr>';
                            echo '<td>' . date('d/m/Y H:i:s', strtotime($log->data . ' ' . $log->hora)) . '</td>';
                            echo '<td>' . htmlspecialchars($log->usuario ?? 'N/A') . '</td>';
                            echo '<td><span class="label label-info">' . ucfirst($log->acao ?? 'N/A') . '</span></td>';
                            echo '<td>';
                            if (isset($log->dados_sensiveis) && $log->dados_sensiveis == 1) {
                                echo '<span class="label label-danger">Sim</span>';
                            } else {
                                echo '<span class="label label-success">Não</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <div class="modal-footer" style="display:flex;justify-content: center">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
            echo '<a title="Icon Title" class="button btn btn-mini btn-info" style="min-width: 140px; top:10px" href="' . base_url() . 'index.php/clientes/editar/' . $result->idClientes . '">
<span class="button__icon"><i class="bx bx-edit"></i></span> <span class="button__text2"> Editar</span></a>';
        } ?>
        <a title="Voltar" class="button btn btn-mini btn-warning" style="min-width: 140px; top:10px" href="<?php echo site_url() ?>/clientes">
          <span class="button__icon"><i class="bx bx-undo"></i></span><span class="button__text2">Voltar</span></a>
    </div>
</div>
