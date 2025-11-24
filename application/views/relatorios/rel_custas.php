<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget-content">
                <ul style="flex-direction: row;" class="site-stats">
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/custasRapid"><i class="fas fa-file-invoice-dollar"></i> <small>Relatório do mês - pdf</small></a></li>
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/custasRapid?format=xls"><i class="fas fa-file-invoice-dollar"></i> <small>Relatório do mês - xls</small></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            <div class="widget-content">
                <form target="_blank" action="<?php echo base_url() ?>index.php/relatorios/custasCustom" method="get">
                    <div class="span12 well">

                        <div class="span6">
                            <label for="">Vencimento de:</label>
                            <input type="date" name="dataInicial" class="span12" />
                        </div>
                        <div class="span6">
                            <label for="">até:</label>
                            <input type="date" name="dataFinal" class="span12" />
                        </div>

                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <div class="span6">
                            <label for="">Situação:</label>
                            <select name="situacao" class="span12">
                                <option value="">Todas</option>
                                <option value="0">Pendente</option>
                                <option value="1">Pago</option>
                            </select>
                        </div>
                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <div class="span6">
                            <label for="">Tipo de impressão:</label>
                            <select name="format" class="span12">
                                <option value="">PDF</option>
                                <option value="xls">XLS</option>
                            </select>
                        </div>
                    </div>

                    <div class="span12" style="margin-top: 10px;">
                        <button type="submit" class="button btn btn-inverse">
                            <span class="button__icon"><i class="bx bx-printer"></i></span>
                            <span class="button__text2">Imprimir</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

