<div class="row-fluid" style="margin-top: 0">
    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-calendar-event"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            <div class="widget-content">
                <div class="span12 well">
                    <form target="_blank" action="<?php echo base_url() ?>index.php/relatorios/audienciasAgendadas"
                          method="get">
                        <div class="span6">
                            <label for="dataInicial">Data de:</label>
                            <input type="date" id="dataInicial" name="dataInicial" class="span12"/>
                        </div>
                        <div class="span6">
                            <label for="dataFinal">até:</label>
                            <input type="date" id="dataFinal" name="dataFinal" class="span12"/>
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
</div>

