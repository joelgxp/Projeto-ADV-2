<!--sidebar-menu-->
<nav id="sidebar">
    <div id="newlog">
        <?php
        $logo_url = (isset($emitente) && $emitente && !empty($emitente->url_logo)) ? $emitente->url_logo : null;
        $logo_fallback = ($configuration['app_theme'] == 'white' || $configuration['app_theme'] == 'whitegreen') ? base_url() . 'assets/img/logo-adv.svg' : base_url() . 'assets/img/logo-adv-branco.svg';
        $logo_fallback_png = ($configuration['app_theme'] == 'white' || $configuration['app_theme'] == 'whitegreen') ? base_url() . 'assets/img/logo-adv.png' : base_url() . 'assets/img/logo-adv-branco.png';
        ?>
        <div class="icon2">
            <?php if ($logo_url): ?>
                <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= isset($emitente->nome) ? htmlspecialchars($emitente->nome) : 'Logo' ?>" style="max-width:100%;max-height:50px;object-fit:contain" onerror="this.src='<?= base_url() ?>assets/img/logo-two.svg'">
            <?php else: ?>
                <img src="<?php echo base_url() ?>assets/img/logo-two.svg" onerror="this.src='<?php echo base_url() ?>assets/img/logo-two.png'">
            <?php endif; ?>
        </div>
        <div class="title1">
            <?php if ($logo_url): ?>
                <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= isset($emitente->nome) ? htmlspecialchars($emitente->nome) : 'Logo' ?>" style="max-width:100%;max-height:45px;object-fit:contain" onerror="this.src='<?= $logo_fallback ?>'; this.onerror=function(){this.src='<?= $logo_fallback_png ?>'}">
            <?php else: ?>
                <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_fallback_png ?>'">
            <?php endif; ?>
        </div>
    </div>
    <a href="#" class="visible-phone">
        <div class="mode">
            <div class="moon-menu">
                <i class='bx bx-chevron-right iconX open-2'></i>
                <i class='bx bx-chevron-left iconX close-2'></i>
            </div>
        </div>
    </a>

    <div class="menu-bar">
        <div class="menu">

            <ul class="menu-links" style="position: relative;">
                <li class="<?php if (isset($menuPainel)) {
                    echo 'active';
                }; ?>">
                    <a class="tip-bottom" title="" href="<?= base_url() ?>"><i class='bx bx-home-alt iconX'></i>
                        <span class="title nav-title">Home</span>
                        <span class="title-tooltip">Início</span>
                    </a>
                </li>

                <?php
                $hasGestaoJuridica = $this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'gPeticaoIA');
                if ($hasGestaoJuridica) :
                    $gestaoActive = isset($menuClientes) || isset($menuProcessos) || isset($menuPrazos) || isset($menuAudiencias) || isset($menuConsultaProcessual) || isset($menuPecasGeradas);
                ?>
                <li class="submenu <?= $gestaoActive ? 'open active' : '' ?>">
                    <a href="#" class="tip-bottom" title=""><i class='bx bx-folder iconX'></i>
                        <span class="title">Gestão Jurídica</span>
                        <span class="title-tooltip">Gestão Jurídica</span>
                        <i class='bx bx-chevron-down submenu-arrow'></i>
                    </a>
                    <ul>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) { ?>
                            <li class="<?php if (isset($menuClientes)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('clientes') ?>"><i class='bx bx-user iconX'></i>
                                    <span class="title">Clientes</span>
                                    <span class="title-tooltip">Clientes</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) { ?>
                            <li class="<?php if (isset($menuProcessos)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('processos') ?>"><i class='bx bx-file-blank iconX'></i>
                                    <span class="title">Processos</span>
                                    <span class="title-tooltip">Processos</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) { ?>
                            <li class="<?php if (isset($menuPrazos)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('prazos') ?>"><i class='bx bx-calendar-check iconX'></i>
                                    <span class="title">Prazos</span>
                                    <span class="title-tooltip">Prazos Processuais</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) { ?>
                            <li class="<?php if (isset($menuAudiencias)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('audiencias') ?>"><i class='bx bx-calendar-event iconX'></i>
                                    <span class="title">Audiências</span>
                                    <span class="title-tooltip">Audiências</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) { ?>
                            <li class="<?php if (isset($menuConsultaProcessual)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('consulta-processual') ?>"><i class='bx bx-search-alt iconX'></i>
                                    <span class="title">Consulta Processual</span>
                                    <span class="title-tooltip">Consulta Processual - API CNJ</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'gPeticaoIA')) { ?>
                            <li class="<?php if (isset($menuPecasGeradas)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('pecas-geradas') ?>"><i class='bx bx-bot iconX'></i>
                                    <span class="title">Petições IA</span>
                                    <span class="title-tooltip">Gerar petições com IA</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) { ?>
                    <li class="<?php if (isset($menuServicos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('servicos') ?>"><i class='bx bx-wrench iconX'></i>
                            <span class="title">Serviços Jurídicos</span>
                            <span class="title-tooltip">Serviços</span>
                        </a>
                    </li>
                <?php } ?>

                <?php
                $hasFinanceiro = $this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vContrato')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vFatura')
                    || $this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca');
                if ($hasFinanceiro) :
                    $financeiroActive = isset($menuLancamentos) || isset($menuContratos) || isset($menuFaturas) || isset($menuCobrancas);
                ?>
                <li class="submenu <?= $financeiroActive ? 'open active' : '' ?>">
                    <a href="#" class="tip-bottom" title=""><i class='bx bx-dollar-circle iconX'></i>
                        <span class="title">Financeiro</span>
                        <span class="title-tooltip">Financeiro</span>
                        <i class='bx bx-chevron-down submenu-arrow'></i>
                    </a>
                    <ul>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) { ?>
                            <li class="<?php if (isset($menuLancamentos)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('financeiro/lancamentos') ?>"><i class="bx bx-bar-chart-alt-2 iconX"></i>
                                    <span class="title">Lançamentos</span>
                                    <span class="title-tooltip">Lançamentos</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vContrato')) { ?>
                            <li class="<?php if (isset($menuContratos)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('contratos') ?>"><i class="bx bx-file-blank iconX"></i>
                                    <span class="title">Contratos</span>
                                    <span class="title-tooltip">Contratos</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vFatura')) { ?>
                            <li class="<?php if (isset($menuFaturas)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('faturas') ?>"><i class="bx bx-receipt iconX"></i>
                                    <span class="title">Faturas</span>
                                    <span class="title-tooltip">Faturas</span>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca')) { ?>
                            <li class="<?php if (isset($menuCobrancas)) { echo 'active'; } ?>">
                                <a class="tip-bottom" title="" href="<?= site_url('cobrancas/cobrancas') ?>"><i class='bx bx-dollar-circle iconX'></i>
                                    <span class="title">Cobranças</span>
                                    <span class="title-tooltip">Cobranças</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php
                // Verifica acesso a logs - permite para todos os usuários autenticados por enquanto
                $permissao = $this->session->userdata('permissao');
                $isAdmin = false;
                if (is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador')) {
                    $isAdmin = true;
                } elseif (is_numeric($permissao)) {
                    $userId = $this->session->userdata('id_admin');
                    if ($userId) {
                        $this->load->database();
                        if ($this->db->table_exists('usuarios')) {
                            $this->db->select('permissoes_id');
                            $this->db->where('idUsuarios', $userId);
                            $user = $this->db->get('usuarios')->row();

                            if ($user && $user->permissoes_id) {
                                $this->db->where('idPermissao', $user->permissoes_id);
                                $permissao_obj = $this->db->get('permissoes')->row();
                                if ($permissao_obj && strtolower($permissao_obj->nome) === 'administrador') {
                                    $isAdmin = true;
                                }
                            }
                        }
                    }
                }

                if ($isAdmin || $this->permission->checkPermission($permissao, 'vLog') || true) {
                ?>
                <li class="submenu <?php if (isset($menuLogs)) { echo 'open active'; } ?>">
                    <a href="#" class="tip-bottom" title=""><i class='bx bx-cog iconX'></i>
                        <span class="title">Sistema</span>
                        <span class="title-tooltip">Sistema</span>
                        <i class='bx bx-chevron-down submenu-arrow'></i>
                    </a>
                    <ul>
                        <li class="<?php if (isset($menuLogs)) { echo 'active'; } ?>">
                            <a class="tip-bottom" title="" href="<?= site_url('logs') ?>"><i class='bx bx-file-blank iconX'></i>
                                <span class="title">Logs</span>
                                <span class="title-tooltip">Logs do Sistema</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>

        <div class="botton-content">
            <li class="">
                <a class="tip-bottom" title="" href="<?= site_url('login/sair'); ?>">
                    <i class='bx bx-log-out-circle iconX'></i>
                    <span class="title">Sair</span>
                    <span class="title-tooltip">Sair</span>
                </a>
            </li>
        </div>
    </div>
</nav>
<!--End sidebar-menu-->
