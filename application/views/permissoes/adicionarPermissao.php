<div class="span12" style="margin-left: 0">
    <form action="<?php echo base_url(); ?>index.php/permissoes/adicionar" id="formPermissao" method="post">
        <div class="span12" style="margin-left: 0">
            <div class="widget-box">
                <div class="widget-title" style="margin: -20px 0 0">
               <span class="icon">
               <i class="fas fa-lock"></i>
               </span>
                    <h5>Cadastro de Permissão</h5>
                </div>
                <div class="widget-content">
                    <div class="span6">
                        <label>Nome da Permissão</label>
                        <input name="nome" type="text" id="nome" class="span12" />
                    </div>
                    <div class="span6">
                        <br />
                        <label>
                            <input name="marcarTodos" type="checkbox" value="1" id="marcarTodos" />
                            <span class="lbl"> Marcar Todos</span>
                        </label>
                        <br />
                    </div>
                    <div class="accordion" id="collapse-group">
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                                      <span><i class='bx bx-group icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Clientes</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse in accordion-body" id="collapseGOne">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vCliente" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Cliente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aCliente" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Cliente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eCliente" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Cliente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dCliente" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Cliente</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vClienteDadosSensiveis" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Visualizar Dados Sensíveis</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eClienteDadosSensiveis" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Dados Sensíveis</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="vClienteProcessos" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Processos do Cliente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="vClienteDocumentos" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Documentos</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vClienteFinanceiro" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Visualizar Dados Financeiros</span>
                                                </label>
                                            </td>
                                            <td colspan="3"></td>
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
                                      <span><i class='bx bx-file-blank icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Processos</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGTwo">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vProcesso" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Processo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aProcesso" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Processo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eProcesso" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Processo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dProcesso" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Processo</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <label>
                                                    <input name="sProcesso" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Sincronizar Processo com API</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree" data-toggle="collapse">
                                      <span><i class='bx bx-stopwatch icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Serviços Jurídicos</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vServico" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Serviço</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aServico" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Serviço</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eServico" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Serviço</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dServico" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Serviço</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree3" data-toggle="collapse">
                                      <span><i class='bx bx-calendar-check icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Prazos</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree3">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vPrazo" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Prazo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aPrazo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Prazo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="ePrazo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Prazo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dPrazo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Prazo</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree33" data-toggle="collapse">
                                      <span><i class='bx bx-calendar-event icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Audiências</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree33">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vAudiencia" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Audiência</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aAudiencia" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Audiência</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eAudiencia" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Audiência</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dAudiencia" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Audiência</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree34" data-toggle="collapse">
                                      <span><i class='bx bx-search-alt icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Consulta Processual</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree34">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td colspan="4">
                                                <label>
                                                    <input name="cConsultaProcessual" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Consultar Processos na API CNJ/DataJud</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree333" data-toggle="collapse">
                                      <span><i class='bx bx-credit-card-front icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Cobranças</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree333">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vCobranca" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Cobranças</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aCobranca" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Cobranças</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eCobranca" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Cobranças</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dCobranca" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Cobranças</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree33333" data-toggle="collapse">
                                      <span><i class='bx bx-box icon-cli'></i></span>
                                      <h5 style="padding-left: 28px">Arquivos</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree33333">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vArquivo" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Arquivo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aArquivo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Arquivo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eArquivo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Arquivo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dArquivo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Arquivo</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree333343" data-toggle="collapse">
                                      <span><i class="bx bx-bar-chart-square icon-cli"></i></span>
                                      <h5 style="padding-left: 28px">Financeiro</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree333343">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vPagamento" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Pagamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aPagamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Pagamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="ePagamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Pagamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dPagamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Pagamento</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="vLancamento" class="marcar" type="checkbox" checked="checked" value="1" />
                                                    <span class="lbl"> Visualizar Lançamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="aLancamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Adicionar Lançamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="eLancamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Editar Lançamento</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="dLancamento" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Excluir Lançamento</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree333335" data-toggle="collapse">
                                      <span><i class="bx bx-chart icon-cli"></i></span>
                                      <h5 style="padding-left: 28px">Relatórios</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree333335">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="rCliente" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Cliente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="rServico" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Serviço</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="rProcesso" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Processo</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="rPrazo" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Prazo</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="rAudiencia" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Audiência</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="rFinanceiro" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Relatório Financeiro</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group widget-box">
                            <div class="accordion-heading">
                                <div class="widget-title">
                                    <a data-parent="#collapse-group" href="#collapseGThree333338" data-toggle="collapse">
                                      <span><i class="bx bx-cog icon-cli"></i></span>
                                      <h5 style="padding-left: 28px">Configurações e Sistema</h5>
                                    </a>
                                </div>
                            </div>
                            <div class="collapse accordion-body" id="collapseGThree333338">
                                <div class="widget-content">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="cUsuario" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Configurar Usuário</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="cEmitente" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Configurar Emitente</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="cPermissao" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Configurar Permissão</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="cBackup" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Backup</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input name="cAuditoria" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Auditoria</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="cEmail" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Emails</span>
                                                </label>
                                            </td>
                                            <td>
                                                <label>
                                                    <input name="cSistema" class="marcar" type="checkbox" value="1" />
                                                    <span class="lbl"> Sistema</span>
                                                </label>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-success" style="display:inline-flex;"><span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Confirmar</span></button>
                                <a title="Cancelar" class="button btn btn-mini btn-warning" href="<?php echo site_url() ?>/permissoes" style="display:inline-flex;">
                                  <span class="button__icon"><i class="bx bx-x"></i></span> <span class="button__text2">Cancelar</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#marcarTodos").change(function() {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
        });
        $("#formPermissao").validate({
            rules: {
                nome: {
                    required: true
                }
            },
            messages: {
                nome: {
                    required: 'Campo obrigatório'
                }
            }
        });
    });
</script>
