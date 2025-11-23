<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<style>
    #formConsulta .control-label {
        width: 125px !important;
        text-align: right;
        padding-right: 15px;
    }
    #formConsulta .controls {
        margin-left: 140px;
    }
    .cliente-search-select-block {
        background: #fff;
        padding: 12px 15px;
        border-radius: 6px;
        border: 1px solid #ececec;
    }
    .cliente-combobox {
        position: relative;
        border: 1px solid #dcdcdc;
        border-radius: 6px;
        background: #fff;
        display: flex;
        flex-direction: column;
    }
    .cliente-combobox__input {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4px;
        padding: 2px 6px;
        background: #fafafa;
        border-bottom: 1px solid #ececec;
        min-height: 30px;
    }
    .cliente-combobox__input i {
        color: #7b7b7b;
        font-size: 1.1em;
    }
    .cliente-combobox__input input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.9em;
        height: 22px;
        line-height: 22px;
    }
    .cliente-combobox__input button {
        border: none;
        background: transparent;
        padding: 0;
        color: #555;
        cursor: pointer;
        display: flex;
        align-items: center;
        height: 22px;
    }
    .cliente-combobox__input button:focus {
        outline: none;
    }
    .cliente-combobox__list {
        max-height: 240px;
        overflow-y: auto;
        display: none;
        border-bottom: 1px solid #efefef;
    }
    .cliente-combobox__list.is-open {
        display: block;
    }
    .cliente-combobox__item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f7f7f7;
    }
    .cliente-combobox__item:last-child {
        border-bottom: none;
    }
    .cliente-combobox__item:hover,
    .cliente-combobox__item.is-active {
        background: #f0f5ff;
    }
    .cliente-combobox__empty {
        padding: 10px 12px;
        color: #999;
        font-style: italic;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-search"></i>
        </span>
        <h5>Consulta Processual - API CNJ/DataJud</h5>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <div class="span12" style="padding: 20px;">
                <form id="formConsulta" class="form-horizontal">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="control-group" style="flex: 0 0 30%; min-width: 200px;">
                        <label for="numero_processo" class="control-label">Número de Processo<span class="required">*</span></label>
                        <div class="controls">
                            <input id="numero_processo" type="text" name="numero_processo" 
                                placeholder="0000123-45.2023.8.13.0139" 
                                    class="span12" required maxlength="25" />
                            <small class="help-inline">Digite o número do processo (será formatado automaticamente)</small>
                        </div>
                    </div>

                        <div class="control-group" style="flex: 1; min-width: 250px;">
                        <label for="segmento" class="control-label">Segmento</label>
                        <div class="controls">
                                <select id="segmento" name="segmento" class="span12" disabled>
                                <option value="">Selecione...</option>
                                <option value="1">Supremo Tribunal Federal (STF)</option>
                                <option value="2">Conselho Nacional de Justiça (CNJ)</option>
                                <option value="3">Superior Tribunal de Justiça (STJ)</option>
                                <option value="4">Justiça Federal</option>
                                <option value="5">Justiça do Trabalho</option>
                                <option value="6">Justiça Eleitoral</option>
                                <option value="7">Justiça Militar da União</option>
                                <option value="8">Justiça Estadual</option>
                                <option value="9">Justiça Militar Estadual</option>
                            </select>
                            <small class="help-inline">Preenchido automaticamente</small>
                        </div>
                    </div>

                        <div class="control-group" style="flex: 1; min-width: 250px;">
                        <label for="tribunal" class="control-label">Tribunal</label>
                        <div class="controls">
                                <select id="tribunal" name="tribunal" class="span12" disabled>
                                <option value="">Selecione...</option>
                                <optgroup label="Tribunais Superiores">
                                    <option value="00">STF - Supremo Tribunal Federal</option>
                                    <option value="90">STJ/TST/TSE - Tribunais Superiores</option>
                                </optgroup>
                                <optgroup label="Justiça Federal">
                                    <option value="01">TRF1 - 1ª Região</option>
                                    <option value="02">TRF2 - 2ª Região</option>
                                    <option value="03">TRF3 - 3ª Região</option>
                                    <option value="04">TRF4 - 4ª Região</option>
                                    <option value="05">TRF5 - 5ª Região</option>
                                    <option value="06">TRF6 - 6ª Região</option>
                                </optgroup>
                                <optgroup label="Justiça do Trabalho">
                                    <option value="01">TRT1 - 1ª Região</option>
                                    <option value="02">TRT2 - 2ª Região</option>
                                    <option value="03">TRT3 - 3ª Região</option>
                                    <option value="04">TRT4 - 4ª Região</option>
                                    <option value="05">TRT5 - 5ª Região</option>
                                    <option value="06">TRT6 - 6ª Região</option>
                                    <option value="07">TRT7 - 7ª Região</option>
                                    <option value="08">TRT8 - 8ª Região</option>
                                    <option value="09">TRT9 - 9ª Região</option>
                                    <option value="10">TRT10 - 10ª Região</option>
                                    <option value="11">TRT11 - 11ª Região</option>
                                    <option value="12">TRT12 - 12ª Região</option>
                                    <option value="13">TRT13 - 13ª Região</option>
                                    <option value="14">TRT14 - 14ª Região</option>
                                    <option value="15">TRT15 - 15ª Região</option>
                                    <option value="16">TRT16 - 16ª Região</option>
                                    <option value="17">TRT17 - 17ª Região</option>
                                    <option value="18">TRT18 - 18ª Região</option>
                                    <option value="19">TRT19 - 19ª Região</option>
                                    <option value="20">TRT20 - 20ª Região</option>
                                    <option value="21">TRT21 - 21ª Região</option>
                                    <option value="22">TRT22 - 22ª Região</option>
                                    <option value="23">TRT23 - 23ª Região</option>
                                    <option value="24">TRT24 - 24ª Região</option>
                                </optgroup>
                                <optgroup label="Justiça Estadual">
                                    <option value="01">TJ-AC - Acre</option>
                                    <option value="02">TJ-AL - Alagoas</option>
                                    <option value="03">TJ-AP - Amapá</option>
                                    <option value="04">TJ-AM - Amazonas</option>
                                    <option value="05">TJ-BA - Bahia</option>
                                    <option value="06">TJ-CE - Ceará</option>
                                    <option value="07">TJ-DF - Distrito Federal</option>
                                    <option value="08">TJ-ES - Espírito Santo</option>
                                    <option value="09">TJ-GO - Goiás</option>
                                    <option value="10">TJ-MA - Maranhão</option>
                                    <option value="11">TJ-MT - Mato Grosso</option>
                                    <option value="12">TJ-MS - Mato Grosso do Sul</option>
                                    <option value="13">TJ-MG - Minas Gerais</option>
                                    <option value="14">TJ-PA - Pará</option>
                                    <option value="15">TJ-PB - Paraíba</option>
                                    <option value="16">TJ-PR - Paraná</option>
                                    <option value="17">TJ-PE - Pernambuco</option>
                                    <option value="18">TJ-PI - Piauí</option>
                                    <option value="19">TJ-RJ - Rio de Janeiro</option>
                                    <option value="20">TJ-RN - Rio Grande do Norte</option>
                                    <option value="21">TJ-RS - Rio Grande do Sul</option>
                                    <option value="22">TJ-RO - Rondônia</option>
                                    <option value="23">TJ-RR - Roraima</option>
                                    <option value="24">TJ-SC - Santa Catarina</option>
                                    <option value="25">TJ-SP - São Paulo</option>
                                    <option value="26">TJ-SE - Sergipe</option>
                                    <option value="27">TJ-TO - Tocantins</option>
                                </optgroup>
                            </select>
                            <small class="help-inline">Preenchido automaticamente</small>
                        </div>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" class="button btn btn-mini btn-success">
                            <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                            <span class="button__text2">Consultar Processo</span>
                        </button>
                        <button type="button" id="btnVincularCliente" class="button btn btn-mini btn-info" disabled>
                            <span class="button__icon"><i class='bx bx-user-plus'></i></span>
                            <span class="button__text2">Vincular Cliente</span>
                        </button>
                    </div>
                </form>

                <div id="endpoint-info" style="margin-top: 15px; padding: 10px; background-color: #f0f0f0; border-radius: 4px; display: none;">
                    <small style="color: #666;">
                        <strong>Endpoint utilizado:</strong> <span id="endpoint-url">-</span><br>
                        <strong>Segmento:</strong> <span id="endpoint-segmento">-</span> | 
                        <strong>Tribunal:</strong> <span id="endpoint-tribunal">-</span>
                    </small>
                </div>

                <div id="resultado" style="margin-top: 20px; display: none;">
                    <h4>Resultado da Consulta</h4>
                    <div id="resultadoContent"></div>
                </div>

                <div id="loading" style="display: none; text-align: center; padding: 20px;">
                    <i class="bx bx-loader-alt bx-spin" style="font-size: 2em;"></i>
                    <p>Consultando processo na API CNJ/DataJud...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Vincular Cliente -->
<div id="modalVincularCliente" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalVincularClienteLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 650px;">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header" style="width: 100%; box-sizing: border-box;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="modalVincularClienteLabel">
                    <i class="bx bx-user"></i> Vincular Cliente ao Processo
                </h4>
            </div>
            <div class="modal-body" style="width: 100%; padding: 20px; box-sizing: border-box; background-color: #f5f5f5;">
                <div class="alert alert-info" style="margin-bottom: 20px;">
                    <strong>Processo:</strong> <span id="numero_processo_modal"></span>
                </div>
                
                <div class="control-group cliente-search-select-block">
                    <label class="control-label" for="cliente_combobox_input">Clientes disponíveis</label>
                    <div class="controls cliente-combobox" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                        <div class="cliente-combobox__input">
                            <i class="bx bx-search"></i>
                            <input type="text" id="cliente_combobox_input" placeholder="Digite para buscar cliente" autocomplete="off" aria-autocomplete="list" aria-controls="cliente_combobox_list">
                            <button type="button" id="cliente_combobox_toggle" aria-label="Mostrar lista de clientes">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                    </div>
                        <div id="cliente_combobox_list" class="cliente-combobox__list" role="listbox"></div>
                    </div>
                </div>
                
                <div id="cliente_selecionado_modal" style="display: none; margin: 15px 0; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                    <strong>Cliente selecionado:</strong> <span id="cliente_nome_modal"></span>
                    <button type="button" class="btn btn-mini btn-danger" onclick="removerClienteModal()" style="margin-left: 10px;">Remover</button>
                    <input type="hidden" id="cliente_id_modal" value="">
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div class="control-group">
                    <label class="control-label">Cadastrar Cliente Rápido:</label>
                    <div class="controls">
                        <button type="button" class="btn btn-mini btn-info" onclick="mostrarFormCadastroRapidoModal()">
                            <i class="bx bx-user-plus"></i> Cadastrar Novo Cliente
                        </button>
                    </div>
                </div>
                
                <div id="form_cadastro_rapido_modal" style="display: none; margin-top: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 4px;">
                    <div class="control-group">
                        <label class="control-label">Nome Completo <span class="required">*</span></label>
                        <div class="controls">
                            <input type="text" id="cliente_nome_rapido_modal" class="span12" required placeholder="Digite o nome completo">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">CPF/CNPJ</label>
                        <div class="controls">
                            <input type="text" id="cliente_documento_rapido_modal" class="span12 cpfcnpj" placeholder="000.000.000-00 ou 00.000.000/0000-00">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Email</label>
                        <div class="controls">
                            <input type="email" id="cliente_email_rapido_modal" class="span12" placeholder="email@exemplo.com">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Telefone</label>
                        <div class="controls">
                            <input type="text" id="cliente_telefone_rapido_modal" class="span12 telefone" placeholder="(00) 0000-0000">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="button" class="btn btn-mini" onclick="ocultarFormCadastroRapidoModal()" style="margin-right: 10px;">Cancelar</button>
                            <button type="button" class="btn btn-mini btn-success" onclick="cadastrarClienteRapidoModal()">
                                <i class="bx bx-save"></i> Salvar Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex;justify-content:center;gap:10px">
                <button type="button" class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
                    <span class="button__icon"><i class="bx bx-x"></i></span>
                    <span class="button__text2">Cancelar</span>
                </button>
                <button type="button" class="button btn btn-success" id="btnSalvarProcessoModal" onclick="salvarProcessoModal()">
                    <span class="button__icon"><i class="bx bx-save"></i></span>
                    <span class="button__text2">Salvar Processo</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Verifica se SweetAlert2 está disponível
    function showAlert(title, message, type) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type || 'info',
                title: title,
                text: message
            });
        } else {
            alert(title + ': ' + message);
        }
    }
    
    function showErrorAlert(title, message, footer) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                footer: footer || ''
            });
        } else {
            alert(title + ': ' + message + (footer ? '\n' + footer : ''));
        }
    }
    
    $(document).ready(function() {
        // Corrige aria-hidden do modal ao abrir/fechar
        var $modalVincular = $('#modalVincularCliente');
        if ($modalVincular.length) {
            $modalVincular.on('show.bs.modal show', function() {
                $(this).attr('aria-hidden', 'false');
            });
            $modalVincular.on('hidden.bs.modal hidden', function() {
                $(this).attr('aria-hidden', 'true');
            });
        }

        // Verifica se SweetAlert2 foi carregado
        if (typeof Swal === 'undefined') {
            // Tenta carregar dinamicamente
            var script = document.createElement('script');
            script.src = '<?= base_url() ?>assets/js/sweetalert2.all.min.js';
            script.onerror = function() {
                console.error('Erro ao carregar SweetAlert2');
            };
            document.head.appendChild(script);
        }
        
        // Máscara para número de processo CNJ: NNNNNNN-DD.AAAA.J.TR.OOOO
        $('#numero_processo').on('input', function(e) {
            var input = this;
            var valor = $(input).val().replace(/[^0-9]/g, '');
            var formatado = '';
            
            // Limita a 20 dígitos
            if (valor.length > 20) {
                valor = valor.substring(0, 20);
            }
            
            if (valor.length > 0) {
                // Formato: NNNNNNN-DD.AAAA.J.TR.OOOO
                // Posições: 0123456-78.9012.3.45.6789
                for (var i = 0; i < valor.length; i++) {
                    if (i === 7) {
                        formatado += '-';
                    } else if (i === 9) {
                        formatado += '.';
                    } else if (i === 13) {
                        formatado += '.';
                    } else if (i === 14) {
                        formatado += '.';
                    } else if (i === 16) {
                        formatado += '.';
                    }
                    formatado += valor[i];
                }
                
                $(input).val(formatado);
            }
            
            // Detecta segmento e tribunal quando tiver 20 dígitos
            if (valor.length >= 20) {
                detectarSegmentoTribunal(valor);
            } else {
                // Limpa os selects se o número não estiver completo
                $('#segmento').val('').prop('disabled', true);
                $('#tribunal').val('').prop('disabled', true);
            }
        });
        
        // Permite colar número formatado ou limpo
        $('#numero_processo').on('paste', function(e) {
            var input = this;
            setTimeout(function() {
                var valor = $(input).val().replace(/[^0-9]/g, '');
                if (valor.length >= 20) {
                    detectarSegmentoTribunal(valor);
                }
            }, 10);
        });
        
        // Função para detectar segmento e tribunal
        function detectarSegmentoTribunal(numeroLimpo) {
            if (numeroLimpo.length < 20) {
                return;
            }
            
            // Extrai segmento (posição 14, índice 13 no número limpo de 20 dígitos)
            // Formato: NNNNNNNDDAAAAJTR0000
            // Posições: 01234567890123456789
            // Segmento está na posição 13 (índice 13)
            var segmento = numeroLimpo.substring(13, 14);
            // Tribunal está nas posições 14-15 (índices 14-16)
            var tribunal = numeroLimpo.substring(14, 16);
            
            // Preenche segmento
            if (segmento && $('#segmento option[value="' + segmento + '"]').length > 0) {
                $('#segmento').val(segmento).prop('disabled', false);
            }
            
            // Preenche tribunal
            if (tribunal && $('#tribunal option[value="' + tribunal + '"]').length > 0) {
                $('#tribunal').val(tribunal).prop('disabled', false);
            } else if (tribunal) {
                // Se não encontrar no select, adiciona como opção
                $('#tribunal').append('<option value="' + tribunal + '">Tribunal ' + tribunal + '</option>')
                    .val(tribunal).prop('disabled', false);
            }
        }
        
        // Garante que SweetAlert2 está disponível
        if (typeof Swal === 'undefined') {
        }
        
        $('#formConsulta').on('submit', function(e) {
            e.preventDefault();
            
            var numeroProcesso = $('#numero_processo').val();
            var segmento = $('#segmento').val();
            var tribunal = $('#tribunal').val();
            var size = 1; // Valor padrão: 1 resultado
            
            if (!numeroProcesso) {
                alert('Por favor, informe o número do processo.');
                return;
            }
            
            $('#loading').show();
            $('#resultado').hide();
            $('#endpoint-info').hide();
            
            $.ajax({
                url: '<?= site_url('consulta-processual/consultar') ?>',
                type: 'POST',
                data: {
                    numero_processo: numeroProcesso,
                    segmento: segmento,
                    tribunal: tribunal,
                    size: size
                },
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    // Tenta encontrar endpoint_info em diferentes locais
                    var endpointInfo = null;
                    if (response.success && response.data && response.data.endpoint_info) {
                        endpointInfo = response.data.endpoint_info;
                    } else if (response.endpoint_info) {
                        endpointInfo = response.endpoint_info;
                    } else if (response.data && response.data.endpoint_info) {
                        endpointInfo = response.data.endpoint_info;
                    }
                    
                    // Exibe informações do endpoint se disponível
                    if (endpointInfo) {
                        $('#endpoint-url').text(endpointInfo.url || '-');
                        $('#endpoint-segmento').text(endpointInfo.segmento || '-');
                        $('#endpoint-tribunal').text(endpointInfo.tribunal || '-');
                        $('#endpoint-info').show();
                    } else {
                        $('#endpoint-info').hide();
                    }
                    
                    if (response.success) {
                        exibirResultado(response.data);
                        // Ativar botão de vincular cliente
                        $('#btnVincularCliente').prop('disabled', false);
                    } else {
                        // Usa função helper para mostrar erro
                        showErrorAlert(
                            'Erro na Consulta',
                            response.message || 'Erro ao consultar processo.',
                            response.numero ? 'Número consultado: ' + response.numero : ''
                        );
                        
                        // Desativar botão de vincular cliente
                        $('#btnVincularCliente').prop('disabled', true);
                        window.dadosProcessoAtual = null;
                        
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading').hide();
                    $('#endpoint-info').hide();
                    $('#btnVincularCliente').prop('disabled', true);
                    window.dadosProcessoAtual = null;
                    
                    var errorMsg = 'Erro ao consultar processo. Tente novamente.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMsg = 'Erro de conexão. Verifique sua internet e tente novamente.';
                    } else if (xhr.status >= 500) {
                        errorMsg = 'Erro no servidor. Tente novamente mais tarde.';
                    }
                    
                    // Usa função helper para mostrar erro
                    showErrorAlert(
                        'Erro na Consulta',
                        errorMsg,
                        'Código HTTP: ' + xhr.status
                    );
                    
                    console.error('=== ERRO NA REQUISIÇÃO ===');
                    console.error('Erro:', error);
                    console.error('Status:', status);
                    console.error('Response Text:', xhr.responseText);
                    console.error('=== FIM DO ERRO ===');
                }
            });
        });
        
        function exibirResultado(dados) {
            var html = '<div class="span12">';
            
            // Verificar se é resultado único ou múltiplo
            var processos = [];
            if (dados.processos && Array.isArray(dados.processos)) {
                // Múltiplos resultados
                processos = dados.processos;
                html += '<h4>Resultados Encontrados: ' + (dados.total || processos.length) + '</h4>';
            } else {
                // Resultado único (compatibilidade)
                processos = [dados];
            }
            
            // Função auxiliar para formatar classe
            function formatarClasse(classe) {
                if (!classe) return '-';
                if (typeof classe === 'string') return classe;
                if (typeof classe === 'object') {
                    if (classe.nome) return classe.nome;
                    if (classe.codigo) return 'Código: ' + classe.codigo;
                }
                return '-';
            }
            
            // Função auxiliar para formatar assunto
            function formatarAssunto(assunto) {
                if (!assunto) return '-';
                if (typeof assunto === 'string') return assunto;
                if (Array.isArray(assunto)) {
                    return assunto.map(function(a) {
                        return typeof a === 'object' && a.nome ? a.nome : (a.codigo ? 'Código: ' + a.codigo : String(a));
                    }).join(', ');
                }
                if (typeof assunto === 'object') {
                    if (assunto.nome) return assunto.nome;
                    if (assunto.codigo) return 'Código: ' + assunto.codigo;
                }
                return '-';
            }
            
            // Exibir cada processo
            processos.forEach(function(processo, index) {
                if (processos.length > 1) {
                    html += '<div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9;">';
                    html += '<h5 style="margin-top: 0; color: #333;">Processo ' + (index + 1) + ' de ' + processos.length + '</h5>';
                }
                
                html += '<div class="widget-box" style="margin-bottom: 20px;">';
                html += '<div class="widget-title"><h5>Dados do Processo</h5></div>';
                html += '<div class="widget-content">';
                html += '<table class="table table-bordered table-striped">';
                html += '<tr><td style="width: 200px;"><strong>Número CNJ:</strong></td><td>' + (processo.numero_formatado || processo.numero || '-') + '</td></tr>';
                html += '<tr><td><strong>Classe:</strong></td><td>' + formatarClasse(processo.classe) + '</td></tr>';
                html += '<tr><td><strong>Grau:</strong></td><td>' + (processo.status || '-') + '</td></tr>';
                html += '<tr><td><strong>Tribunal:</strong></td><td>' + (processo.tribunal || '-') + '</td></tr>';
                html += '<tr><td><strong>Segmento:</strong></td><td>' + (processo.segmento || '-') + '</td></tr>';
                html += '<tr><td><strong>Vara/Órgão Julgador:</strong></td><td>' + (processo.vara || '-') + '</td></tr>';
                html += '<tr><td><strong>Comarca:</strong></td><td>' + (processo.comarca || '-') + '</td></tr>';
                html += '<tr><td><strong>Data de Distribuição:</strong></td><td>' + (processo.dataDistribuicao || '-') + '</td></tr>';
                html += '<tr><td><strong>Última Movimentação:</strong></td><td>' + (processo.dataUltimaMovimentacao || '-') + '</td></tr>';
                if (processo.valor) {
                    html += '<tr><td><strong>Valor da Causa:</strong></td><td>R$ ' + parseFloat(processo.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
                }
                html += '</table>';
                html += '</div></div>';
                
                if (processo.partes && processo.partes.length > 0) {
                    html += '<h5>Partes do Processo</h5>';
                    html += '<table class="table table-bordered">';
                    html += '<thead><tr><th>Tipo</th><th>Nome</th><th>Documento</th></tr></thead>';
                    html += '<tbody>';
                    processo.partes.forEach(function(parte) {
                        html += '<tr>';
                        html += '<td>' + (parte.tipo || '-') + '</td>';
                        html += '<td>' + (parte.nome || '-') + '</td>';
                        html += '<td>' + (parte.documento || '-') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                }
                
                if (processo.movimentos && processo.movimentos.length > 0) {
                    html += '<div class="widget-box" style="margin-bottom: 20px;">';
                    html += '<div class="widget-title"><h5>Movimentações (' + processo.movimentos.length + ')</h5></div>';
                    html += '<div class="widget-content">';
                    html += '<div style="max-height: 400px; overflow-y: auto;">';
                    html += '<table class="table table-bordered table-striped table-condensed">';
                    html += '<thead><tr><th style="width: 150px;">Data/Hora</th><th style="width: 200px;">Tipo</th><th>Descrição</th></tr></thead>';
                    html += '<tbody>';
                    // Mostra todas as movimentações (mais recentes primeiro)
                    var movimentosExibir = processo.movimentos.slice().reverse();
                    movimentosExibir.forEach(function(mov) {
                        html += '<tr>';
                        var dataHora = '-';
                        if (mov.dataHora) {
                            try {
                                var dt = new Date(mov.dataHora);
                                dataHora = dt.toLocaleString('pt-BR');
                            } catch(e) {
                                dataHora = mov.dataHora.substring(0, 19);
                            }
                        }
                        html += '<td>' + dataHora + '</td>';
                        html += '<td>' + (mov.nome || mov.codigo || '-') + '</td>';
                        var descricao = '-';
                        if (mov.complementosTabelados && Array.isArray(mov.complementosTabelados)) {
                            descricao = mov.complementosTabelados.map(function(c) {
                                return c.nome || c.descricao || String(c.valor);
                            }).join('; ');
                        }
                        html += '<td>' + descricao + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '</div>';
                    html += '</div></div>';
                }
                
                // Exibir assuntos se disponíveis
                if (processo.dados_completos && processo.dados_completos.assuntos && Array.isArray(processo.dados_completos.assuntos) && processo.dados_completos.assuntos.length > 0) {
                    html += '<div class="widget-box" style="margin-bottom: 20px;">';
                    html += '<div class="widget-title"><h5>Assuntos do Processo</h5></div>';
                    html += '<div class="widget-content">';
                    html += '<ul>';
                    processo.dados_completos.assuntos.forEach(function(assunto) {
                        html += '<li>' + (assunto.nome || 'Código: ' + assunto.codigo) + '</li>';
                    });
                    html += '</ul>';
                    html += '</div></div>';
                }
                
                if (processos.length > 1) {
                    html += '</div>';
                }
            });
            
            html += '</div>';
            
            // Adicionar controles de paginação se houver múltiplos resultados
            if (dados.paginacao && dados.paginacao.has_more) {
                html += '<div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 4px;">';
                html += '<strong>Paginação:</strong> ';
                html += '<button type="button" id="btnProximaPagina" class="btn btn-sm btn-primary" style="margin-left: 10px;">';
                html += '<i class="bx bx-chevron-right"></i> Próxima Página';
                html += '</button>';
                html += '<input type="hidden" id="searchAfterProximo" value=\'' + JSON.stringify(dados.paginacao.search_after_proximo) + '\'>';
                html += '<input type="hidden" id="sizeAtual" value="' + (dados.paginacao.size || 1) + '">';
                html += '<input type="hidden" id="totalResultados" value="' + (dados.paginacao.total || 0) + '">';
                html += '<div style="margin-top: 10px; color: #666;">';
                html += 'Total de resultados: ' + (dados.paginacao.total || 0);
                html += '</div>';
                html += '</div>';
            }
            
            $('#resultadoContent').html(html);
            $('#resultado').show();
            
            // Armazenar dados do primeiro processo para o modal (ou permitir seleção)
            if (processos.length > 0) {
                window.dadosProcessoAtual = processos[0]; // Usa o primeiro processo
                $('#btnVincularCliente').prop('disabled', false);
            }
            
            // Event handler para próxima página
            $('#btnProximaPagina').off('click').on('click', function() {
                var searchAfter = $('#searchAfterProximo').val();
                var size = parseInt($('#sizeAtual').val()) || 100;
                var numeroProcesso = $('#numero_processo').val();
                var segmento = $('#segmento').val();
                var tribunal = $('#tribunal').val();
                
                if (!searchAfter || searchAfter === 'null') {
                    showAlert('Aviso', 'Não há mais páginas disponíveis.', 'info');
                    return;
                }
                
                $('#loading').show();
                $('#resultado').hide();
                
                $.ajax({
                    url: '<?= site_url('consulta-processual/consultar') ?>',
                    type: 'POST',
                    data: {
                        numero_processo: numeroProcesso,
                        segmento: segmento,
                        tribunal: tribunal,
                        size: size,
                        search_after: searchAfter
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#loading').hide();
                        if (response.success) {
                            exibirResultado(response.data);
                        } else {
                            showErrorAlert('Erro', response.message || 'Erro ao carregar próxima página.');
                        }
                    },
                    error: function() {
                        $('#loading').hide();
                        showErrorAlert('Erro', 'Erro ao carregar próxima página.');
                    }
                });
            });
        }
        
        // Handler para botão vincular cliente
        $('#btnVincularCliente').on('click', function() {
            if (!window.dadosProcessoAtual) {
                showAlert('Atenção', 'Nenhum processo consultado.', 'warning');
                return;
            }
            abrirModalVincularCliente();
        });
    });
    
    // Variável global para armazenar dados do processo atual
    window.dadosProcessoAtual = null;
    
    // Funções para gerenciar modal de vincular cliente
    var timeoutBuscaModal = null;
    
    function abrirModalVincularCliente() {
        if (!window.dadosProcessoAtual) {
            showAlert('Atenção', 'Nenhum processo consultado.', 'warning');
            return;
        }
        
        // Limpar campos do modal
        $('#cliente_combobox_input').val('');
        $('#cliente_combobox_list').removeClass('is-open').empty();
        $('#cliente_id_modal').val('');
        $('#cliente_nome_modal').text('');
        $('#cliente_selecionado_modal').hide();
        $('#form_cadastro_rapido_modal').hide();
        $('#cliente_nome_rapido_modal').val('');
        $('#cliente_documento_rapido_modal').val('');
        $('#cliente_email_rapido_modal').val('');
        $('#cliente_telefone_rapido_modal').val('');
        
        // Preencher informações do processo
        var numeroProcesso = window.dadosProcessoAtual.numero_formatado || window.dadosProcessoAtual.numero || '-';
        $('#numero_processo_modal').text(numeroProcesso);
        
        // Abrir modal (usando jQuery se disponível, senão Bootstrap)
        if (typeof $.fn.modal !== 'undefined') {
            // Remove listener anterior se existir
            $('#modalVincularCliente').off('shown.bs.modal shown');
            // Aguarda a modal abrir completamente antes de carregar clientes
            $('#modalVincularCliente').one('shown.bs.modal shown', function() {
                carregarListaClientes();
                inicializarComboboxClienteModal();
            });
            $('#modalVincularCliente').modal('show');
            
            setTimeout(function() {
                if (!$('#cliente_combobox_list').children().length) {
                    carregarListaClientes();
                    inicializarComboboxClienteModal();
                }
            }, 500);
        } else {
            $('#modalVincularCliente').show();
            // Se não usar Bootstrap modal, carrega após um pequeno delay
            setTimeout(function() {
                carregarListaClientes();
                inicializarComboboxClienteModal();
            }, 300);
        }
        
        // Inicializar máscaras após abrir o modal
        setTimeout(function() {
            if (typeof $ !== 'undefined' && $.fn.mask) {
                // Máscara inicial para CPF/CNPJ
                $('#cliente_documento_rapido_modal').mask('000.000.000-00', {reverse: false});
                
                // Máscara dinâmica para CPF/CNPJ (ajusta conforme tamanho)
                $('#cliente_documento_rapido_modal').off('input').on('input', function() {
                    var valor = $(this).val().replace(/\D/g, '');
                    if (valor.length <= 11) {
                        $(this).mask('000.000.000-00', {reverse: false});
                    } else {
                        $(this).mask('00.000.000/0000-00', {reverse: false});
                    }
                });
                
                // Máscara para telefone
                $('#cliente_telefone_rapido_modal').mask('(00) 0000-0000');
            }
        }, 300);
    }
    
    // Variável global para armazenar lista completa de clientes
    window.listaClientesCompleta = [];
    
    function carregarListaClientes() {
        $('#cliente_combobox_list').removeClass('is-open').html('<div class="cliente-combobox__empty">Carregando clientes...</div>');
        
        $.ajax({
            url: '<?= site_url('consulta-processual/buscar-cliente') ?>',
            type: 'GET',
            data: { name: '' }, // Busca vazia retorna todos
            dataType: 'json',
            success: function(clientes) {
                if (Array.isArray(clientes)) {
                    window.listaClientesCompleta = clientes;
                } else {
                    window.listaClientesCompleta = [];
                }
                
                renderClientesCombobox('');
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar clientes:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                window.listaClientesCompleta = [];
                $('#cliente_combobox_list').html('<div class="cliente-combobox__empty">Erro ao carregar clientes.</div>');
            }
        });
    }
    
    function renderClientesCombobox(filtro) {
        if (!Array.isArray(window.listaClientesCompleta)) {
            window.listaClientesCompleta = [];
        }
        
        filtro = (filtro || '').toLowerCase();
        var $list = $('#cliente_combobox_list');
        $list.empty();
        
        if (window.listaClientesCompleta.length === 0) {
            $list.html('<div class="cliente-combobox__empty">Nenhum cliente cadastrado.</div>');
            return;
        }
        
        var clientesFiltrados = window.listaClientesCompleta.filter(function(cliente) {
            if (!filtro) return true;
            var nome = (cliente.nome || '').toLowerCase();
            var documento = (cliente.documento || '').toLowerCase();
            var email = (cliente.email || '').toLowerCase();
            return nome.indexOf(filtro) !== -1 || documento.indexOf(filtro) !== -1 || email.indexOf(filtro) !== -1;
        });
        
        if (clientesFiltrados.length === 0) {
            $list.html('<div class="cliente-combobox__empty">Nenhum cliente encontrado.</div>');
            return;
        }
        
        clientesFiltrados.forEach(function(cliente) {
            var texto = cliente.nome || 'Sem nome';
            if (cliente.documento) {
                texto += ' · ' + cliente.documento;
            }
            if (cliente.email) {
                texto += ' · ' + cliente.email;
            }
            var item = $('<div class="cliente-combobox__item" role="option">').text(texto);
            item.data('cliente', cliente);
            $list.append(item);
        });
    }
    
    function inicializarComboboxClienteModal() {
        var $input = $('#cliente_combobox_input');
        var $list = $('#cliente_combobox_list');
        var $wrapper = $('.cliente-combobox');
        var closeTimeout = null;
        
        var abrirLista = function() {
            if (!$list.hasClass('is-open')) {
                $list.addClass('is-open');
                $wrapper.attr('aria-expanded', 'true');
            }
        };
        
        var fecharLista = function() {
            $list.removeClass('is-open');
            $wrapper.attr('aria-expanded', 'false');
        };
        
        $input.off('focus').on('focus', function() {
            abrirLista();
            renderClientesCombobox($input.val());
        });
        
        $input.off('input').on('input', function() {
            abrirLista();
            renderClientesCombobox($input.val());
        });
        
        $input.off('blur').on('blur', function() {
            closeTimeout = setTimeout(function() {
                fecharLista();
            }, 200);
        });
        
        $('#cliente_combobox_toggle').off('click').on('click', function() {
            if ($list.hasClass('is-open')) {
                fecharLista();
            } else {
                $input.focus();
                abrirLista();
                renderClientesCombobox($input.val());
            }
        });
        
        $list.off('mousedown').on('mousedown', '.cliente-combobox__item', function(e) {
            e.preventDefault();
            clearTimeout(closeTimeout);
            var cliente = $(this).data('cliente');
            if (cliente) {
                        selecionarClienteModal(cliente);
                    }
            fecharLista();
        });
    }
    
    function selecionarClienteModal(cliente) {
        $('#cliente_id_modal').val(cliente.id);
        $('#cliente_nome_modal').text(cliente.nome || 'Cliente sem nome');
        $('#cliente_selecionado_modal').show();
        $('#cliente_combobox_input').val(cliente.nome || '');
        $('#cliente_combobox_list').removeClass('is-open');
        $('.cliente-combobox').attr('aria-expanded', 'false');
    }
    
    function removerClienteModal() {
        $('#cliente_id_modal').val('');
        $('#cliente_nome_modal').text('');
        $('#cliente_selecionado_modal').hide();
        $('#cliente_combobox_input').val('');
    }
    
    function mostrarFormCadastroRapidoModal() {
        $('#form_cadastro_rapido_modal').slideDown();
    }
    
    function ocultarFormCadastroRapidoModal() {
        $('#form_cadastro_rapido_modal').slideUp();
        // Limpa campos
        $('#cliente_nome_rapido_modal').val('');
        $('#cliente_documento_rapido_modal').val('');
        $('#cliente_email_rapido_modal').val('');
        $('#cliente_telefone_rapido_modal').val('');
    }
    
    function cadastrarClienteRapidoModal() {
        var nome = $('#cliente_nome_rapido_modal').val();
        var documento = $('#cliente_documento_rapido_modal').val();
        var email = $('#cliente_email_rapido_modal').val();
        var telefone = $('#cliente_telefone_rapido_modal').val();
        
        if (!nome) {
            showAlert('Atenção', 'Nome é obrigatório.', 'warning');
            return;
        }
        
        $.ajax({
            url: '<?= site_url('consulta-processual/cadastrar-cliente-rapido') ?>',
            type: 'POST',
            data: {
                nome: nome,
                documento: documento,
                email: email,
                telefone: telefone
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Adiciona novo cliente à lista
                    if (response.cliente) {
                        window.listaClientesCompleta.push(response.cliente);
                        renderClientesCombobox($('#cliente_combobox_input').val());
                    }
                    selecionarClienteModal(response.cliente);
                    ocultarFormCadastroRapidoModal();
                    showAlert('Sucesso', response.message, 'success');
                } else {
                    if (response.cliente_existente) {
                        if (confirm(response.message + '\n\nDeseja usar este cliente?')) {
                            selecionarClienteModal(response.cliente_existente);
                            ocultarFormCadastroRapidoModal();
                        }
                    } else {
                        showAlert('Erro', response.message || 'Erro ao cadastrar cliente.', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao cadastrar cliente:', error);
                showAlert('Erro', 'Erro ao cadastrar cliente.', 'error');
            }
        });
    }
    
    function salvarProcessoModal() {
        if (!window.dadosProcessoAtual) {
            showAlert('Erro', 'Nenhum processo consultado.', 'error');
            return;
        }
        
        var clienteId = $('#cliente_id_modal').val();
        
        if (!clienteId) {
            showAlert('Atenção', 'Selecione um cliente para vincular antes de salvar o processo.', 'warning');
            return;
        }
        
        // Extrai número do processo (pode estar em diferentes formatos)
        var numeroProcesso = window.dadosProcessoAtual.numero_limpo || 
                            window.dadosProcessoAtual.numero || 
                            window.dadosProcessoAtual.numero_formatado ||
                            (window.dadosProcessoAtual.numeroCNJ ? window.dadosProcessoAtual.numeroCNJ.replace(/[^0-9]/g, '') : null);
        
        if (!numeroProcesso) {
            showAlert('Erro', 'Número do processo não encontrado nos dados.', 'error');
            return;
        }
        
        // Remove formatação se necessário
        if (typeof numeroProcesso === 'string') {
            numeroProcesso = numeroProcesso.replace(/[^0-9]/g, '');
        }
        
        // Confirmação
        var msgConfirmacao = 'Deseja salvar este processo no sistema?\n\nCliente vinculado: ' + $('#cliente_nome_modal').text();
        
        if (!confirm(msgConfirmacao)) {
            return;
        }
        
        // Desabilita botão para evitar duplo clique
        $('#btnSalvarProcessoModal').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Salvando...');
        
        $.ajax({
            url: '<?= site_url('consulta-processual/salvar-processo') ?>',
            type: 'POST',
            data: {
                numero_processo: numeroProcesso,
                cliente_id: clienteId || '',
                dados_processo: JSON.stringify(window.dadosProcessoAtual)
            },
            dataType: 'json',
            success: function(response) {
                $('#btnSalvarProcessoModal').prop('disabled', false).html('<i class="bx bx-save"></i> Salvar Processo');
                
                if (response.success) {
                    showAlert('Sucesso', response.message, 'success');
                        if (typeof $.fn.modal !== 'undefined') {
                            $('#modalVincularCliente').modal('hide');
                        } else {
                            $('#modalVincularCliente').hide();
                        }
                        
                        if (response.processo_id) {
                            window.location.href = '<?= base_url() ?>index.php/processos/visualizar/' + response.processo_id;
                        }
                } else {
                    if (response.processo_id) {
                        if (confirm(response.message + '\n\nDeseja visualizar o processo existente?')) {
                            // Fechar modal
                            if (typeof $.fn.modal !== 'undefined') {
                                $('#modalVincularCliente').modal('hide');
                            } else {
                                $('#modalVincularCliente').hide();
                            }
                            window.location.href = '<?= base_url() ?>index.php/processos/visualizar/' + response.processo_id;
                        }
                    } else {
                        showAlert('Erro', response.message || 'Erro ao salvar processo.', 'error');
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#btnSalvarProcessoModal').prop('disabled', false).html('<i class="bx bx-save"></i> Salvar Processo');
                console.error('Erro ao salvar processo:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                var errorMsg = 'Erro ao salvar processo.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {
                    // Ignora erro de parse
                }
                showAlert('Erro', errorMsg, 'error');
            }
        });
    }
    
    // Funções antigas (removidas - mantidas apenas para compatibilidade se necessário)
    var timeoutsBusca = {};
    
    function inicializarBuscaCliente(index) {
        $('#busca_cliente_' + index).on('input', function() {
            var termo = $(this).val();
            var resultadoDiv = $('#resultado_busca_cliente_' + index);
            
            // Limpa timeout anterior
            if (timeoutsBusca[index]) {
                clearTimeout(timeoutsBusca[index]);
            }
            
            if (termo.length < 2) {
                resultadoDiv.hide().empty();
                return;
            }
            
            // Aguarda 300ms antes de buscar (debounce)
            timeoutsBusca[index] = setTimeout(function() {
                $.ajax({
                    url: '<?= site_url('consulta-processual/buscar-cliente') ?>',
                    type: 'GET',
                    data: { name: termo },
                    dataType: 'json',
                    success: function(clientes) {
                        resultadoDiv.empty();
                        if (clientes.length > 0) {
                            clientes.forEach(function(cliente) {
                                var item = $('<div>').css({
                                    'padding': '8px',
                                    'cursor': 'pointer',
                                    'border-bottom': '1px solid #eee'
                                }).hover(
                                    function() { $(this).css('background-color', '#f0f0f0'); },
                                    function() { $(this).css('background-color', 'white'); }
                                ).html(
                                    '<strong>' + cliente.nome + '</strong><br>' +
                                    '<small>Doc: ' + (cliente.documento || '-') + ' | Email: ' + (cliente.email || '-') + '</small>'
                                ).click(function() {
                                    selecionarCliente(index, cliente);
                                });
                                resultadoDiv.append(item);
                            });
                            resultadoDiv.show();
                        } else {
                            resultadoDiv.hide();
                        }
                    },
                    error: function() {
                        resultadoDiv.hide();
                    }
                });
            }, 300);
        });
        
        // Fecha resultado ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#busca_cliente_' + index).length && 
                !$(e.target).closest('#resultado_busca_cliente_' + index).length) {
                $('#resultado_busca_cliente_' + index).hide();
            }
        });
    }
    
    function selecionarCliente(index, cliente) {
        $('#cliente_id_' + index).val(cliente.id);
        $('#cliente_nome_' + index).text(cliente.nome);
        $('#cliente_selecionado_' + index).show();
        $('#busca_cliente_' + index).val('');
        $('#resultado_busca_cliente_' + index).hide().empty();
    }
    
    function removerCliente(index) {
        $('#cliente_id_' + index).val('');
        $('#cliente_nome_' + index).text('');
        $('#cliente_selecionado_' + index).hide();
    }
    
    function mostrarFormCadastroRapido(index) {
        $('#form_cadastro_rapido_' + index).slideDown();
    }
    
    function ocultarFormCadastroRapido(index) {
        $('#form_cadastro_rapido_' + index).slideUp();
        // Limpa campos
        $('#cliente_nome_rapido_' + index).val('');
        $('#cliente_documento_rapido_' + index).val('');
        $('#cliente_email_rapido_' + index).val('');
        $('#cliente_telefone_rapido_' + index).val('');
    }
    
    function cadastrarClienteRapido(index) {
        var nome = $('#cliente_nome_rapido_' + index).val();
        var documento = $('#cliente_documento_rapido_' + index).val();
        var email = $('#cliente_email_rapido_' + index).val();
        var telefone = $('#cliente_telefone_rapido_' + index).val();
        
        if (!nome) {
            showAlert('Atenção', 'Nome é obrigatório.', 'warning');
            return;
        }
        
        $.ajax({
            url: '<?= site_url('consulta-processual/cadastrar-cliente-rapido') ?>',
            type: 'POST',
            data: {
                nome: nome,
                documento: documento,
                email: email,
                telefone: telefone
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    selecionarCliente(index, response.cliente);
                    ocultarFormCadastroRapido(index);
                    showAlert('Sucesso', response.message, 'success');
                } else {
                    if (response.cliente_existente) {
                        if (confirm(response.message + '\n\nDeseja usar este cliente?')) {
                            selecionarCliente(index, response.cliente_existente);
                            ocultarFormCadastroRapido(index);
                        }
                    } else {
                        showErrorAlert('Erro', response.message);
                    }
                }
            },
            error: function() {
                showErrorAlert('Erro', 'Erro ao cadastrar cliente.');
            }
        });
    }
    
    function salvarProcesso(index, dadosProcesso) {
        var clienteId = $('#cliente_id_' + index).val();
        var numeroProcesso = dadosProcesso.numero_limpo || dadosProcesso.numero;
        
        if (!numeroProcesso) {
            showErrorAlert('Erro', 'Número do processo não encontrado.');
            return;
        }
        
        // Confirmação
        if (!confirm('Deseja salvar este processo no sistema?' + (clienteId ? '\n\nCliente vinculado: ' + $('#cliente_nome_' + index).text() : '\n\nNenhum cliente vinculado.'))) {
            return;
        }
        
        $.ajax({
            url: '<?= site_url('consulta-processual/salvar-processo') ?>',
            type: 'POST',
            data: {
                numero_processo: numeroProcesso,
                cliente_id: clienteId || '',
                dados_processo: JSON.stringify(dadosProcesso)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('Sucesso', response.message, 'success');
                        if (response.processo_id) {
                            window.location.href = '<?= base_url() ?>index.php/processos/visualizar/' + response.processo_id;
                        }
                } else {
                    if (response.processo_id) {
                        if (confirm(response.message + '\n\nDeseja visualizar o processo existente?')) {
                            window.location.href = '<?= base_url() ?>index.php/processos/visualizar/' + response.processo_id;
                        }
                    } else {
                        showErrorAlert('Erro', response.message);
                    }
                }
            },
            error: function() {
                showErrorAlert('Erro', 'Erro ao salvar processo.');
            }
        });
    }
</script>

