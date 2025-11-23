<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
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
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e0e0e0;
    }
    .partes-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    .parte-box {
        flex: 1;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background: #f9f9f9;
    }
    .parte-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn-remove-parte {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    .btn-add-parte {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 3px;
        cursor: pointer;
        margin-top: 10px;
    }
    .numero-cnj-container {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .numero-cnj-container input {
        flex: 1;
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
                    <!-- TOPO: Dados Principais do Processo -->
                    <div class="section-title" style="margin-top: 20px;">Dados Principais do Processo</div>
                    
                    <div class="span12" style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <div class="span6" style="flex: 1; min-width: 300px;">
                            <div class="control-group">
                                <label for="numeroProcesso" class="control-label">Número CNJ <span class="required">*</span></label>
                                <div class="controls">
                                    <div class="numero-cnj-container">
                                        <input id="numeroProcesso" type="text" name="numeroProcesso" 
                                            placeholder="0000123-45.2023.8.13.0139" 
                                            value="<?php echo set_value('numeroProcesso', isset($result->numeroProcessoFormatado) ? $result->numeroProcessoFormatado : ($result->numeroProcesso ?? '')); ?>" required readonly />
                                        <button type="button" id="btnValidarCNJ" class="btn btn-info btn-mini">
                                            <i class='bx bx-check'></i> Validar
                                        </button>
                                    </div>
                                    <div id="validacaoStatus" class="validacao-status"></div>
                                    <small class="help-inline">Número CNJ é fixo e não pode ser alterado.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                        </div>
                        
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                                    <small class="help-inline">Será preenchido automaticamente ao validar CNJ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="span12" style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <div class="span6" style="flex: 1; min-width: 300px;">
                            <div class="control-group">
                                <label for="vara" class="control-label">Vara/Comarca</label>
                                <div class="controls">
                                    <input id="vara" type="text" name="vara" placeholder="Vara" value="<?php echo set_value('vara', $result->vara ?? ''); ?>" />
                                    <input id="comarca" type="text" name="comarca" placeholder="Comarca" value="<?php echo set_value('comarca', $result->comarca ?? ''); ?>" style="margin-top: 5px;" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                        </div>
                    </div>

                    <div class="span12" style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                        </div>
                        
                        <div class="span6" style="flex: 1; min-width: 300px;">
                            <div class="control-group">
                                <label for="dataDistribuicao" class="control-label">Data de Distribuição</label>
                                <div class="controls">
                                    <input id="dataDistribuicao" type="date" name="dataDistribuicao" value="<?php echo set_value('dataDistribuicao', $result->dataDistribuicao ?? ''); ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="span12" style="display: flex; flex-wrap: wrap; gap: 15px;">
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                        </div>
                        
                        <div class="span6" style="flex: 1; min-width: 300px;">
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
                        </div>
                    </div>

                    <!-- SEÇÃO: Partes do Processo -->
                    <div class="section-title" style="margin-top: 30px;">Partes do Processo</div>
                    
                    <div class="partes-container">
                        <!-- Polo Ativo -->
                        <div class="parte-box">
                            <h6 style="margin-top: 0; color: #28a745;">Polo Ativo</h6>
                            <div id="partesAtivo">
                                <?php 
                                if (!isset($contadorPartes)) {
                                    $contadorPartes = ['ativo' => 0, 'passivo' => 0];
                                }
                                if (isset($partes_ativo) && !empty($partes_ativo)) {
                                    foreach ($partes_ativo as $index => $parte) {
                                        $contadorPartes['ativo'] = max($contadorPartes['ativo'], $index + 1);
                                        echo '<div class="parte-item" id="parte-ativo-' . ($index + 1) . '">';
                                        echo '<div style="flex: 1;">';
                                        echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][tipo_polo]" value="ativo">';
                                        echo '<input type="hidden" name="partes_ativo[' . ($index + 1) . '][clientes_id]" value="' . ($parte->clientes_id ?? '') . '">';
                                        echo '<div style="display: flex; gap: 10px; margin-bottom: 5px;">';
                                        echo '<input type="text" name="partes_ativo[' . ($index + 1) . '][nome]" placeholder="Nome" value="' . htmlspecialchars($parte->nome ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '<input type="text" name="partes_ativo[' . ($index + 1) . '][cpf_cnpj]" placeholder="CPF/CNPJ" value="' . htmlspecialchars($parte->cpf_cnpj ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '</div>';
                                        echo '<div style="display: flex; gap: 10px;">';
                                        echo '<input type="text" name="partes_ativo[' . ($index + 1) . '][email]" placeholder="E-mail" value="' . htmlspecialchars($parte->email ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '<input type="text" name="partes_ativo[' . ($index + 1) . '][telefone]" placeholder="Telefone" value="' . htmlspecialchars($parte->telefone ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<button type="button" class="btn-remove-parte" onclick="removerParte(\'ativo\', ' . ($index + 1) . ')"><i class="bx bx-trash"></i></button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" class="btn-add-parte" onclick="adicionarParte('ativo')">
                                <i class='bx bx-plus'></i> Adicionar Parte
                            </button>
                        </div>

                        <!-- Polo Passivo -->
                        <div class="parte-box">
                            <h6 style="margin-top: 0; color: #dc3545;">Polo Passivo</h6>
                            <div id="partesPassivo">
                                <?php 
                                if (!isset($contadorPartes)) {
                                    $contadorPartes = ['ativo' => 0, 'passivo' => 0];
                                }
                                if (isset($partes_passivo) && !empty($partes_passivo)) {
                                    foreach ($partes_passivo as $index => $parte) {
                                        $contadorPartes['passivo'] = max($contadorPartes['passivo'], $index + 1);
                                        echo '<div class="parte-item" id="parte-passivo-' . ($index + 1) . '">';
                                        echo '<div style="flex: 1;">';
                                        echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][tipo_polo]" value="passivo">';
                                        echo '<input type="hidden" name="partes_passivo[' . ($index + 1) . '][clientes_id]" value="' . ($parte->clientes_id ?? '') . '">';
                                        echo '<div style="display: flex; gap: 10px; margin-bottom: 5px;">';
                                        echo '<input type="text" name="partes_passivo[' . ($index + 1) . '][nome]" placeholder="Nome" value="' . htmlspecialchars($parte->nome ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '<input type="text" name="partes_passivo[' . ($index + 1) . '][cpf_cnpj]" placeholder="CPF/CNPJ" value="' . htmlspecialchars($parte->cpf_cnpj ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '</div>';
                                        echo '<div style="display: flex; gap: 10px;">';
                                        echo '<input type="text" name="partes_passivo[' . ($index + 1) . '][email]" placeholder="E-mail" value="' . htmlspecialchars($parte->email ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '<input type="text" name="partes_passivo[' . ($index + 1) . '][telefone]" placeholder="Telefone" value="' . htmlspecialchars($parte->telefone ?? '') . '" class="span6" style="flex: 1;">';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<button type="button" class="btn-remove-parte" onclick="removerParte(\'passivo\', ' . ($index + 1) . ')"><i class="bx bx-trash"></i></button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" class="btn-add-parte" onclick="adicionarParte('passivo')">
                                <i class='bx bx-plus'></i> Adicionar Parte
                            </button>
                        </div>
                    </div>

                    <!-- Advogado Responsável -->
                    <div class="section-title" style="margin-top: 30px;">Advogado Responsável</div>
                    <div class="span12">
                        <div class="control-group">
                            <label for="usuarios_id" class="control-label">Advogado</label>
                            <div class="controls">
                                <select id="usuarios_id" name="usuarios_id">
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
                                            $selected = set_value('usuarios_id', $result->usuarios_id ?? '') == $usuario_id ? 'selected' : '';
                                            echo '<option value="' . $usuario_id . '" ' . $selected . '>' . $usuario_nome . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="section-title" style="margin-top: 30px;">Documentos</div>
                    <div class="span12">
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
                                                    <?= htmlspecialchars($doc->titulo) ?> 
                                                    (<?= isset($doc->tamanho) ? number_format($doc->tamanho / 1024, 2) : '0' ?> KB)
                                                    - <?= isset($doc->dataUpload) ? date('d/m/Y H:i', strtotime($doc->dataUpload)) : '' ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="section-title" style="margin-top: 30px;">Observações</div>
                    <div class="span12">
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações Gerais</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="8" class="span12" placeholder="Informações adicionais sobre o processo..."><?php echo set_value('observacoes', $result->observacoes ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé: Botões -->
                <div class="form-actions">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center; gap: 10px;">
                            <button type="submit" class="button btn btn-success">
                                <span class="button__icon"><i class='bx bx-save'></i></span>
                                <span class="button__text2">Salvar Alterações</span>
                            </button>
                            <a href="<?= base_url() ?>index.php/processos" class="button btn btn-warning">
                                <span class="button__icon"><i class='bx bx-x'></i></span>
                                <span class="button__text2">Cancelar</span>
                            </a>
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

    $(document).ready(function() {
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

            $('#btnValidarCNJ').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Validando...');

            $.ajax({
                url: '<?= site_url("processos/validar_cnj") ?>',
                method: 'POST',
                data: {
                    numero: numero,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    $('#btnValidarCNJ').prop('disabled', false).html('<i class="bx bx-check"></i> Validar');
                    
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
                    $('#btnValidarCNJ').prop('disabled', false).html('<i class="bx bx-check"></i> Validar');
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: 'Erro ao validar número CNJ'
                    });
                }
            });
        });
    });

    function adicionarParte(tipoPolo) {
        contadorPartes[tipoPolo]++;
        var index = contadorPartes[tipoPolo];
        var containerId = tipoPolo == 'ativo' ? 'partesAtivo' : 'partesPassivo';
        
        var html = '<div class="parte-item" id="parte-' + tipoPolo + '-' + index + '">' +
            '<div style="flex: 1;">' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][tipo_polo]" value="' + tipoPolo + '">' +
                '<div style="margin-bottom: 5px;">' +
                    '<input type="text" id="busca-cliente-' + tipoPolo + '-' + index + '" placeholder="Buscar cliente existente..." class="span12" style="margin-bottom: 5px;" autocomplete="off">' +
                    '<div id="resultados-busca-' + tipoPolo + '-' + index + '" style="display: none; position: absolute; background: white; border: 1px solid #ddd; border-radius: 3px; max-height: 200px; overflow-y: auto; z-index: 1000; width: 100%;"></div>' +
                '</div>' +
                '<div style="display: flex; gap: 10px; margin-bottom: 5px;">' +
                    '<input type="text" name="partes_' + tipoPolo + '[' + index + '][nome]" id="nome-parte-' + tipoPolo + '-' + index + '" placeholder="Nome" class="span6" style="flex: 1;">' +
                    '<input type="text" name="partes_' + tipoPolo + '[' + index + '][cpf_cnpj]" id="cpf-cnpj-parte-' + tipoPolo + '-' + index + '" placeholder="CPF/CNPJ" class="span6" style="flex: 1;">' +
                '</div>' +
                '<div style="display: flex; gap: 10px;">' +
                    '<input type="text" name="partes_' + tipoPolo + '[' + index + '][email]" id="email-parte-' + tipoPolo + '-' + index + '" placeholder="E-mail" class="span6" style="flex: 1;">' +
                    '<input type="text" name="partes_' + tipoPolo + '[' + index + '][telefone]" id="telefone-parte-' + tipoPolo + '-' + index + '" placeholder="Telefone" class="span6" style="flex: 1;">' +
                '</div>' +
                '<input type="hidden" name="partes_' + tipoPolo + '[' + index + '][clientes_id]" id="clientes-id-' + tipoPolo + '-' + index + '" value="">' +
            '</div>' +
            '<button type="button" class="btn-remove-parte" onclick="removerParte(\'' + tipoPolo + '\', ' + index + ')">' +
                '<i class="bx bx-trash"></i>' +
            '</button>' +
        '</div>';
        
        $('#' + containerId).append(html);
        
        // Configurar busca de cliente
        configurarBuscaCliente(tipoPolo, index);
    }

    function configurarBuscaCliente(tipoPolo, index) {
        var inputBusca = $('#busca-cliente-' + tipoPolo + '-' + index);
        var resultadosDiv = $('#resultados-busca-' + tipoPolo + '-' + index);
        var timeoutBusca;

        inputBusca.on('input', function() {
            var termo = $(this).val();
            
            clearTimeout(timeoutBusca);
            
            if (termo.length < 2) {
                resultadosDiv.hide().empty();
                return;
            }

            timeoutBusca = setTimeout(function() {
                $.ajax({
                    url: '<?= site_url("processos/buscar_cliente") ?>',
                    method: 'GET',
                    data: { termo: termo },
                    dataType: 'json',
                    success: function(clientes) {
                        resultadosDiv.empty();
                        
                        if (clientes.length > 0) {
                            clientes.forEach(function(cliente) {
                                var item = $('<div style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;" onmouseover="this.style.background=\'#f0f0f0\'" onmouseout="this.style.background=\'white\'">' +
                                    '<strong>' + cliente.nome + '</strong><br>' +
                                    '<small>CPF/CNPJ: ' + (cliente.documento || '-') + ' | Email: ' + (cliente.email || '-') + '</small>' +
                                    '</div>');
                                
                                item.on('click', function() {
                                    $('#nome-parte-' + tipoPolo + '-' + index).val(cliente.nome);
                                    $('#cpf-cnpj-parte-' + tipoPolo + '-' + index).val(cliente.documento);
                                    $('#email-parte-' + tipoPolo + '-' + index).val(cliente.email);
                                    $('#telefone-parte-' + tipoPolo + '-' + index).val(cliente.telefone || cliente.celular);
                                    $('#clientes-id-' + tipoPolo + '-' + index).val(cliente.id);
                                    inputBusca.val('');
                                    resultadosDiv.hide().empty();
                                });
                                
                                resultadosDiv.append(item);
                            });
                            resultadosDiv.show();
                        } else {
                            resultadosDiv.hide();
                        }
                    }
                });
            }, 300);
        });

        // Fechar resultados ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#parte-' + tipoPolo + '-' + index).length) {
                resultadosDiv.hide();
            }
        });
    }

    function removerParte(tipoPolo, index) {
        $('#parte-' + tipoPolo + '-' + index).remove();
    }
</script>
