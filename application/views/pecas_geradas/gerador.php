<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-magic"></i></span>
        <h5>Gerar Petição com IA</h5>
    </div>

    <?php if (!empty($custom_error)): ?>
        <div class="alert alert-danger"><?= $custom_error ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('pecas-geradas/executar-geracao') ?>" class="form-horizontal">
        <div class="widget-box">
            <div class="widget-title"><h5>Contexto</h5></div>
            <div class="widget-content">
                <div class="control-group">
                    <label class="control-label">Processo</label>
                    <div class="controls">
                        <select name="processos_id" id="processos_id" class="span6">
                            <option value="">-- Selecione (opcional) --</option>
                            <?php foreach ($processos ?? [] as $p): ?>
                                <option value="<?= $p->idProcessos ?>" <?= ($processos_id ?? '') == $p->idProcessos ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->numeroProcesso ?? '') ?> - <?= htmlspecialchars($p->nomeCliente ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Prazo vinculado</label>
                    <div class="controls">
                        <input type="number" name="prazos_id" value="<?= $prazos_id ?? '' ?>" placeholder="ID do prazo (opcional)" class="span4">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Contrato vinculado</label>
                    <div class="controls">
                        <input type="number" name="contratos_id" value="<?= $contratos_id ?? '' ?>" placeholder="ID do contrato (opcional)" class="span4">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Cliente (se sem processo)</label>
                    <div class="controls">
                        <select name="clientes_id" class="span6">
                            <option value="">-- Selecione (opcional) --</option>
                            <?php foreach ($clientes ?? [] as $c): ?>
                                <option value="<?= $c->idClientes ?>"><?= htmlspecialchars($c->nomeCliente ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Contexto textual adicional</label>
                    <div class="controls">
                        <textarea name="contexto_manual" rows="4" class="span12" placeholder="Descreva o caso, fatos relevantes..."><?= htmlspecialchars($this->input->post('contexto_manual') ?? '') ?></textarea>
                    </div>
                </div>
                <?php if (!empty($documentos)): ?>
                <div class="control-group">
                    <label class="control-label">Documentos para contexto</label>
                    <div class="controls">
                        <?php foreach ($documentos as $d): ?>
                            <label class="checkbox"><input type="checkbox" name="anexos_ids[]" value="<?= $d->idDocumentos ?>"> <?= htmlspecialchars($d->nome ?? '') ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="control-group">
                    <label class="control-label">Incluir movimentações</label>
                    <div class="controls">
                        <input type="checkbox" name="incluir_movimentacoes" value="1" checked>
                    </div>
                </div>
            </div>
        </div>

        <div class="widget-box">
            <div class="widget-title"><h5>Parâmetros da peça</h5></div>
            <div class="widget-content">
                <div class="control-group">
                    <label class="control-label">Tipo de peça <span style="color:red">*</span></label>
                    <div class="controls">
                        <select name="tipo_peca" required class="span6">
                            <?php foreach ($tipos_peca ?? [] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($this->input->post('tipo_peca') ?? 'peticao_simples') == $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Modelo base</label>
                    <div class="controls">
                        <select name="modelos_pecas_id" class="span6">
                            <option value="">-- Nenhum (gerar do zero) --</option>
                            <?php foreach ($modelos ?? [] as $m): ?>
                                <option value="<?= $m->id ?>"><?= htmlspecialchars($m->nome ?? '') ?> (<?= $m->tipo_peca ?? '' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Tese principal <span style="color:red">*</span></label>
                    <div class="controls">
                        <textarea name="tese_principal" required rows="4" class="span12" placeholder="Descreva a tese jurídica principal..."><?= htmlspecialchars($this->input->post('tese_principal') ?? '') ?></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Pontos a enfatizar</label>
                    <div class="controls">
                        <textarea name="pontos_enfatizar" rows="2" class="span12" placeholder="Pontos que devem ser destacados..."><?= htmlspecialchars($this->input->post('pontos_enfatizar') ?? '') ?></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Tom</label>
                    <div class="controls">
                        <select name="tom" class="span4">
                            <option value="tecnico" <?= ($this->input->post('tom') ?? 'tecnico') == 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                            <option value="didatico" <?= ($this->input->post('tom') ?? '') == 'didatico' ? 'selected' : '' ?>>Didático</option>
                            <option value="conciso" <?= ($this->input->post('tom') ?? '') == 'conciso' ? 'selected' : '' ?>>Conciso</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">Gerar Petição</button>
            <a href="<?= site_url('pecas-geradas/listar') ?>" class="btn">Cancelar</a>
        </div>
    </form>
</div>
