/**
 * Biblioteca JavaScript para formulário de clientes
 * Gerencia máscaras, validações, busca de CEP e CNPJ
 */
var ClienteForm = {
    config: {
        isEdit: false,
        estadoValue: '',
        baseUrl: ''
    },

    /**
     * Inicializa o formulário
     * @param {Object} options - Opções de configuração
     */
    init: function(options) {
        this.config = $.extend({}, this.config, options || {});
        
        this.setupPasswordToggle();
        this.loadEstados();
        this.setupMasks();
        this.setupCEP();
        this.setupCNPJ();
        this.setupValidation();
        this.setupDocumentoValidation();
    },

    /**
     * Configura toggle de visibilidade da senha
     */
    setupPasswordToggle: function() {
        var self = this;
        var container = document.querySelector('div');
        var input = document.querySelector('#senha');
        var icon = document.querySelector('#imgSenha');

        if (!icon || !input) return;

        var togglePassword = function() {
            container.classList.toggle('visible');
            if (container.classList.contains('visible')) {
                icon.src = self.config.baseUrl + 'assets/img/eye-off.svg';
                input.type = 'text';
            } else {
                icon.src = self.config.baseUrl + 'assets/img/eye.svg';
                input.type = 'password';
            }
        };

        icon.addEventListener('click', togglePassword);
        icon.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
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
            if (self.config.estadoValue) {
                $("#estado option[value=" + self.config.estadoValue + "]").prop("selected", true);
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
        
        // Máscara dinâmica para CPF/CNPJ
        this.aplicarMascaras();
    },

    /**
     * Aplica máscaras dinâmicas para CPF/CNPJ
     */
    aplicarMascaras: function() {
        var self = this;
        $('#documento').on('input', function() {
            var v = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            var result = '';
            
            // CPF: 11 dígitos numéricos
            if (/^\d{0,11}$/.test(v)) {
                for (var i = 0; i < v.length && i < 11; i++) {
                    if (i === 3 || i === 6) result += '.';
                    if (i === 9) result += '-';
                    result += v[i];
                }
            }
            // CNPJ tradicional: 14 dígitos numéricos
            else if (/^\d{12,14}$/.test(v) && !/[A-Z]/.test(v)) {
                for (var i = 0; i < v.length && i < 14; i++) {
                    if (i === 2 || i === 5) result += '.';
                    if (i === 8) result += '/';
                    if (i === 12) result += '-';
                    result += v[i];
                }
            }
            // CNPJ alfanumérico: 14 caracteres (letras e números)
            else {
                for (var i = 0; i < v.length && i < 14; i++) {
                    if (i === 2 || i === 5) result += '.';
                    if (i === 8) result += '/';
                    if (i === 12) result += '-';
                    result += v[i];
                }
            }
            $(this).val(result);
        });
    },

    /**
     * Configura busca de CEP via ViaCEP
     */
    setupCEP: function() {
        var self = this;
        $("#cep").on('blur', function() {
            self.buscarCEP($(this).val());
        });
    },

    /**
     * Busca endereço por CEP
     * @param {string} cep - CEP a buscar
     */
    buscarCEP: function(cep) {
        var self = this;
        var cepClean = cep.replace(/\D/g, '');
        var validacep = /^[0-9]{8}$/;

        if (cepClean === "") {
            this.limpaFormularioCEP();
            return;
        }

        if (!validacep.test(cepClean)) {
            this.limpaFormularioCEP();
            Swal.fire({
                icon: "error",
                title: "Atenção",
                text: "Formato de CEP inválido."
            });
            return;
        }

        // Mostra loading
        this.showLoading(['#rua', '#bairro', '#cidade', '#estado']);

        // Consulta ViaCEP
        $.getJSON("https://viacep.com.br/ws/" + cepClean + "/json/?callback=?", function(dados) {
            self.hideLoading();
            
                    if (!("erro" in dados)) {
                        $("#rua").val(dados.logradouro || "");
                        $("#bairro").val(dados.bairro || "");
                        $("#cidade").val(dados.localidade || "");
                        $("#estado").val(dados.uf || "");
                        
                        // Atualiza select de estado se necessário
                        if (dados.uf) {
                            $("#estado option[value=" + dados.uf + "]").prop("selected", true);
                        }
                        
                        self.showFeedback('Endereço encontrado com sucesso!', 'success');
                        setTimeout(function() {
                            self.hideFeedback();
                        }, 3000);
                    } else {
                        self.limpaFormularioCEP();
                        self.showFeedback('CEP não encontrado.', 'error');
                        Swal.fire({
                            icon: "warning",
                            title: "Atenção",
                            text: "CEP não encontrado."
                        });
                    }
        }).fail(function() {
            self.hideLoading();
            self.limpaFormularioCEP();
            self.showFeedback('Erro ao buscar CEP. Tente novamente.', 'error');
            Swal.fire({
                icon: "error",
                title: "Erro",
                text: "Erro ao buscar CEP. Tente novamente."
            });
        });
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
            var ndocumento = $('#documento').val().trim();
            
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

                    // Foca no campo nome
                    if ($("#nomeCliente").length) {
                        $("#nomeCliente").focus();
                    } else if ($("#nomeEmitente").length) {
                        $("#nomeEmitente").focus();
                    }
                    
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
        
        // Adicionar método customizado para validar CPF/CNPJ
        $.validator.addMethod("verific_cpf_cnpj", function(value, element) {
            return self.validarDocumento(value);
        }, "O campo CPF/CNPJ não é válido.");

        // Adicionar método customizado para validar email único (será validado no backend)
        $.validator.addMethod("unique_email", function(value, element) {
            // Validação de formato apenas, unicidade será validada no backend
            return this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }, "Por favor, insira um e-mail válido.");

        $('#formCliente').validate({
            rules: {
                nomeCliente: {
                    required: true
                },
                documento: {
                    verific_cpf_cnpj: true
                },
                email: {
                    email: true,
                    unique_email: true
                }
            },
            messages: {
                nomeCliente: {
                    required: 'Campo Requerido.'
                },
                documento: {
                    verific_cpf_cnpj: 'O campo CPF/CNPJ não é válido.'
                },
                email: {
                    email: 'Por favor, insira um e-mail válido.',
                    unique_email: 'Por favor, insira um e-mail válido.'
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
    },

    /**
     * Configura validação em tempo real do documento
     * Validação on blur para melhorar UX
     */
    setupDocumentoValidation: function() {
        var self = this;
        var $documento = $('#documento');
        
        $documento.on('blur', function() {
            var documento = $(this).val();
            var $field = $(this);
            var $controlGroup = $field.parents('.control-group');
            
            if (documento && documento.trim() !== '') {
                if (!self.validarDocumento(documento)) {
                    $field.addClass('error');
                    $controlGroup.addClass('error');
                    
                    // Adicionar mensagem de erro se não existir
                    if ($controlGroup.find('.help-inline').length === 0) {
                        $controlGroup.find('.controls').append(
                            '<span class="help-inline">CPF/CNPJ inválido.</span>'
                        );
                    }
                } else {
                    $field.removeClass('error');
                    $controlGroup.removeClass('error');
                    $controlGroup.find('.help-inline').remove();
                }
            } else {
                // Limpar erros se campo vazio
                $field.removeClass('error');
                $controlGroup.removeClass('error');
                $controlGroup.find('.help-inline').remove();
            }
        });

        // Validação também ao digitar (com debounce)
        var timeout;
        $documento.on('input', function() {
            clearTimeout(timeout);
            var $field = $(this);
            var $controlGroup = $field.parents('.control-group');
            
            timeout = setTimeout(function() {
                var documento = $field.val();
                if (documento && documento.trim() !== '') {
                    // Remover erro temporariamente durante digitação
                    $controlGroup.find('.help-inline').remove();
                }
            }, 500);
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
