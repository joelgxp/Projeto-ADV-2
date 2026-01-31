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
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) { ?>
            <li><a data-toggle="tab" href="#tab7">Acesso ao Portal</a></li>
            <?php } ?>
            <?php if (isset($interacoes) && !empty($interacoes)) { ?>
            <li><a data-toggle="tab" href="#tab6">Interações</a></li>
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
                                <?php if ($result->pessoa_fisica == 1) : ?>
                                    <tr>
                                        <td style="text-align: right; width: 30%"><strong>Nome Completo</strong></td>
                                        <td><?php echo $result->nomeCliente ?></td>
                                    </tr>
                                    <?php if (isset($result->rg) && $result->rg) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>RG</strong></td>
                                        <td><?php echo $result->rg ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td style="text-align: right"><strong>CPF</strong></td>
                                        <td><?php echo $result->documento ?></td>
                                    </tr>
                                    <?php if (isset($result->data_nascimento) && $result->data_nascimento) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Data de Nascimento</strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($result->data_nascimento)) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->estado_civil) && $result->estado_civil) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Estado Civil</strong></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $result->estado_civil)) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->nacionalidade) && $result->nacionalidade) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Nacionalidade</strong></td>
                                        <td><?php echo $result->nacionalidade ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->profissao) && $result->profissao) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Profissão</strong></td>
                                        <td><?php echo $result->profissao ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->nome_mae) && $result->nome_mae) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Nome da Mãe</strong></td>
                                        <td><?php echo $result->nome_mae ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->nome_pai) && $result->nome_pai) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Nome do Pai</strong></td>
                                        <td><?php echo $result->nome_pai ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->dependentes) && $result->dependentes) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Dependentes</strong></td>
                                        <td><?php echo nl2br($result->dependentes) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->foto) && $result->foto) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Foto/Documento</strong></td>
                                        <td>
                                            <a href="<?php echo base_url() . $result->foto; ?>" target="_blank">
                                                <img src="<?php echo base_url() . $result->foto; ?>" style="max-width: 150px; max-height: 150px;" alt="Foto">
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->documentos_adicionais) && $result->documentos_adicionais) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Documentos Adicionais</strong></td>
                                        <td><?php echo nl2br($result->documentos_adicionais) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <tr>
                                        <td style="text-align: right; width: 30%"><strong>Razão Social</strong></td>
                                        <td><?php echo isset($result->razao_social) && $result->razao_social ? $result->razao_social : $result->nomeCliente ?></td>
                                    </tr>
                                    <?php if (isset($result->nome_fantasia) && $result->nome_fantasia) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Nome Fantasia</strong></td>
                                        <td><?php echo $result->nome_fantasia ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td style="text-align: right"><strong>CNPJ</strong></td>
                                        <td><?php echo $result->documento ?></td>
                                    </tr>
                                    <?php if (isset($result->inscricao_estadual) && $result->inscricao_estadual) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Inscrição Estadual</strong></td>
                                        <td><?php echo $result->inscricao_estadual ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->inscricao_municipal) && $result->inscricao_municipal) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Inscrição Municipal</strong></td>
                                        <td><?php echo $result->inscricao_municipal ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->data_constituicao) && $result->data_constituicao) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Data de Constituição</strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($result->data_constituicao)) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->cnae) && $result->cnae) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>CNAE</strong></td>
                                        <td><?php echo $result->cnae ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->ramo_atividade) && $result->ramo_atividade) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Ramo de Atividade</strong></td>
                                        <td><?php echo $result->ramo_atividade ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->site) && $result->site) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Site</strong></td>
                                        <td><a href="<?php echo $result->site; ?>" target="_blank"><?php echo $result->site; ?></a></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->redes_sociais) && $result->redes_sociais) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Redes Sociais</strong></td>
                                        <td><?php echo nl2br($result->redes_sociais) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->representantes_legais) && $result->representantes_legais) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Representantes Legais</strong></td>
                                        <td><?php echo nl2br($result->representantes_legais) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($result->socios) && $result->socios) : ?>
                                    <tr>
                                        <td style="text-align: right"><strong>Sócios</strong></td>
                                        <td><?php echo nl2br($result->socios) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <tr>
                                    <td style="text-align: right"><strong>Data de Cadastro</strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($result->dataCadastro)) ?></td>
                                </tr>
                                <?php if (isset($result->observacoes) && $result->observacoes) : ?>
                                <tr>
                                    <td style="text-align: right"><strong>Observações Gerais</strong></td>
                                    <td><?php echo nl2br($result->observacoes) ?></td>
                                </tr>
                                <?php endif; ?>
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
        
        <!--Tab 7 - Acesso ao Portal (Fase 6)-->
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) { ?>
        <div id="tab7" class="tab-pane" style="min-height: 300px">
            <div class="widget-box">
                <div class="widget-title">
                    <h5><i class='bx bx-link-external'></i> Gerenciar Acesso ao Portal do Cliente</h5>
                </div>
                <div class="widget-content">
                    <?php if (isset($acesso_ativo) && $acesso_ativo) : ?>
                        <!-- Acesso Ativo Existe -->
                        <?php
                        $diasRestantes = isset($acesso_dias_restantes) ? $acesso_dias_restantes : 0;
                        $estaExpirado = isset($acesso_expirado) ? $acesso_expirado : false;
                        $linkCompleto = isset($link_acesso_completo) ? $link_acesso_completo : base_url('index.php/mine/acesso/' . $acesso_ativo->token_acesso);
                        ?>
                        
                        <div class="alert <?php echo $estaExpirado ? 'alert-danger' : ($diasRestantes <= 30 ? 'alert-warning' : 'alert-success'); ?>">
                            <?php if ($estaExpirado) : ?>
                                <strong>⚠️ Link Expirado</strong>
                                <p>Este link de acesso expirou em <?php echo date('d/m/Y', strtotime($acesso_ativo->data_expiracao)); ?>. Gere um novo link para o cliente.</p>
                            <?php elseif ($diasRestantes <= 30) : ?>
                                <strong>⏰ Atenção: Link Expirando em Breve</strong>
                                <p>Este link expira em <strong><?php echo $diasRestantes; ?> dia(s)</strong> (<?php echo date('d/m/Y', strtotime($acesso_ativo->data_expiracao)); ?>). Considere renovar.</p>
                            <?php else : ?>
                                <strong>✅ Link Ativo</strong>
                                <p>Link de acesso válido até <strong><?php echo date('d/m/Y', strtotime($acesso_ativo->data_expiracao)); ?></strong> (<?php echo $diasRestantes; ?> dias restantes).</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="widget-box" style="margin-top: 20px;">
                            <div class="widget-title">
                                <h5>Link de Acesso</h5>
                            </div>
                            <div class="widget-content">
                                <div class="form-group">
                                    <label><strong>Link Completo:</strong></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="link_acesso" value="<?php echo htmlspecialchars($linkCompleto); ?>" readonly>
                                        <div class="input-group-append">
                                            <button class="btn btn-info" type="button" onclick="copiarLink()">
                                                <i class='bx bx-copy'></i> Copiar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Copie este link e envie ao cliente ou use os botões abaixo para renovar/desativar.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label><strong>Informações do Acesso:</strong></label>
                                    <table class="table table-bordered" style="margin-bottom: 0;">
                                        <tr>
                                            <td style="width: 30%;"><strong>Data de Criação:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($acesso_ativo->data_criacao)); ?></td>
                                        </tr>
                                        <?php if ($acesso_ativo->data_renovacao) : ?>
                                        <tr>
                                            <td><strong>Última Renovação:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($acesso_ativo->data_renovacao)); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>Data de Expiração:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($acesso_ativo->data_expiracao)); ?></td>
                                        </tr>
                                        <?php if ($acesso_ativo->ultimo_acesso) : ?>
                                        <tr>
                                            <td><strong>Último Acesso:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($acesso_ativo->ultimo_acesso)); ?></td>
                                        </tr>
                                        <?php else : ?>
                                        <tr>
                                            <td><strong>Último Acesso:</strong></td>
                                            <td><span class="text-muted">Nunca acessou</span></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <?php if ($estaExpirado) : ?>
                                                    <span class="badge badge-danger">Expirado</span>
                                                <?php else : ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <form method="post" action="<?php echo site_url('clientes/renovarLinkAcesso'); ?>" style="display: inline-block; margin: 5px;">
                                <?php echo form_hidden('cliente_id', $result->idClientes); ?>
                                <?php echo form_hidden('acesso_id', $acesso_ativo->id); ?>
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Deseja renovar este link de acesso? O link será válido por mais 365 dias.');">
                                    <i class='bx bx-refresh'></i> Renovar Link
                                </button>
                            </form>
                            
                            <form method="post" action="<?php echo site_url('clientes/desativarLinkAcesso'); ?>" style="display: inline-block; margin: 5px;">
                                <?php echo form_hidden('cliente_id', $result->idClientes); ?>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja desativar este link de acesso? O cliente não poderá mais acessar o portal com este link.');">
                                    <i class='bx bx-x'></i> Desativar Link
                                </button>
                            </form>
                            
                            <form method="post" action="<?php echo site_url('clientes/gerarLinkAcesso'); ?>" style="display: inline-block; margin: 5px;">
                                <?php echo form_hidden('cliente_id', $result->idClientes); ?>
                                <button type="submit" class="btn btn-info" onclick="return confirm('Deseja gerar um novo link de acesso? O link atual será desativado automaticamente.');">
                                    <i class='bx bx-plus'></i> Gerar Novo Link
                                </button>
                            </form>
                        </div>
                        
                    <?php else : ?>
                        <!-- Nenhum Acesso Ativo -->
                        <div class="alert alert-info">
                            <strong>ℹ️ Nenhum Link de Acesso Ativo</strong>
                            <p>Este cliente ainda não possui um link de acesso ao portal. Clique no botão abaixo para gerar um novo link válido por 365 dias.</p>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <form method="post" action="<?php echo site_url('clientes/gerarLinkAcesso'); ?>">
                                <?php echo form_hidden('cliente_id', $result->idClientes); ?>
                                
                                <?php if (empty($result->email)) : ?>
                                    <div class="alert alert-warning">
                                        <strong>⚠️ Atenção:</strong> Este cliente não possui e-mail cadastrado. O link será gerado, mas não será possível enviar por e-mail automaticamente.
                                    </div>
                                <?php else : ?>
                                    <p><strong>E-mail do Cliente:</strong> <?php echo htmlspecialchars($result->email); ?></p>
                                    <p>O link será enviado automaticamente para este e-mail após a geração.</p>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn btn-success btn-large" style="padding: 15px 40px; font-size: 16px;">
                                    <i class='bx bx-link'></i> Gerar Link de Acesso (365 dias)
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($acessos) && !empty($acessos)) : ?>
                        <!-- Histórico de Acessos -->
                        <div class="widget-box" style="margin-top: 30px;">
                            <div class="widget-title">
                                <h5>Histórico de Acessos</h5>
                            </div>
                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data de Criação</th>
                                            <th>Data de Expiração</th>
                                            <th>Último Acesso</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($acessos as $acesso) : 
                                            $dataExpiracao = strtotime($acesso->data_expiracao);
                                            $dataAtual = time();
                                            $estaExpirado = $dataExpiracao < $dataAtual;
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($acesso->data_criacao)); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($acesso->data_expiracao)); ?></td>
                                            <td>
                                                <?php if ($acesso->ultimo_acesso) : ?>
                                                    <?php echo date('d/m/Y H:i', strtotime($acesso->ultimo_acesso)); ?>
                                                <?php else : ?>
                                                    <span class="text-muted">Nunca acessou</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$acesso->ativo) : ?>
                                                    <span class="badge badge-secondary">Desativado</span>
                                                <?php elseif ($estaExpirado) : ?>
                                                    <span class="badge badge-danger">Expirado</span>
                                                <?php else : ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php } ?>
        
        <!--Tab 6 - Histórico de Interações-->
        <?php if (isset($interacoes) && !empty($interacoes)) { ?>
        <div id="tab6" class="tab-pane" style="min-height: 300px">
            <div class="widget-box">
                <div class="widget-title">
                    <h5>Histórico de Interações do Cliente</h5>
                </div>
                <div class="widget-content">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interacoes as $interacao) { 
                                $tipo_labels = [
                                    'criacao' => ['label' => 'Criação', 'cor' => '#4d9c79'],
                                    'edicao' => ['label' => 'Edição', 'cor' => '#436eee'],
                                    'exclusao' => ['label' => 'Exclusão', 'cor' => '#CD0000'],
                                    'reuniao' => ['label' => 'Reunião', 'cor' => '#AEB404'],
                                    'telefone' => ['label' => 'Telefone', 'cor' => '#FF7F00'],
                                    'email' => ['label' => 'E-mail', 'cor' => '#436eee'],
                                    'nota' => ['label' => 'Nota', 'cor' => '#808080'],
                                    'status' => ['label' => 'Status', 'cor' => '#FF7F00'],
                                ];
                                $tipo_info = $tipo_labels[$interacao->tipo ?? ''] ?? ['label' => ucfirst($interacao->tipo ?? 'N/A'), 'cor' => '#E0E4CC'];
                            ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($interacao->data_hora ?? $interacao->dataCadastro ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($interacao->nome_usuario ?? $interacao->usuario_nome ?? 'Sistema'); ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo $tipo_info['cor']; ?>; border-color: <?php echo $tipo_info['cor']; ?>">
                                            <?php echo $tipo_info['label']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($interacao->descricao ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($interacao->ip_address ?? '-'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
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

<script>
function copiarLink() {
    var linkInput = document.getElementById('link_acesso');
    if (linkInput) {
        linkInput.select();
        linkInput.setSelectionRange(0, 99999); // Para dispositivos móveis
        
        try {
            document.execCommand('copy');
            
            // Feedback visual
            var btn = event.target.closest('button');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bx bx-check"></i> Copiado!';
            btn.classList.remove('btn-info');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-info');
            }, 2000);
            
            alert('Link copiado para a área de transferência!');
        } catch (err) {
            alert('Erro ao copiar link. Selecione o texto manualmente.');
        }
    }
}
</script>
