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
</style>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-calendar-event"></i>
                </span>
                <h5>Editar Compromisso</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <?php
            // Preparar dados do resultado
            $tipo_compromisso = isset($result->tipo_compromisso) ? $result->tipo_compromisso : set_value('tipo_compromisso', 'audiencia');
            $dataHora = isset($result->dataHora) ? date('Y-m-d', strtotime($result->dataHora)) : set_value('dataHora');
            $hora = isset($result->dataHora) ? date('H:i', strtotime($result->dataHora)) : set_value('hora', '09:00');
            ?>
            <form action="<?php echo current_url(); ?>" id="formAudiencia" method="post" class="form-horizontal">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="tipo_compromisso" class="control-label">Tipo de Compromisso<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo_compromisso" name="tipo_compromisso" required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="audiencia" <?= $tipo_compromisso == 'audiencia' ? 'selected' : '' ?>>Audiência</option>
                                    <option value="reuniao" <?= $tipo_compromisso == 'reuniao' ? 'selected' : '' ?>>Reunião</option>
                                    <option value="diligencia" <?= $tipo_compromisso == 'diligencia' ? 'selected' : '' ?>>Diligência</option>
                                    <option value="prazo" <?= $tipo_compromisso == 'prazo' ? 'selected' : '' ?>>Prazo</option>
                                    <option value="evento" <?= $tipo_compromisso == 'evento' ? 'selected' : '' ?>>Evento</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="usuarios_id" class="control-label">Responsável<span class="required">*</span></label>
                            <div class="controls">
                                <select id="usuarios_id" name="usuarios_id" required>
                                    <option value="">Selecione o responsável...</option>
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
                                            $selected = '';
                                            if (isset($result) && is_object($result) && isset($result->usuarios_id) && $result->usuarios_id == $usuario_id) {
                                                $selected = 'selected';
                                            } elseif (set_value('usuarios_id') == $usuario_id) {
                                                $selected = 'selected';
                                            }
                                            echo '<option value="' . $usuario_id . '" ' . $selected . '>' . htmlspecialchars($usuario_nome) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="visibilidade" class="control-label">Visibilidade</label>
                            <div class="controls">
                                <select id="visibilidade" name="visibilidade">
                                    <?php $visibilidade_atual = isset($result->visibilidade) ? $result->visibilidade : set_value('visibilidade', 'publico'); ?>
                                    <option value="publico" <?= $visibilidade_atual == 'publico' ? 'selected' : '' ?>>Público (toda equipe)</option>
                                    <option value="equipe" <?= $visibilidade_atual == 'equipe' ? 'selected' : '' ?>>Equipe (apenas advogados/assistentes)</option>
                                    <option value="privado" <?= $visibilidade_atual == 'privado' ? 'selected' : '' ?>>Privado (apenas responsável)</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataHora" class="control-label">Data<span class="required">*</span></label>
                            <div class="controls">
                                <input id="dataHora" type="date" name="dataHora" value="<?= $dataHora ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="hora" class="control-label">Hora<span class="required">*</span></label>
                            <div class="controls">
                                <input id="hora" type="time" name="hora" value="<?= $hora ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="duracao_estimada" class="control-label">Duração Estimada (minutos)</label>
                            <div class="controls">
                                <?php $duracao = isset($result->duracao_estimada) ? $result->duracao_estimada : set_value('duracao_estimada', '60'); ?>
                                <input id="duracao_estimada" type="number" name="duracao_estimada" min="15" step="15" value="<?= $duracao ?>" />
                                <span class="help-inline">Padrão: 60 minutos</span>
                            </div>
                        </div>
                        
                        <!-- Campos específicos por tipo - AUDIÊNCIA -->
                        <div id="campos_audiencia" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="processos_id" class="control-label">Processo<span class="required">*</span></label>
                                <div class="controls">
                                    <select id="processos_id" name="processos_id">
                                        <option value="">Selecione um processo...</option>
                                        <?php if (isset($processos) && !empty($processos)) {
                                            foreach ($processos as $p) {
                                                $id = is_object($p) ? $p->idProcessos : (isset($p['idProcessos']) ? $p['idProcessos'] : $p['id']);
                                                $numero = is_object($p) ? ($p->numeroProcesso ?? 'N/A') : ($p['numeroProcesso'] ?? 'N/A');
                                                $classe = is_object($p) ? ($p->classe ?? 'N/A') : ($p['classe'] ?? 'N/A');
                                                $selected = (isset($result->processos_id) && $result->processos_id == $id) || set_value('processos_id') == $id ? 'selected' : '';
                                                echo '<option value="' . $id . '" ' . $selected . '>' . htmlspecialchars($numero) . ' - ' . htmlspecialchars($classe) . '</option>';
                                            }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="tipo" class="control-label">Tipo de Audiência</label>
                                <div class="controls">
                                    <?php $tipo_aud = isset($result->tipo_audiencia) ? $result->tipo_audiencia : (isset($result->tipo) ? $result->tipo : set_value('tipo')); ?>
                                    <select id="tipo" name="tipo">
                                        <option value="">Selecione...</option>
                                        <option value="Audiência Inicial" <?= $tipo_aud == 'Audiência Inicial' ? 'selected' : '' ?>>Audiência Inicial</option>
                                        <option value="Audiência de Instrução" <?= $tipo_aud == 'Audiência de Instrução' ? 'selected' : '' ?>>Audiência de Instrução</option>
                                        <option value="Audiência de Conciliação" <?= $tipo_aud == 'Audiência de Conciliação' ? 'selected' : '' ?>>Audiência de Conciliação</option>
                                        <option value="Audiência de Julgamento" <?= $tipo_aud == 'Audiência de Julgamento' ? 'selected' : '' ?>>Audiência de Julgamento</option>
                                        <option value="Audiência de Sentença" <?= $tipo_aud == 'Audiência de Sentença' ? 'selected' : '' ?>>Audiência de Sentença</option>
                                        <option value="Audiência de Justificação" <?= $tipo_aud == 'Audiência de Justificação' ? 'selected' : '' ?>>Audiência de Justificação</option>
                                        <option value="Outras" <?= $tipo_aud == 'Outras' ? 'selected' : '' ?>>Outras</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="tribunal" class="control-label">Tribunal</label>
                                <div class="controls">
                                    <input id="tribunal" type="text" name="tribunal" value="<?= isset($result->tribunal) ? htmlspecialchars($result->tribunal) : set_value('tribunal') ?>" placeholder="Ex: TRF1, TJSP..." />
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="juiz" class="control-label">Juiz</label>
                                <div class="controls">
                                    <input id="juiz" type="text" name="juiz" value="<?= isset($result->juiz) ? htmlspecialchars($result->juiz) : set_value('juiz') ?>" placeholder="Nome do juiz" />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos específicos por tipo - PRAZO -->
                        <div id="campos_prazo" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="processos_id_prazo" class="control-label">Processo<span class="required">*</span></label>
                                <div class="controls">
                                    <select id="processos_id_prazo" name="processos_id">
                                        <option value="">Selecione um processo...</option>
                                        <?php if (isset($processos) && !empty($processos)) {
                                            foreach ($processos as $p) {
                                                $id = is_object($p) ? $p->idProcessos : (isset($p['idProcessos']) ? $p['idProcessos'] : $p['id']);
                                                $numero = is_object($p) ? ($p->numeroProcesso ?? 'N/A') : ($p['numeroProcesso'] ?? 'N/A');
                                                $classe = is_object($p) ? ($p->classe ?? 'N/A') : ($p['classe'] ?? 'N/A');
                                                $selected = (isset($result->processos_id) && $result->processos_id == $id) || set_value('processos_id') == $id ? 'selected' : '';
                                                echo '<option value="' . $id . '" ' . $selected . '>' . htmlspecialchars($numero) . ' - ' . htmlspecialchars($classe) . '</option>';
                                            }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="prazos_id" class="control-label">Prazo Vinculado<span class="required">*</span></label>
                                <div class="controls">
                                    <select id="prazos_id" name="prazos_id">
                                        <option value="">Selecione um prazo...</option>
                                        <?php if (isset($prazos) && !empty($prazos)) {
                                            foreach ($prazos as $pz) {
                                                $id = is_object($pz) ? $pz->idPrazos : (isset($pz['idPrazos']) ? $pz['idPrazos'] : $pz['id']);
                                                $tipo = is_object($pz) ? ($pz->tipo ?? 'N/A') : ($pz['tipo'] ?? 'N/A');
                                                $descricao = is_object($pz) ? ($pz->descricao ?? '') : ($pz['descricao'] ?? '');
                                                $vencimento = is_object($pz) ? ($pz->dataVencimento ?? '') : ($pz['dataVencimento'] ?? '');
                                                $texto = htmlspecialchars($tipo);
                                                if ($descricao) {
                                                    $texto .= ' - ' . htmlspecialchars(substr($descricao, 0, 50));
                                                }
                                                if ($vencimento) {
                                                    $texto .= ' (Vence: ' . date('d/m/Y', strtotime($vencimento)) . ')';
                                                }
                                                $selected = (isset($result->prazos_id) && $result->prazos_id == $id) || set_value('prazos_id') == $id ? 'selected' : '';
                                                echo '<option value="' . $id . '" ' . $selected . '>' . $texto . '</option>';
                                            }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos específicos por tipo - DILIGÊNCIA -->
                        <div id="campos_diligencia" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="tipo_diligencia" class="control-label">Tipo de Diligência</label>
                                <div class="controls">
                                    <?php $tipo_dil = isset($result->tipo_diligencia) ? $result->tipo_diligencia : set_value('tipo_diligencia'); ?>
                                    <select id="tipo_diligencia" name="tipo_diligencia">
                                        <option value="">Selecione...</option>
                                        <option value="Pesquisa" <?= $tipo_dil == 'Pesquisa' ? 'selected' : '' ?>>Pesquisa</option>
                                        <option value="Contato" <?= $tipo_dil == 'Contato' ? 'selected' : '' ?>>Contato</option>
                                        <option value="Documentação" <?= $tipo_dil == 'Documentação' ? 'selected' : '' ?>>Documentação</option>
                                        <option value="Análise" <?= $tipo_dil == 'Análise' ? 'selected' : '' ?>>Análise</option>
                                        <option value="Outras" <?= $tipo_dil == 'Outras' ? 'selected' : '' ?>>Outras</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos específicos por tipo - EVENTO -->
                        <div id="campos_evento" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="tipo_evento" class="control-label">Tipo de Evento</label>
                                <div class="controls">
                                    <?php $tipo_evt = isset($result->tipo_evento) ? $result->tipo_evento : set_value('tipo_evento'); ?>
                                    <select id="tipo_evento" name="tipo_evento">
                                        <option value="">Selecione...</option>
                                        <option value="Feriado" <?= $tipo_evt == 'Feriado' ? 'selected' : '' ?>>Feriado</option>
                                        <option value="Recesso" <?= $tipo_evt == 'Recesso' ? 'selected' : '' ?>>Recesso</option>
                                        <option value="Treinamento" <?= $tipo_evt == 'Treinamento' ? 'selected' : '' ?>>Treinamento</option>
                                        <option value="Reunião Geral" <?= $tipo_evt == 'Reunião Geral' ? 'selected' : '' ?>>Reunião Geral</option>
                                        <option value="Outros" <?= $tipo_evt == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="abrangencia" class="control-label">Abrangência</label>
                                <div class="controls">
                                    <?php $abrang = isset($result->abrangencia) ? $result->abrangencia : set_value('abrangencia', 'pessoal'); ?>
                                    <select id="abrangencia" name="abrangencia">
                                        <option value="pessoal" <?= $abrang == 'pessoal' ? 'selected' : '' ?>>Pessoal</option>
                                        <option value="equipe" <?= $abrang == 'equipe' ? 'selected' : '' ?>>Equipe</option>
                                        <option value="escritorio" <?= $abrang == 'escritorio' ? 'selected' : '' ?>>Escritório</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <!-- Campos específicos por tipo - REUNIÃO -->
                        <div id="campos_reuniao" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="participantes" class="control-label">Participantes</label>
                                <div class="controls">
                                    <?php 
                                    $participantes_val = isset($result->participantes) ? $result->participantes : set_value('participantes');
                                    // Se for JSON, tentar decodificar
                                    if (!empty($participantes_val) && (substr($participantes_val, 0, 1) == '[' || substr($participantes_val, 0, 1) == '{')) {
                                        $participantes_decoded = json_decode($participantes_val, true);
                                        if (is_array($participantes_decoded)) {
                                            $participantes_val = implode("\n", $participantes_decoded);
                                        }
                                    }
                                    ?>
                                    <textarea id="participantes" name="participantes" rows="3" placeholder="Liste os participantes da reunião, um por linha"><?= htmlspecialchars($participantes_val) ?></textarea>
                                    <span class="help-inline">Um participante por linha</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label for="local" class="control-label">Local</label>
                            <div class="controls">
                                <input id="local" type="text" name="local" value="<?= isset($result->local) ? htmlspecialchars($result->local) : set_value('local') ?>" placeholder="Ex: Fórum, Sala, Escritório..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <?php $status_atual = isset($result->status) ? strtolower($result->status) : set_value('status', 'agendada'); ?>
                                <select id="status" name="status">
                                    <option value="agendada" <?= $status_atual == 'agendada' ? 'selected' : '' ?>>Agendada</option>
                                    <option value="realizada" <?= $status_atual == 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="cancelada" <?= $status_atual == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="adiada" <?= $status_atual == 'adiada' ? 'selected' : '' ?>>Adiada</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="4"><?= isset($result->observacoes) ? htmlspecialchars($result->observacoes) : set_value('observacoes') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span>
                                    <span class="button__text2">Salvar Alterações</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/audiencias" class="button btn btn-mini btn-warning" style="display:inline-flex;">
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

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Mostrar/ocultar campos baseado no tipo de compromisso
        function toggleCamposPorTipo() {
            var tipo = $('#tipo_compromisso').val();
            
            // Ocultar todos os campos específicos
            $('.campos-tipo').hide();
            
            // Mostrar campos do tipo selecionado
            if (tipo) {
                $('#campos_' + tipo).show();
            }
            
            // Atualizar validações dinâmicas
            atualizarValidacoes(tipo);
        }
            
        // Atualizar validações baseado no tipo
        function atualizarValidacoes(tipo) {
            var rules = {
                tipo_compromisso: { required: true },
                usuarios_id: { required: true },
                dataHora: { required: true, date: true },
                hora: { required: true }
            };
            
            var messages = {
                tipo_compromisso: { required: 'Campo obrigatório.' },
                usuarios_id: { required: 'Campo obrigatório.' },
                dataHora: { required: 'Campo obrigatório.', date: 'Data inválida.' },
                hora: { required: 'Campo obrigatório.' }
            };
            
            // Validações condicionais por tipo
            if (tipo === 'audiencia' || tipo === 'prazo') {
                rules['processos_id'] = { required: true };
                messages['processos_id'] = { required: 'Processo é obrigatório para este tipo.' };
            }
            
            if (tipo === 'prazo') {
                rules['prazos_id'] = { required: true };
                messages['prazos_id'] = { required: 'Prazo vinculado é obrigatório.' };
            }
            
            // Atualizar validação do formulário
            var validator = $('#formAudiencia').validate();
            validator.settings.rules = rules;
            validator.settings.messages = messages;
        }
        
        // Event listener para mudança de tipo
        $('#tipo_compromisso').on('change', toggleCamposPorTipo);
        
        // Inicializar campos ao carregar
        toggleCamposPorTipo();
        
        // Sincronizar selects de processos (audiencia e prazo)
        $('#processos_id, #processos_id_prazo').on('change', function() {
            var valor = $(this).val();
            if ($(this).attr('id') === 'processos_id') {
                $('#processos_id_prazo').val(valor);
            } else {
                $('#processos_id').val(valor);
            }
        });
        
        // Validação do formulário
        $('#formAudiencia').validate({
            rules: {
                tipo_compromisso: { required: true },
                usuarios_id: { required: true },
                dataHora: { required: true, date: true },
                hora: { required: true }
            },
            messages: {
                tipo_compromisso: { required: 'Campo obrigatório.' },
                usuarios_id: { required: 'Campo obrigatório.' },
                dataHora: { required: 'Campo obrigatório.', date: 'Data inválida.' },
                hora: { required: 'Campo obrigatório.' }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            },
            submitHandler: function(form) {
                // Sincronizar processos_id antes de enviar
                var tipo = $('#tipo_compromisso').val();
                if (tipo === 'audiencia' && $('#processos_id').val()) {
                    // Já está correto
                } else if (tipo === 'prazo' && $('#processos_id_prazo').val()) {
                    // Criar um campo hidden para processos_id se necessário
                    if ($('#processos_id').length === 0 || !$('#processos_id').val()) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'processos_id',
                            value: $('#processos_id_prazo').val()
                        }).appendTo(form);
                    }
                }
                
                form.submit();
            }
        });
    });
</script>
