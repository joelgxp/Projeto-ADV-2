<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-wrench"></i>
                </span>
                <h5>Configurações do Sistema</h5>
            </div>
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home">Gerais</a></li>
                <li><a data-toggle="tab" href="#menu3">Notificações</a></li>
                <li><a data-toggle="tab" href="#menu4">Atualizações</a></li>
                <li><a data-toggle="tab" href="#menu7">E-mail</a></li>
            </ul>
            <form action="<?php echo current_url(); ?>" id="formConfigurar" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <?php echo $custom_error; ?>
                    <!-- Menu Gerais -->
                    <div id="home" class="tab-pane fade in active">
                        <div class="control-group">
                            <label for="app_name" class="control-label">Nome do Sistema</label>
                            <div class="controls">
                                <input type="text" required name="app_name" value="<?= isset($configuration['app_name']) ? $configuration['app_name'] : '' ?>">
                                <span class="help-inline">Nome do sistema</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="app_theme" class="control-label">Tema do Sistema</label>
                            <div class="controls">
                                <select name="app_theme" id="app_theme">
                                    <option value="default" <?= (!isset($configuration['app_theme']) || $configuration['app_theme'] == 'default') ? 'selected' : ''; ?>>Escuro</option>
                                    <option value="white" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'white') ? 'selected' : ''; ?>>Claro</option>
                                    <option value="puredark" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'puredark') ? 'selected' : ''; ?>>Pure dark</option>
                                    <option value="darkorange" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'darkorange') ? 'selected' : ''; ?>>Dark orange</option>
                                    <option value="darkviolet" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'darkviolet') ? 'selected' : ''; ?>>Dark violet</option>
                                    <option value="whitegreen" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'whitegreen') ? 'selected' : ''; ?>>White green</option>
                                    <option value="whiteblack" <?= (isset($configuration['app_theme']) && $configuration['app_theme'] == 'whiteblack') ? 'selected' : ''; ?>>White black</option>
                                </select>
                                <span class="help-inline">Selecione o tema que que deseja usar no sistema</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="per_page" class="control-label">Registros por Página</label>
                            <div class="controls">
                                <select name="per_page" id="theme">
                                    <option value="10">10</option>
                                    <option value="20" <?= (isset($configuration['per_page']) && $configuration['per_page'] == '20') ? 'selected' : ''; ?>>20</option>
                                    <option value="50" <?= (isset($configuration['per_page']) && $configuration['per_page'] == '50') ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?= (isset($configuration['per_page']) && $configuration['per_page'] == '100') ? 'selected' : ''; ?>>100</option>
                                </select>
                                <span class="help-inline">Selecione quantos registros deseja exibir nas listas</span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="span8">
                                <div class="span9">
                                    <button type="submit" class="button btn btn-primary">
                                    <span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Salvar Alterações</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Menu Notificações -->
                    <div id="menu3" class="tab-pane fade">
                        <div class="control-group">
                            <label for="processo_notification" class="control-label">Notificação de Processos</label>
                            <div class="controls">
                                <select name="processo_notification" id="processo_notification">
                                    <option value="todos" <?= (!isset($configuration['processo_notification']) || $configuration['processo_notification'] == 'todos') ? 'selected' : ''; ?>>Notificar a Todos</option>
                                    <option value="cliente" <?= (isset($configuration['processo_notification']) && $configuration['processo_notification'] == 'cliente') ? 'selected' : ''; ?>>Somente o Cliente</option>
                                    <option value="advogado" <?= (isset($configuration['processo_notification']) && $configuration['processo_notification'] == 'advogado') ? 'selected' : ''; ?>>Somente o Advogado Responsável</option>
                                    <option value="emitente" <?= (isset($configuration['processo_notification']) && $configuration['processo_notification'] == 'emitente') ? 'selected' : ''; ?>>Somente o Escritório</option>
                                    <option value="nenhum" <?= (isset($configuration['processo_notification']) && $configuration['processo_notification'] == 'nenhum') ? 'selected' : ''; ?>>Não Notificar</option>
                                </select>
                                <span class="help-inline">Selecione a opção de notificação por e-mail ao criar ou atualizar processos.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="prazo_notification" class="control-label">Notificação de Prazos</label>
                            <div class="controls">
                                <select name="prazo_notification" id="prazo_notification">
                                    <option value="todos" <?= (!isset($configuration['prazo_notification']) || $configuration['prazo_notification'] == 'todos') ? 'selected' : ''; ?>>Notificar a Todos</option>
                                    <option value="cliente" <?= (isset($configuration['prazo_notification']) && $configuration['prazo_notification'] == 'cliente') ? 'selected' : ''; ?>>Somente o Cliente</option>
                                    <option value="advogado" <?= (isset($configuration['prazo_notification']) && $configuration['prazo_notification'] == 'advogado') ? 'selected' : ''; ?>>Somente o Advogado Responsável</option>
                                    <option value="emitente" <?= (isset($configuration['prazo_notification']) && $configuration['prazo_notification'] == 'emitente') ? 'selected' : ''; ?>>Somente o Escritório</option>
                                    <option value="nenhum" <?= (isset($configuration['prazo_notification']) && $configuration['prazo_notification'] == 'nenhum') ? 'selected' : ''; ?>>Não Notificar</option>
                                </select>
                                <span class="help-inline">Selecione a opção de notificação por e-mail ao criar ou atualizar prazos processuais.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="audiencia_notification" class="control-label">Notificação de Audiências</label>
                            <div class="controls">
                                <select name="audiencia_notification" id="audiencia_notification">
                                    <option value="todos" <?= (!isset($configuration['audiencia_notification']) || $configuration['audiencia_notification'] == 'todos') ? 'selected' : ''; ?>>Notificar a Todos</option>
                                    <option value="cliente" <?= (isset($configuration['audiencia_notification']) && $configuration['audiencia_notification'] == 'cliente') ? 'selected' : ''; ?>>Somente o Cliente</option>
                                    <option value="advogado" <?= (isset($configuration['audiencia_notification']) && $configuration['audiencia_notification'] == 'advogado') ? 'selected' : ''; ?>>Somente o Advogado Responsável</option>
                                    <option value="emitente" <?= (isset($configuration['audiencia_notification']) && $configuration['audiencia_notification'] == 'emitente') ? 'selected' : ''; ?>>Somente o Escritório</option>
                                    <option value="nenhum" <?= (isset($configuration['audiencia_notification']) && $configuration['audiencia_notification'] == 'nenhum') ? 'selected' : ''; ?>>Não Notificar</option>
                                </select>
                                <span class="help-inline">Selecione a opção de notificação por e-mail ao criar ou atualizar audiências.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="email_automatico" class="control-label">Enviar Email Automático</label>
                            <div class="controls">
                                <select name="email_automatico" id="email_automatico">
                                    <option value="1" <?= (!isset($configuration['email_automatico']) || $configuration['email_automatico'] == '1') ? 'selected' : ''; ?>>Ativar</option>
                                    <option value="0" <?= (isset($configuration['email_automatico']) && $configuration['email_automatico'] == '0') ? 'selected' : ''; ?>>Desativar</option>
                                </select>
                                <span class="help-inline">Ativar ou Desativar a opção de envio de e-mail automático para processos, prazos e audiências.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="notifica_whats" class="control-label">Notificação do WhatsApp</label>
                            <div class="controls">
                                <textarea rows="5" cols="20" name="notifica_whats" id="notifica_whats" placeholder="Use as tags abaixo para criar seu texto!" style="margin: 0px; width: 606px; height: 86px;"><?php echo isset($configuration['notifica_whats']) ? $configuration['notifica_whats'] : ''; ?></textarea>
                            </div>
                            <div class="span3">
                                <label for="notifica_whats_select">Tags de preenchimento<span class="required"></span></label>
                                <select class="span12" name="notifica_whats_select" id="notifica_whats_select" value="">
                                    <option value="0">Selecione...</option>
                                    <option value="{CLIENTE_NOME}">Nome do Cliente</option>
                                    <option value="{NUMERO_PROCESSO}">Número do Processo</option>
                                    <option value="{STATUS_PROCESSO}">Status do Processo</option>
                                    <option value="{CLASSE_PROCESSO}">Classe Processual</option>
                                    <option value="{ASSUNTO_PROCESSO}">Assunto do Processo</option>
                                    <option value="{VALOR_CAUSA}">Valor da Causa</option>
                                    <option value="{VARA}">Vara</option>
                                    <option value="{COMARCA}">Comarca</option>
                                    <option value="{TIPO_PRAZO}">Tipo de Prazo</option>
                                    <option value="{DATA_VENCIMENTO}">Data de Vencimento</option>
                                    <option value="{DESCRICAO_PRAZO}">Descrição do Prazo</option>
                                    <option value="{TIPO_AUDIENCIA}">Tipo de Audiência</option>
                                    <option value="{DATA_AUDIENCIA}">Data/Hora da Audiência</option>
                                    <option value="{LOCAL_AUDIENCIA}">Local da Audiência</option>
                                    <option value="{STATUS_AUDIENCIA}">Status da Audiência</option>
                                    <option value="{OBSERVACOES}">Observações</option>
                                    <option value="{EMITENTE}">Nome do Escritório</option>
                                    <option value="{TELEFONE_EMITENTE}">Telefone do Escritório</option>
                                    <option value="{ADVOGADO_NOME}">Nome do Advogado Responsável</option>
                                </select>
                            </div>
                            <span6 class="span10">
                                Para negrito use: *palavra*
                                Para itálico use: _palavra_
                                Para riscado use: ~palavra~
                                </span>
                        </div>
                        <div class="form-actions">
                            <div class="span8">
                                <div class="span9">
                                  <button type="submit" class="button btn btn-primary">
                                  <span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Salvar Alterações</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Menu Atualização -->
                    <div id="menu4" class="tab-pane fade">
                        <div class="form-actions">
                            <div class="span8">
                                <div class="span9" style="display:flex">
                                    <button href="#modal-confirmabanco" data-toggle="modal" type="button" class="button btn btn-warning">
                                      <span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Banco de Dados</span></button>
                                    <button href="#modal-confirmaratualiza" data-toggle="modal" type="button" class="button btn btn-danger">
                                      <span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar Adv</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Menu E-mail -->
                    <div id="menu7" class="tab-pane fade">
                        <div class="control-group">
                            <label for="EMAIL_PROTOCOL" class="control-label">Protocolo de E-mail</label>
                            <div class="controls">
                                <input type="text" name="EMAIL_PROTOCOL" value="<?= isset($_ENV['EMAIL_PROTOCOL']) ? $_ENV['EMAIL_PROTOCOL'] : '' ?>" id="EMAIL_PROTOCOL">
                                <span class="help-inline">Informe o protocolo que será utilizado</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_HOST" class="control-label">Endereço do Host</label>
                            <div class="controls">
                                <input type="text" name="EMAIL_SMTP_HOST" value="<?= isset($_ENV['EMAIL_SMTP_HOST']) ? $_ENV['EMAIL_SMTP_HOST'] : '' ?>" id="EMAIL_SMTP_HOST">
                                <span class="help-inline">Informe o endereço do host</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_CRYPTO" class="control-label">Tipo de criptografia</label>
                            <div class="controls">
                                <select name="EMAIL_SMTP_CRYPTO" id="EMAIL_SMTP_CRYPTO">
                                    <option value="tls" <?= (isset($_ENV['EMAIL_SMTP_CRYPTO']) && $_ENV['EMAIL_SMTP_CRYPTO'] == 'tls') ? 'selected' : ''; ?>>tls</option>
                                    <option value="ssl" <?= (isset($_ENV['EMAIL_SMTP_CRYPTO']) && $_ENV['EMAIL_SMTP_CRYPTO'] == 'ssl') ? 'selected' : ''; ?>>ssl</option>
                                </select>
                                <span class="help-inline">Tipo de criptografia que será utilizada.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_PORT" class="control-label">Porta</label>
                            <div class="controls">
                                <input type="text" name="EMAIL_SMTP_PORT" value="<?= isset($_ENV['EMAIL_SMTP_PORT']) ? $_ENV['EMAIL_SMTP_PORT'] : '' ?>" id="EMAIL_SMTP_PORT">
                                <span class="help-inline">Informe a porta que será utilizada.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_USER" class="control-label">Usuário</label>
                            <div class="controls">
                                <input type="text" name="EMAIL_SMTP_USER" value="<?= isset($_ENV['EMAIL_SMTP_USER']) ? $_ENV['EMAIL_SMTP_USER'] : '' ?>" id="EMAIL_SMTP_USER">
                                <span class="help-inline">Informe nome de usuáriodo e-mail.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_PASS" class="control-label">Senha</label>
                            <div class="controls">
                                <input type="password" name="EMAIL_SMTP_PASS" value="<?= isset($_ENV['EMAIL_SMTP_PASS']) ? $_ENV['EMAIL_SMTP_PASS'] : '' ?>" id="EMAIL_SMTP_PASS">
                                <span class="help-inline">Informe a senha do e-mail.</span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="span8">
                                <div class="span9">
                                  <button type="submit" class="button btn btn-primary">
                                  <span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Salvar Alterações</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal -->
<div id="modal-confirmaratualiza" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Atualização de sistema</h5>
        </div>
        <div class="modal-body">
            <h5 style="text-align: left">Deseja realmente fazer a atualização de sistema?</h5>
            <h7 style="text-align: left">Recomendamos que faça um backup antes de prosseguir!</h7>
            <h7 style="text-align: left"><br>Faça o backup dos seguintes arquivos pois os mesmo serão excluídos:</h7>
            <h7 style="text-align: left"><br>* ./assets/anexos</h7>
            <h7 style="text-align: left"><br>* ./assets/arquivos</h7>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
          <button type="button" class="button btn btn-mini btn-danger" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class='bx bx-x' ></i></span> <span class="button__text2">Cancelar</span></button>
          <button id="update-mapos" type="button" class="button btn btn-warning"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
        </div>
    </form>
</div>
<!-- Modal -->
<div id="modal-confirmabanco" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Atualização de sistema</h5>
        </div>
        <div class="modal-body">
            <h5 style="text-align: left">Deseja realmente fazer a atualização do banco de dados?</h5>
            <h7 style="text-align: left">Recomendamos que faça um backup antes de prosseguir!
                <a target="_blank" title="Fazer Bakup" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/adv/backup">Fazer Backup</a>
            </h7>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
          <button type="button" class="button btn btn-mini btn-danger" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class='bx bx-x' ></i></span> <span class="button__text2">Cancelar</span></button>
          <button id="update-database" type="button" class="button btn btn-warning"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
        </div>
    </form>
</div>
<script>
    $('#update-database').click(function() {
        window.location = "<?= site_url('adv/atualizarBanco') ?>"
    });
    $('#update-mapos').click(function() {
        window.location = "<?= site_url('adv/atualizarAdv') ?>"
    });
    $(document).ready(function() {
        $('#notifica_whats_select').change(function() {
            if ($(this).val() != "0")
                document.getElementById("notifica_whats").value += $(this).val();
            $(this).prop('selectedIndex', 0);
        });
    });
</script>
