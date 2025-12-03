<!DOCTYPE html>
<html lang="pt-br">

<head>
  <title><?= $this->config->item('app_name') ?> </title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-login.css" />
  <link href="<?= base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
</head>

<body>
  <div class="main-login">
    <div class="left-login">
      <!-- Saudação -->
      <h1 class="h-one">
        <?php
        function saudacao($nome = '')
        {
            $hora = date('H');
            if ($hora >= 00 && $hora < 12) {
                return 'Olá! Bom dia' . (empty($nome) ? '' : ', ' . $nome);
            } elseif ($hora >= 12 && $hora < 18) {
                return 'Olá! Boa tarde' . (empty($nome) ? '' : ', ' . $nome);
            } else {
                return 'Olá! Boa noite' . (empty($nome) ? '' : ', ' . $nome);
            }
        }
        $login = 'bem-vindo';
        echo saudacao($login);
        // Irá retornar conforme o horário:
        ?>
      </h1>
      <h2 class="h-two"> Ao Sistema de Gestão Jurídica</h2>
      <img src="<?php echo base_url() ?>assets/img/dashboard-animate.svg" class="left-login-image" alt="Adv - Versão: <?= $this->config->item('app_version'); ?>">
    </div>
    <form class="form-vertical" id="formLogin" method="post" action="#" onsubmit="return false;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <?php if ($this->session->flashdata('error') != null) { ?>
        <div id="loginbox">
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('error'); ?>
          </div>
        </div>
      <?php } ?>
      <div class="d-flex flex-column">
        <div class="right-login">
          <div class="container">
            <div class="card">
              <div class="content">
                <div id="newlog">
                  <div class="icon2">
                    <img src="<?php echo base_url() ?>assets/img/logo-two.svg" onerror="this.src='<?php echo base_url() ?>assets/img/logo-two.png'">
                  </div>
                  <div class="title01">
                    <?= '<img src="' . base_url() . 'assets/img/logo-adv-branco.svg" onerror="this.src=\'' . base_url() . 'assets/img/logo-adv-branco.png\'">'; ?>
                  </div>
                </div>
                <div id="mcell">Versão: <?= $this->config->item('app_version'); ?></div>
                <div class="input-field">
                  <label class="fas fa-user" for="nome"></label>
                  <input id="email" name="email" type="text" placeholder="Email">
                </div>
                <div class="input-field">
                  <label class="fas fa-lock" for="senha"></label>
                  <input name="senha" type="password" placeholder="Senha">
                </div>
                <div class="center">
                  <button id="btn-acessar">Acessar</button>
                </div>
                <div class="links-uteis"><a href="https://github.com/RamonSilva20/mapos">
                    <p></p>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <a href="#notification" id="call-modal" role="button" class="btn" data-toggle="modal" style="display: none;">notification</a>
      <div id="notification" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
          <h4 id="myModalLabel">Adv</h4>
        </div>
        <div class="modal-body">
          <h5 style="text-align: center" id="message">Os dados de acesso estão incorretos, por favor tente novamente!</h5>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" data-dismiss="modal">Fechar</button>
        </div>
      </div>
    </form>
  </div>

  <script src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
  <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#email').focus();
      
      // Prevenir submissão normal do formulário
      $("#formLogin").on('submit', function(e) {
        e.preventDefault();
        return false;
      });
      
      // Gerenciar aria-hidden do modal corretamente para acessibilidade
      $('#notification').on('shown', function() {
        $(this).removeAttr('aria-hidden');
        $(this).find('.btn-primary').focus();
      });
      $('#notification').on('hidden', function() {
        $(this).attr('aria-hidden', 'true');
      });
      
      $("#formLogin").validate({
        rules: {
          email: {
            required: true,
            email: true
          },
          senha: {
            required: true
          }
        },
        messages: {
          email: {
            required: '',
            email: 'Insira Email válido'
          },
          senha: {
            required: 'Campos Requeridos.'
          }
        },
        submitHandler: function(form) {
          var dados = $(form).serialize();
          $('#btn-acessar').addClass('disabled');
          $('#progress-acessar').removeClass('hide');

          // Construir URL relativa simples - substituir /login por /login/verificarLogin
          var currentPath = window.location.pathname;
          var url = currentPath.replace(/\/login\/?$/, '') + '/login/verificarLogin';
          
          console.log('Tentando fazer login em:', window.location.origin + url);
          
          $.ajax({
            type: "POST",
            url: url,
            data: dados,
            dataType: 'json',
            timeout: 10000,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                if (data && data.result == true) {
                    // Atualiza o token antes de redirecionar
                    var newCsrfToken = data.ADV_TOKEN || data.MAPOS_TOKEN; 
                    if (newCsrfToken) {
                        $("input[name='<?= $this->security->get_csrf_token_name(); ?>']").val(newCsrfToken);
                    }
                    // Redireciona para o dashboard imediatamente
                    window.location.href = "<?= base_url(); ?>index.php/adv";
                    return false;
                } else {
                    $('#btn-acessar').removeClass('disabled');
                    $('#progress-acessar').addClass('hide');
                    $('#message').text(data && data.message ? data.message : 'Os dados de acesso estão incorretos, por favor tente novamente!');
                    $('#call-modal').trigger('click');

                    // Atualiza o token a cada requisição
                    var newCsrfToken = data && (data.ADV_TOKEN || data.MAPOS_TOKEN); 
                    if (newCsrfToken) {
                        $("input[name='<?= $this->security->get_csrf_token_name(); ?>']").val(newCsrfToken);
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#btn-acessar').removeClass('disabled');
                $('#progress-acessar').addClass('hide');
                
                var errorMessage = 'Erro ao conectar com o servidor.';
                
                // Tentar obter mensagem de erro mais específica
                if (xhr.status === 0 || status === 'error') {
                    errorMessage = 'Erro de conexão. URL: ' + url + '. Verifique se o servidor está rodando e se a URL está correta.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Acesso negado. Tente recarregar a página e fazer login novamente.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Erro interno do servidor. Verifique os logs.';
                } else if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch(e) {
                        errorMessage = 'Erro ' + xhr.status + ': ' + (xhr.responseText.substring(0, 100) || error);
                    }
                } else {
                    errorMessage = 'Erro ' + xhr.status + ': ' + error;
                }
                
                $('#message').text(errorMessage);
                $('#call-modal').trigger('click');
                
                // Log no console para debug
                console.error('Erro no login:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.responseText,
                    error: error
                });
            }
          });

          return false;
        },

        errorClass: "help-inline",
        errorElement: "span",
        highlight: function(element, errorClass, validClass) {
          $(element).parents('.control-group').addClass('error');
        },
        unhighlight: function(element, errorClass, validClass) {
          $(element).parents('.control-group').removeClass('error');
          $(element).parents('.control-group').addClass('success');
        }
      });
    });
  </script>
</body>

</html>
