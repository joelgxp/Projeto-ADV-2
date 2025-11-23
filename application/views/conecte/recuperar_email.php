<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Recuperar Email - <?php echo $this->config->item('app_name') ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon.png" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
    <script type="text/javascript" src="<?= base_url(); ?>assets/js/funcoesGlobal.js"></script>
    <script type="text/javascript" src="<?= base_url(); ?>assets/js/csrf.js"></script>
</head>

<body>
    <div class="row-fluid" style="width: 100vw;height: 100vh;display: flex;align-items: center;justify-content: center">
        <div class="widget-box" style="align-items: center;padding: 0 15px;max-width: 500px;">
            <div class="widget-title">
                <h5 style="padding-left: 10px">Recuperar Email Cadastrado</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <form action="<?php echo base_url() . "index.php/mine/recuperarEmail" ?>" id="formRecuperarEmail" method="post" class="form-horizontal">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    
                    <div class="control-group" style="margin: 15px;">
                        <label for="documento" class="control-label">CPF/CNPJ<span class="required">*</span></label>
                        <div class="controls">
                            <input id="documento" class="cpfcnpj" type="text" name="documento" placeholder="000.000.000-00 ou 00.000.000/0000-00" value="" />
                            <span class="help-inline">Informe seu CPF ou CNPJ cadastrado</span>
                        </div>
                    </div>

                    <div class="form-actions" style="background-color:transparent;border:none;padding: 10px;margin-top: 15px">
                        <div class="span12">
                            <div class="span6 offset3" style="display:flex;justify-content: center;gap: 10px;">
                                <button type="submit" class="button btn btn-success btn-large">
                                    <span class="button__icon"><i class='bx bx-mail-send'></i></span>
                                    <span class="button__text2">Enviar</span>
                                </button>
                                <a href="<?php echo base_url() ?>index.php/mine" class="button btn btn-warning">
                                    <span class="button__icon"><i class='bx bx-lock-alt'></i></span>
                                    <span class="button__text2">Voltar</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
    <script src="<?php echo base_url() ?>assets/js/bootstrap.min.js"></script>
    
    <?php if ($this->session->flashdata('success') != null) { ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '<?php echo $this->session->flashdata('success'); ?>',
                showConfirmButton: false,
                timer: 4000
            })
        </script>
    <?php } ?>

    <?php if ($this->session->flashdata('error') != null) { ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: '<?php echo $this->session->flashdata('error'); ?>',
                showConfirmButton: false,
                timer: 4000
            })
        </script>
    <?php } ?>
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Máscara para CPF/CNPJ
            $('.cpfcnpj').mask('000.000.000-00', {reverse: true});
            
            $('#formRecuperarEmail').validate({
                rules: {
                    documento: {
                        required: true,
                        minlength: 11
                    }
                },
                messages: {
                    documento: {
                        required: 'Campo Requerido.',
                        minlength: 'CPF/CNPJ inválido.'
                    }
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

