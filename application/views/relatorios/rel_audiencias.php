<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-calendar-event"></i>
        </span>
        <h5>Relatório de Audiências</h5>
    </div>
    <div class="widget-box">
        <div class="widget-content nopadding tab-content">
            <div class="span12" style="padding: 20px;">
                <form action="<?php echo base_url() ?>index.php/relatorios/audienciasAgendadas" method="get" target="_blank">
                    <div class="control-group">
                        <label class="control-label">Relatório de Audiências Agendadas</label>
                    </div>
                    <div class="control-group">
                        <label for="dataInicial" class="control-label">Data Inicial</label>
                        <div class="controls">
                            <input type="date" id="dataInicial" name="dataInicial" class="span4" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="dataFinal" class="control-label">Data Final</label>
                        <div class="controls">
                            <input type="date" id="dataFinal" name="dataFinal" class="span4" />
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button btn btn-mini btn-success">
                            <span class="button__icon"><i class='bx bx-file'></i></span>
                            <span class="button__text2">Gerar Relatório de Audiências</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

