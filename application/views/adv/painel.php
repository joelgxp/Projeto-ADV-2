<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo base_url(); ?>js/dist/excanvas.min.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.donutRenderer.min.js"></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar.min.js'></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar/locales/pt-br.js'></script>

<link href='<?= base_url(); ?>assets/css/fullcalendar.min.css' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<!-- New Bem-vindos -->
<div id="content-bemv">
    <div class="bemv">Dashboard</div>
    <div></div>
</div>

<!-- Action boxes -->
<ul class="cardBox">
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('clientes') ?>">
                <div class="grid-blak">
                    <div class="numbers">Clientes</div>
                    <div class="cardName">F1</div>
                </div>
                <div class="lord-icon02">
                    <i class='bx bx-user iconBx02'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('processos') ?>">
                <div class="grid-blak">
                    <div class="numbers">Processos</div>
                    <div class="cardName">F2</div>
                </div>
                <div class="lord-icon02">
                    <i class='bx bx-file-blank iconBx02'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('servicos') ?>">
                <div class="grid-blak">
                    <div class="numbers">Serviços Jurídicos</div>
                    <div class="cardName">F3</div>
                </div>
                <div class="lord-icon03">
                    <i class='bx bx-wrench iconBx03'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('prazos') ?>">
                <div class="grid-blak">
                    <div class="numbers N-tittle">Prazos</div>
                    <div class="cardName">F4</div>
                </div>
                <div class="lord-icon04">
                    <i class='bx bx-calendar-check iconBx04'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('audiencias') ?>">
                <div class="grid-blak">
                    <div class="numbers N-tittle">Audiências</div>
                    <div class="cardName">F5</div>
                </div>
                <div class="lord-icon05">
                    <i class='bx bx-calendar-event iconBx05'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) : ?>
        <li class="card">
            <a class="cardLink" href="<?= site_url('financeiro/lancamentos') ?>">
                <div class="grid-blak">
                    <div class="numbers N-tittle">Financeiro</div>
                    <div class="cardName">F6</div>
                </div>
                <div class="lord-icon06">
                    <i class="bx bx-bar-chart-alt-2 iconBx06"></i>
                </div>
            </a>
        </li>
    <?php endif ?>
</ul>
<!-- End-Action boxes -->

<div class="row-fluid" style="margin-top: 0; display: flex">
    <div class="Sspan12">
        <div class="widget-box2">
            <div>
                <h5 class="cardHeader">Agenda - Audiências e Prazos</h5>
            </div>
            <div class="widget-content">
                <table>
                    <div id='source-calendar'>
                        <form method="post">
                            <select style="padding-left: 30px" class="span12" name="tipoEventoGet" id="tipoEventoGet" value="">
                                <option value="">Todos os Eventos</option>
                                <option value="audiencia">Audiências</option>
                                <option value="prazo">Prazos</option>
                            </select>
                            <button type="button" class="btn-xs" id="btn-calendar"><i class="bx bx-search iconX2"></i></button>
                        </form>
                    </div>
                </table>
            </div>
        </div>

        <!-- New widget right -->
        <div class="new-statisc">
            <div class="widget-box-new widbox-blak" style="height:100%">
                <div>
                    <h5 class="cardHeader">Estatísticas do Sistema</h5>
                </div>

                <div class="new-bottons">
                    <a href="<?php echo base_url(); ?>index.php/clientes/adicionar" class="card tip-top" title="Adicionar Clientes">
                        <div><i class='bx bxs-group iconBx'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->table_exists('clientes') ? $this->db->count_all('clientes') : 0; ?></div>
                            <div class="cardName">Clientes</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/processos/adicionar" class="card tip-top" title="Adicionar Processo">
                        <div><i class='bx bxs-file-blank iconBx2'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->table_exists('processos') ? $this->db->count_all('processos') : 0; ?></div>
                            <div class="cardName">Processos</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url() ?>index.php/servicos/adicionar" class="card tip-top" title="Adicionar Serviços Jurídicos">
                        <div><i class='bx bxs-stopwatch iconBx3'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->table_exists('servicos_juridicos') ? $this->db->count_all('servicos_juridicos') : ($this->db->table_exists('servicos') ? $this->db->count_all('servicos') : 0); ?></div>
                            <div class="cardName">Serviços Jurídicos</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/prazos/adicionar" class="card tip-top" title="Adicionar Prazo">
                        <div><i class='bx bxs-calendar-check iconBx4'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->table_exists('prazos') ? $this->db->count_all('prazos') : 0; ?></div>
                            <div class="cardName">Prazos</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/audiencias/adicionar" class="card tip-top" title="Adicionar Audiência">
                        <div><i class='bx bxs-calendar-event iconBx6'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->table_exists('audiencias') ? $this->db->count_all('audiencias') : 0; ?></div>
                            <div class="cardName">Audiências</div>
                        </div>
                    </a>

                    <!-- responsavel por fazer complementar a variavel "$financeiro_mes_dia->" de receita e despesa -->
                    <?php if (isset($estatisticas_financeiro) && $estatisticas_financeiro != null && is_object($estatisticas_financeiro)) {
                        if ((isset($estatisticas_financeiro->total_receita) && $estatisticas_financeiro->total_receita != null) || 
                            (isset($estatisticas_financeiro->total_despesa) && $estatisticas_financeiro->total_despesa != null) || 
                            (isset($estatisticas_financeiro->total_receita_pendente) && $estatisticas_financeiro->total_receita_pendente != null) || 
                            (isset($estatisticas_financeiro->total_despesa_pendente) && $estatisticas_financeiro->total_despesa_pendente != null)) {  ?>

                            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
                                <?php 
                                $diaRec = "VALOR_" . date('m') . "_REC";
                                $diaDes = "VALOR_" . date('m') . "_DES";
                                $valorRec = (isset($financeiro_mes_dia) && is_object($financeiro_mes_dia) && isset($financeiro_mes_dia->$diaRec)) ? $financeiro_mes_dia->$diaRec : 0;
                                $valorDes = (isset($financeiro_mes_dia) && is_object($financeiro_mes_dia) && isset($financeiro_mes_dia->$diaDes)) ? $financeiro_mes_dia->$diaDes : 0;
                                ?>

                                <a href="<?php echo base_url() ?>index.php/financeiro/lancamentos" class="card tip-top" title="Adicionar receita">
                                    <div><i class='bx bxs-up-arrow-circle iconBx7'></i></div>
                                    <div>
                                        <div class="cardName1 cardName2">R$ <?php echo number_format(($valorRec - $valorDes), 2, ',', '.'); ?></div>
                                        <div class="cardName">Receita do dia</div>
                                    </div>
                                </a>

                                <a href="<?php echo base_url() ?>index.php/financeiro/lancamentos" class="card tip-top" title="Adiciona despesa">
                                    <div><i class='bx bxs-down-arrow-circle iconBx8'></i></div>
                                    <div>
                                        <div class="cardName1 cardName2">R$ <?php echo number_format($valorDes, 2, ',', '.'); ?></div>
                                        <div class="cardName">Despesa do dia</div>
                                    </div>
                                </a>
                            <?php endif ?>

                    <?php  }
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim new widget right -->

<?php if (isset($estatisticas_financeiro) && $estatisticas_financeiro != null && is_object($estatisticas_financeiro)) {
    if ((isset($estatisticas_financeiro->total_receita) && $estatisticas_financeiro->total_receita != null) || 
        (isset($estatisticas_financeiro->total_despesa) && $estatisticas_financeiro->total_despesa != null) || 
        (isset($estatisticas_financeiro->total_receita_pendente) && $estatisticas_financeiro->total_receita_pendente != null) || 
        (isset($estatisticas_financeiro->total_despesa_pendente) && $estatisticas_financeiro->total_despesa_pendente != null)) {  ?>

        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
            <!-- Start Charts -->
            <div class="new-balance">
                <div class="widget-box0">
                    <div class="widget-title2">
                        <h5 class="cardHeader">Balanço Mensal do Ano</h5>
                        <form method="get" style="display:flex;margin-right:18px;justify-content:flex-end">
                            <input type="number" name="year" style="width:65px;margin-left:17px;margin-bottom:25px;margin-top:10px;padding-left: 35px" value="<?php echo intval(preg_replace('/[^0-9]/', '', $this->input->get('year'))) ?: date('Y') ?>">
                            <button type="submit" class="btn-xsx"><i class='bx bx-search iconX'></i></button>
                        </form>
                    </div>
                    <div class="widget-content" style="padding:10px 25px 5px 25px">
                        <div class="row-fluid" style="margin-top:-35px;">
                            <div class="span12">
                                <canvas id="myChart" style="overflow-x: scroll;margin-left: -14px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="widget-box-statist">
                    <h5 class="cardHeader">Estatísticas Financeiras</h5>
                    <div class="widget-content" style="padding:10px;margin:25px 0 0">
                        <canvas id="estatisticasFinanceiras"> </canvas>
                    </div>
                </div>
            </div>
        <?php endif ?>

<script type="text/javascript">
    if (window.outerWidth > 2000) {
        Chart.defaults.font.size = 15;
    };
    if (window.outerWidth < 2000 && window.outerWidth > 1367) {
        Chart.defaults.font.size = 11;
    };
    if (window.outerWidth < 1367 && window.outerWidth > 480) {
        Chart.defaults.font.size = 9.5;
    };
    if (window.outerWidth < 480) {
        Chart.defaults.font.size = 8.5;
    };

    var ctx = document.getElementById('myChart').getContext('2d');
    var estatisticasFinanceiras = document.getElementById('estatisticasFinanceiras').getContext('2d');

    <?php
    // Função auxiliar para obter valor seguro do objeto financeiro_mes
    function get_financeiro_valor($obj, $prop, $default = 0) {
        return (isset($obj) && is_object($obj) && isset($obj->$prop)) ? (float)$obj->$prop : $default;
    }
    
    $fm = isset($financeiro_mes) && is_object($financeiro_mes) ? $financeiro_mes : (object)[];
    $fmi = isset($financeiro_mesinadipl) && is_object($financeiro_mesinadipl) ? $financeiro_mesinadipl : (object)[];
    ?>
    
    var myChart = new Chart(ctx, {
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                    label: 'Receita Líquida',
                    data: [<?php 
                        echo number_format((get_financeiro_valor($fm, 'VALOR_JAN_REC') - get_financeiro_valor($fm, 'VALOR_JAN_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_FEV_REC') - get_financeiro_valor($fm, 'VALOR_FEV_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_MAR_REC') - get_financeiro_valor($fm, 'VALOR_MAR_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_ABR_REC') - get_financeiro_valor($fm, 'VALOR_ABR_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_MAI_REC') - get_financeiro_valor($fm, 'VALOR_MAI_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_JUN_REC') - get_financeiro_valor($fm, 'VALOR_JUN_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_JUL_REC') - get_financeiro_valor($fm, 'VALOR_JUL_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_AGO_REC') - get_financeiro_valor($fm, 'VALOR_AGO_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_SET_REC') - get_financeiro_valor($fm, 'VALOR_SET_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_OUT_REC') - get_financeiro_valor($fm, 'VALOR_OUT_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_NOV_REC') - get_financeiro_valor($fm, 'VALOR_NOV_DES')), 2, '.', '') . ',';
                        echo number_format((get_financeiro_valor($fm, 'VALOR_DEZ_REC') - get_financeiro_valor($fm, 'VALOR_DEZ_DES')), 2, '.', '');
                    ?>],

                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Receita Bruta',
                    data: [<?php 
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JAN_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_FEV_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_MAR_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_ABR_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_MAI_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JUN_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JUL_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_AGO_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_SET_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_OUT_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_NOV_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_DEZ_REC'), 2, '.', '');
                    ?>],

                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Despesas',
                    data: [<?php 
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JAN_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_FEV_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_MAR_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_ABR_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_MAI_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JUN_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_JUL_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_AGO_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_SET_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_OUT_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_NOV_DES'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fm, 'VALOR_DEZ_DES'), 2, '.', '');
                    ?>],

                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Inadimplência',
                    data: [<?php 
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_JAN_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_FEV_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_MAR_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_ABR_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_MAI_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_JUN_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_JUL_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_AGO_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_SET_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_OUT_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_NOV_REC'), 2, '.', '') . ',';
                        echo number_format(get_financeiro_valor($fmi, 'VALOR_DEZ_REC'), 2, '.', '');
                    ?>],

                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderRadius: 15,
                }
            ]

        },
        // configuração
        type: 'bar',
        options: {
            locale: 'pt-BR',
            scales: {
                y: {
                    ticks: {
                        callback: (value, index, values) => {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumSignificantDidits: 1
                            }).format(value);
                        }
                    }
                },
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Meses'
                    }
                }
            },

            plugins: {
                tooltip: {
                    callbacks: {
                        beforeTitle: function(context) {
                            return 'Referente ao mês de';
                        }
                    }
                },

                legend: {
                    position: "bottom",
                    labels: {
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    var statusFinanceiro = document.getElementById('estatisticasFinanceiras');
    if (statusFinanceiro) {
    var myChart = new Chart(statusFinanceiro, {
        data: {
            labels: [
                'Receita total', 'Receita pendente',
                'Previsto em caixa', 'Despesa total',
                'Despesa pendente', 'Previsto a entrar'
            ],
            datasets: [{
                label: 'Total',
                data: [
                    <?php 
                    $total_receita = (isset($estatisticas_financeiro) && is_object($estatisticas_financeiro) && isset($estatisticas_financeiro->total_receita)) ? $estatisticas_financeiro->total_receita : 0;
                    $total_receita_pendente = (isset($estatisticas_financeiro) && is_object($estatisticas_financeiro) && isset($estatisticas_financeiro->total_receita_pendente)) ? $estatisticas_financeiro->total_receita_pendente : 0;
                    $total_despesa = (isset($estatisticas_financeiro) && is_object($estatisticas_financeiro) && isset($estatisticas_financeiro->total_despesa)) ? $estatisticas_financeiro->total_despesa : 0;
                    $total_despesa_pendente = (isset($estatisticas_financeiro) && is_object($estatisticas_financeiro) && isset($estatisticas_financeiro->total_despesa_pendente)) ? $estatisticas_financeiro->total_despesa_pendente : 0;
                    echo number_format($total_receita, 2, '.', ''); ?>,
                    <?php echo number_format($total_receita_pendente, 2, '.', ''); ?>,
                    <?php echo number_format(($total_receita - $total_despesa), 2, '.', ''); ?>,
                    <?php echo number_format($total_despesa, 2, '.', ''); ?>,
                    <?php echo number_format($total_despesa_pendente, 2, '.', ''); ?>,
                    <?php echo number_format(($total_receita_pendente - $total_despesa_pendente), 2, '.', ''); ?>
                ],

                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderWidth: 1
            }]
        },

        // configuração
        type: 'polarArea',
        options: {
            locale: 'pt-BR',
            scales: {
                r: {
                    ticks: {
                        callback: (value, index, values) => {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumSignificantDidits: 1
                            }).format(value);
                        }
                    },
                    beginAtZero: true,
                }
            },
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        usePointStyle: true,

                    }
                }
            }
        }
    });
    }

    function responsiveFonts() {
        myChart.update();
    }
</script>
<?php  }
} ?>
</div>
</div>

<!-- Start Processos e Prazos -->
<div class="span12A" style="margin-left: 0">
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) : ?>
    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Processos Em Andamento</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>N° Processo</th>
                        <th>Cliente</th>
                        <th>Classe</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($processos_em_andamento) && !empty($processos_em_andamento)) : ?>
                        <?php foreach ($processos_em_andamento as $p) : ?>
                            <?php
                                    switch ($p->status ?? '') {
                                        case 'Em Andamento':
                                            $cor = '#436eee';
                                            break;
                                        case 'Aguardando':
                                            $cor = '#FF7F00';
                                            break;
                                        case 'Finalizado':
                                            $cor = '#256';
                                            break;
                                        case 'Suspenso':
                                            $cor = '#CDB380';
                                            break;
                                        case 'Arquivado':
                                            $cor = '#808080';
                                            break;
                                        default:
                                            $cor = '#E0E4CC';
                                            break;
                                    }
                                ?>
                            <tr>
                                <td>
                                    <?= isset($p->numeroProcesso) ? $p->numeroProcesso : 'N/A' ?>
                                </td>

                                <td class="cli1">
                                    <?= isset($p->nomeCliente) ? $p->nomeCliente : 'N/A' ?>
                                </td>

                                <td><?= isset($p->classe) ? $p->classe : 'N/A' ?></td>

                                <td>
                                    <span class="badge" style="background-color: <?= $cor ?>; border-color: <?= $cor ?>;"><?= isset($p->status) ? $p->status : 'N/A' ?></span>
                                </td>

                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) : ?>
                                        <a href="<?= base_url() ?>index.php/processos/visualizar/<?= isset($p->idProcessos) ? $p->idProcessos : '' ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i> </a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">Nenhum processo em andamento.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>
    
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) : ?>
    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Prazos Vencidos</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Processo</th>
                        <th>Cliente</th>
                        <th>Descrição</th>
                        <th>Data Vencimento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($prazos_vencidos) && !empty($prazos_vencidos)) : ?>
                        <?php foreach ($prazos_vencidos as $pz) : ?>
                            <?php
                                    $status_labels = [
                                        'pendente' => ['label' => 'Pendente', 'cor' => '#FF7F00'],
                                        'concluido' => ['label' => 'Concluído', 'cor' => '#4d9c79'],
                                        'cancelado' => ['label' => 'Cancelado', 'cor' => '#808080'],
                                    ];
                                    $status = $pz->status ?? 'pendente';
                                    $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                                ?>
                            <tr>
                                <td>
                                    <?= isset($pz->numeroProcesso) ? $pz->numeroProcesso : 'N/A' ?>
                                </td>

                                <td class="cli1">
                                    <?= isset($pz->nomeCliente) ? $pz->nomeCliente : 'N/A' ?>
                                </td>

                                <td><?= isset($pz->descricao) ? $pz->descricao : 'N/A' ?></td>
                                
                                <td><?php if (isset($pz->dataVencimento) && $pz->dataVencimento != null) {
                                    echo date('d/m/Y', strtotime($pz->dataVencimento));
                                } else {
                                    echo "N/A";
                                } ?></td>
                                
                                <td>
                                    <span class="badge" style="background-color: <?= $status_info['cor'] ?>; border-color: <?= $status_info['cor'] ?>;"><?= $status_info['label'] ?></span>
                                </td>

                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) : ?>
                                        <a href="<?= base_url() ?>index.php/prazos/visualizar/<?= isset($pz->idPrazos) ? $pz->idPrazos : '' ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i> </a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">Nenhum prazo vencido.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) : ?>
    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Audiências Agendadas</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Processo</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($audiencias_agendadas) && !empty($audiencias_agendadas)) : ?>
                        <?php foreach ($audiencias_agendadas as $aud) : ?>
                            <?php
                                    $status_labels = [
                                        'agendada' => ['label' => 'Agendada', 'cor' => '#436eee'],
                                        'realizada' => ['label' => 'Realizada', 'cor' => '#4d9c79'],
                                        'cancelada' => ['label' => 'Cancelada', 'cor' => '#808080'],
                                    ];
                                    $status = $aud->status ?? 'agendada';
                                    $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'cor' => '#E0E4CC'];
                                ?>
                            <tr>
                                <td>
                                    <?= isset($aud->numeroProcesso) ? $aud->numeroProcesso : 'N/A' ?>
                                </td>

                                <td class="cli1">
                                    <?= isset($aud->nomeCliente) ? $aud->nomeCliente : 'N/A' ?>
                                </td>

                                <td><?= isset($aud->tipo) ? $aud->tipo : 'N/A' ?></td>
                                
                                <td><?php if (isset($aud->dataHora) && $aud->dataHora != null) {
                                    echo date('d/m/Y H:i', strtotime($aud->dataHora));
                                } else {
                                    echo "N/A";
                                } ?></td>
                                
                                <td><?= isset($aud->local) ? $aud->local : 'N/A' ?></td>
                                
                                <td>
                                    <span class="badge" style="background-color: <?= $status_info['cor'] ?>; border-color: <?= $status_info['cor'] ?>;"><?= $status_info['label'] ?></span>
                                </td>

                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) : ?>
                                        <a href="<?= base_url() ?>index.php/audiencias/visualizar/<?= isset($aud->idAudiencias) ? $aud->idAudiencias : '' ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i> </a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">Nenhuma audiência agendada.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>

    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Últimos Lançamentos Pendentes</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered lanc-table">
                <thead>
                    <tr>
                        <th class="tipo-col">Tipo</th>
                        <th class="cliente-col">Cliente/Fornecedor</th>
                        <th class="descricao-col">Descrição</th>
                        <th class="vencimento-col">Vencimento</th>
                        <th class="valor-col">V.T. Faturado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($lancamentos) && !empty($lancamentos)): ?>
                        <?php foreach ($lancamentos as $lancamento): ?>
                            <tr>
                                <td>
                                    <?php if ($lancamento->tipo == 'receita'): ?>
                                        <span class="label label-success"><b><?php echo ucfirst($lancamento->tipo); ?></b></span>
                                    <?php elseif ($lancamento->tipo == 'despesa'): ?>
                                        <span class="label label-important"><b><?php echo ucfirst($lancamento->tipo); ?></b></span>
                                    <?php else: ?>
                                        <?php echo ucfirst($lancamento->tipo); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-truncate"><?php echo isset($lancamento->cliente_fornecedor) ? $lancamento->cliente_fornecedor : '-'; ?></td>
                                <td class="text-truncate"><?php echo isset($lancamento->descricao) ? $lancamento->descricao : '-'; ?></td>
                                <td><?php echo isset($lancamento->data_vencimento) ? date_format(date_create($lancamento->data_vencimento), 'd/m/Y') : '-'; ?></td>
                                <td>R$ <?php 
                                    // Usar valor_desconto se > 0, senão usar valor
                                    $valor = floatval($lancamento->valor_desconto > 0 ? $lancamento->valor_desconto : $lancamento->valor);
                                    echo number_format($valor, 2, ',', '.'); 
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nenhum lançamento encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

                        
</div>
<!-- Fim Status Processos e Prazos -->

<!-- Modal Audiência Calendar -->
<div id="calendarModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Detalhes da Audiência</h3>
    </div>
    <div class="modal-body">
        <h4><b>ID:</b> <span id="modalId" class="modal-id"></span></h4>
        <div id="modalTipo" class="modal-Tipo"></div>
        <div id="modalProcesso" class="modal-Processo"></div>
        <div id="modalDataHora" class="modal-DataHora"></div>
        <div id="modalLocal" class="modal-Local"></div>
        <div id="modalStatus" class="modal-Status"></div>
        <div id="modalObservacoes" class="modal-Observacoes"></div>
    </div>
    <div class="modal-footer">
        <?php
            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
                echo '<a id="modalIdVisualizar" style="margin-right: 1%" href="" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
            }
            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) {
                echo '<a id="modalIdEditar" style="margin-right: 1%" href="" class="btn btn-info tip-top" title="Editar Audiência"><i class="fas fa-edit"></i></a>';
            }
            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dAudiencia')) {
                echo '<a id="linkExcluir" href="#modal-excluir-audiencia" role="button" data-toggle="modal" audiencia="" class="btn btn-danger tip-top" title="Excluir Audiência"><i class="fas fa-trash-alt"></i></a>  ';
            }
        ?>
    </div>
</div>

<!-- Modal Excluir Audiência -->
<div id="modal-excluir-audiencia" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/audiencias/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Audiência</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modalIdExcluirAudiencia" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta audiência?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<!-- Modal Excluir Prazo -->
<div id="modal-excluir-prazo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/prazos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Prazo</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modalIdExcluirPrazo" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este prazo?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var srcCalendarEl = document.getElementById('source-calendar');
        var srcCalendar = new FullCalendar.Calendar(srcCalendarEl, {
            locale: 'pt-br',
            height: 500,
            editable: false,
            selectable: false,
            businessHours: true,
            dayMaxEvents: true,
            displayEventTime: true,
            events: {
                url: "<?= base_url() . "index.php/adv/calendario"; ?>",
                method: 'GET',
                extraParams: function() {
                    return {
                        tipoEvento: $("#tipoEventoGet").val() || '',
                    };
                },
                failure: function(error) {
                    console.error('Erro ao buscar eventos do calendário:', error);
                    alert('Falha ao buscar eventos do calendário! Verifique o console para mais detalhes.');
                },
            },
            eventClick: function(info) {
                var eventObj = info.event.extendedProps;
                var eventTitle = info.event.title || '';
                var isAudiencia = eventTitle.indexOf('Audiência') !== -1;
                var isPrazo = eventTitle.indexOf('Prazo') !== -1;
                
                $('#modalId').html(eventObj.id);
                
                if (isAudiencia) {
                    $('#myModalLabel').text('Detalhes da Audiência');
                    $('#modalIdVisualizar').attr("href", "<?php echo base_url(); ?>index.php/audiencias/visualizar/" + eventObj.id);
                    if (eventObj.editar) {
                        $('#modalIdEditar').show();
                        $('#linkExcluir').show();
                        $('#modalIdEditar').attr("href", "<?php echo base_url(); ?>index.php/audiencias/editar/" + eventObj.id);
                        $('#linkExcluir').attr("href", "#modal-excluir-audiencia");
                        $('#linkExcluir').attr("audiencia", eventObj.id);
                        $('#modalIdExcluirAudiencia').val(eventObj.id);
                    } else {
                        $('#modalIdEditar').hide();
                        $('#linkExcluir').hide();
                    }
                } else if (isPrazo) {
                    $('#myModalLabel').text('Detalhes do Prazo');
                    $('#modalIdVisualizar').attr("href", "<?php echo base_url(); ?>index.php/prazos/visualizar/" + eventObj.id);
                    if (eventObj.editar) {
                        $('#modalIdEditar').show();
                        $('#linkExcluir').show();
                        $('#modalIdEditar').attr("href", "<?php echo base_url(); ?>index.php/prazos/editar/" + eventObj.id);
                        $('#linkExcluir').attr("href", "#modal-excluir-prazo");
                        $('#linkExcluir').attr("prazo", eventObj.id);
                        $('#modalIdExcluirPrazo').val(eventObj.id);
                    } else {
                        $('#modalIdEditar').hide();
                        $('#linkExcluir').hide();
                    }
                }
                
                $('#modalTipo').html(eventObj.tipo || 'N/A');
                $('#modalProcesso').html(eventObj.processo || 'N/A');
                $('#modalDataHora').html(eventObj.dataHora || 'N/A');
                $('#modalLocal').html(eventObj.local || 'N/A');
                $('#modalStatus').html(eventObj.status || 'N/A');
                $('#modalObservacoes').html(eventObj.observacoes || 'N/A');
                $('#calendarModal').modal();
            },
        });

        srcCalendar.render();

        $('#btn-calendar').on('click', function() {
            srcCalendar.refetchEvents();
        });
    });
</script>
