<?php
/**
 * View parcial compartilhada para formulÃ¡rio de clientes.
 */

$is_edit = isset($cliente) && $cliente !== null;

$tipo_cliente_value = set_value(
    'tipo_cliente',
    $is_edit
        ? ($cliente->tipo_cliente ?? (! empty($cliente->pessoa_fisica) ? 'fisica' : 'juridica'))
        : 'fisica'
);

$documento_hidden = set_value('documento', $is_edit ? ($cliente->documento_raw ?? '') : '');
$documento_pf_value = set_value(
    'documento_pf',
    $is_edit && $tipo_cliente_value !== 'juridica' ? ($cliente->documento_pf ?? '') : ''
);
$documento_pj_value = set_value(
    'documento_pj',
    $is_edit && $tipo_cliente_value === 'juridica' ? ($cliente->documento_pj ?? '') : ''
);

$valueOr = function ($field, $clienteField = null) use ($is_edit, $cliente) {
    $default = '';
    if ($is_edit && $clienteField !== null && isset($cliente->$clienteField)) {
        $default = $cliente->$clienteField;
    }
    return set_value($field, $default);
};

$senha_value = $is_edit ? '' : set_value('senha');
$foto_atual = $is_edit && ! empty($cliente->foto) ? $cliente->foto : '';
$documentos_atuais = $is_edit && ! empty($cliente->documentos_adicionais) ? $cliente->documentos_adicionais : '';
$estado_value = $valueOr('estado', 'estado');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/clientes-form.css'); ?>">

<?php if (! empty($custom_error)): ?>
    <div class="alert alert-danger"><?php echo $custom_error; ?></div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($action_url); ?>" id="formCliente" method="post" class="form-horizontal cliente-form" enctype="multipart/form-data">
    <?php if ($is_edit): ?>
        <?php echo form_hidden('idClientes', $cliente->idClientes); ?>
    <?php endif; ?>

    <input type="hidden" name="documento" id="documento" value="<?php echo htmlspecialchars($documento_hidden); ?>">
    <input type="hidden" name="tipo_cliente" id="tipo_cliente_hidden" value="<?php echo htmlspecialchars($tipo_cliente_value); ?>">
    <input type="hidden" name="tipo_cliente" id="tipo_cliente" value="<?php echo htmlspecialchars($tipo_cliente_value); ?>">

    <div class="cliente-tabs cliente-tabs--sections" data-cliente-tabs>
        <div class="cliente-tabs__nav" role="tablist">
            <button type="button" class="cliente-step active" data-tab-target="dados-gerais" role="tab" aria-controls="tab-dados-gerais" aria-selected="true">
                Dados Gerais
            </button>
            <button type="button" class="cliente-step" data-tab-target="contato" role="tab" aria-controls="tab-contato" aria-selected="false">
                Contato &amp; Acesso
            </button>
            <button type="button" class="cliente-step" data-tab-target="endereco" role="tab" aria-controls="tab-endereco" aria-selected="false">
                Endereço
            </button>
            <button type="button" class="cliente-step" data-tab-target="documentos" role="tab" aria-controls="tab-documentos" aria-selected="false">
                Documentos
            </button>
        </div>

        <div class="cliente-tabs__content">
            <section class="cliente-tab active" id="tab-dados-gerais" data-tab-panel="dados-gerais" role="tabpanel" aria-hidden="false">
                <div class="cliente-card">
                    <div class="cliente-card__body">
                        <div class="control-group">
                            <label for="nomeCliente" class="control-label">Nome Completo / Responsável<span class="required">*</span></label>
                            <div class="controls">
                                <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo htmlspecialchars($valueOr('nomeCliente', 'nomeCliente')); ?>" required>
                </div>
                        </div>
                    </div>
                </div>
                <div class="cliente-grid cliente-grid--2 cliente-grid--stacked">
                    <div class="cliente-card cliente-card--naked campos-pf<?php echo $tipo_cliente_value === 'juridica' ? ' is-hidden' : ''; ?>">
                        <div class="cliente-card__body">
                            <div class="control-group">
                                <label for="documento_pf" class="control-label">CPF<span class="required">*</span></label>
                                <div class="controls">
                                    <input id="documento_pf" type="text" name="documento_pf" data-documento-target="pf" value="<?php echo htmlspecialchars($documento_pf_value); ?>" placeholder="000.000.000-00" aria-label="CPF do cliente">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="rg" class="control-label">RG</label>
                                <div class="controls">
                                    <input id="rg" type="text" name="rg" value="<?php echo htmlspecialchars($valueOr('rg', 'rg')); ?>" aria-label="RG do cliente">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="data_nascimento" class="control-label">Data de Nascimento</label>
                                <div class="controls">
                                    <input id="data_nascimento" type="date" name="data_nascimento" value="<?php echo htmlspecialchars($valueOr('data_nascimento', 'data_nascimento')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="estado_civil" class="control-label">Estado Civil</label>
                                <div class="controls">
                                    <select id="estado_civil" name="estado_civil">
                                        <option value="">Selecione...</option>
                                        <?php
                                        $estadosCivis = ['solteiro', 'casado', 'divorciado', 'viuvo', 'uniao_estavel'];
                                        $estadoCivilAtual = strtolower($valueOr('estado_civil', 'estado_civil'));
                                        foreach ($estadosCivis as $estadoCivil) {
                                            $label = ucwords(str_replace('_', ' ', $estadoCivil));
                                            $selected = $estadoCivilAtual === $estadoCivil ? 'selected' : '';
                                            echo "<option value=\"{$estadoCivil}\" {$selected}>{$label}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nacionalidade" class="control-label">Nacionalidade</label>
                                <div class="controls">
                                    <input id="nacionalidade" type="text" name="nacionalidade" value="<?php echo htmlspecialchars($valueOr('nacionalidade', 'nacionalidade')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="profissao" class="control-label">Profissão</label>
                                <div class="controls">
                                    <input id="profissao" type="text" name="profissao" value="<?php echo htmlspecialchars($valueOr('profissao', 'profissao')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nome_mae" class="control-label">Nome da Mãe</label>
                                <div class="controls">
                                    <input id="nome_mae" type="text" name="nome_mae" value="<?php echo htmlspecialchars($valueOr('nome_mae', 'nome_mae')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nome_pai" class="control-label">Nome do Pai</label>
                                <div class="controls">
                                    <input id="nome_pai" type="text" name="nome_pai" value="<?php echo htmlspecialchars($valueOr('nome_pai', 'nome_pai')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="dependentes" class="control-label">Dependentes</label>
                                <div class="controls">
                                    <textarea id="dependentes" name="dependentes" rows="3"><?php echo htmlspecialchars($valueOr('dependentes', 'dependentes')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cliente-card cliente-card--naked campos-pj<?php echo $tipo_cliente_value === 'juridica' ? '' : ' is-hidden'; ?>">
                        <div class="cliente-card__body">
                            <div class="control-group">
                                <label for="documento_pj" class="control-label">CNPJ<span class="required">*</span></label>
                                <div class="controls controls-inline">
                                    <input id="documento_pj" type="text" name="documento_pj" data-documento-target="pj" value="<?php echo htmlspecialchars($documento_pj_value); ?>" placeholder="00.000.000/0000-00" aria-label="CNPJ da empresa">
                                    <button id="buscar_info_cnpj" class="btn btn-xs btn-info" type="button">Buscar</button>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="razao_social" class="control-label">Razão Social<span class="required">*</span></label>
                                <div class="controls">
                                    <input id="razao_social" type="text" name="razao_social" value="<?php echo htmlspecialchars($valueOr('razao_social', 'razao_social')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="nome_fantasia" class="control-label">Nome Fantasia</label>
                                <div class="controls">
                                    <input id="nome_fantasia" type="text" name="nome_fantasia" value="<?php echo htmlspecialchars($valueOr('nome_fantasia', 'nome_fantasia')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="inscricao_estadual" class="control-label">Inscrição Estadual</label>
                                <div class="controls">
                                    <input id="inscricao_estadual" type="text" name="inscricao_estadual" value="<?php echo htmlspecialchars($valueOr('inscricao_estadual', 'inscricao_estadual')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="inscricao_municipal" class="control-label">Inscrição Municipal</label>
                                <div class="controls">
                                    <input id="inscricao_municipal" type="text" name="inscricao_municipal" value="<?php echo htmlspecialchars($valueOr('inscricao_municipal', 'inscricao_municipal')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="data_constituicao" class="control-label">Data de Constituição</label>
                                <div class="controls">
                                    <input id="data_constituicao" type="date" name="data_constituicao" value="<?php echo htmlspecialchars($valueOr('data_constituicao', 'data_constituicao')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="cnae" class="control-label">CNAE</label>
                                <div class="controls">
                                    <input id="cnae" type="text" name="cnae" value="<?php echo htmlspecialchars($valueOr('cnae', 'cnae')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="ramo_atividade" class="control-label">Ramo de Atividade</label>
                                <div class="controls">
                                    <input id="ramo_atividade" type="text" name="ramo_atividade" value="<?php echo htmlspecialchars($valueOr('ramo_atividade', 'ramo_atividade')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="representantes_legais" class="control-label">Representantes Legais</label>
                                <div class="controls">
                                    <textarea id="representantes_legais" name="representantes_legais" rows="2"><?php echo htmlspecialchars($valueOr('representantes_legais', 'representantes_legais')); ?></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="socios" class="control-label">Sócios</label>
                                <div class="controls">
                                    <textarea id="socios" name="socios" rows="2"><?php echo htmlspecialchars($valueOr('socios', 'socios')); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cliente-tab" id="tab-contato" data-tab-panel="contato" role="tabpanel" aria-hidden="true">
                    <div class="cliente-card">
                        <div class="cliente-card__body">
                            <div class="control-group">
                                <label for="contato" class="control-label">Contato</label>
                                <div class="controls">
                                    <input id="contato" type="text" name="contato" value="<?php echo htmlspecialchars($valueOr('contato', 'contato')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="email" class="control-label">E-mail</label>
                                <div class="controls">
                                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($valueOr('email', 'email')); ?>" autocomplete="off">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="site" class="control-label">Site</label>
                                <div class="controls">
                                    <input id="site" type="url" name="site" value="<?php echo htmlspecialchars($valueOr('site', 'site')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="telefone" class="control-label">Telefone</label>
                                <div class="controls">
                                    <input id="telefone" type="text" name="telefone" value="<?php echo htmlspecialchars($valueOr('telefone', 'telefone')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="celular" class="control-label">Celular</label>
                                <div class="controls">
                                    <input id="celular" type="text" name="celular" value="<?php echo htmlspecialchars($valueOr('celular', 'celular')); ?>">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="redes_sociais" class="control-label">Redes Sociais</label>
                                <div class="controls">
                                    <textarea id="redes_sociais" name="redes_sociais" rows="2"><?php echo htmlspecialchars($valueOr('redes_sociais', 'redes_sociais')); ?></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="senha" class="control-label">Senha de Acesso</label>
                                <div class="controls senha-wrapper">
                                    <input class="form-control" id="senha" type="password" name="senha" autocomplete="new-password" value="<?php echo htmlspecialchars($senha_value); ?>" <?php echo $is_edit ? 'placeholder="NÃ£o preencha para manter."' : ''; ?>>
                                    <img id="imgSenha" src="<?php echo base_url('assets/img/eye.svg'); ?>" alt="Mostrar/ocultar senha" role="button" tabindex="0" aria-label="Alternar visibilidade da senha">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cliente-tab" id="tab-endereco" data-tab-panel="endereco" role="tabpanel" aria-hidden="true">
                <div class="cliente-card">
                    <div class="cliente-card__body">
                                <div class="control-group">
                                    <label for="cep" class="control-label">CEP</label>
                                    <div class="controls">
                                        <input id="cep" type="text" name="cep" value="<?php echo htmlspecialchars($valueOr('cep', 'cep')); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                            <label for="numero" class="control-label">Número</label>
                                    <div class="controls">
                                        <input id="numero" type="text" name="numero" value="<?php echo htmlspecialchars($valueOr('numero', 'numero')); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="rua" class="control-label">Rua</label>
                                    <div class="controls">
                                        <input id="rua" type="text" name="rua" value="<?php echo htmlspecialchars($valueOr('rua', 'rua')); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="bairro" class="control-label">Bairro</label>
                                    <div class="controls">
                                        <input id="bairro" type="text" name="bairro" value="<?php echo htmlspecialchars($valueOr('bairro', 'bairro')); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="cidade" class="control-label">Cidade</label>
                                    <div class="controls">
                                        <input id="cidade" type="text" name="cidade" value="<?php echo htmlspecialchars($valueOr('cidade', 'cidade')); ?>">
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="estado" class="control-label">Estado</label>
                                    <div class="controls">
                                        <select id="estado" name="estado" data-selected="<?php echo htmlspecialchars($estado_value); ?>">
                                            <option value="">Selecione...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label for="complemento" class="control-label">Complemento</label>
                                    <div class="controls">
                                        <input id="complemento" type="text" name="complemento" value="<?php echo htmlspecialchars($valueOr('complemento', 'complemento')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cliente-tab" id="tab-documentos" data-tab-panel="documentos" role="tabpanel" aria-hidden="true">
                    <div class="cliente-card">
                        <div class="cliente-card__body">
                            <div class="control-group">
                            <label for="fotoCliente" class="control-label">Foto / Documento</label>
                                <div class="controls">
                                    <input type="file" id="fotoCliente" name="fotoCliente" accept=".jpg,.jpeg,.png,.gif">
                                    <?php if ($foto_atual): ?>
                                        <div class="file-preview">
                                            <img src="<?php echo base_url($foto_atual); ?>" alt="Foto atual" class="file-preview__img">
                                            <label class="file-remove">
                                                <input type="checkbox" name="remover_foto" value="1">
                                                Remover foto atual
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="control-group">
                            <label for="documentosCliente" class="control-label">Documentos adicionais</label>
                                <div class="controls">
                                    <input type="file" id="documentosCliente" name="documentosCliente" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                    <?php if ($documentos_atuais): ?>
                                        <div class="file-preview">
                                            <a href="<?php echo base_url($documentos_atuais); ?>" target="_blank" rel="noopener">Ver documento atual</a>
                                            <label class="file-remove">
                                                <input type="checkbox" name="remover_documentos" value="1">
                                                Remover documento atual
                                            </label>
                                        </div>
                                    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="cliente-card">
                        <div class="cliente-card__body">
                            <div class="control-group">
                            <label for="observacoes" class="control-label">Observações Gerais</label>
                                <div class="controls">
                                    <textarea id="observacoes" name="observacoes" rows="4"><?php echo htmlspecialchars($valueOr('observacoes', 'observacoes')); ?></textarea>
                            </div>
                        </div>
                            <div class="control-group">
                            <label for="observacoes_juridicas" class="control-label">Observações Jurídicas</label>
                                <div class="controls">
                                    <textarea id="observacoes_juridicas" name="observacoes_juridicas" rows="4"><?php echo htmlspecialchars($valueOr('observacoes_juridicas', 'observacoes_juridicas')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="form-actions">
        <div class="span12">
            <div class="span6 offset3 form-actions__buttons">
                <button type="submit" class="button btn <?php echo $is_edit ? 'btn-primary' : 'btn-success'; ?>">
                    <span class="button__icon"><i class="<?php echo $is_edit ? 'bx bx-sync' : 'bx bx-save'; ?>"></i></span>
                    <span class="button__text2"><?php echo htmlspecialchars($submit_text); ?></span>
                </button>
                <a class="button btn btn-warning" href="<?php echo site_url('clientes'); ?>">
                    <span class="button__icon"><i class="bx bx-undo"></i></span>
                    <span class="button__text2">Voltar</span>
                </a>
            </div>
        </div>
    </div>
</form>

<script src="<?php echo base_url('assets/js/jquery.mask.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/jquery.validate.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/sweetalert2.all.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/funcoes.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/clientes-form.js'); ?>"></script>
<script>
    (function($) {
        $(function() {
            if (typeof ClienteForm !== 'undefined') {
                ClienteForm.init({
                    isEdit: <?php echo $is_edit ? 'true' : 'false'; ?>,
                    estadoValue: '<?php echo addslashes($estado_value); ?>',
                    baseUrl: '<?php echo base_url(); ?>',
                    tipoCliente: '<?php echo addslashes($tipo_cliente_value); ?>'
                });
            }
        });
    })(jQuery);
</script>
