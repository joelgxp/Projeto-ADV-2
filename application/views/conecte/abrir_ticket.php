<div class="widget-box">
    <div class="widget-title">
        <h5><i class='bx bx-message-add'></i> Abrir Novo Ticket</h5>
    </div>
    <div class="widget-content">
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo base_url() ?>index.php/tickets/abrir">
            <div class="form-group">
                <label for="assunto"><strong>Assunto *</strong></label>
                <input type="text" class="form-control" id="assunto" name="assunto" required 
                       placeholder="Ex: Dúvida sobre andamento do processo" 
                       value="<?php echo set_value('assunto'); ?>">
            </div>

            <div class="form-group">
                <label for="processos_id"><strong>Vincular a Processo (Opcional)</strong></label>
                <select class="form-control" id="processos_id" name="processos_id">
                    <option value="">Selecione um processo (opcional)</option>
                    <?php if (isset($processos) && $processos) : ?>
                        <?php foreach ($processos as $processo) : 
                            $this->load->model('processos_model');
                            $numeroFormatado = $this->processos_model->formatarNumeroProcesso($processo->numeroProcesso);
                        ?>
                            <option value="<?php echo $processo->idProcessos; ?>" <?php echo set_select('processos_id', $processo->idProcessos); ?>>
                                <?php echo $numeroFormatado . ' - ' . ($processo->assunto ?? 'Sem assunto'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="form-text text-muted">Se você selecionar um processo, o ticket será enviado ao advogado responsável.</small>
            </div>

            <div class="form-group">
                <label for="prioridade"><strong>Prioridade</strong></label>
                <select class="form-control" id="prioridade" name="prioridade">
                    <option value="normal" <?php echo set_select('prioridade', 'normal', true); ?>>Normal</option>
                    <option value="baixa" <?php echo set_select('prioridade', 'baixa'); ?>>Baixa</option>
                    <option value="alta" <?php echo set_select('prioridade', 'alta'); ?>>Alta</option>
                    <option value="urgente" <?php echo set_select('prioridade', 'urgente'); ?>>Urgente</option>
                </select>
            </div>

            <div class="form-group">
                <label for="mensagem"><strong>Mensagem *</strong></label>
                <textarea class="form-control" id="mensagem" name="mensagem" rows="8" required 
                          placeholder="Descreva sua dúvida ou solicitação..."><?php echo set_value('mensagem'); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">
                    <i class='bx bx-send'></i> Enviar Ticket
                </button>
                <a href="<?php echo base_url() ?>index.php/tickets" class="btn btn-default">
                    <i class='bx bx-arrow-back'></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

