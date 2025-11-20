<?php
/**
 * Partial view compartilhada para formulário de cliente
 * 
 * @param object|null $cliente Cliente para edição (null para adicionar)
 * @param string $action_url URL de ação do formulário
 * @param string $submit_text Texto do botão de submit
 * @param string $form_title Título do formulário
 * @param string $custom_error Mensagem de erro customizada
 */

// Determinar valores dos campos
$is_edit = isset($cliente) && $cliente !== null;
$documento_value = $is_edit ? $cliente->documento : set_value('documento');
$nomeCliente_value = $is_edit ? $cliente->nomeCliente : set_value('nomeCliente');
$contato_value = $is_edit ? $cliente->contato : set_value('contato');
$telefone_value = $is_edit ? $cliente->telefone : set_value('telefone');
$celular_value = $is_edit ? $cliente->celular : set_value('celular');
$email_value = $is_edit ? $cliente->email : set_value('email');
$senha_value = $is_edit ? '' : set_value('senha');
$fornecedor_checked = $is_edit ? ($cliente->fornecedor == 1) : false;
$cep_value = $is_edit ? $cliente->cep : set_value('cep');
$rua_value = $is_edit ? $cliente->rua : set_value('rua');
$numero_value = $is_edit ? $cliente->numero : set_value('numero');
$complemento_value = $is_edit ? $cliente->complemento : set_value('complemento');
$bairro_value = $is_edit ? $cliente->bairro : set_value('bairro');
$cidade_value = $is_edit ? $cliente->cidade : set_value('cidade');
$estado_value = $is_edit ? $cliente->estado : set_value('estado');
?>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/css/clientes-form.css">

<?php if (isset($custom_error) && $custom_error != ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($custom_error); ?></div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($action_url); ?>" id="formCliente" method="post" class="form-horizontal" enctype="multipart/form-data">
    <?php if ($is_edit): ?>
        <?php echo form_hidden('idClientes', $cliente->idClientes); ?>
    <?php endif; ?>
    
    <div class="widget-content nopadding tab-content">
        <div class="span6">
            <div class="control-group">
                <label for="documento" class="control-label">CPF/CNPJ</label>
                <div class="controls">
                    <input id="documento" class="cpfcnpj" type="text" name="documento" value="<?php echo htmlspecialchars($documento_value); ?>" aria-label="CPF ou CNPJ do cliente" />
                    <button id="buscar_info_cnpj" class="btn btn-xs" type="button" aria-label="Buscar informações do CNPJ">Buscar(CNPJ)</button>
                </div>
            </div>
            <div class="control-group">
                <label for="nomeCliente" class="control-label">Nome/Razão Social<span class="required">*</span></label>
                <div class="controls">
                    <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo htmlspecialchars($nomeCliente_value); ?>" required aria-required="true" aria-label="Nome ou razão social do cliente" />
                </div>
            </div>
            <div class="control-group">
                <label for="contato" class="control-label">Contato:</label>
                <div class="controls">
                    <input id="contato" class="contato" type="text" name="contato" value="<?php echo htmlspecialchars($contato_value); ?>" aria-label="Nome do contato" />
                </div>
            </div>
            <div class="control-group">
                <label for="telefone" class="control-label">Telefone</label>
                <div class="controls">
                    <input id="telefone" type="text" name="telefone" value="<?php echo htmlspecialchars($telefone_value); ?>" aria-label="Telefone do cliente" />
                </div>
            </div>
            <div class="control-group">
                <label for="celular" class="control-label">Celular</label>
                <div class="controls">
                    <input id="celular" type="text" name="celular" value="<?php echo htmlspecialchars($celular_value); ?>" aria-label="Celular do cliente" />
                </div>
            </div>
            <div class="control-group">
                <label for="email" class="control-label">Email</label>
                <div class="controls">
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" autocomplete="off" aria-label="Email do cliente" />
                </div>
            </div>
            <div class="control-group">
                <label for="senha" class="control-label">Senha</label>
                <div class="controls">
                    <input class="form-control" id="senha" type="password" name="senha" autocomplete="new-password" value="<?php echo htmlspecialchars($senha_value); ?>" 
                           <?php echo $is_edit ? 'placeholder="Não preencha se não quiser alterar."' : ''; ?> aria-label="Senha do cliente" />
                    <img id="imgSenha" src="<?php echo base_url() ?>assets/img/eye.svg" alt="Mostrar/ocultar senha" role="button" tabindex="0" aria-label="Alternar visibilidade da senha" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Tipo de Cliente</label>
                <div class="controls">
                    <label for="fornecedor" class="btn btn-default">Fornecedor
                        <input type="checkbox" id="fornecedor" name="fornecedor" class="badgebox" value="1" <?php echo $fornecedor_checked ? 'checked' : ''; ?> aria-label="Marcar como fornecedor" />
                        <span class="badge">&check;</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="span6">
            <div class="control-group">
                <label for="cep" class="control-label">CEP</label>
                <div class="controls">
                    <input id="cep" type="text" name="cep" value="<?php echo htmlspecialchars($cep_value); ?>" aria-label="CEP do endereço" />
                </div>
            </div>
            <div class="control-group">
                <label for="rua" class="control-label">Rua</label>
                <div class="controls">
                    <input id="rua" type="text" name="rua" value="<?php echo htmlspecialchars($rua_value); ?>" aria-label="Nome da rua" />
                </div>
            </div>
            <div class="control-group">
                <label for="numero" class="control-label">Número</label>
                <div class="controls">
                    <input id="numero" type="text" name="numero" value="<?php echo htmlspecialchars($numero_value); ?>" aria-label="Número do endereço" />
                </div>
            </div>
            <div class="control-group">
                <label for="complemento" class="control-label">Complemento</label>
                <div class="controls">
                    <input id="complemento" type="text" name="complemento" value="<?php echo htmlspecialchars($complemento_value); ?>" aria-label="Complemento do endereço" />
                </div>
            </div>
            <div class="control-group">
                <label for="bairro" class="control-label">Bairro</label>
                <div class="controls">
                    <input id="bairro" type="text" name="bairro" value="<?php echo htmlspecialchars($bairro_value); ?>" aria-label="Bairro" />
                </div>
            </div>
            <div class="control-group">
                <label for="cidade" class="control-label">Cidade</label>
                <div class="controls">
                    <input id="cidade" type="text" name="cidade" value="<?php echo htmlspecialchars($cidade_value); ?>" aria-label="Cidade" />
                </div>
            </div>
            <div class="control-group">
                <label for="estado" class="control-label">Estado</label>
                <div class="controls">
                    <select id="estado" name="estado" aria-label="Estado">
                        <option value="">Selecione...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <div class="span12">
            <div class="span6 offset3" style="display:flex;justify-content: center">
                <button type="submit" class="button btn <?php echo $is_edit ? 'btn-primary' : 'btn-mini btn-success'; ?>" <?php echo $is_edit ? 'style="max-width: 160px"' : ''; ?>>
                    <span class="button__icon"><i class="<?php echo $is_edit ? 'bx bx-sync' : 'bx bx-save'; ?>"></i></span>
                    <span class="button__text2"><?php echo htmlspecialchars($submit_text); ?></span>
                </button>
                <a title="Voltar" class="button btn btn-warning" href="<?php echo site_url() ?>/clientes" aria-label="Voltar para lista de clientes">
                    <span class="button__icon"><i class="bx bx-undo"></i></span>
                    <span class="button__text2">Voltar</span>
                </a>
            </div>
        </div>
    </div>
</form>

<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/clientes-form.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        ClienteForm.init({
            isEdit: <?php echo $is_edit ? 'true' : 'false'; ?>,
            estadoValue: '<?php echo htmlspecialchars($estado_value, ENT_QUOTES); ?>',
            baseUrl: '<?php echo base_url(); ?>'
        });
    });
</script>
