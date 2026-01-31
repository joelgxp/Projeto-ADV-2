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
                    <i class="fas fa-calendar-check"></i>
                </span>
                <h5>Cadastro de Prazo Processual</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formPrazo" method="post" class="form-horizontal">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="processos_id" class="control-label">Processo<span class="required">*</span></label>
                            <div class="controls">
                                <select id="processos_id" name="processos_id" required>
                                    <option value="">Selecione um processo...</option>
                                    <?php if (isset($processos) && !empty($processos)) {
                                        foreach ($processos as $p) {
                                            // Verificar se é objeto ou array
                                            $id = is_object($p) ? $p->idProcessos : (isset($p['idProcessos']) ? $p['idProcessos'] : $p['id']);
                                            $numero = is_object($p) ? ($p->numeroProcesso ?? 'N/A') : ($p['numeroProcesso'] ?? 'N/A');
                                            $classe = is_object($p) ? ($p->classe ?? 'N/A') : ($p['classe'] ?? 'N/A');
                                            echo '<option value="' . $id . '">' . htmlspecialchars($numero) . ' - ' . htmlspecialchars($classe) . '</option>';
                                        }
                                    } else {
                                        echo '<!-- Nenhum processo encontrado -->';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo" class="control-label">Tipo de Prazo<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Resposta">Resposta</option>
                                    <option value="Recurso">Recurso</option>
                                    <option value="Impugnação">Impugnação</option>
                                    <option value="Contestação">Contestação</option>
                                    <option value="Alegação">Alegação</option>
                                    <option value="Recurso Especial">Recurso Especial</option>
                                    <option value="Agravo">Agravo</option>
                                    <option value="Embargos">Embargos</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="descricao" class="control-label">Descrição<span class="required">*</span></label>
                            <div class="controls">
                                <textarea id="descricao" name="descricao" rows="3" required><?php echo set_value('descricao'); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label for="legislacao" class="control-label">Legislação</label>
                            <div class="controls">
                                <select id="legislacao" name="legislacao">
                                    <option value="CPC" <?= set_value('legislacao', 'CPC') == 'CPC' ? 'selected' : '' ?>>CPC - Código de Processo Civil</option>
                                    <option value="CLT" <?= set_value('legislacao') == 'CLT' ? 'selected' : '' ?>>CLT - Consolidação das Leis do Trabalho</option>
                                    <option value="tributario" <?= set_value('legislacao') == 'tributario' ? 'selected' : '' ?>>Tributário</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataPrazo" class="control-label">Data do Prazo (Início)<span class="required">*</span></label>
                            <div class="controls">
                                <input id="dataPrazo" type="date" name="dataPrazo" value="<?php echo set_value('dataPrazo', date('Y-m-d')); ?>" required />
                                <span class="help-block" style="margin-top: 5px; font-size: 11px; color: #666;">
                                    Data da intimação/publicação (prazos contam a partir do dia seguinte)
                                </span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="diasUteis" class="control-label">Dias Úteis</label>
                            <div class="controls" style="display: flex; gap: 5px; align-items: center;">
                                <input id="diasUteis" type="number" name="diasUteis" min="1" value="<?php echo set_value('diasUteis', '15'); ?>" style="width: 80px;" />
                                <button type="button" id="btnCalcularPrazo" class="btn btn-mini btn-info" title="Calcular data de vencimento automaticamente">
                                    <i class="bx bx-calculator"></i> Calcular
                                </button>
                                <span class="help-block" style="margin: 0; font-size: 11px; color: #666; margin-left: 5px;">
                                    O sistema sugere automaticamente baseado no tipo de prazo
                                </span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="dataVencimento" class="control-label">Data de Vencimento<span class="required">*</span></label>
                            <div class="controls">
                                <input id="dataVencimento" type="date" name="dataVencimento" value="<?php echo set_value('dataVencimento'); ?>" required />
                                <span class="help-block" style="margin-top: 5px; font-size: 11px; color: #666;" id="infoVencimento">
                                    Calculada automaticamente considerando feriados e dias úteis
                                </span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <select id="status" name="status">
                                    <option value="pendente" <?= set_value('status', 'pendente') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="proximo_vencer" <?= set_value('status') == 'proximo_vencer' ? 'selected' : '' ?>>Próximo a Vencer</option>
                                    <option value="vencendo_hoje" <?= set_value('status') == 'vencendo_hoje' ? 'selected' : '' ?>>Vencendo Hoje</option>
                                    <option value="vencido" <?= set_value('status') == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                    <option value="cumprido" <?= set_value('status') == 'cumprido' ? 'selected' : '' ?>>Cumprido</option>
                                    <option value="prorrogado" <?= set_value('status') == 'prorrogado' ? 'selected' : '' ?>>Prorrogado</option>
                                    <option value="cancelado" <?= set_value('status') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <span class="help-block" style="margin-top: 5px; font-size: 11px; color: #666;">
                                    O status será atualizado automaticamente conforme a data de vencimento
                                </span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="prioridade" class="control-label">Prioridade</label>
                            <div class="controls">
                                <select id="prioridade" name="prioridade">
                                    <option value="Baixa" <?= set_value('prioridade', 'Normal') == 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                                    <option value="Normal" <?= set_value('prioridade', 'Normal') == 'Normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="Alta" <?= set_value('prioridade') == 'Alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="Urgente" <?= set_value('prioridade') == 'Urgente' ? 'selected' : '' ?>>Urgente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12" style="display:flex;justify-content:center;">
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button type="submit" class="button btn btn-mini btn-success" style="display:inline-flex;">
                                    <span class="button__icon"><i class='bx bx-save'></i></span>
                                    <span class="button__text2">Salvar Prazo</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/prazos" class="button btn btn-mini btn-warning" style="display:inline-flex;">
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
        // Auto-calcular quando tipo de prazo ou data for alterada
        function calcularDataVencimento() {
            var tipoPrazo = $('#tipo').val();
            var dataPrazo = $('#dataPrazo').val();
            var legislacao = $('#legislacao').val();
            var diasUteis = $('#diasUteis').val();
            
            if (!tipoPrazo || !dataPrazo) {
                return;
            }
            
            // Se não informou dias úteis, usar sugestão do sistema
            if (!diasUteis || diasUteis <= 0) {
                // Fazer requisição AJAX para calcular via backend
                $.ajax({
                    url: '<?php echo base_url(); ?>index.php/prazos/calcularDataVencimento',
                    method: 'POST',
                    data: {
                        tipo: tipoPrazo,
                        dataInicio: dataPrazo,
                        legislacao: legislacao,
                        <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#dataVencimento').val(response.dataVencimento);
                            $('#diasUteis').val(response.diasUteis);
                            $('#infoVencimento').text(response.info || 'Data calculada automaticamente');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao calcular prazo:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        $('#infoVencimento').text('Erro ao calcular prazo. Verifique o console.');
                    }
                });
            } else {
                // Calcular com dias úteis informados
                $.ajax({
                    url: '<?php echo base_url(); ?>index.php/prazos/calcularDataVencimento',
                    method: 'POST',
                    data: {
                        tipo: tipoPrazo,
                        dataInicio: dataPrazo,
                        diasUteis: diasUteis,
                        legislacao: legislacao,
                        <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#dataVencimento').val(response.dataVencimento);
                            $('#infoVencimento').text(response.info || 'Data calculada com ' + diasUteis + ' dias úteis');
                        } else {
                            console.error('Erro na resposta:', response);
                            $('#infoVencimento').text('Erro: ' + (response.message || 'Erro desconhecido'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao calcular prazo:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        $('#infoVencimento').text('Erro ao calcular prazo. Verifique o console.');
                    }
                });
            }
        }
        
        // Event listeners
        $('#tipo').on('change', function() {
            calcularDataVencimento();
        });
        
        $('#dataPrazo').on('change', function() {
            calcularDataVencimento();
        });
        
        $('#legislacao').on('change', function() {
            calcularDataVencimento();
        });
        
        $('#btnCalcularPrazo').on('click', function() {
            calcularDataVencimento();
        });
        
        // Calcular automaticamente ao carregar se tipo e data estiverem preenchidos
        if ($('#tipo').val() && $('#dataPrazo').val()) {
            calcularDataVencimento();
        }
        
        $('#formPrazo').validate({
            rules: {
                processos_id: {
                    required: true
                },
                tipo: {
                    required: true
                },
                descricao: {
                    required: true
                },
                dataVencimento: {
                    required: true,
                    date: true
                }
            },
            messages: {
                processos_id: {
                    required: 'Campo obrigatório.'
                },
                tipo: {
                    required: 'Campo obrigatório.'
                },
                descricao: {
                    required: 'Campo obrigatório.'
                },
                dataVencimento: {
                    required: 'Campo obrigatório.',
                    date: 'Data inválida.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>

