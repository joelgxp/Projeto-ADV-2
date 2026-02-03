<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Criar Senha - <?= $this->config->item('app_name') ?></title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-login.css" />
  <link href="<?= base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet" />
  <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
</head>
<body>
  <div class="main-login">
    <div class="left-login">
      <h1 class="h-one">Criar sua senha</h1>
      <h2 class="h-two">Sistema de Gestão Jurídica</h2>
      <img src="<?= base_url() ?>assets/img/dashboard-animate.svg" class="left-login-image" alt="Adv">
    </div>
    <form id="formDefinirSenha" method="post" action="<?= site_url('login/definir_senha_salvar') ?>" class="form-vertical">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="t" value="<?= htmlspecialchars($token ?? ''); ?>">
      <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?= $error ?>
        </div>
      <?php endif; ?>
      <div class="d-flex flex-column">
        <div class="right-login">
          <div class="container">
            <div class="card">
              <div class="content">
                <div id="newlog">
                  <?php $logo_definir = (isset($emitente) && $emitente && !empty($emitente->url_logo)) ? $emitente->url_logo : base_url() . 'assets/img/logo-adv-branco.svg'; ?>
                  <div class="icon2">
                    <img src="<?= isset($emitente) && $emitente && !empty($emitente->url_logo) ? htmlspecialchars($emitente->url_logo) : base_url() . 'assets/img/logo-two.svg' ?>" onerror="this.src='<?= base_url() ?>assets/img/logo-two.png'" style="max-width:100%;max-height:50px;object-fit:contain">
                  </div>
                  <div class="title01">
                    <img src="<?= htmlspecialchars($logo_definir) ?>" alt="Logo" style="max-width:100%;max-height:50px;object-fit:contain" onerror="this.src='<?= base_url() ?>assets/img/logo-adv-branco.svg'; this.onerror=function(){this.src='<?= base_url() ?>assets/img/logo-adv-branco.png'}">
                  </div>
                </div>
                <p style="text-align: center; color: #999; margin-bottom: 20px;">Defina sua senha de acesso ao sistema</p>
                <div id="form-erros" style="min-height: 20px; margin-bottom: 10px;"></div>
                <div class="input-field">
                  <label class="fas fa-lock" for="senha"></label>
                  <span class="pwd-toggle-wrap" style="position:relative;display:inline-block;flex:1">
                    <input id="senha" name="senha" type="password" placeholder="Nova senha" required minlength="8" autocomplete="new-password" style="padding-right:36px">
                    <i class="bx bx-show-alt pwd-toggle-icon" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#999;font-size:20px" title="Mostrar senha"></i>
                  </span>
                </div>
                <div class="input-field">
                  <label class="fas fa-lock" for="confirmar_senha"></label>
                  <span class="pwd-toggle-wrap" style="position:relative;display:inline-block;flex:1">
                    <input id="confirmar_senha" name="confirmar_senha" type="password" placeholder="Confirmar senha" required minlength="8" autocomplete="new-password" style="padding-right:36px">
                    <i class="bx bx-show-alt pwd-toggle-icon" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#999;font-size:20px" title="Mostrar senha"></i>
                  </span>
                </div>
                <div id="senha-requisitos" style="margin: 10px 0; font-size: 12px;">
                  <div id="req-tamanho"><span class="req-icon">○</span> Mínimo 8 caracteres</div>
                  <div id="req-letra"><span class="req-icon">○</span> Pelo menos uma letra</div>
                  <div id="req-numero"><span class="req-icon">○</span> Pelo menos um número</div>
                  <div id="req-especial"><span class="req-icon">○</span> Pelo menos um caractere especial (!@#$%...)</div>
                </div>
                <div id="confirmar-status" style="font-size: 12px; min-height: 18px; margin-bottom: 5px;"></div>
                <div class="center">
                  <button type="submit" id="btn-definir">Criar senha e acessar</button>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                  <a href="<?= site_url('login') ?>" style="color: #999;">Voltar ao login</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <script src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
  <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url() ?>assets/js/password-toggle.js"></script>
  <script src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
  <style>
    .req-ok { color: #28a745 !important; }
    .req-ok .req-icon { color: #28a745; }
    .req-fail { color: #6c757d !important; }
    .req-fail .req-icon { color: #6c757d; }
    .confirmar-ok { color: #28a745; }
    .confirmar-fail { color: #dc3545; }
  </style>
  <script>
    $(document).ready(function() {
      function validarSenha(senha) {
        return {
          tamanho: senha.length >= 8,
          letra: /[a-zA-Z]/.test(senha),
          numero: /[0-9]/.test(senha),
          especial: /[^a-zA-Z0-9]/.test(senha),
          valida: function() {
            return this.tamanho && this.letra && this.numero && this.especial;
          }
        };
      }
      function atualizarRequisitos(senha) {
        var v = validarSenha(senha);
        $('#req-tamanho').toggleClass('req-ok', v.tamanho).toggleClass('req-fail', !v.tamanho).find('.req-icon').text(v.tamanho ? '✓' : '○');
        $('#req-letra').toggleClass('req-ok', v.letra).toggleClass('req-fail', !v.letra).find('.req-icon').text(v.letra ? '✓' : '○');
        $('#req-numero').toggleClass('req-ok', v.numero).toggleClass('req-fail', !v.numero).find('.req-icon').text(v.numero ? '✓' : '○');
        $('#req-especial').toggleClass('req-ok', v.especial).toggleClass('req-fail', !v.especial).find('.req-icon').text(v.especial ? '✓' : '○');
      }
      function atualizarConfirmar(senha, confirmar) {
        var $s = $('#confirmar-status');
        if (confirmar.length === 0) {
          $s.html('').removeClass('confirmar-ok confirmar-fail');
        } else if (senha === confirmar) {
          $s.html('✓ Senhas coincidem').removeClass('confirmar-fail').addClass('confirmar-ok');
        } else {
          $s.html('✗ As senhas não coincidem').removeClass('confirmar-ok').addClass('confirmar-fail');
        }
      }
      $('#senha').on('input', function() {
        atualizarRequisitos($(this).val());
        atualizarConfirmar($(this).val(), $('#confirmar_senha').val());
      });
      $('#confirmar_senha').on('input', function() {
        atualizarConfirmar($('#senha').val(), $(this).val());
      });
      $('#senha').trigger('input');

      $.validator.addMethod('senhaForte', function(value) {
        var v = validarSenha(value);
        return v.valida();
      }, 'A senha deve atender a todos os requisitos acima.');

      $('#formDefinirSenha').validate({
        rules: {
          senha: { required: true, minlength: 8, senhaForte: true },
          confirmar_senha: { required: true, equalTo: '#senha' }
        },
        messages: {
          senha: { required: 'Digite a senha.', minlength: 'Mínimo 8 caracteres.' },
          confirmar_senha: { required: 'Confirme a senha.', equalTo: 'As senhas não coincidem.' }
        },
        errorPlacement: function(error, element) {
          $('#form-erros').html(error).css('color', '#d9534f').show();
        },
        submitHandler: function(form) {
          $('#btn-definir').prop('disabled', true).text('Processando...');
          form.submit();
        }
      });
    });
  </script>
</body>
</html>
