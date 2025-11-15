<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-file-alt"></i>
        </span>
        <h5>Relatório de Processos</h5>
    </div>
    <div class="widget-box">
        <div class="widget-content nopadding tab-content">
            <div class="span12" style="padding: 20px;">
                <form action="<?php echo base_url() ?>index.php/relatorios/processosRapid" method="get" target="_blank">
                    <div class="control-group">
                        <label class="control-label">Relatório Rápido</label>
                        <div class="controls">
                            <button type="submit" class="button btn btn-mini btn-success">
                                <span class="button__icon"><i class='bx bx-file'></i></span>
                                <span class="button__text2">Gerar Relatório de Processos</span>
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                <form action="<?php echo base_url() ?>index.php/relatorios/processosCustom" method="get" target="_blank">
                    <div class="control-group">
                        <label class="control-label">Relatório Customizado</label>
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
                    <div class="control-group">
                        <label for="status" class="control-label">Status</label>
                        <div class="controls">
                            <select id="status" name="status" class="span4">
                                <option value="">Todos</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="suspenso">Suspenso</option>
                                <option value="arquivado">Arquivado</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="tipo_processo" class="control-label">Tipo de Processo</label>
                        <div class="controls">
                            <select id="tipo_processo" name="tipo_processo" class="span4">
                                <option value="">Todos</option>
                                <option value="civel">Cível</option>
                                <option value="trabalhista">Trabalhista</option>
                                <option value="tributario">Tributário</option>
                                <option value="criminal">Criminal</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="button btn btn-mini btn-success">
                            <span class="button__icon"><i class='bx bx-file'></i></span>
                            <span class="button__text2">Gerar Relatório Customizado</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

