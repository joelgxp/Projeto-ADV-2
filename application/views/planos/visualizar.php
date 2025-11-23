<div class="widget-box">
    <div class="widget-title" style="margin: 0;font-size: 1.1em">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#tab1">Dados do Plano</a></li>
            <li><a data-toggle="tab" href="#tab2">Clientes com este Plano</a></li>
        </ul>
    </div>
    <div class="widget-content tab-content">
        <div id="tab1" class="tab-pane active" style="min-height: 300px">
            <div class="accordion" id="collapse-group">
                <div class="accordion-group widget-box">
                    <div class="accordion-heading">
                        <div class="widget-title">
                            <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                                <span><i class='bx bx-tag icon-cli'></i></span>
                                <h5 style="padding-left: 28px">Informações do Plano</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse in accordion-body" id="collapseGOne">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Nome</strong></td>
                                    <td><?php echo $result->nome ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Descrição</strong></td>
                                    <td><?php echo $result->descricao ? nl2br($result->descricao) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Valor Mensal</strong></td>
                                    <td>R$ <?php echo number_format($result->valor_mensal, 2, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Status</strong></td>
                                    <td>
                                        <?php if ($result->status == 1) : ?>
                                            <span class="label label-success">Ativo</span>
                                        <?php else : ?>
                                            <span class="label label-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Data de Cadastro</strong></td>
                                    <td><?php echo $result->dataCadastro ? date('d/m/Y H:i', strtotime($result->dataCadastro)) : '-' ?></td>
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
                                <span><i class='bx bx-slider icon-cli'></i></span>
                                <h5 style="padding-left: 28px">Limites e Funcionalidades</h5>
                            </a>
                        </div>
                    </div>
                    <div class="collapse accordion-body" id="collapseGTwo">
                        <div class="widget-content">
                            <table class="table table-bordered" style="border: 1px solid #ddd">
                                <tbody>
                                <tr>
                                    <td style="text-align: right; width: 30%"><strong>Limite de Processos</strong></td>
                                    <td><?php echo $result->limite_processos == 0 ? 'Ilimitado' : $result->limite_processos ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Limite de Prazos</strong></td>
                                    <td><?php echo $result->limite_prazos == 0 ? 'Ilimitado' : $result->limite_prazos ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Limite de Audiências</strong></td>
                                    <td><?php echo $result->limite_audiencias == 0 ? 'Ilimitado' : $result->limite_audiencias ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Limite de Documentos</strong></td>
                                    <td><?php echo $result->limite_documentos == 0 ? 'Ilimitado' : $result->limite_documentos ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Acesso ao Portal</strong></td>
                                    <td>
                                        <?php if ($result->acesso_portal == 1) : ?>
                                            <span class="label label-success">Sim</span>
                                        <?php else : ?>
                                            <span class="label label-danger">Não</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Suporte Prioritário</strong></td>
                                    <td>
                                        <?php if ($result->suporte_prioritario == 1) : ?>
                                            <span class="label label-success">Sim</span>
                                        <?php else : ?>
                                            <span class="label label-danger">Não</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right"><strong>Relatórios Avançados</strong></td>
                                    <td>
                                        <?php if ($result->relatorios_avancados == 1) : ?>
                                            <span class="label label-success">Sim</span>
                                        <?php else : ?>
                                            <span class="label label-danger">Não</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="tab2" class="tab-pane" style="min-height: 300px">
            <?php if (empty($clientes)) : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Documento</th>
                            <th>Data Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4">Nenhum cliente vinculado a este plano.</td>
                        </tr>
                    </tbody>
                </table>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Documento</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente) : ?>
                            <tr>
                                <td><?php echo $cliente->nomeCliente ?></td>
                                <td><?php echo $cliente->email ?></td>
                                <td><?php echo $cliente->documento ?></td>
                                <td><?php echo $cliente->dataCadastro ? date('d/m/Y', strtotime($cliente->dataCadastro)) : '-' ?></td>
                                <td>
                                    <a href="<?php echo base_url() ?>index.php/clientes/visualizar/<?php echo $cliente->idClientes ?>" class="btn-nwe" title="Ver Cliente">
                                        <i class="bx bx-show bx-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-actions" style="padding: 20px;">
        <div class="span12">
            <div class="span6 offset3" style="display:flex;justify-content: center">
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) : ?>
                    <a href="<?php echo base_url() ?>index.php/planos/editar/<?php echo $result->idPlanos ?>" class="button btn btn-primary">
                        <span class="button__icon"><i class='bx bx-edit'></i></span><span class="button__text2">Editar</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo base_url() ?>index.php/planos" class="button btn btn-warning">
                    <span class="button__icon"><i class='bx bx-arrow-back'></i></span><span class="button__text2">Voltar</span>
                </a>
            </div>
        </div>
    </div>
</div>

