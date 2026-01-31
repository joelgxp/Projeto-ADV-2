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
                <h5>Cadastro de Compromisso</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formAudiencia" method="post" class="form-horizontal">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="tipo_compromisso" class="control-label">Tipo de Compromisso<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo_compromisso" name="tipo_compromisso" required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="audiencia" <?= set_value('tipo_compromisso') == 'audiencia' ? 'selected' : '' ?>>Audiência</option>
                                    <option value="reuniao" <?= set_value('tipo_compromisso') == 'reuniao' ? 'selected' : '' ?>>Reunião</option>
                                    <option value="diligencia" <?= set_value('tipo_compromisso') == 'diligencia' ? 'selected' : '' ?>>Diligência</option>
                                    <option value="prazo" <?= set_value('tipo_compromisso') == 'prazo' ? 'selected' : '' ?>>Prazo</option>
                                    <option value="evento" <?= set_value('tipo_compromisso') == 'evento' ? 'selected' : '' ?>>Evento</option>
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
                                            $selected = set_value('usuarios_id') == $usuario_id ? 'selected' : '';
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
                                    <option value="publico" <?= set_value('visibilidade', 'publico') == 'publico' ? 'selected' : '' ?>>Público (toda equipe)</option>
                                    <option value="equipe" <?= set_value('visibilidade') == 'equipe' ? 'selected' : '' ?>>Equipe (apenas advogados/assistentes)</option>
                                    <option value="privado" <?= set_value('visibilidade') == 'privado' ? 'selected' : '' ?>>Privado (apenas responsável)</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataHora" class="control-label">Data<span class="required">*</span></label>
                            <div class="controls">
                                <input id="dataHora" type="date" name="dataHora" value="<?php echo set_value('dataHora', date('Y-m-d')); ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="hora" class="control-label">Hora<span class="required">*</span></label>
                            <div class="controls">
                                <input id="hora" type="time" name="hora" value="<?php echo set_value('hora', '09:00'); ?>" required />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="duracao_estimada" class="control-label">Duração Estimada (minutos)</label>
                            <div class="controls">
                                <input id="duracao_estimada" type="number" name="duracao_estimada" min="15" step="15" value="<?php echo set_value('duracao_estimada', '60'); ?>" />
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
                                                echo '<option value="' . $id . '">' . htmlspecialchars($numero) . ' - ' . htmlspecialchars($classe) . '</option>';
                                            }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="tipo" class="control-label">Tipo de Audiência</label>
                                <div class="controls">
                                    <select id="tipo" name="tipo">
                                        <option value="">Selecione...</option>
                                        <option value="Audiência Inicial">Audiência Inicial</option>
                                        <option value="Audiência de Instrução">Audiência de Instrução</option>
                                        <option value="Audiência de Conciliação">Audiência de Conciliação</option>
                                        <option value="Audiência de Julgamento">Audiência de Julgamento</option>
                                        <option value="Audiência de Sentença">Audiência de Sentença</option>
                                        <option value="Audiência de Justificação">Audiência de Justificação</option>
                                        <option value="Outras">Outras</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="tribunal" class="control-label">Tribunal</label>
                                <div class="controls">
                                    <input id="tribunal" type="text" name="tribunal" value="<?php echo set_value('tribunal'); ?>" placeholder="Ex: TRF1, TJSP..." />
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="juiz" class="control-label">Juiz</label>
                                <div class="controls">
                                    <input id="juiz" type="text" name="juiz" value="<?php echo set_value('juiz'); ?>" placeholder="Nome do juiz" />
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
                                                echo '<option value="' . $id . '">' . htmlspecialchars($numero) . ' - ' . htmlspecialchars($classe) . '</option>';
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
                                                echo '<option value="' . $id . '">' . $texto . '</option>';
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
                                    <select id="tipo_diligencia" name="tipo_diligencia">
                                        <option value="">Selecione...</option>
                                        <option value="Pesquisa">Pesquisa</option>
                                        <option value="Contato">Contato</option>
                                        <option value="Documentação">Documentação</option>
                                        <option value="Análise">Análise</option>
                                        <option value="Outras">Outras</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos específicos por tipo - EVENTO -->
                        <div id="campos_evento" class="campos-tipo" style="display: none;">
                            <div class="control-group">
                                <label for="tipo_evento" class="control-label">Tipo de Evento</label>
                                <div class="controls">
                                    <select id="tipo_evento" name="tipo_evento">
                                        <option value="">Selecione...</option>
                                        <option value="Feriado">Feriado</option>
                                        <option value="Recesso">Recesso</option>
                                        <option value="Treinamento">Treinamento</option>
                                        <option value="Reunião Geral">Reunião Geral</option>
                                        <option value="Outros">Outros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="abrangencia" class="control-label">Abrangência</label>
                                <div class="controls">
                                    <select id="abrangencia" name="abrangencia">
                                        <option value="pessoal" <?= set_value('abrangencia', 'pessoal') == 'pessoal' ? 'selected' : '' ?>>Pessoal</option>
                                        <option value="equipe" <?= set_value('abrangencia') == 'equipe' ? 'selected' : '' ?>>Equipe</option>
                                        <option value="escritorio" <?= set_value('abrangencia') == 'escritorio' ? 'selected' : '' ?>>Escritório</option>
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
                                    <textarea id="participantes" name="participantes" rows="3" placeholder="Liste os participantes da reunião, um por linha"><?php echo set_value('participantes'); ?></textarea>
                                    <span class="help-inline">Um participante por linha</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group">
                            <label for="local" class="control-label">Local</label>
                            <div class="controls">
                                <input id="local" type="text" name="local" value="<?php echo set_value('local'); ?>" placeholder="Ex: Fórum, Sala, Escritório..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <select id="status" name="status">
                                    <option value="agendada" <?= set_value('status', 'agendada') == 'agendada' ? 'selected' : '' ?>>Agendada</option>
                                    <option value="realizada" <?= set_value('status') == 'realizada' ? 'selected' : '' ?>>Realizada</option>
                                    <option value="cancelada" <?= set_value('status') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="adiada" <?= set_value('status') == 'adiada' ? 'selected' : '' ?>>Adiada</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="4"><?php echo set_value('observacoes'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span>
                                    <span class="button__text2">Salvar Compromisso</span>
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

