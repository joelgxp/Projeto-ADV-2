/**
 * Biblioteca JavaScript para formulário de clientes
 * Gerencia máscaras, validações, busca de CEP e CNPJ
 */
var ClienteForm = {
    config: {
        isEdit: false,
        estadoValue: '',
        baseUrl: '',
        tipoCliente: 'fisica'
    },

    /**
     * Configura exibição condicional conforme tipo do cliente
     */
    setupTipoClienteToggle: function() {
        var self = this;
        var $radios = $('input[name="tipo_cliente"]');
        var $select = $('select[name="tipo_cliente"]').not('#tipo_cliente_widget');
        var $widgetSelect = $('#tipo_cliente_widget');
        var $hidden = $('#tipo_cliente_hidden');

        var updateValue = function(tipo) {
            if ($select.length) {
                $select.val(tipo);
            }
            if ($widgetSelect.length && $widgetSelect.val() !== tipo) {
                $widgetSelect.val(tipo);
            }
            if ($hidden.length) {
                $hidden.val(tipo);
            }
        };

        if ($radios.length) {
        $radios.on('change', function() {
                var valor = $(this).val();
                updateValue(valor);
                self.toggleCampos(valor);
        });

            var initialTipoRadio = this.config.tipoCliente || $radios.filter(':checked').val() || 'fisica';
            $radios.filter('[value="' + initialTipoRadio + '"]').prop('checked', true);
            updateValue(initialTipoRadio);
            this.toggleCampos(initialTipoRadio);
            return;
        }

        var handleSelectChange = function(valor) {
            updateValue(valor);
            self.toggleCampos(valor);
        };

        if ($widgetSelect.length) {
            $widgetSelect.on('change', function() {
                handleSelectChange($(this).val());
            });

            var initialTipoWidget = this.config.tipoCliente || ($hidden.length ? $hidden.val() : null) || $widgetSelect.val() || 'fisica';
            $widgetSelect.val(initialTipoWidget);
            handleSelectChange(initialTipoWidget);

            if ($select.length) {
                $select.val(initialTipoWidget);
                $select.on('change', function() {
                    handleSelectChange($(this).val());
                });
            }

            return;
        }

        if ($select.length) {
            $select.on('change', function() {
                handleSelectChange($(this).val());
            });

            var initialTipoSelect = this.config.tipoCliente || $select.val() || 'fisica';
            $select.val(initialTipoSelect);
            handleSelectChange(initialTipoSelect);
        }
    },

    /**
     * Retorna o tipo de cliente selecionado
     */
    getTipoCliente: function() {
        var $radioChecked = $('input[name="tipo_cliente"]:checked');
        if ($radioChecked.length) {
            return $radioChecked.val();
        }
        var $hidden = $('#tipo_cliente_hidden');
        if ($hidden.length && $hidden.val()) {
            return $hidden.val();
        }
        var $widgetSelect = $('#tipo_cliente_widget');
        if ($widgetSelect.length) {
            return $widgetSelect.val() || this.config.tipoCliente || 'fisica';
        }
        var $select = $('select[name="tipo_cliente"]');
        if ($select.length) {
            return $select.val() || this.config.tipoCliente || 'fisica';
        }
        return this.config.tipoCliente || 'fisica';
    },

    /**
     * Alterna exibição de campos PF / PJ / Advogado
     */
    toggleCampos: function(tipo) {
        tipo = tipo || this.getTipoCliente();

        $('.campos-pf').toggleClass('is-hidden', tipo === 'juridica');
        $('.campos-pj').toggleClass('is-hidden', tipo !== 'juridica');

        // Habilitar/desabilitar campos de acordo com o tipo
        // Campos PF: habilitados apenas quando tipo é 'fisica'
        $('#documento_pf').prop('disabled', tipo === 'juridica');
        
        // Campos PJ: habilitados apenas quando tipo é 'juridica'
        $('#documento_pj').prop('disabled', tipo !== 'juridica');
        $('#razao_social').prop('disabled', tipo !== 'juridica');

        this.config.tipoCliente = tipo;
        this.updateDocumentoHidden();
    },

    /**
     * Mantém o campo oculto documento sincronizado
     */
    setupDocumentoSync: function() {
        var self = this;
        $('#documento_pf, #documento_pj').on('input blur', function() {
            self.updateDocumentoHidden();
        });
        this.updateDocumentoHidden();
    },

    updateDocumentoHidden: function() {
        var tipo = this.getTipoCliente();
        var valor = tipo === 'juridica' ? $('#documento_pj').val() : $('#documento_pf').val();

        $('#documento').val(valor || '');
    },

    /**
     * Inicializa o formulário
     * @param {Object} options - Opções de configuração
     */
    init: function(options) {
        this.config = $.extend({}, this.config, options || {});
        
        this.setupTipoClienteToggle();
        this.setupTabs();
        this.setupPasswordToggle();
        this.loadEstados();
        this.setupMasks();
        this.setupDocumentoSync();
        this.setupCEP();
        this.setupCNPJ();
        this.setupValidation();
        this.setupDocumentoValidation();
        
        // Garantir que os campos estejam habilitados corretamente após inicialização
        // Especialmente importante no modo de edição
        var self = this;
        setTimeout(function() {
            var tipoAtual = self.getTipoCliente();
            self.toggleCampos(tipoAtual);
        }, 200);
    },

    /**
     * Inicializa tabs do formulário
     */
    setupTabs: function() {
        var self = this;
        var $navButtons = $('.cliente-tabs__nav button');
        var $tabs = $('.cliente-tab');

        if (! $navButtons.length || ! $tabs.length) {
            return;
        }

        this.currentTab = $navButtons.filter('.active').data('tab-target') || $navButtons.first().data('tab-target');

        $navButtons.on('click', function() {
            var target = $(this).data('tab-target');
            self.showTab(target, true);
        });

        this.showTab(this.currentTab || $navButtons.first().data('tab-target'), false);
    },

    showTab: function(tabId, focusFirstField) {
        if (!tabId) {
            return;
        }

        this.currentTab = tabId;

        var $navButtons = $('.cliente-tabs__nav button');
        var $panes = $('.cliente-tab');

        $navButtons
            .removeClass('active')
            .attr('aria-selected', 'false')
            .filter('[data-tab-target="' + tabId + '"]')
            .addClass('active')
            .attr('aria-selected', 'true');

        $panes
            .removeClass('active')
            .attr('aria-hidden', 'true')
            .filter('[data-tab-panel="' + tabId + '"]')
            .addClass('active')
            .attr('aria-hidden', 'false');

        if (focusFirstField) {
            var $targetPane = $panes.filter('[data-tab-panel="' + tabId + '"]');
            var $focusable = $targetPane.find('input:not([type="hidden"]), select, textarea').filter(':enabled:visible');
            if ($focusable.length) {
                $focusable.first().focus();
            }
        }
    },

    ensureTabVisible: function(element) {
        var $el = $(element);
        var $pane = $el.closest('.cliente-tab');

        if ($pane.length) {
            var tabId = $pane.data('tab-panel');
            if (tabId && (this.currentTab !== tabId)) {
                this.showTab(tabId, false);
            }
        }
    },

    /**
     * Configura toggle de visibilidade da senha
     */
    setupPasswordToggle: function() {
        var self = this;
        var $input = $('#senha');
        var $icon = $('#imgSenha');

        if (! $input.length || ! $icon.length) {
            return;
        }

        var togglePassword = function() {
            var isText = $input.attr('type') === 'text';
            $input.attr('type', isText ? 'password' : 'text');
            var iconName = isText ? 'eye.svg' : 'eye-off.svg';
            $icon.attr('src', self.config.baseUrl + 'assets/img/' + iconName);
        };

        $icon.on('click', togglePassword);
        $icon.on('keypress', function(e) {
            if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                togglePassword();
            }
        });
    },

    /**
     * Carrega lista de estados do JSON
     */
    loadEstados: function() {
        var self = this;
        $.getJSON(this.config.baseUrl + 'assets/json/estados.json', function(data) {
            var $estado = $('#estado');
            for (var i in data.estados) {
                $estado.append(new Option(data.estados[i].nome, data.estados[i].sigla));
            }
            var selected = $estado.data('selected') || self.config.estadoValue;
            if (selected) {
                $estado.val(selected);
            }
        });
    },

    /**
     * Configura máscaras de entrada
     */
    setupMasks: function() {
        // Máscara para celular
        $("#celular").mask("(00) 00000-0000");
        
        // Máscara para CEP
        $("#cep").mask("00000-000");
        
        // Máscara dinâmica para telefone
        var telefoneN = function(val) {
            return val.replace(/\D/g, '').length > 10 ? '(00) 00000-0000' : '(00) 0000-00009';
        };
        var telefoneOptions = {
            onKeyPress: function(val, e, field, options) {
                field.mask(telefoneN.apply({}, arguments), options);
            }
        };
        $('#telefone').mask(telefoneN, telefoneOptions);
        
        // Máscaras para documentos
        this.aplicarMascaras();
    },

    /**
     * Aplica máscaras dinâmicas para CPF e CNPJ
     */
    aplicarMascaras: function() {
        var self = this;
        $('#documento_pf').on('input', function() {
            var value = $(this).val().replace(/\D/g, '').slice(0, 11);
            var formatted = '';

            for (var i = 0; i < value.length; i++) {
                if (i === 3 || i === 6) {
                    formatted += '.';
                }
                if (i === 9) {
                    formatted += '-';
                }
                formatted += value[i];
            }

            $(this).val(formatted);
            self.updateDocumentoHidden();
        });

        $('#documento_pj').on('input', function() {
            var v = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 14);
            var result = '';

            for (var i = 0; i < v.length; i++) {
                if (i === 2 || i === 5) {
                    result += '.';
                }
                if (i === 8) {
                    result += '/';
                }
                if (i === 12) {
                    result += '-';
                }
                result += v[i];
            }

            $(this).val(result);
            self.updateDocumentoHidden();
        });
    },

    /**
     * Configura busca de CEP via ViaCEP
     */
    setupCEP: function() {
        var self = this;
        // Consulta via CEP desabilitada
    },

    /**
     * Busca endereço por CEP
     * @param {string} cep - CEP a buscar
     */
    buscarCEP: function(cep) {
        // consulta via CEP desativada
    },

    /**
     * Limpa campos do formulário de CEP
     */
    limpaFormularioCEP: function() {
        $("#rua").val("");
        $("#bairro").val("");
        $("#cidade").val("");
        $("#estado").val("");
    },

    /**
     * Configura busca de CNPJ via ReceitaWS
     */
    setupCNPJ: function() {
        var self = this;
        $('#buscar_info_cnpj').on('click', function() {
            var ndocumento = $('#documento_pj').val().trim();
            
            if (self.validarDocumento(ndocumento)) {
                // Se for CNPJ alfanumérico, exibe alerta
                var docClean = ndocumento.replace(/[^A-Z0-9]/g, '');
                if (/^[A-Z0-9]{14}$/.test(docClean) && /[A-Z]/.test(docClean)) {
                    Swal.fire({
                        icon: "info",
                        title: "Atenção",
                        text: "A consulta automática ainda não está disponível para o novo formato de CNPJ alfanumérico. Preencha os dados manualmente."
                    });
                    return;
                }

                self.buscarCNPJ(ndocumento.replace(/\D/g, ''));
                self.updateDocumentoHidden();
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Atenção",
                    text: "CNPJ inválido!"
                });
            }
        });
    },

    /**
     * Busca informações de CNPJ via ReceitaWS
     * @param {string} cnpj - CNPJ a buscar (apenas números)
     */
    buscarCNPJ: function(cnpj) {
        var self = this;
        
        // Mostra loading
        this.showLoading(['#nomeCliente', '#email', '#cep', '#rua', '#numero', '#bairro', '#cidade', '#estado', '#complemento', '#telefone']);
        
        // Preenche com "..." enquanto consulta
        $("#nomeCliente").val("...");
        $("#email").val("...");
        $("#cep").val("...");
        $("#rua").val("...");
        $("#numero").val("...");
        $("#bairro").val("...");
        $("#cidade").val("...");
        $("#estado").val("...");
        $("#complemento").val("...");
        $("#telefone").val("...");

        $.ajax({
            url: "https://www.receitaws.com.br/v1/cnpj/" + cnpj,
            dataType: 'jsonp',
            crossDomain: true,
            contentType: "text/javascript",
            timeout: 5000,
            success: function(dados) {
                self.hideLoading();
                
                if (dados.status == "OK") {
                    // Atualiza campos com valores da consulta
                    if ($("#nomeCliente").length) {
                        $("#nomeCliente").val(self.capitalLetter(dados.nome || ""));
                    }
                    if ($("#nomeEmitente").length) {
                        $("#nomeEmitente").val(self.capitalLetter(dados.nome || ""));
                    }
                    
                    $("#cep").val((dados.cep || "").replace(/\./g, ''));
                    $("#email").val((dados.email || "").toLowerCase());
                    $("#telefone").val((dados.telefone || "").split("/")[0].replace(/\ /g, ''));
                    $("#rua").val(self.capitalLetter(dados.logradouro || ""));
                    $("#numero").val(dados.numero || "");
                    $("#bairro").val(self.capitalLetter(dados.bairro || ""));
                    $("#cidade").val(self.capitalLetter(dados.municipio || ""));
                    $("#estado").val(dados.uf || "");
                    
                    if (dados.uf) {
                        $("#estado option[value=" + dados.uf + "]").prop("selected", true);
                    }
                    
                    if (dados.complemento && dados.complemento !== "") {
                        $("#complemento").val(self.capitalLetter(dados.complemento));
                    } else {
                        $("#complemento").val("");
                    }

                    if ($("#documento_pj").length) {
                        $("#documento_pj").val(cnpj);
                        $("#documento_pj").trigger('input');
                    }

                    // Foca no campo nome
                    if ($("#nomeCliente").length) {
                        $("#nomeCliente").focus();
                    } else if ($("#nomeEmitente").length) {
                        $("#nomeEmitente").focus();
                    }

                    self.updateDocumentoHidden();
                    
                    self.showFeedback('Informações do CNPJ carregadas com sucesso!', 'success');
                    setTimeout(function() {
                        self.hideFeedback();
                    }, 3000);
                } else {
                    self.limpaFormularioCNPJ();
                    self.showFeedback('CNPJ não encontrado.', 'error');
                    Swal.fire({
                        icon: "warning",
                        title: "Atenção",
                        text: "CNPJ não encontrado."
                    });
                }
            },
            error: function() {
                self.hideLoading();
                self.limpaFormularioCNPJ();
                self.showFeedback('Erro ao buscar CNPJ. Tente novamente.', 'error');
                Swal.fire({
                    icon: "warning",
                    title: "Atenção",
                    text: "CNPJ não encontrado ou erro na consulta."
                });
            }
        });
    },

    /**
     * Limpa campos após busca de CNPJ
     */
    limpaFormularioCNPJ: function() {
        $("#nomeCliente").val("");
        $("#nomeEmitente").val("");
        $("#cep").val("");
        $("#email").val("");
        $("#numero").val("");
        $("#complemento").val("");
        $("#telefone").val("");
    },

    /**
     * Valida documento (CPF/CNPJ)
     * Sincronizado com verific_cpf_cnpj do backend
     * @param {string} documento - Documento a validar
     * @returns {boolean} - true se válido
     */
    validarDocumento: function(documento) {
        if (!documento || documento.trim() === '') {
            return false;
        }

        // Remove tudo que não for letra ou número
        var docLimpo = documento.replace(/[^a-zA-Z0-9]/g, '');

        // CPF: 11 dígitos numéricos
        if (docLimpo.length === 11 && /^\d{11}$/.test(docLimpo)) {
            return this.validarCPF(docLimpo);
        }

        // CNPJ tradicional: 14 dígitos numéricos
        if (docLimpo.length === 14 && /^\d{14}$/.test(docLimpo)) {
            return this.validarCNPJ(documento);
        }

        // Novo CNPJ alfanumérico: 14 caracteres, letras e números
        if (docLimpo.length === 14 && /^[A-Z0-9]{14}$/i.test(docLimpo)) {
            // Validação básica de formato para CNPJ alfanumérico
            return true;
        }

        return false;
    },

    /**
     * Valida CPF
     * @param {string} cpf - CPF a validar (apenas números)
     * @returns {boolean} - true se válido
     */
    validarCPF: function(cpf) {
        // Verifica se foi informado todos os dígitos corretamente
        if (cpf.length !== 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de dígitos repetidos
        if (/^(\d)\1{10}$/.test(cpf)) {
            return false;
        }

        // Faz o cálculo para validar o CPF
        var soma = 0;
        var resto;

        for (var i = 1; i <= 9; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) {
            return false;
        }

        soma = 0;
        for (var i = 1; i <= 10; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) {
            return false;
        }

        return true;
    },

    /**
     * Valida CNPJ (numérico ou alfanumérico)
     * @param {string} cnpj - CNPJ a validar
     * @returns {boolean} - true se válido
     */
    validarCNPJ: function(cnpj) {
        cnpj = cnpj.replace(/[^\w]/g, '').toUpperCase();

        // CNPJ numérico tradicional
        if (/^\d{14}$/.test(cnpj)) {
            if (/^(\d)\1{13}$/.test(cnpj)) {
                return false;
            }

            var tamanho = cnpj.length - 2;
            var numeros = cnpj.substring(0, tamanho);
            var digitos = cnpj.substring(tamanho);

            var soma = 0;
            var pos = tamanho - 7;
            for (var i = tamanho; i >= 1; i--) {
                soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
                if (pos < 2) pos = 9;
            }

            var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != parseInt(digitos.charAt(0))) {
                return false;
            }

            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (var i = tamanho; i >= 1; i--) {
                soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
                if (pos < 2) pos = 9;
            }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

            return resultado == parseInt(digitos.charAt(1));
        }

        // CNPJ alfanumérico - aceita formato (validação básica)
        if (/^[A-Z0-9]{14}$/.test(cnpj)) {
            // Validação básica de formato para CNPJ alfanumérico
            return true;
        }

        return false;
    },

    /**
     * Configura validação do formulário com jQuery Validate
     * Sincronizado com regras do backend (form_validation.php)
     */
    setupValidation: function() {
        var self = this;
        
        $.validator.addMethod("verific_cpf_cnpj", function(value, element) {
            if (this.optional(element)) {
                return true;
            }
            return self.validarDocumento(value);
        }, "O campo CPF/CNPJ não é válido.");

        $.validator.addMethod("unique_email", function(value, element) {
            return this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }, "Por favor, insira um e-mail válido.");

        $('#formCliente').validate({
            ignore: '.is-hidden :input',
            rules: {
                tipo_cliente: {
                    required: true
                },
                nomeCliente: {
                    required: true
                },
                documento_pf: {
                    required: function() {
                        return self.getTipoCliente() !== 'juridica';
                    },
                    verific_cpf_cnpj: true
                },
                documento_pj: {
                    required: function() {
                        return self.getTipoCliente() === 'juridica';
                    },
                    verific_cpf_cnpj: true
                },
                razao_social: {
                    required: function() {
                        return self.getTipoCliente() === 'juridica';
                    }
                },
                email: {
                    email: true,
                    unique_email: true
                }
            },
            messages: {
                tipo_cliente: {
                    required: 'Selecione o tipo de cliente.'
                },
                nomeCliente: {
                    required: 'Campo Requerido.'
                },
                documento_pf: {
                    required: 'Informe o CPF.',
                    verific_cpf_cnpj: 'CPF inválido.'
                },
                documento_pj: {
                    required: 'Informe o CNPJ.',
                    verific_cpf_cnpj: 'CNPJ inválido.'
                },
                razao_social: {
                    required: 'Razão social é obrigatória.'
                },
                email: {
                    email: 'Por favor, insira um e-mail válido.',
                    unique_email: 'Por favor, insira um e-mail válido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            invalidHandler: function(event, validator) {
                if (validator.errorList && validator.errorList.length) {
                    self.ensureTabVisible(validator.errorList[0].element);
                }
            },
            showErrors: function(errorMap, errorList) {
                if (errorList && errorList.length) {
                    self.ensureTabVisible(errorList[0].element);
                }
                this.defaultShowErrors();
            },
            onkeyup: function(element) {
                var campo = $(element).attr('name');
                if (campo === 'documento_pf' || campo === 'documento_pj') {
                    return false;
                }
                return true;
            },
            onfocusout: function(element) {
                var campo = $(element).attr('name');
                if (campo === 'documento_pf' || campo === 'documento_pj') {
                    return;
                }
                this.element(element);
            },
            highlight: function(element) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element) {
                $(element).parents('.control-group').removeClass('error').addClass('success');
            }
        });
    },

    /**
     * Configura validação em tempo real do documento
     * Validação on blur para melhorar UX
     */
    setupDocumentoValidation: function() {
        var self = this;

        ['#documento_pf', '#documento_pj'].forEach(function(selector) {
            var $field = $(selector);
            if (! $field.length) {
                return;
            }

            var validationTimeout = null;
            var lastValue = '';

            var validar = function(force) {
                var documento = $field.val() || '';
                var docLimpo = documento.replace(/[^a-zA-Z0-9]/g, '');
                var $controlGroup = $field.parents('.control-group');
                var $helpInline = $controlGroup.find('.help-inline.documento-error');

                if (!force && documento === lastValue) {
                    return;
                }

                if (!documento.trim()) {
                    $field.removeClass('error');
                    $controlGroup.removeClass('error');
                    $helpInline.remove();
                    lastValue = '';
                    return;
                }

                if (docLimpo.length !== 11 && docLimpo.length !== 14) {
                    return;
                }

                if (!self.validarDocumento(documento)) {
                    $field.addClass('error');
                    $controlGroup.addClass('error');
                    if ($helpInline.length === 0) {
                        $controlGroup.find('.controls').append(
                            '<span class="help-inline documento-error">CPF/CNPJ inválido.</span>'
                        );
                    }
                } else {
                    $field.removeClass('error');
                    $controlGroup.removeClass('error');
                    $helpInline.remove();
                    if ($('#formCliente').data('validator')) {
                        $field.valid();
                    }
                }

                lastValue = documento;
            };

            $field.on('blur', function() {
                clearTimeout(validationTimeout);
                validar(true);
            });

            $field.on('input', function() {
                clearTimeout(validationTimeout);
                var documento = $(this).val() || '';
                var docLimpo = documento.replace(/[^a-zA-Z0-9]/g, '');

                if (docLimpo.length >= 11) {
                    validationTimeout = setTimeout(function() {
                        validar(true);
                    }, 400);
                }
            });
        });
    },

    /**
     * Capitaliza primeira letra de cada palavra
     * @param {string} str - String a capitalizar
     * @returns {string} - String capitalizada
     */
    capitalLetter: function(str) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        str = str.toLowerCase().split(" ");
        for (var i = 0, x = str.length; i < x; i++) {
            if (str[i].length > 0) {
                str[i] = str[i][0].toUpperCase() + str[i].substr(1);
            }
        }
        return str.join(" ");
    },

    /**
     * Mostra estado de loading nos campos especificados
     * @param {Array} selectors - Array de seletores CSS
     */
    showLoading: function(selectors) {
        var self = this;
        selectors.forEach(function(selector) {
            var $field = $(selector);
            $field.addClass('loading');
            $field.prop('disabled', true);
            $field.attr('aria-busy', 'true');
        });
        
        // Mostrar feedback visual
        this.showFeedback('Buscando informações...', 'info');
    },

    /**
     * Remove estado de loading dos campos
     */
    hideLoading: function() {
        $('.loading').removeClass('loading').prop('disabled', false).attr('aria-busy', 'false');
        this.hideFeedback();
    },

    /**
     * Mostra mensagem de feedback
     * @param {string} message - Mensagem a exibir
     * @param {string} type - Tipo: 'success', 'error', 'info'
     */
    showFeedback: function(message, type) {
        type = type || 'info';
        var $feedback = $('#cliente-feedback');
        
        if ($feedback.length === 0) {
            $feedback = $('<div id="cliente-feedback" class="feedback-message" role="alert" aria-live="polite"></div>');
            $('#formCliente').prepend($feedback);
        }
        
        $feedback
            .removeClass('success error info')
            .addClass(type + ' show')
            .text(message);
    },

    /**
     * Esconde mensagem de feedback
     */
    hideFeedback: function() {
        $('#cliente-feedback').removeClass('show');
    }
};
