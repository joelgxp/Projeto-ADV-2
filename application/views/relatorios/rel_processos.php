<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-alt"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget-content">
                <ul style="flex-direction: row;" class="site-stats">
                    <li><a href="<?php echo base_url() ?>index.php/relatorios/processosRapid" target="_blank"><i
                                    class="fas fa-file-alt"></i> <small>Todos os Processos - pdf</small></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-alt"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            <div class="widget-content">
                <div class="span12 well">
                    <form target="_blank" action="<?php echo base_url() ?>index.php/relatorios/processosCustom"
                          method="get">
                        <div class="span6">
                            <label for="dataInicial">Distribuído de:</label>
                            <input type="date" id="dataInicial" name="dataInicial" class="span12"/>
                        </div>
                        <div class="span6">
                            <label for="dataFinal">até:</label>
                            <input type="date" id="dataFinal" name="dataFinal" class="span12"/>
                        </div>
                        <div class="span6">
                            <label for="status">Status:</label>
                            <select id="status" name="status" class="span12">
                                <option value="">Todos</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="suspenso">Suspenso</option>
                                <option value="arquivado">Arquivado</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                        <div class="span6">
                            <label for="tipo_processo">Tipo de Processo:</label>
                            <select id="tipo_processo" name="tipo_processo" class="span12">
                                <option value="">Todos</option>
                                <option value="civel">Cível</option>
                                <option value="trabalhista">Trabalhista</option>
                                <option value="tributario">Tributário</option>
                                <option value="criminal">Criminal</option>
                                <option value="outros">Outros</option>
                            </select>
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

