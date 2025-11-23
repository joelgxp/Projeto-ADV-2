$(function () {
    $("#celular").mask("(00) 00000-0000")
    $("#cep").mask("00000-000")
    $('#cpfUser').mask('000.000.000-00', { reverse: true });
    $('.cnpjEmitente').mask('00.000.000/0000-00', { reverse: true });
});


$(function () {
    if ($('.cpfcnpjmine').val() != null) {
        if ($('.cpfcnpjmine').val() != "") {
            $(".cpfcnpjmine").prop('readonly', true);
        }
    }
    if ($('.cpfUser').val() != null) {
        var cpfUser = $('.cpfUser').val().length;
        if (cpfUser == "14") {
            $(".cpfUser").prop('readonly', true);
        }
    }

});

$(function () {
    var telefoneN = function (val) {
        return val.replace(/\D/g, '').length > 10 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
        telefoneOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(telefoneN.apply({}, arguments), options);
            },
        };
    $('#telefone').mask(telefoneN, telefoneOptions);
    $('#telefone').on('paste', function (e) {
        e.preventDefault();
        var clipboardCurrentData = (e.originalEvent || e).clipboardData.getData('text/plain');
        $('#telefone').val(clipboardCurrentData);
    });

});

$(document).ready(function () {
    if ($("[name='idClientes']").val()) {
        $("#nomeCliente").focus();
    } else {
        $("#documento").focus();
    }

    // INICIO FUNÇÃO DE MASCARA CPF/CNPJ
    if ($("[name='idClientes']").val()) {
        $("#nomeCliente").focus();
    } else {
        $("#documento").focus();
    }

    // Máscara dinâmica para CPF, CNPJ tradicional e CNPJ alfanumérico
    $('#documento').on('input', function () {
        let v = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let result = '';
        // CPF: 11 dígitos numéricos
        if (/^\d{0,11}$/.test(v)) {
            for (let i = 0; i < v.length && i < 11; i++) {
                if (i === 3 || i === 6) result += '.';
                if (i === 9) result += '-';
                result += v[i];
            }
        }
        // CNPJ tradicional: 14 dígitos numéricos
        else if (/^\d{12,14}$/.test(v) && !/[A-Z]/.test(v)) {
            for (let i = 0; i < v.length && i < 14; i++) {
                if (i === 2 || i === 5) result += '.';
                if (i === 8) result += '/';
                if (i === 12) result += '-';
                result += v[i];
            }
        }
        // CNPJ alfanumérico: 14 caracteres (letras e números)
        else {
            for (let i = 0; i < v.length && i < 14; i++) {
                if (i === 2 || i === 5) result += '.';
                if (i === 8) result += '/';
                if (i === 12) result += '-';
                result += v[i];
            }
        }
        $(this).val(result);
         // FIM FUNÇÃO DE MASCARA CPF/CNPJ
    });

    function limpa_formulario_cep() {
        // Limpa valores do formulário de cep.
        $("#rua").val("");
        $("#bairro").val("");
        $("#cidade").val("");
        $("#estado").val("");
    }

    function capitalizeFirstLetter(string) {
        if (typeof string === 'undefined') {
            return;
        }

        return string.charAt(0).toUpperCase() + string.slice(1).toLocaleLowerCase();
    }

    function capital_letter(str) {
        if (typeof str === 'undefined') { return; }
        str = str.toLocaleLowerCase().split(" ");

        for (var i = 0, x = str.length; i < x; i++) {
            str[i] = str[i][0].toUpperCase() + str[i].substr(1);
        }

        return str.join(" ");
    }

    // Valida CNPJ
     // Função auxiliar para calcular o DV alfanumérico
   function valorCharAlfanumerico(char) {
    const ascii = char.charCodeAt(0);
    return ascii - 48;
    }

    // Função auxiliar para calcular o DV alfanumérico
    function calcularDVAlfanumerico(cnpjBase) {
    let valores = cnpjBase.split('').map(valorCharAlfanumerico);

    // Cálculo do 1º DV
    let pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let soma1 = valores.reduce((acc, val, i) => acc + val * pesos1[i], 0);
    let resto1 = soma1 % 11;
    let dv1 = (resto1 === 0 || resto1 === 1) ? 0 : 11 - resto1;

    // Cálculo do 2º DV
    valores.push(dv1);
    let pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    let soma2 = valores.reduce((acc, val, i) => acc + val * pesos2[i], 0);
    let resto2 = soma2 % 11;
    let dv2 = (resto2 === 0 || resto2 === 1) ? 0 : 11 - resto2;

    return `${dv1}${dv2}`;
}

function validarCNPJ(cnpj) {
    
    cnpj = cnpj.replace(/[^\w]/g, '').toUpperCase();

    // CNPJ numérico tradicional
    if (/^\d{14}$/.test(cnpj)) {
        if (/^(\d)\1{13}$/.test(cnpj)) {
            
            return false;
        }

        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);

        let soma = 0;
        let pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
            if (pos < 2) pos = 9;
        }

        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != parseInt(digitos.charAt(0))) {
           
            return false;
        }

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

        const valido = resultado == parseInt(digitos.charAt(1));
        return valido;
    }

    // CNPJ alfanumérico
    if (/^[A-Z0-9]{12}\d{2}$/.test(cnpj)) {
        let base = cnpj.substring(0, 12);
        let dv = cnpj.substring(12, 14);
        const calculado = calcularDVAlfanumerico(base);
        const valido = calculado === dv;
        return false;
    }
}
    //finaliza a validação do CNPJ

    $('#buscar_info_cnpj').on('click', function () {
        // Pega o valor original do campo, sem remover letras
        // Usa documento_pj pois a busca só funciona para pessoa jurídica
        var ndocumento = $('#documento_pj').length > 0 ? $('#documento_pj').val().trim() : $('#documento').val().trim();

        if (validarCNPJ(ndocumento)) {
            // Se for CNPJ alfanumérico, exibe alerta e não faz requisição
            if (/^[A-Z0-9]{14}$/.test(ndocumento.replace(/[^A-Z0-9]/g, '')) && /[A-Z]/.test(ndocumento)) {
                Swal.fire({
                    icon: "info",
                    title: "Atenção",
                    text: "A consulta automática ainda não está disponível para o novo formato de CNPJ alfanumérico. Preencha os dados manualmente."
                });
                return;
            }

        //Nova variável "ndocumento" somente com dígitos.
        var ndocumento = $('#documento_pj').length > 0 ? $('#documento_pj').val().replace(/\D/g, '') : $('#documento').val().replace(/\D/g, '');

            //Preenche os campos com "..." enquanto consulta webservice.
            if ($("#razao_social").length > 0) {
                $("#razao_social").val("...");
            } else if ($("#nomeCliente").length > 0) {
                $("#nomeCliente").val("...");
            }
            if ($("#email").length > 0) {
                $("#email").val("...");
            }
            if ($("#cep").length > 0) {
                $("#cep").val("...");
            }
            if ($("#rua").length > 0) {
                $("#rua").val("...");
            }
            if ($("#numero").length > 0) {
                $("#numero").val("...");
            }
            if ($("#bairro").length > 0) {
                $("#bairro").val("...");
            }
            if ($("#cidade").length > 0) {
                $("#cidade").val("...");
            }
            if ($("#estado").length > 0) {
                $("#estado").val("...");
            }
            if ($("#complemento").length > 0) {
                $("#complemento").val("...");
            }
            if ($("#telefone").length > 0) {
                $("#telefone").val("...");
            }
            //Consulta o webservice receitaws.com.br/
            $.ajax({
                url: "https://www.receitaws.com.br/v1/cnpj/" + ndocumento,
                dataType: 'jsonp',
                crossDomain: true,
                contentType: "text/javascript",
                success: function (dados) {
                    if (dados.status == "OK") {
                        //Atualiza os campos com os valores da consulta.
                        // Para Pessoa Jurídica (razao_social) ou Pessoa Física (nomeCliente)
                        if ($("#razao_social").length > 0) {
                            $("#razao_social").val(capital_letter(dados.nome));
                        } else if ($("#nomeCliente").length > 0) {
                            $("#nomeCliente").val(capital_letter(dados.nome));
                        }
                        if ($("#nomeEmitente").length > 0) {
                            $("#nomeEmitente").val(capital_letter(dados.nome));
                        }
                        // Nome fantasia se existir
                        if ($("#nome_fantasia").length > 0 && dados.fantasia) {
                            $("#nome_fantasia").val(capital_letter(dados.fantasia));
                        }
                        // Inscrição estadual se existir
                        if ($("#inscricao_estadual").length > 0 && dados.inscricao_estadual) {
                            $("#inscricao_estadual").val(dados.inscricao_estadual);
                        }
                        // CNAE se existir
                        if ($("#cnae").length > 0 && dados.atividade_principal && dados.atividade_principal.length > 0) {
                            $("#cnae").val(dados.atividade_principal[0].code);
                        }
                        // Ramo de atividade se existir
                        if ($("#ramo_atividade").length > 0 && dados.atividade_principal && dados.atividade_principal.length > 0) {
                            $("#ramo_atividade").val(capital_letter(dados.atividade_principal[0].text));
                        }
                        
                        if (dados.cep) {
                            $("#cep").val(dados.cep.replace(/\./g, ''));
                        }
                        if (dados.email) {
                            $("#email").val(dados.email.toLocaleLowerCase());
                        }
                        if (dados.telefone) {
                            $("#telefone").val(dados.telefone.split("/")[0].replace(/\ /g, ''));
                        }
                        if (dados.logradouro) {
                            $("#rua").val(capital_letter(dados.logradouro));
                        }
                        if (dados.numero) {
                            $("#numero").val(dados.numero);
                        }
                        if (dados.bairro) {
                            $("#bairro").val(capital_letter(dados.bairro));
                        }
                        if (dados.municipio) {
                            $("#cidade").val(capital_letter(dados.municipio));
                        }
                        if (dados.uf) {
                            $("#estado").val(dados.uf);
                        }
                        if (dados.complemento && dados.complemento != "") {
                            $("#complemento").val(capital_letter(dados.complemento));
                        } else {
                            $("#complemento").val("");
                        }

                        // Focar no campo apropriado
                        if ($("#razao_social").length > 0) {
                            document.getElementById("razao_social").focus();
                        } else if ($("#nomeCliente").length > 0) {
                            document.getElementById("nomeCliente").focus();
                        } else if ($("#nomeEmitente").length > 0) {
                            document.getElementById("nomeEmitente").focus();
                        }
                        
                        Swal.fire({
                            icon: "success",
                            title: "Sucesso",
                            text: "Dados do CNPJ preenchidos automaticamente!"
                        });
                    } //end if.
                    else {
                        //CNPJ pesquisado não foi encontrado.
                        if ($("#razao_social").length > 0) {
                            $("#razao_social").val("");
                        }
                        if ($("#nomeCliente").length > 0) {
                            $("#nomeCliente").val("");
                        }
                        if ($("#nomeEmitente").length > 0) {
                            $("#nomeEmitente").val("");
                        }
                        $("#cep").val("");
                        $("#email").val("");
                        $("#numero").val("");
                        $("#complemento").val("");
                        $("#telefone").val("");

                        Swal.fire({
                            icon: "warning",
                            title: "Atenção",
                            text: "CNPJ não encontrado."
                        });
                    }
                },
                error: function () {
                    //CNPJ pesquisado não foi encontrado.
                    if ($("#razao_social").length > 0) {
                        $("#razao_social").val("");
                    }
                    if ($("#nomeCliente").length > 0) {
                        $("#nomeCliente").val("");
                    }
                    if ($("#nomeEmitente").length > 0) {
                        $("#nomeEmitente").val("");
                    }
                    $("#cep").val("");
                    $("#email").val("");
                    $("#numero").val("");
                    $("#complemento").val("");
                    $("#telefone").val("");

                    Swal.fire({
                        icon: "warning",
                        title: "Atenção",
                        text: "CNPJ não encontrado."
                    });
                },
                timeout: 2000,
            });
        } else {
            Swal.fire({
                icon: "warning",
                title: "Atenção",
                text: "CNPJ inválido!"
            });
        }
    });

}); 
