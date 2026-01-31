<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-file"></i></span>
        <h5><?= isset($modelo) && $modelo ? 'Editar' : 'Adicionar' ?> Modelo de Peça</h5>
    </div>

    <?php if (!empty($custom_error)): ?>
        <div class="alert alert-danger"><?= $custom_error ?></div>
    <?php endif; ?>

    <form method="post" action="<?= isset($modelo) && $modelo ? site_url('pecas-geradas/editar-modelo/' . $modelo->id) : site_url('pecas-geradas/adicionar-modelo') ?>">
        <div class="widget-box">
            <div class="widget-content">
                <div class="control-group">
                    <label class="control-label">Nome <span style="color:red">*</span></label>
                    <div class="controls">
                        <input type="text" name="nome" required value="<?= htmlspecialchars($modelo->nome ?? $this->input->post('nome') ?? '') ?>" class="span6">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Tipo de peça <span style="color:red">*</span></label>
                    <div class="controls">
                        <select name="tipo_peca" required class="span6">
                            <?php foreach ($tipos_peca ?? [] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($modelo->tipo_peca ?? $this->input->post('tipo_peca') ?? '') == $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Área</label>
                    <div class="controls">
                        <select name="area" class="span4">
                            <option value="">-- Selecione --</option>
                            <?php foreach ($areas ?? [] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($modelo->area ?? $this->input->post('area') ?? '') == $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Corpo do modelo (placeholders: {{NOME_AUTOR}}, {{NOME_REU}}, {{NUMERO_PROCESSO}}, {{VARA}}, {{COMARCA}}, etc.)</label>
                    <div class="controls">
                        <textarea name="corpo" rows="15" class="span12" style="font-family: monospace;"><?= htmlspecialchars($modelo->corpo ?? $this->input->post('corpo') ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="<?= site_url('pecas-geradas/modelos') ?>" class="btn">Cancelar</a>
        </div>
    </form>
</div>
