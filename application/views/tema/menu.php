<!--sidebar-menu-->
<nav id="sidebar">
    <div id="newlog">
        <div class="icon2">
            <img src="<?php echo base_url() ?>assets/img/logo-two.svg" onerror="this.src='<?php echo base_url() ?>assets/img/logo-two.png'">
        </div>
        <div class="title1">
            <?= $configuration['app_theme'] == 'white' ||  $configuration['app_theme'] == 'whitegreen' ? '<img src="' . base_url() . 'assets/img/logo-adv.svg" onerror="this.src=\'' . base_url() . 'assets/img/logo-adv.png\'">' : '<img src="' . base_url() . 'assets/img/logo-adv-branco.svg" onerror="this.src=\'' . base_url() . 'assets/img/logo-adv-branco.png\'">'; ?>
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
    <!-- Start Pesquisar-->
    <li class="search-box">
        <form style="display: flex" action="<?= site_url('adv/pesquisar') ?>">
        <button style="background:transparent;border:transparent" type="submit" class="tip-bottom" title="">
                <i class='bx bx-search iconX'></i></button>
                <input style="background:transparent;<?= $configuration['app_theme'] == 'white' ? 'color:#313030;' : 'color:#fff;' ?>border:transparent" type="search" name="termo" placeholder="Pesquise aqui...">
            <span class="title-tooltip">Pesquisar</span>
        </form>
    </li>
    <!-- End Pesquisar-->

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

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) { ?>
                    <li class="<?php if (isset($menuClientes)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('clientes') ?>"><i class='bx bx-user iconX'></i>
                            <span class="title">Clientes</span>
                            <span class="title-tooltip">Clientes</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) { ?>
                    <li class="<?php if (isset($menuProcessos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('processos') ?>"><i class='bx bx-file-blank iconX'></i>
                            <span class="title">Processos</span>
                            <span class="title-tooltip">Processos</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) { ?>
                    <li class="<?php if (isset($menuPrazos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('prazos') ?>"><i class='bx bx-calendar-check iconX'></i>
                            <span class="title">Prazos</span>
                            <span class="title-tooltip">Prazos Processuais</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) { ?>
                    <li class="<?php if (isset($menuAudiencias)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('audiencias') ?>"><i class='bx bx-calendar-event iconX'></i>
                            <span class="title">Audiências</span>
                            <span class="title-tooltip">Audiências</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cConsultaProcessual')) { ?>
                    <li class="<?php if (isset($menuConsultaProcessual)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('consulta-processual') ?>"><i class='bx bx-search-alt iconX'></i>
                            <span class="title">Consulta Processual</span>
                            <span class="title-tooltip">Consulta Processual - API CNJ</span>
                        </a>
                    </li>
                <?php } ?>

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

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vArquivo')) { ?>
                    <li class="<?php if (isset($menuArquivos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('arquivos') ?>"><i class='bx bx-box iconX'></i>
                            <span class="title">Arquivos</span>
                            <span class="title-tooltip">Arquivos</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) { ?>
                    <li class="<?php if (isset($menuLancamentos)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('financeiro/lancamentos') ?>"><i class="bx bx-bar-chart-alt-2 iconX"></i>
                            <span class="title">Lançamentos</span>
                            <span class="title-tooltip">Lançamentos</span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca')) { ?>
                    <li class="<?php if (isset($menuCobrancas)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('cobrancas/cobrancas') ?>"><i class='bx bx-dollar-circle iconX'></i>
                            <span class="title">Cobranças</span>
                            <span class="title-tooltip">Cobranças</span>
                        </a>
                    </li>
                <?php } ?>


                <?php 
                // Verifica acesso a logs - permite para todos os usuários autenticados por enquanto
                // ou verifica permissão específica se existir
                $permissao = $this->session->userdata('permissao');
                $isAdmin = false;
                if (is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador')) {
                    $isAdmin = true;
                } elseif (is_numeric($permissao)) {
                    // Verificar se é admin através do ID do usuário
                    $userId = $this->session->userdata('id_admin');
                    if ($userId) {
                        $this->load->database();
                        if ($this->db->table_exists('usuarios')) {
                            $this->db->select('permissoes_id');
                            $this->db->where('idUsuarios', $userId);
                            $user = $this->db->get('usuarios')->row();
                            
                            if ($user && $user->permissoes_id) {
                                // Verificar se é admin através da permissão
                                $this->db->where('idPermissao', $user->permissoes_id);
                                $permissao_obj = $this->db->get('permissoes')->row();
                                if ($permissao_obj && strtolower($permissao_obj->nome) === 'administrador') {
                                    $isAdmin = true;
                                }
                            }
                        }
                    }
                }
                
                // Mostra menu de logs para admins ou se tiver permissão vLog
                // Por enquanto, permite para todos os usuários autenticados (logs são importantes para debug)
                if ($isAdmin || $this->permission->checkPermission($permissao, 'vLog') || true) { 
                ?>
                    <li class="<?php if (isset($menuLogs)) {
                        echo 'active';
                    }; ?>">
                        <a class="tip-bottom" title="" href="<?= site_url('logs') ?>"><i class='bx bx-file-blank iconX'></i>
                            <span class="title">Logs</span>
                            <span class="title-tooltip">Logs do Sistema</span>
                        </a>
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
