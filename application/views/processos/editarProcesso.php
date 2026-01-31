<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<link href="<?php echo base_url() ?>assets/css/select2.css" rel="stylesheet" />
<script src="<?php echo base_url() ?>assets/js/select2.min.js"></script>
<style>
    .control-group.error .help-inline {
        display: flex;
    }
    .form-horizontal .control-group {
        border-bottom: 1px solid #ffffff;
    }
    .form-horizontal .controls {
        margin-left: 20px;
        padding-bottom: 8px 0;
    }
    .form-horizontal .control-label {
        text-align: left;
        padding-top: 15px;
    }
    .nopadding {
        padding: 0 20px !important;
        margin-right: 20px;
    }
    .widget-title h5 {
        padding-bottom: 30px;
        text-align-last: left;
        font-size: 2em;
        font-weight: 500;
    }
    .partes-container {
        width: 100%;
        max-width: 100%;
        margin-top: 30px;
        margin-bottom: 20px;
        box-sizing: border-box;
        overflow: visible;
    }
    .parte-box {
        width: 100%;
        max-width: 100%;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background: #f9f9f9;
        margin-bottom: 15px;
        box-sizing: border-box;
        overflow: visible;
    }
    .parte-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 12px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .parte-info {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }
    .parte-nome {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .parte-detalhes {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .btn-remove-parte {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
        white-space: nowrap;
        margin-left: 10px;
        flex-shrink: 0;
        box-sizing: border-box;
    }
    .btn-remove-parte:hover {
        background: #c82333;
    }
    .btn-add-parte {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
        width: 100%;
        max-width: 100%;
        font-size: 13px;
        box-sizing: border-box;
    }
    .btn-add-parte:hover {
        background: #218838;
    }
    .form-nova-parte {
        background: #f8f9fa;
        border: 1px solid #28a745;
        border-radius: 4px;
        padding: 12px;
        margin-bottom: 10px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        overflow: visible;
    }
    .form-nova-parte.hidden {
        display: none;
    }
    .busca-cliente-wrapper {
        position: relative;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        box-sizing: border-box;
    }
    .busca-cliente-wrapper .span12 {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .resultados-busca-cliente {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        width: 100%;
        margin-top: 2px;
        background: white;
        border: 1px solid #ccc;
        border-radius: 3px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        display: none;
        box-sizing: border-box;
    }
    .select2-cliente {
        width: 100% !important;
    }
    .select2-container {
        z-index: 1051;
    }
    .resultado-item {
        padding: 8px 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        box-sizing: border-box;
    }
    .resultado-item:hover {
        background: #f5f5f5;
    }
    .resultado-item:last-child {
        border-bottom: none;
    }
    .acoes-form-parte {
        display: flex;
        gap: 8px;
        margin-top: 20px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .acoes-form-parte .btn {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }
    .numero-cnj-container {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .numero-cnj-container input {
        width: 220px !important;
        height: 30px !important;
        flex: none;
        box-sizing: border-box;
    }
    .validacao-status {
        margin-top: 5px;
        font-size: 12px;
    }
    .validacao-status.valido {
        color: #28a745;
    }
    .validacao-status.invalido {
        color: #dc3545;
    }
    .advogados-container {
        width: 100%;
        max-width: 100%;
        margin-top: 20px;
        margin-bottom: 20px;
        box-sizing: border-box;
        overflow: visible;
    }
    .advogado-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 12px 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .advogado-info {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }
    .advogado-nome {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .advogado-papel {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 5px;
        text-transform: uppercase;
    }
    .advogado-papel.principal {
        background: #007bff;
        color: white;
    }
    .advogado-papel.coadjuvante {
        background: #6c757d;
        color: white;
    }
    .advogado-papel.estagiario {
        background: #17a2b8;
        color: white;
    }
    .btn-remove-advogado {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
        white-space: nowrap;
        margin-left: 10px;
        flex-shrink: 0;
        box-sizing: border-box;
    }
    .btn-remove-advogado:hover {
        background: #c82333;
    }
    .btn-add-advogado {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
    }
    .btn-add-advogado:hover {
        background: #218838;
    }
    .form-novo-advogado {
        width: 100%;
        max-width: 100%;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 15px;
        box-sizing: border-box;
        overflow: visible;
    }
    .acoes-form-advogado {
        margin-top: 15px;
        display: flex;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .acoes-form-advogado .btn {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }
</style>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-gavel"></i>
                </span>
                <h5>Editar Processo Judicial</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formProcesso" method="post" class="form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="idProcessos" value="<?= isset($result->idProcessos) ? $result->idProcessos : ($result->id ?? '') ?>">
                
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="numeroProcesso" class="control-label">Número CNJ <span class="required">*</span></label>
                            <div class="controls">
                                <div class="numero-cnj-container">
                                    <input id="numeroProcesso" type="text" name="numeroProcesso" 
                                        placeholder="0000123-45.2023.8.13.0139" 
                                        value="<?php echo set_value('numeroProcesso', isset($result->numeroProcessoFormatado) ? $result->numeroProcessoFormatado : ($result->numeroProcesso ?? '')); ?>" required readonly />
                                    <button type="button" id="btnValidarCNJ" class="btn btn-info btn-mini" title="Validar CNJ">
                                        <i class='bx bx-search'></i>
                                    </button>
                                </div>
                                <div id="validacaoStatus" class="validacao-status"></div>
                                <small class="help-inline">Número CNJ é fixo e não pode ser alterado.</small>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tribunal" class="control-label">Tribunal</label>
                            <div class="controls">
                                <select id="tribunal" name="tribunal">
                                    <option value="">Selecione...</option>
                                    <option value="TJ" <?= set_value('tribunal', $result->tribunal ?? '') == 'TJ' ? 'selected' : '' ?>>TJ - Tribunal de Justiça</option>
                                    <option value="TRF" <?= set_value('tribunal', $result->tribunal ?? '') == 'TRF' ? 'selected' : '' ?>>TRF - Tribunal Regional Federal</option>
                                    <option value="TRT" <?= set_value('tribunal', $result->tribunal ?? '') == 'TRT' ? 'selected' : '' ?>>TRT - Tribunal Regional do Trabalho</option>
                                    <option value="TRE" <?= set_value('tribunal', $result->tribunal ?? '') == 'TRE' ? 'selected' : '' ?>>TRE - Tribunal Regional Eleitoral</option>
                                    <option value="STJ" <?= set_value('tribunal', $result->tribunal ?? '') == 'STJ' ? 'selected' : '' ?>>STJ - Superior Tribunal de Justiça</option>
                                    <option value="STF" <?= set_value('tribunal', $result->tribunal ?? '') == 'STF' ? 'selected' : '' ?>>STF - Supremo Tribunal Federal</option>
                                    <option value="TST" <?= set_value('tribunal', $result->tribunal ?? '') == 'TST' ? 'selected' : '' ?>>TST - Tribunal Superior do Trabalho</option>
                                    <option value="TSE" <?= set_value('tribunal', $result->tribunal ?? '') == 'TSE' ? 'selected' : '' ?>>TSE - Tribunal Superior Eleitoral</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="segmento" class="control-label">Segmento</label>
                            <div class="controls">
                                <select id="segmento" name="segmento">
                                    <option value="">Selecione...</option>
                                    <option value="1" <?= set_value('segmento', $result->segmento ?? '') == '1' ? 'selected' : '' ?>>Supremo Tribunal Federal (STF)</option>
                                    <option value="2" <?= set_value('segmento', $result->segmento ?? '') == '2' ? 'selected' : '' ?>>Conselho Nacional de Justiça (CNJ)</option>
                                    <option value="3" <?= set_value('segmento', $result->segmento ?? '') == '3' ? 'selected' : '' ?>>Superior Tribunal de Justiça (STJ)</option>
                                    <option value="4" <?= set_value('segmento', $result->segmento ?? '') == '4' ? 'selected' : '' ?>>Justiça Federal</option>
                                    <option value="5" <?= set_value('segmento', $result->segmento ?? '') == '5' ? 'selected' : '' ?>>Justiça do Trabalho</option>
                                    <option value="6" <?= set_value('segmento', $result->segmento ?? '') == '6' ? 'selected' : '' ?>>Justiça Eleitoral</option>
                                    <option value="7" <?= set_value('segmento', $result->segmento ?? '') == '7' ? 'selected' : '' ?>>Justiça Militar da União</option>
                                    <option value="8" <?= set_value('segmento', $result->segmento ?? '') == '8' ? 'selected' : '' ?>>Justiça Estadual</option>
                                    <option value="9" <?= set_value('segmento', $result->segmento ?? '') == '9' ? 'selected' : '' ?>>Justiça Militar Estadual</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="vara" class="control-label">Vara</label>
                            <div class="controls">
                                <input id="vara" type="text" name="vara" placeholder="Vara" value="<?php echo set_value('vara', $result->vara ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="comarca" class="control-label">Comarca</label>
                            <div class="controls">
                                <input id="comarca" type="text" name="comarca" placeholder="Comarca" value="<?php echo set_value('comarca', $result->comarca ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="classe" class="control-label">Classe</label>
                            <div class="controls">
                                <select id="classe" name="classe">
                                    <option value="">Selecione...</option>
                                    <option value="Ação de Cobrança" <?= set_value('classe', $result->classe ?? '') == 'Ação de Cobrança' ? 'selected' : '' ?>>Ação de Cobrança</option>
                                    <option value="Ação de Despejo" <?= set_value('classe', $result->classe ?? '') == 'Ação de Despejo' ? 'selected' : '' ?>>Ação de Despejo</option>
                                    <option value="Ação de Indenização" <?= set_value('classe', $result->classe ?? '') == 'Ação de Indenização' ? 'selected' : '' ?>>Ação de Indenização</option>
                                    <option value="Ação Trabalhista" <?= set_value('classe', $result->classe ?? '') == 'Ação Trabalhista' ? 'selected' : '' ?>>Ação Trabalhista</option>
                                    <option value="Ação Previdenciária" <?= set_value('classe', $result->classe ?? '') == 'Ação Previdenciária' ? 'selected' : '' ?>>Ação Previdenciária</option>
                                    <option value="Ação de Família" <?= set_value('classe', $result->classe ?? '') == 'Ação de Família' ? 'selected' : '' ?>>Ação de Família</option>
                                    <option value="Ação Criminal" <?= set_value('classe', $result->classe ?? '') == 'Ação Criminal' ? 'selected' : '' ?>>Ação Criminal</option>
                                    <option value="Mandado de Segurança" <?= set_value('classe', $result->classe ?? '') == 'Mandado de Segurança' ? 'selected' : '' ?>>Mandado de Segurança</option>
                                    <option value="Habeas Corpus" <?= set_value('classe', $result->classe ?? '') == 'Habeas Corpus' ? 'selected' : '' ?>>Habeas Corpus</option>
                                    <option value="Outros" <?= set_value('classe', $result->classe ?? '') == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="assunto" class="control-label">Assunto</label>
                            <div class="controls">
                                <select id="assunto" name="assunto">
                                    <option value="">Selecione...</option>
                                    <option value="Contratos" <?= set_value('assunto', $result->assunto ?? '') == 'Contratos' ? 'selected' : '' ?>>Contratos</option>
                                    <option value="Danos Morais" <?= set_value('assunto', $result->assunto ?? '') == 'Danos Morais' ? 'selected' : '' ?>>Danos Morais</option>
                                    <option value="Danos Materiais" <?= set_value('assunto', $result->assunto ?? '') == 'Danos Materiais' ? 'selected' : '' ?>>Danos Materiais</option>
                                    <option value="FGTS" <?= set_value('assunto', $result->assunto ?? '') == 'FGTS' ? 'selected' : '' ?>>FGTS</option>
                                    <option value="Férias" <?= set_value('assunto', $result->assunto ?? '') == 'Férias' ? 'selected' : '' ?>>Férias</option>
                                    <option value="13º Salário" <?= set_value('assunto', $result->assunto ?? '') == '13º Salário' ? 'selected' : '' ?>>13º Salário</option>
                                    <option value="Pensão Alimentícia" <?= set_value('assunto', $result->assunto ?? '') == 'Pensão Alimentícia' ? 'selected' : '' ?>>Pensão Alimentícia</option>
                                    <option value="Guarda" <?= set_value('assunto', $result->assunto ?? '') == 'Guarda' ? 'selected' : '' ?>>Guarda</option>
                                    <option value="Divórcio" <?= set_value('assunto', $result->assunto ?? '') == 'Divórcio' ? 'selected' : '' ?>>Divórcio</option>
                                    <option value="Outros" <?= set_value('assunto', $result->assunto ?? '') == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataDistribuicao" class="control-label">Data de Distribuição</label>
                            <div class="controls">
                                <input id="dataDistribuicao" type="date" name="dataDistribuicao" value="<?php echo set_value('dataDistribuicao', $result->dataDistribuicao ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="valorCausa" class="control-label">Valor da Causa</label>
                            <div class="controls">
                                <input id="valorCausa" type="text" name="valorCausa" 
                                    placeholder="0,00" 
                                    value="<?php
                                        $valorCausa = isset($result->valorCausa) ? $result->valorCausa : null;
                                        echo set_value('valorCausa', $valorCausa ? number_format($valorCausa, 2, ',', '.') : '');
                                    ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status <span class="required">*</span></label>
                            <div class="controls">
                                <select id="status" name="status" required>
                                    <option value="em_andamento" <?= set_value('status', $result->status ?? 'em_andamento') == 'em_andamento' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="arquivado" <?= set_value('status', $result->status ?? '') == 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                                    <option value="finalizado" <?= set_value('status', $result->status ?? '') == 'finalizado' ? 'selected' : '' ?>>Encerrado</option>
                                    <option value="suspenso" <?= set_value('status', $result->status ?? '') == 'suspenso' ? 'selected' : '' ?>>Suspenso</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Advogados Responsáveis <span class="required">*</span></label>
                            <div class="controls">
                                <div class="advogados-container">
                                    <div id="advogadosList"></div>
                                    <?php if ($pode_editar): ?>
                                    <button type="button" class="btn-add-advogado" onclick="mostrarFormNovoAdvogado()">
                                        <i class="bx bx-plus"></i> Adicionar Advogado
                                    </button>
                                    <div id="formNovoAdvogado" class="form-novo-advogado hidden">
                                        <div class="control-group">
                                            <label for="advogado-select-novo">Advogado</label>
                                            <select id="advogado-select-novo" class="span12">
                                                <option value="">Selecione um advogado...</option>
                                                <?php
                                                if (isset($usuarios) && $usuarios) {
                                                    $usuarios_id_col = null;
                                                    $usuarios_nome_col = null;
                                                    if (!empty($usuarios)) {
                                                        $first = reset($usuarios);
                                                        $usuarios_id_col = isset($first->idUsuarios) ? 'idUsuarios' : (isset($first->id) ? 'id' : null);
                                                        $usuarios_nome_col = isset($first->nome) ? 'nome' : null;
                                                    }
                                                    foreach ($usuarios as $usuario) {
                                                        $usuario_id = $usuarios_id_col ? $usuario->$usuarios_id_col : null;
                                                        $usuario_nome = $usuarios_nome_col ? $usuario->$usuarios_nome_col : 'Usuário';
                                                        echo '<option value="' . $usuario_id . '" data-nome="' . htmlspecialchars($usuario_nome) . '">' . htmlspecialchars($usuario_nome) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="control-group">
                                            <label for="papel-novo">Papel</label>
                                            <select id="papel-novo" class="span12">
                                                <option value="principal">Principal</option>
                                                <option value="coadjuvante">Coadjuvante</option>
                                                <option value="estagiario">Estagiário</option>
                                            </select>
                                        </div>
                                        <div class="acoes-form-advogado">
                                            <button type="button" class="btn btn-success" onclick="salvarNovoAdvogado()">Adicionar</button>
                                            <button type="button" class="btn btn-secondary" onclick="cancelarNovoAdvogado()">Cancelar</button>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" style="margin-bottom: 20px; display: block;">Partes do Processo</label>
                            <div class="controls">
                                <div class="partes-container">
                                    <!-- Polo Ativo -->
                                    <div class="parte-box">
                                        <h6 style="margin-top: 0; color: #28a745; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
                                            <i class='bx bx-user-check'></i> Polo Ativo
                                        </h6>
                                        <div id="partesAtivo">
                                            <?php 
                                            if (!isset($contadorPartes)) {
                                                $contadorPartes = ['ativo' => 0, 'passivo' => 0];
                                            }
                                            if (isset($partes_ativo) && !empty($partes_ativo)) {
                                                foreach ($partes_ativo as $index => $parte) {
                                                    $contadorPartes['ativo'] = max($contadorPartes['ativo'], $index + 1);
                                                    $nome = htmlspecialchars($parte->nome ?? '');
                                                    $cpf_cnpj = htmlspecialchars($parte->cpf_cnpj ?? '');
                                                    $email = htmlspecialchars($parte->email ?? '');
                                                    $telefone = htmlspecialchars($parte->telefone ?? '');
                                                    $clientes_id = $parte->clientes_id ?? '';
                                                    
                                                    $detalhes = [];
                                                    if ($cpf_cnpj) $detalhes[] = 'CPF/CNPJ: ' . $cpf_cnpj;
                                                    
                                                    echo '<div class="parte-item" id="parte-ativo-' . ($index + 1) . '">';
                                                    echo '<div class="parte-info">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][tipo_polo]" value="ativo">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][nome]" value="' . $nome . '">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][cpf_cnpj]" value="' . $cpf_cnpj . '">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][email]" value="' . $email . '">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][telefone]" value="' . $telefone . '">';
                                                    echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][clientes_id]" value="' . $clientes_id . '">';
                                                    echo '<div class="parte-nome">' . $nome . '</div>';
                                                    if (count($detalhes) > 0) {
                                                        echo '<div class="parte-detalhes">' . implode(' | ', $detalhes) . '</div>';
                                                    }
                                                    echo '</div>';
                                                    echo '<button type="button" class="btn-remove-parte" onclick="removerParte(\'ativo\', ' . ($index + 1) . ')">';
                                                    echo '<i class="bx bx-trash"></i> Remover';
                                                    echo '</button>';
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div id="formNovaParteAtivo" class="form-nova-parte hidden">
                                            <div class="busca-cliente-wrapper">
                                                <input type="text" id="cliente-select-ativo" class="span12 select2-cliente" style="width: 100%;" placeholder="Digite o nome do cliente para buscar...">
                                            </div>
                                            <input type="hidden" id="cpf-cnpj-novo-ativo" value="">
                                            <input type="hidden" id="clientes-id-novo-ativo" value="">
                                            <input type="hidden" id="nome-novo-ativo" value="">
                                            <input type="hidden" id="email-novo-ativo" value="">
                                            <input type="hidden" id="telefone-novo-ativo" value="">
                                            <div class="acoes-form-parte">
                                                <button type="button" class="btn btn-success btn-mini" onclick="salvarNovaParte('ativo')">
                                                    <i class='bx bx-check'></i> Adicionar
                                                </button>
                                                <button type="button" class="btn btn-default btn-mini" onclick="cancelarNovaParte('ativo')">
                                                    <i class='bx bx-x'></i> Cancelar
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-add-parte" onclick="mostrarFormNovaParte('ativo')">
                                            <i class='bx bx-plus'></i> Adicionar Parte
                                        </button>
                                    </div>

                                    <!-- Polo Passivo -->
                                    <div class="parte-box">
                                        <h6 style="margin-top: 0; color: #dc3545; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
                                            <i class='bx bx-user-x'></i> Polo Passivo
                                        </h6>
                                        <div id="partesPassivo">
                                            <?php 
                                            if (!isset($contadorPartes)) {
                                                $contadorPartes = ['ativo' => 0, 'passivo' => 0];
                                            }
                                            if (isset($partes_passivo) && !empty($partes_passivo)) {
                                                foreach ($partes_passivo as $index => $parte) {
                                                    $contadorPartes['passivo'] = max($contadorPartes['passivo'], $index + 1);
                                                    $nome = htmlspecialchars($parte->nome ?? '');
                                                    $cpf_cnpj = htmlspecialchars($parte->cpf_cnpj ?? '');
                                                    $email = htmlspecialchars($parte->email ?? '');
                                                    $telefone = htmlspecialchars($parte->telefone ?? '');
                                                    $clientes_id = $parte->clientes_id ?? '';
                                                    
                                                    $detalhes = [];
                                                    if ($cpf_cnpj) $detalhes[] = 'CPF/CNPJ: ' . $cpf_cnpj;
                                                    
                                                    echo '<div class="parte-item" id="parte-passivo-' . ($index + 1) . '">';
                                                    echo '<div class="parte-info">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][tipo_polo]" value="passivo">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][nome]" value="' . $nome . '">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][cpf_cnpj]" value="' . $cpf_cnpj . '">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][email]" value="' . $email . '">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][telefone]" value="' . $telefone . '">';
                                                    echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][clientes_id]" value="' . $clientes_id . '">';
                                                    echo '<div class="parte-nome">' . $nome . '</div>';
                                                    if (count($detalhes) > 0) {
                                                        echo '<div class="parte-detalhes">' . implode(' | ', $detalhes) . '</div>';
                                                    }
                                                    echo '</div>';
                                                    echo '<button type="button" class="btn-remove-parte" onclick="removerParte(\'passivo\', ' . ($index + 1) . ')">';
                                                    echo '<i class="bx bx-trash"></i> Remover';
                                                    echo '</button>';
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div id="formNovaPartePassivo" class="form-nova-parte hidden">
                                            <div class="busca-cliente-wrapper">
                                                <input type="text" id="cliente-select-passivo" class="span12 select2-cliente" style="width: 100%;" placeholder="Digite o nome do cliente para buscar...">
                                            </div>
                                            <input type="hidden" id="cpf-cnpj-novo-passivo" value="">
                                            <input type="hidden" id="clientes-id-novo-passivo" value="">
                                            <input type="hidden" id="nome-novo-passivo" value="">
                                            <input type="hidden" id="email-novo-passivo" value="">
                                            <input type="hidden" id="telefone-novo-passivo" value="">
                                            <div class="acoes-form-parte">
                                                <button type="button" class="btn btn-success btn-mini" onclick="salvarNovaParte('passivo')">
                                                    <i class='bx bx-check'></i> Adicionar
                                                </button>
                                                <button type="button" class="btn btn-default btn-mini" onclick="cancelarNovaParte('passivo')">
                                                    <i class='bx bx-x'></i> Cancelar
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn-add-parte" onclick="mostrarFormNovaParte('passivo')">
                                            <i class='bx bx-plus'></i> Adicionar Parte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="control-group">
                            <label for="documentos" class="control-label">Upload de Documentos</label>
                            <div class="controls">
                                <input type="file" id="documentos" name="documentos[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.rtf" />
                                <small class="help-inline">Selecione um ou mais documentos para anexar ao processo (máx. 10MB por arquivo)</small>
                                <div id="preview-documentos" style="margin-top: 10px;"></div>
                                
                                <?php if (isset($documentos) && !empty($documentos)): ?>
                                    <div style="margin-top: 15px;">
                                        <strong>Documentos já anexados:</strong>
                                        <ul style="list-style: none; padding: 0; margin-top: 5px;">
                                            <?php foreach ($documentos as $doc): ?>
                                                <li style="padding: 5px; background: #e8f5e9; margin-bottom: 5px; border-radius: 3px;">
                                                    <i class="bx bx-file"></i> 
                                                    <?= htmlspecialchars($doc->nome ?? $doc->titulo ?? '-') ?> 
                                                    (<?= isset($doc->tamanho) ? number_format($doc->tamanho / 1024, 2) : '0' ?> KB)
                                                    - <?= isset($doc->dataCadastro) ? date('d/m/Y H:i', strtotime($doc->dataCadastro)) : (isset($doc->dataUpload) ? date('d/m/Y H:i', strtotime($doc->dataUpload)) : '') ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="8" class="span12" placeholder="Informações adicionais sobre o processo..."><?php echo set_value('observacoes', $result->observacoes ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span>
                                    <span class="button__text2">Salvar Processo</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/processos" class="button btn btn-mini btn-warning" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-x'></i></span>
                                    <span class="button__text2">Cancelar</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var contadorPartes = {
        ativo: <?= isset($contadorPartes) && isset($contadorPartes['ativo']) ? $contadorPartes['ativo'] : 0 ?>,
        passivo: <?= isset($contadorPartes) && isset($contadorPartes['passivo']) ? $contadorPartes['passivo'] : 0 ?>
    };
    
    // Dados das partes do POST para restaurar em caso de erro
    var partesAtivoRestaurar = <?php echo json_encode(isset($partes_ativo_post) && is_array($partes_ativo_post) ? $partes_ativo_post : [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
    var partesPassivoRestaurar = <?php echo json_encode(isset($partes_passivo_post) && is_array($partes_passivo_post) ? $partes_passivo_post : [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
    
    // Debug: verificar se há dados para restaurar
    console.log('Partes Ativo para restaurar:', partesAtivoRestaurar);
    console.log('Partes Passivo para restaurar:', partesPassivoRestaurar);

    $(document).ready(function() {
        // Remover required de campos ocultos antes de submeter o formulário
        $('form').on('submit', function(e) {
            $('.hidden input[required]').removeAttr('required');
        });
        
        // Restaurar partes em caso de erro de validação (partes do POST têm prioridade sobre as do banco quando há erro)
        if (Array.isArray(partesAtivoRestaurar) && partesAtivoRestaurar.length > 0) {
            // Limpar partes existentes do banco se houver partes do POST (erro de validação)
            $('#partesAtivo').empty();
            partesAtivoRestaurar.forEach(function(parte, index) {
                if (parte && (parte.nome || parte.clientes_id)) {
                    var indice = index + 1;
                    contadorPartes['ativo'] = Math.max(contadorPartes['ativo'], indice);
                    var nome = String(parte.nome || '').trim();
                    var cpf_cnpj = String(parte.cpf_cnpj || '').trim();
                    var email = String(parte.email || '').trim();
                    var telefone = String(parte.telefone || '').trim();
                    var clientes_id = String(parte.clientes_id || '').trim();
                    
                    // Se não tem nome mas tem clientes_id, buscar o nome (será preenchido depois se necessário)
                    if (!nome && clientes_id) {
                        nome = 'Cliente ID: ' + clientes_id;
                    }
                    
                    if (nome || clientes_id) {
                        var detalhesTexto = '';
                        if (cpf_cnpj) {
                            detalhesTexto = '<div class="parte-detalhes">CPF/CNPJ: ' + $('<div>').text(cpf_cnpj).html() + '</div>';
                        }
                        
                        var html = '<div class="parte-item" id="parte-ativo-' + indice + '">' +
                            '<div class="parte-info">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][tipo_polo]" value="ativo">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][nome]" value="' + $('<div>').text(nome).html() + '">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][cpf_cnpj]" value="' + $('<div>').text(cpf_cnpj).html() + '">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][email]" value="' + $('<div>').text(email).html() + '">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][telefone]" value="' + $('<div>').text(telefone).html() + '">' +
                                '<input type="hidden" name="partes_ativo[' + indice + '][clientes_id]" value="' + $('<div>').text(clientes_id).html() + '">' +
                                '<div class="parte-nome">' + $('<div>').text(nome).html() + '</div>' +
                                detalhesTexto +
                            '</div>' +
                            '<button type="button" class="btn-remove-parte" onclick="removerParte(\'ativo\', ' + indice + ')">' +
                                '<i class="bx bx-trash"></i> Remover' +
                            '</button>' +
                        '</div>';
                        
                        $('#partesAtivo').append(html);
                    }
                }
            });
        }
        
        if (Array.isArray(partesPassivoRestaurar) && partesPassivoRestaurar.length > 0) {
            // Limpar partes existentes do banco se houver partes do POST (erro de validação)
            $('#partesPassivo').empty();
            partesPassivoRestaurar.forEach(function(parte, index) {
                if (parte && (parte.nome || parte.clientes_id)) {
                    var indice = index + 1;
                    contadorPartes['passivo'] = Math.max(contadorPartes['passivo'], indice);
                    var nome = String(parte.nome || '').trim();
                    var cpf_cnpj = String(parte.cpf_cnpj || '').trim();
                    var email = String(parte.email || '').trim();
                    var telefone = String(parte.telefone || '').trim();
                    var clientes_id = String(parte.clientes_id || '').trim();
                    
                    // Se não tem nome mas tem clientes_id, buscar o nome (será preenchido depois se necessário)
                    if (!nome && clientes_id) {
                        nome = 'Cliente ID: ' + clientes_id;
                    }
                    
                    if (nome || clientes_id) {
                        var detalhesTexto = '';
                        if (cpf_cnpj) {
                            detalhesTexto = '<div class="parte-detalhes">CPF/CNPJ: ' + $('<div>').text(cpf_cnpj).html() + '</div>';
                        }
                        
                        var html = '<div class="parte-item" id="parte-passivo-' + indice + '">' +
                            '<div class="parte-info">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][tipo_polo]" value="passivo">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][nome]" value="' + $('<div>').text(nome).html() + '">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][cpf_cnpj]" value="' + $('<div>').text(cpf_cnpj).html() + '">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][email]" value="' + $('<div>').text(email).html() + '">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][telefone]" value="' + $('<div>').text(telefone).html() + '">' +
                                '<input type="hidden" name="partes_passivo[' + indice + '][clientes_id]" value="' + $('<div>').text(clientes_id).html() + '">' +
                                '<div class="parte-nome">' + $('<div>').text(nome).html() + '</div>' +
                                detalhesTexto +
                            '</div>' +
                            '<button type="button" class="btn-remove-parte" onclick="removerParte(\'passivo\', ' + indice + ')">' +
                                '<i class="bx bx-trash"></i> Remover' +
                            '</button>' +
                        '</div>';
                        
                        $('#partesPassivo').append(html);
                    }
                }
            });
        }
        
        // Máscara para número de processo
        $('#numeroProcesso').on('blur', function() {
            var numero = $(this).val().replace(/[^0-9]/g, '');
            if (numero.length == 20) {
                var formatado = numero.substring(0, 7) + '-' + 
                               numero.substring(7, 9) + '.' + 
                               numero.substring(9, 13) + '.' + 
                               numero.substring(13, 14) + '.' + 
                               numero.substring(14, 16) + '.' + 
                               numero.substring(16, 20);
                $(this).val(formatado);
            }
        });

        // Máscara para valor da causa
        $('#valorCausa').mask('#.##0,00', {reverse: true});


        // Preview de documentos selecionados
        $('#documentos').on('change', function() {
            var files = this.files;
            var preview = $('#preview-documentos');
            preview.empty();
            
            if (files.length > 0) {
                var list = $('<ul style="list-style: none; padding: 0; margin-top: 10px;"></ul>');
                
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    var size = (file.size / 1024 / 1024).toFixed(2);
                    var item = $('<li style="padding: 5px; background: #f0f0f0; margin-bottom: 5px; border-radius: 3px;">' +
                        '<i class="bx bx-file"></i> ' + file.name + ' (' + size + ' MB)' +
                        '</li>');
                    list.append(item);
                }
                
                preview.append(list);
            }
        });

        // Validação CNJ
        $('#btnValidarCNJ').on('click', function() {
            var numero = $('#numeroProcesso').val();
            if (!numero) {
                Swal.fire({
                    type: 'error',
                    title: 'Erro',
                    text: 'Informe o número do processo CNJ'
                });
                return;
            }

            $('#btnValidarCNJ').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');

            $.ajax({
                url: '<?= site_url("processos/validar_cnj") ?>',
                method: 'POST',
                data: {
                    numero: numero,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    $('#btnValidarCNJ').prop('disabled', false).html('<i class="bx bx-search"></i>');
                    
                    if (response.valido) {
                        $('#validacaoStatus').removeClass('invalido').addClass('valido')
                            .html('<i class="bx bx-check-circle"></i> Número CNJ válido!');
                        
                        // Preencher campos automaticamente se disponível
                        if (response.dados) {
                            // Mapear tribunal
                            var tribunalMap = {
                                '00': 'STF',
                                '90': 'STJ',
                                '01': 'TRF',
                                '02': 'TRF',
                                '03': 'TRF',
                                '04': 'TRF',
                                '05': 'TRF',
                                '06': 'TRF',
                                '13': 'TJ',
                                '26': 'TJ'
                            };
                            if (response.dados.tribunal && tribunalMap[response.dados.tribunal]) {
                                $('#tribunal').val(tribunalMap[response.dados.tribunal]);
                            }
                            
                            // Mapear segmento (usar o número diretamente)
                            if (response.dados.segmento) {
                                $('#segmento').val(response.dados.segmento);
                            }
                        }
                    } else {
                        $('#validacaoStatus').removeClass('valido').addClass('invalido')
                            .html('<i class="bx bx-x-circle"></i> ' + (response.erros.join(' ') || 'Número CNJ inválido'));
                    }
                },
                error: function() {
                    $('#btnValidarCNJ').prop('disabled', false).html('<i class="bx bx-search"></i>');
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: 'Erro ao validar número CNJ'
                    });
                }
            });
        });
    });

    function inicializarSelect2Cliente(tipoPolo) {
        $('#cliente-select-' + tipoPolo).select2({
            placeholder: 'Digite o nome do cliente para buscar...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: '<?= site_url("processos/buscar_cliente") ?>',
                dataType: 'json',
                delay: 300,
                quietMillis: 300,
                data: function (term, page) {
                    return {
                        term: term,
                        page: page
                    };
                },
                results: function (data, page) {
                    return {
                        results: $.map(data, function (item) {
                            // Armazenar todos os dados no objeto para recuperar depois
                            var result = {
                                id: item.id,
                                text: item.text || item.nome || item.id,
                                nome: item.nome || item.text || '',
                                documento: item.documento || '',
                                email: item.email || '',
                                telefone: item.telefone || item.celular || ''
                            };
                            return result;
                        }),
                        more: false
                    };
                }
            },
            formatResult: function(item) {
                return item.text || item.nome || item.id;
            },
            formatSelection: function(item) {
                // Armazenar dados completos no objeto do Select2
                if (item) {
                    item.nome = item.nome || item.text;
                    item.documento = item.documento || '';
                    item.email = item.email || '';
                    item.telefone = item.telefone || '';
                }
                return item.text || item.nome || item.id;
            },
            escapeMarkup: function(m) {
                return m;
            }
        });
        
        // Quando um cliente é selecionado (select2-selecting ocorre antes da seleção)
        $('#cliente-select-' + tipoPolo).on('select2-selecting', function (e) {
            var data = e.choice;
            if (data) {
                $('#clientes-id-novo-' + tipoPolo).val(data.id);
                $('#nome-novo-' + tipoPolo).val(data.nome);
                $('#cpf-cnpj-novo-' + tipoPolo).val(data.documento || '');
                $('#email-novo-' + tipoPolo).val(data.email || '');
                $('#telefone-novo-' + tipoPolo).val(data.telefone || '');
            }
        });
        
        // select2-selected ocorre após a seleção ser confirmada
        $('#cliente-select-' + tipoPolo).on('select2-selected', function (e) {
            var data = e.choice || e.object;
            if (data) {
                $('#clientes-id-novo-' + tipoPolo).val(data.id);
                $('#nome-novo-' + tipoPolo).val(data.nome || data.text);
                $('#cpf-cnpj-novo-' + tipoPolo).val(data.documento || '');
                $('#email-novo-' + tipoPolo).val(data.email || '');
                $('#telefone-novo-' + tipoPolo).val(data.telefone || '');
            } else {
                // Tentar obter dados do Select2 diretamente
                var selectedData = $('#cliente-select-' + tipoPolo).select2('data');
                if (selectedData) {
                    $('#clientes-id-novo-' + tipoPolo).val(selectedData.id);
                    $('#nome-novo-' + tipoPolo).val(selectedData.text || selectedData.nome);
                }
            }
        });
        
        // Quando o cliente é removido
        $('#cliente-select-' + tipoPolo).on('select2-clearing', function (e) {
            $('#clientes-id-novo-' + tipoPolo).val('');
            $('#nome-novo-' + tipoPolo).val('');
            $('#cpf-cnpj-novo-' + tipoPolo).val('');
            $('#email-novo-' + tipoPolo).val('');
            $('#telefone-novo-' + tipoPolo).val('');
        });
        
        // Fallback: verificar dados quando o valor muda
        $('#cliente-select-' + tipoPolo).on('change', function() {
            var selectedData = $('#cliente-select-' + tipoPolo).select2('data');
            if (selectedData && selectedData.id) {
                $('#clientes-id-novo-' + tipoPolo).val(selectedData.id);
                $('#nome-novo-' + tipoPolo).val(selectedData.text || selectedData.nome || $('#cliente-select-' + tipoPolo).val());
                // Tentar obter dados completos se disponíveis
                if (selectedData.documento) {
                    $('#cpf-cnpj-novo-' + tipoPolo).val(selectedData.documento);
                }
                if (selectedData.email) {
                    $('#email-novo-' + tipoPolo).val(selectedData.email);
                }
                if (selectedData.telefone) {
                    $('#telefone-novo-' + tipoPolo).val(selectedData.telefone);
                }
            }
        });
    }
    
    function mostrarFormNovaParte(tipoPolo) {
        var form = $('#formNovaParte' + (tipoPolo.charAt(0).toUpperCase() + tipoPolo.slice(1)));
        form.removeClass('hidden');
        
        // Inicializar Select2 se ainda não foi inicializado
        if (!$('#cliente-select-' + tipoPolo).hasClass('select2-offscreen')) {
            inicializarSelect2Cliente(tipoPolo);
        }
        
        // Focar no Select2 após um pequeno delay para garantir que foi renderizado
        setTimeout(function() {
            $('#cliente-select-' + tipoPolo).select2('focus');
        }, 100);
    }
    
    function cancelarNovaParte(tipoPolo) {
        var form = $('#formNovaParte' + (tipoPolo.charAt(0).toUpperCase() + tipoPolo.slice(1)));
        form.addClass('hidden');
        
        // Limpar Select2
        if ($('#cliente-select-' + tipoPolo).hasClass('select2-offscreen')) {
            $('#cliente-select-' + tipoPolo).select2('data', null);
        } else {
            $('#cliente-select-' + tipoPolo).val('');
        }
        
        // Limpar campos hidden
        form.find('input[type="hidden"]').val('');
    }

    function salvarNovaParte(tipoPolo) {
        // Verificar se há dados selecionados no Select2
        var selectedData = $('#cliente-select-' + tipoPolo).select2('data');
        var clientesId = $('#clientes-id-novo-' + tipoPolo).val();
        var nome = $('#nome-novo-' + tipoPolo).val().trim();
        var valorSelect = $('#cliente-select-' + tipoPolo).val();
        
        // Se não tiver nome nos campos hidden, tentar obter do Select2
        if (!nome || !clientesId) {
            if (selectedData) {
                // Dados do Select2 estão disponíveis
                if (!clientesId && selectedData.id) {
                    clientesId = selectedData.id;
                    $('#clientes-id-novo-' + tipoPolo).val(clientesId);
                }
                if (!nome) {
                    nome = selectedData.nome || selectedData.text || valorSelect || '';
                    if (nome) {
                        $('#nome-novo-' + tipoPolo).val(nome);
                    }
                }
                if (selectedData.documento) {
                    $('#cpf-cnpj-novo-' + tipoPolo).val(selectedData.documento);
                }
                if (selectedData.email) {
                    $('#email-novo-' + tipoPolo).val(selectedData.email);
                }
                if (selectedData.telefone) {
                    $('#telefone-novo-' + tipoPolo).val(selectedData.telefone);
                }
            } else if (valorSelect) {
                // Se não tiver selectedData mas tiver valor, buscar os dados completos via AJAX
                var termoBusca = valorSelect;
                $.ajax({
                    url: '<?= site_url("processos/buscar_cliente") ?>',
                    method: 'GET',
                    data: { term: termoBusca },
                    dataType: 'json',
                    async: false,
                    success: function(data) {
                        if (data && data.length > 0) {
                            // Procurar o cliente que corresponde ao nome digitado
                            var clienteEncontrado = data.find(function(item) {
                                return (item.text || item.nome) === termoBusca;
                            });
                            if (!clienteEncontrado && data.length === 1) {
                                clienteEncontrado = data[0];
                            }
                            if (clienteEncontrado) {
                                clientesId = clienteEncontrado.id;
                                nome = clienteEncontrado.nome || clienteEncontrado.text || termoBusca;
                                $('#clientes-id-novo-' + tipoPolo).val(clientesId);
                                $('#nome-novo-' + tipoPolo).val(nome);
                                $('#cpf-cnpj-novo-' + tipoPolo).val(clienteEncontrado.documento || '');
                                $('#email-novo-' + tipoPolo).val(clienteEncontrado.email || '');
                                $('#telefone-novo-' + tipoPolo).val(clienteEncontrado.telefone || clienteEncontrado.celular || '');
                            }
                        }
                    }
                });
            }
        }
        
        // Validar se tem nome e cliente ID
        nome = $('#nome-novo-' + tipoPolo).val().trim();
        clientesId = $('#clientes-id-novo-' + tipoPolo).val();
        
        if (!nome || nome === '' || !clientesId) {
            Swal.fire({
                type: 'warning',
                title: 'Atenção',
                text: 'Selecione um cliente da lista para adicionar como parte'
            });
            // Focar no Select2
            if ($('#cliente-select-' + tipoPolo).hasClass('select2-offscreen')) {
                $('#cliente-select-' + tipoPolo).select2('focus');
            } else {
                $('#cliente-select-' + tipoPolo).focus();
            }
            return;
        }

        contadorPartes[tipoPolo]++;
        var index = contadorPartes[tipoPolo];
        var containerId = tipoPolo == 'ativo' ? 'partesAtivo' : 'partesPassivo';
        
        var nome = $('#nome-novo-' + tipoPolo).val();
        var cpf_cnpj = $('#cpf-cnpj-novo-' + tipoPolo).val() || '';
        var email = $('#email-novo-' + tipoPolo).val() || '';
        var telefone = $('#telefone-novo-' + tipoPolo).val() || '';
        var clientes_id = $('#clientes-id-novo-' + tipoPolo).val() || '';

        var detalhesTexto = '';
        if (cpf_cnpj) {
            detalhesTexto = '<div class="parte-detalhes">CPF/CNPJ: ' + cpf_cnpj + '</div>';
        }

        var html = '<div class="parte-item" id="parte-' + tipoPolo + '-' + index + '">' +
            '<div class="parte-info">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][tipo_polo]" value="' + tipoPolo + '">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][nome]" value="' + nome.replace(/"/g, '&quot;') + '">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][cpf_cnpj]" value="' + cpf_cnpj + '">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][email]" value="' + email + '">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][telefone]" value="' + telefone + '">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][clientes_id]" value="' + clientes_id + '">' +
                '<div class="parte-nome">' + nome + '</div>' +
                detalhesTexto +
            '</div>' +
            '<button type="button" class="btn-remove-parte" onclick="removerParte(\'' + tipoPolo + '\', ' + index + ')">' +
                '<i class="bx bx-trash"></i> Remover' +
            '</button>' +
        '</div>';
        
        $('#' + containerId).append(html);
        cancelarNovaParte(tipoPolo);
    }


    function removerParte(tipoPolo, index) {
        Swal.fire({
            title: 'Remover parte?',
            text: 'Deseja realmente remover esta parte do processo?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $('#parte-' + tipoPolo + '-' + index).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }
    
    var advogadosAdicionados = [];
    var contadorAdvogados = 0;
    
    // Advogados existentes do processo
    var advogadosExistentes = <?php 
        if (isset($advogados) && is_array($advogados)) {
            $advogados_array = [];
            foreach ($advogados as $adv) {
                $advogados_array[] = [
                    'usuarios_id' => isset($adv->usuarios_id) ? $adv->usuarios_id : (isset($adv->idUsuarios) ? $adv->idUsuarios : ''),
                    'nome' => isset($adv->nome_usuario) ? $adv->nome_usuario : (isset($adv->nomeAdvogado) ? $adv->nomeAdvogado : ''),
                    'papel' => isset($adv->papel) ? $adv->papel : 'coadjuvante'
                ];
            }
            echo json_encode($advogados_array, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        } else {
            echo '[]';
        }
    ?>;
    
    // Restaurar advogados do POST em caso de erro
    var advogadosRestaurar = <?php echo json_encode(isset($advogados_post) ? $advogados_post : [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
    
    function carregarAdvogadosExistentes() {
        if (Array.isArray(advogadosExistentes) && advogadosExistentes.length > 0 && advogadosAdicionados.length === 0) {
            advogadosExistentes.forEach(function(adv) {
                if (adv && adv.usuarios_id && adv.nome) {
                    adicionarAdvogadoLista(adv.usuarios_id, adv.nome, adv.papel || 'coadjuvante');
                }
            });
        }
    }
    
    function restaurarAdvogadosDoPost() {
        if (Array.isArray(advogadosRestaurar) && advogadosRestaurar.length > 0) {
            $('#advogadosList').empty();
            advogadosAdicionados = [];
            contadorAdvogados = 0;
            
            advogadosRestaurar.forEach(function(adv) {
                if (adv && adv.usuarios_id) {
                    var usuarioNome = '';
                    // Buscar nome do usuário no select
                    $('#advogado-select-novo option').each(function() {
                        if ($(this).val() == adv.usuarios_id) {
                            usuarioNome = $(this).text() || $(this).data('nome') || 'Advogado';
                            return false;
                        }
                    });
                    
                    if (usuarioNome) {
                        adicionarAdvogadoLista(adv.usuarios_id, usuarioNome, adv.papel || 'coadjuvante');
                    }
                }
            });
        }
    }
    
    function mostrarFormNovoAdvogado() {
        $('#formNovoAdvogado').removeClass('hidden');
        $('#advogado-select-novo').focus();
    }
    
    function cancelarNovoAdvogado() {
        $('#formNovoAdvogado').addClass('hidden');
        $('#advogado-select-novo').val('');
        $('#papel-novo').val('principal');
    }
    
    function salvarNovoAdvogado() {
        var usuariosId = $('#advogado-select-novo').val();
        var papel = $('#papel-novo').val();
        
        if (!usuariosId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Selecione um advogado para adicionar.'
            });
            return;
        }
        
        // Verificar se já foi adicionado
        var jaAdicionado = advogadosAdicionados.some(function(adv) {
            return adv.usuarios_id == usuariosId;
        });
        
        if (jaAdicionado) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'Este advogado já foi adicionado.'
            });
            return;
        }
        
        // Se está adicionando como principal, verificar se já existe um
        if (papel === 'principal') {
            var temPrincipal = advogadosAdicionados.some(function(adv) {
                return adv.papel === 'principal';
            });
            
            if (temPrincipal) {
                Swal.fire({
                    icon: 'question',
                    title: 'Alterar Advogado Principal?',
                    text: 'Já existe um advogado principal. Deseja substituir?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, substituir',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Remover principal anterior
                        advogadosAdicionados = advogadosAdicionados.map(function(adv) {
                            if (adv.papel === 'principal') {
                                adv.papel = 'coadjuvante';
                            }
                            return adv;
                        });
                        
                        var usuarioNome = $('#advogado-select-novo option:selected').text();
                        adicionarAdvogadoLista(usuariosId, usuarioNome, papel);
                        cancelarNovoAdvogado();
                        atualizarAdvogadosLista();
                    }
                });
                return;
            }
        }
        
        var usuarioNome = $('#advogado-select-novo option:selected').text();
        adicionarAdvogadoLista(usuariosId, usuarioNome, papel);
        cancelarNovoAdvogado();
    }
    
    function adicionarAdvogadoLista(usuariosId, usuarioNome, papel) {
        contadorAdvogados++;
        var indice = contadorAdvogados;
        
        advogadosAdicionados.push({
            indice: indice,
            usuarios_id: usuariosId,
            nome: usuarioNome,
            papel: papel
        });
        
        atualizarAdvogadosLista();
    }
    
    function removerAdvogado(indice) {
        var advogado = advogadosAdicionados.find(function(adv) {
            return adv.indice == indice;
        });
        
        if (advogado && advogado.papel === 'principal') {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                text: 'É necessário ter pelo menos 1 advogado Principal. Altere o papel antes de remover ou adicione outro advogado Principal.'
            });
            return;
        }
        
        Swal.fire({
            icon: 'question',
            title: 'Remover Advogado?',
            text: 'Deseja realmente remover este advogado?',
            showCancelButton: true,
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                advogadosAdicionados = advogadosAdicionados.filter(function(adv) {
                    return adv.indice != indice;
                });
                atualizarAdvogadosLista();
            }
        });
    }
    
    function atualizarAdvogadosLista() {
        $('#advogadosList').empty();
        
        // Criar inputs hidden para o formulário
        advogadosAdicionados.forEach(function(adv) {
            var papelLabel = {
                'principal': 'Principal',
                'coadjuvante': 'Coadjuvante',
                'estagiario': 'Estagiário'
            }[adv.papel] || adv.papel;
            
            var papelClass = adv.papel;
            var podeRemover = <?php echo isset($pode_editar) && $pode_editar ? 'true' : 'false'; ?>;
            
            var html = '<div class="advogado-item" id="advogado-' + adv.indice + '">' +
                '<div class="advogado-info">' +
                    '<input type="hidden" name="advogados[' + adv.indice + '][usuarios_id]" value="' + $('<div>').text(adv.usuarios_id).html() + '">' +
                    '<input type="hidden" name="advogados[' + adv.indice + '][papel]" value="' + $('<div>').text(adv.papel).html() + '">' +
                    '<div class="advogado-nome">' + $('<div>').text(adv.nome).html() + '</div>' +
                    '<span class="advogado-papel ' + papelClass + '">' + $('<div>').text(papelLabel).html() + '</span>' +
                '</div>';
            
            if (podeRemover) {
                html += '<button type="button" class="btn-remove-advogado" onclick="removerAdvogado(' + adv.indice + ')">' +
                    '<i class="bx bx-trash"></i> Remover' +
                '</button>';
            }
            
            html += '</div>';
            
            $('#advogadosList').append(html);
        });
        
        // Atualizar compatibilidade com formato antigo (manter o primeiro principal como usuarios_id)
        var principal = advogadosAdicionados.find(function(adv) {
            return adv.papel === 'principal';
        });
        
        if (principal) {
            // Remover campo antigo se existir
            $('#usuarios_id').remove();
            // Criar campo hidden com valor do principal (compatibilidade)
            $('<input>').attr({
                type: 'hidden',
                id: 'usuarios_id',
                name: 'usuarios_id',
                value: principal.usuarios_id
            }).appendTo('#formProcesso');
        }
    }
    
    // Validação antes de submeter
    $('#formProcesso').on('submit', function(e) {
        var temPrincipal = advogadosAdicionados.some(function(adv) {
            return adv.papel === 'principal';
        });
        
        if (!temPrincipal || advogadosAdicionados.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validação',
                text: 'É necessário ter pelo menos 1 advogado com papel Principal.'
            });
            return false;
        }
        
        return true;
    });
    
    // Carregar advogados existentes quando a página carregar
    $(document).ready(function() {
        restaurarAdvogadosDoPost();
        carregarAdvogadosExistentes();
    });
</script>
