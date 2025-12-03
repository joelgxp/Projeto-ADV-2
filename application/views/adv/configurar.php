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
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="widget-content nopadding tab-content">
                    <?php echo $custom_error; ?>
                    <!-- Menu Gerais -->
                    <div id="home" class="tab-pane fade in active">
                        <div class="control-group">
                            <label for="app_name" class="control-label">Nome do Sistema</label>
                            <div class="controls">
                                <input type="text" required name="app_name" value="<?php echo isset($configuration['app_name']) ? htmlspecialchars($configuration['app_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                <span class="help-inline">Nome do sistema</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="app_theme" class="control-label">Tema do Sistema</label>
                            <div class="controls">
                                <select name="app_theme" id="app_theme">
                                    <option value="default"<?php echo (!isset($configuration['app_theme']) || $configuration['app_theme'] == 'default') ? ' selected="selected"' : ''; ?>>Escuro</option>
                                    <option value="white"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'white') ? ' selected="selected"' : ''; ?>>Claro</option>
                                    <option value="puredark"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'puredark') ? ' selected="selected"' : ''; ?>>Pure dark</option>
                                    <option value="darkorange"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'darkorange') ? ' selected="selected"' : ''; ?>>Dark orange</option>
                                    <option value="darkviolet"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'darkviolet') ? ' selected="selected"' : ''; ?>>Dark violet</option>
                                    <option value="whitegreen"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'whitegreen') ? ' selected="selected"' : ''; ?>>White green</option>
                                    <option value="whiteblack"<?php echo (isset($configuration['app_theme']) && $configuration['app_theme'] == 'whiteblack') ? ' selected="selected"' : ''; ?>>White black</option>
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
                                <textarea rows="5" cols="20" name="notifica_whats" id="notifica_whats" placeholder="Use as tags abaixo para criar seu texto!" style="margin: 0px; width: 606px; height: 86px;"><?php echo isset($configuration['notifica_whats']) ? htmlspecialchars($configuration['notifica_whats'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
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
                        <?php
                        // Verificar incompatibilidade de porta/criptografia
                        $smtp_port = isset($_ENV['EMAIL_SMTP_PORT']) ? (int)$_ENV['EMAIL_SMTP_PORT'] : 587;
                        $smtp_crypto = isset($_ENV['EMAIL_SMTP_CRYPTO']) ? strtolower($_ENV['EMAIL_SMTP_CRYPTO']) : 'tls';
                        $has_incompatibility = false;
                        $incompatibility_msg = '';
                        
                        if ($smtp_port === 465 && $smtp_crypto !== 'ssl') {
                            $has_incompatibility = true;
                            $incompatibility_msg = '❌ ERRO: Porta 465 requer SSL, mas está configurado como ' . strtoupper($smtp_crypto) . '. Altere para SSL.';
                        } elseif ($smtp_port === 587 && $smtp_crypto !== 'tls') {
                            $has_incompatibility = true;
                            $incompatibility_msg = '❌ ERRO: Porta 587 requer TLS, mas está configurado como ' . strtoupper($smtp_crypto) . '. Altere para TLS.';
                        }
                        
                        if ($has_incompatibility):
                        ?>
                        <div class="alert alert-danger" style="margin: 15px; padding: 15px; border-left: 4px solid #f44336;">
                            <strong><i class="bx bx-error-circle"></i> Incompatibilidade Detectada!</strong><br>
                            <?php echo htmlspecialchars($incompatibility_msg); ?><br>
                            <small>O sistema corrigirá automaticamente ao salvar, mas você pode alterar manualmente agora.</small>
                        </div>
                        <?php endif; ?>
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
                                    <option value="tls" <?= (isset($_ENV['EMAIL_SMTP_CRYPTO']) && $_ENV['EMAIL_SMTP_CRYPTO'] == 'tls') ? 'selected' : ''; ?>>TLS (Recomendado - Porta 587)</option>
                                    <option value="ssl" <?= (isset($_ENV['EMAIL_SMTP_CRYPTO']) && $_ENV['EMAIL_SMTP_CRYPTO'] == 'ssl') ? 'selected' : ''; ?>>SSL (Porta 465)</option>
                                </select>
                                <span class="help-inline">
                                    <strong>TLS:</strong> Geralmente usa porta 587 (Gmail, Outlook, etc.)<br>
                                    <strong>SSL:</strong> Geralmente usa porta 465 (servidores mais antigos)
                                </span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="EMAIL_SMTP_PORT" class="control-label">Porta</label>
                            <div class="controls">
                                <input type="number" name="EMAIL_SMTP_PORT" value="<?= isset($_ENV['EMAIL_SMTP_PORT']) ? $_ENV['EMAIL_SMTP_PORT'] : '587' ?>" id="EMAIL_SMTP_PORT" min="1" max="65535" onchange="atualizarAjudaPorta()">
                                <span class="help-inline" id="ajuda-porta">
                                    Porta SMTP. <strong>587</strong> para TLS, <strong>465</strong> para SSL, <strong>25</strong> para sem criptografia (não recomendado).
                                </span>
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
                        
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
                        
                        <div class="control-group">
                            <label class="control-label">Testar Configuração de E-mail</label>
                            <div class="controls">
                                <div style="display: flex; gap: 10px; align-items: flex-end;">
                                    <div style="flex: 0 0 50%; max-width: 50%;">
                                        <input type="email" id="email_teste" placeholder="seuemail@exemplo.com" style="width: 100%;">
                                        <span class="help-inline">Digite um e-mail para receber o teste</span>
                                    </div>
                                    <div>
                                        <button type="button" id="btnTestarEmail" class="button btn btn-success" style="display:inline-flex;">
                                            <span class="button__icon"><i class='bx bx-mail-send'></i></span>
                                            <span class="button__text2">Enviar Teste</span>
                                        </button>
                                    </div>
                                </div>
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
          <button id="update-adv" type="button" class="button btn btn-warning"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
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
<script type="text/javascript">
    var testarEmailUrl = '<?php echo site_url("adv/testarEmail"); ?>';
    
    $('#update-database').click(function() {
        window.location = "<?= site_url('adv/atualizarBanco') ?>"
    });
    $('#update-adv').click(function() {
        window.location = "<?= site_url('adv/atualizarAdv') ?>"
    });
    $(document).ready(function() {
        $('#notifica_whats_select').change(function() {
            if ($(this).val() != "0")
                document.getElementById("notifica_whats").value += $(this).val();
            $(this).prop('selectedIndex', 0);
        });
    });
    
    // Testar envio de e-mail
    $('#btnTestarEmail').on('click', function() {
        var email = $('#email_teste').val().trim();
        
        if (!email) {
            alert('Por favor, informe um e-mail para testar.');
            $('#email_teste').focus();
            return false;
        }
        
        // Validar formato do e-mail
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Por favor, informe um e-mail válido.');
            $('#email_teste').focus();
            return false;
        }
        
        // Confirmar envio
        if (!confirm('Enviar e-mail de teste para: ' + email + '?')) {
            return false;
        }
        
        // Desabilitar botão
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Enviando...');
        
        // Pegar token CSRF do cookie ou do form existente
        var csrfTokenName = $('meta[name="csrf-token-name"]').attr('content') || 'ADV_TOKEN';
        var csrfCookieName = $('meta[name="csrf-cookie-name"]').attr('content') || 'ADV_COOKIE';
        
        // Função helper para pegar cookie
        function getCookie(name) {
            var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : '';
        }
        
        // Tentar pegar do cookie primeiro
        var csrfToken = getCookie(csrfCookieName);
        
        // Se não encontrar no cookie, tentar pegar do form existente
        if (!csrfToken) {
            csrfToken = $('input[name="' + csrfTokenName + '"]').val();
        }
        
        // Se ainda não tiver, pegar do input hidden do form principal
        if (!csrfToken) {
            csrfToken = $('#formConfigurar input[name="' + csrfTokenName + '"]').val() || '';
        }
        
        // Validar que temos o token antes de continuar
        if (!csrfToken) {
            alert('Erro: Token CSRF não encontrado. Por favor, recarregue a página e tente novamente.');
            $btn.prop('disabled', false).html(originalText);
            return false;
        }
        
        // Criar form com token CSRF
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = testarEmailUrl;
        form.style.display = 'none';
        
        var emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email_teste';
        emailInput.value = email;
        form.appendChild(emailInput);
        
        var tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = csrfTokenName;
        tokenInput.value = csrfToken;
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    });
    
    // Função para atualizar ajuda quando a porta mudar
    function atualizarAjudaPorta() {
        var porta = parseInt($('#EMAIL_SMTP_PORT').val());
        var criptografia = $('#EMAIL_SMTP_CRYPTO').val();
        var ajuda = $('#ajuda-porta');
        
        if (porta === 465 && criptografia !== 'ssl') {
            ajuda.html('<span style="color: #f44336;">⚠️ Porta 465 requer SSL! Altere a criptografia para SSL.</span>');
            $('#EMAIL_SMTP_CRYPTO').val('ssl');
            atualizarAjudaCriptografia();
        } else if (porta === 587 && criptografia !== 'tls') {
            ajuda.html('<span style="color: #f44336;">⚠️ Porta 587 requer TLS! Altere a criptografia para TLS.</span>');
            $('#EMAIL_SMTP_CRYPTO').val('tls');
            atualizarAjudaCriptografia();
        } else {
            ajuda.html('Porta SMTP. <strong>587</strong> para TLS, <strong>465</strong> para SSL, <strong>25</strong> para sem criptografia (não recomendado).');
        }
    }
    
    // Função para atualizar ajuda quando a criptografia mudar
    function atualizarAjudaCriptografia() {
        var criptografia = $('#EMAIL_SMTP_CRYPTO').val();
        var porta = parseInt($('#EMAIL_SMTP_PORT').val());
        var ajuda = $('#ajuda-criptografia');
        
        if (criptografia === 'ssl' && porta !== 465) {
            ajuda.html('<span style="color: #f44336;">⚠️ SSL geralmente usa porta 465! Considere alterar a porta.</span><br><strong>SSL:</strong> Geralmente usa porta 465');
        } else if (criptografia === 'tls' && porta !== 587 && porta !== 25) {
            ajuda.html('<span style="color: #f44336;">⚠️ TLS geralmente usa porta 587! Considere alterar a porta.</span><br><strong>TLS:</strong> Geralmente usa porta 587 (Gmail, Outlook, etc.)');
        } else {
            ajuda.html('<strong>TLS:</strong> Geralmente usa porta 587 (Gmail, Outlook, etc.)<br><strong>SSL:</strong> Geralmente usa porta 465 (servidores mais antigos)');
        }
    }
    
    // Verificar compatibilidade ao carregar a página
    $(document).ready(function() {
        atualizarAjudaPorta();
        atualizarAjudaCriptografia();
        
        // Verificar se há incompatibilidade e mostrar alerta
        var porta = parseInt($('#EMAIL_SMTP_PORT').val()) || 587;
        var criptografia = $('#EMAIL_SMTP_CRYPTO').val();
        
        if (porta === 465 && criptografia !== 'ssl') {
            // Mostrar alerta proeminente
            var alertHtml = '<div class="alert alert-danger" id="alerta-incompatibilidade" style="margin: 15px; padding: 15px; border-left: 4px solid #f44336; animation: pulse 2s infinite;">' +
                '<strong><i class="bx bx-error-circle"></i> Incompatibilidade Detectada!</strong><br>' +
                'Porta 465 requer SSL, mas está configurado como ' + criptografia.toUpperCase() + '.<br>' +
                '<small>Altere a criptografia para SSL ou o sistema corrigirá automaticamente ao salvar.</small>' +
                '</div>';
            
            // Inserir antes do primeiro control-group na aba de e-mail
            $('#menu7 .control-group').first().before(alertHtml);
            
            // Destacar o campo de criptografia
            $('#EMAIL_SMTP_CRYPTO').css({
                'border': '2px solid #f44336',
                'box-shadow': '0 0 5px rgba(244, 67, 54, 0.5)'
            });
        } else if (porta === 587 && criptografia !== 'tls') {
            var alertHtml = '<div class="alert alert-danger" id="alerta-incompatibilidade" style="margin: 15px; padding: 15px; border-left: 4px solid #f44336;">' +
                '<strong><i class="bx bx-error-circle"></i> Incompatibilidade Detectada!</strong><br>' +
                'Porta 587 requer TLS, mas está configurado como ' + criptografia.toUpperCase() + '.<br>' +
                '<small>Altere a criptografia para TLS ou o sistema corrigirá automaticamente ao salvar.</small>' +
                '</div>';
            
            $('#menu7 .control-group').first().before(alertHtml);
            
            $('#EMAIL_SMTP_CRYPTO').css({
                'border': '2px solid #f44336',
                'box-shadow': '0 0 5px rgba(244, 67, 54, 0.5)'
            });
        }
        
        // Remover destaque quando corrigir
        $('#EMAIL_SMTP_CRYPTO, #EMAIL_SMTP_PORT').on('change', function() {
            atualizarAjudaPorta();
            atualizarAjudaCriptografia();
            
            var novaPorta = parseInt($('#EMAIL_SMTP_PORT').val()) || 587;
            var novaCriptografia = $('#EMAIL_SMTP_CRYPTO').val();
            
            // Se estiver compatível, remover alerta e destaque
            if ((novaPorta === 465 && novaCriptografia === 'ssl') || 
                (novaPorta === 587 && novaCriptografia === 'tls') ||
                (novaPorta !== 465 && novaPorta !== 587)) {
                $('#alerta-incompatibilidade').fadeOut(300, function() {
                    $(this).remove();
                });
                $('#EMAIL_SMTP_CRYPTO').css({
                    'border': '',
                    'box-shadow': ''
                });
            }
        });
    });
    
    // Adicionar animação CSS para o alerta
    if (!$('style#alerta-animacao').length) {
        $('head').append('<style id="alerta-animacao">@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }</style>');
    }
</script>
