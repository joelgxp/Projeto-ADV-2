<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-balance-scale"></i></span>
        <h5>Adicionar Jurisprudência</h5>
    </div>

    <form method="post" action="<?= site_url('pecas-geradas/adicionar-jurisprudencia') ?>">
        <div class="widget-box">
            <div class="widget-content">
                <div class="control-group">
                    <label class="control-label">Tribunal</label>
                    <div class="controls">
                        <input type="text" name="tribunal" value="<?= htmlspecialchars($this->input->post('tribunal') ?? '') ?>" class="span6" placeholder="Ex: TJMG, STJ">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Número do processo</label>
                    <div class="controls">
                        <input type="text" name="numero_processo" value="<?= htmlspecialchars($this->input->post('numero_processo') ?? '') ?>" class="span6">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Data</label>
                    <div class="controls">
                        <input type="date" name="data" value="<?= htmlspecialchars($this->input->post('data') ?? '') ?>" class="span4">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Área</label>
                    <div class="controls">
                        <select name="area" class="span4">
                            <option value="">-- Selecione --</option>
                            <option value="civel">Cível</option>
                            <option value="trabalhista">Trabalhista</option>
                            <option value="tributario">Tributário</option>
                            <option value="criminal">Criminal</option>
                            <option value="familia">Família</option>
                            <option value="consumidor">Consumidor</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Assunto</label>
                    <div class="controls">
                        <input type="text" name="assunto" value="<?= htmlspecialchars($this->input->post('assunto') ?? '') ?>" class="span6" placeholder="Ex: dano moral, rescisão">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Trecho relevante <span style="color:red">*</span></label>
                    <div class="controls">
                        <textarea name="trecho" required rows="6" class="span12"><?= htmlspecialchars($this->input->post('trecho') ?? '') ?></textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Link</label>
                    <div class="controls">
                        <input type="url" name="link" value="<?= htmlspecialchars($this->input->post('link') ?? '') ?>" class="span6">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="<?= site_url('pecas-geradas/jurisprudencia') ?>" class="btn">Cancelar</a>
        </div>
    </form>
</div>
