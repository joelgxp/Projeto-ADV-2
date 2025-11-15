<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-chart-bar"></i>
                </span>
                <h5>Relatórios do Sistema</h5>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" style="padding: 20px;">
                    <div class="row-fluid">
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-users"></i></span>
                                    <h5>Clientes</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/clientes') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-users"></i> Relatório de Clientes
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rProcesso')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                                    <h5>Processos</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/processos') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-file-alt"></i> Relatório de Processos
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rPrazo')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-calendar-check"></i></span>
                                    <h5>Prazos</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/prazos') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-calendar-check"></i> Relatório de Prazos
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rAudiencia')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-calendar-event"></i></span>
                                    <h5>Audiências</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/audiencias') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-calendar-event"></i> Relatório de Audiências
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="row-fluid" style="margin-top: 20px;">
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-wrench"></i></span>
                                    <h5>Serviços Jurídicos</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/servicos') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-wrench"></i> Relatório de Serviços
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-hand-holding-usd"></i></span>
                                    <h5>Financeiro</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/financeiro') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-hand-holding-usd"></i> Relatório Financeiro
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rHonorario')) { ?>
                        <div class="span3">
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon"><i class="fas fa-gavel"></i></span>
                                    <h5>Honorários</h5>
                                </div>
                                <div class="widget-content">
                                    <a href="<?php echo site_url('relatorios/honorarios') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-gavel"></i> Relatório de Honorários
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

