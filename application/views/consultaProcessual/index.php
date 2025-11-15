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
                    <div class="control-group">
                        <label for="numero_processo" class="control-label">Número de Processo<span class="required">*</span></label>
                        <div class="controls">
                            <input id="numero_processo" type="text" name="numero_processo" 
                                placeholder="0000123-45.2023.8.13.0139 ou 00001234520238130139" 
                                class="span8" required />
                            <small class="help-inline">Aceita formato CNJ ou número limpo</small>
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="tribunal" class="control-label">Tribunal (Opcional)</label>
                        <div class="controls">
                            <input id="tribunal" type="text" name="tribunal" 
                                placeholder="Deixe em branco para auto-detectar" 
                                class="span4" maxlength="2" />
                            <small class="help-inline">Será detectado automaticamente do número do processo</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button btn btn-mini btn-success">
                            <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                            <span class="button__text2">Consultar Processo</span>
                        </button>
                    </div>
                </form>

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

<script type="text/javascript">
    $(document).ready(function() {
        $('#formConsulta').on('submit', function(e) {
            e.preventDefault();
            
            var numeroProcesso = $('#numero_processo').val();
            var tribunal = $('#tribunal').val();
            
            if (!numeroProcesso) {
                alert('Por favor, informe o número do processo.');
                return;
            }
            
            $('#loading').show();
            $('#resultado').hide();
            
            $.ajax({
                url: '<?= site_url('consulta-processual/consultar') ?>',
                type: 'POST',
                data: {
                    numero_processo: numeroProcesso,
                    tribunal: tribunal
                },
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.success) {
                        exibirResultado(response.data);
                    } else {
                        alert('Erro: ' + (response.message || 'Erro ao consultar processo.'));
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading').hide();
                    alert('Erro ao consultar processo. Tente novamente.');
                    console.error(error);
                }
            });
        });
        
        function exibirResultado(dados) {
            var html = '<div class="span12">';
            
            html += '<table class="table table-bordered">';
            html += '<tr><td><strong>Número:</strong></td><td>' + (dados.numero_formatado || dados.numero || '-') + '</td></tr>';
            html += '<tr><td><strong>Classe:</strong></td><td>' + (dados.classe || '-') + '</td></tr>';
            html += '<tr><td><strong>Assunto:</strong></td><td>' + (dados.assunto || '-') + '</td></tr>';
            html += '<tr><td><strong>Situação:</strong></td><td>' + (dados.situacao || dados.status || '-') + '</td></tr>';
            html += '<tr><td><strong>Valor da Causa:</strong></td><td>R$ ' + (dados.valor ? parseFloat(dados.valor).toFixed(2).replace('.', ',') : '-') + '</td></tr>';
            html += '<tr><td><strong>Vara:</strong></td><td>' + (dados.vara || '-') + '</td></tr>';
            html += '<tr><td><strong>Comarca:</strong></td><td>' + (dados.comarca || '-') + '</td></tr>';
            html += '<tr><td><strong>Data Distribuição:</strong></td><td>' + (dados.dataDistribuicao || '-') + '</td></tr>';
            html += '<tr><td><strong>Última Movimentação:</strong></td><td>' + (dados.dataUltimaMovimentacao || '-') + '</td></tr>';
            html += '</table>';
            
            if (dados.partes && dados.partes.length > 0) {
                html += '<h5>Partes do Processo</h5>';
                html += '<table class="table table-bordered">';
                html += '<thead><tr><th>Tipo</th><th>Nome</th><th>Documento</th></tr></thead>';
                html += '<tbody>';
                dados.partes.forEach(function(parte) {
                    html += '<tr>';
                    html += '<td>' + (parte.tipo || '-') + '</td>';
                    html += '<td>' + (parte.nome || '-') + '</td>';
                    html += '<td>' + (parte.documento || '-') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            }
            
            if (dados.movimentos && dados.movimentos.length > 0) {
                html += '<h5>Movimentações (' + dados.movimentos.length + ')</h5>';
                html += '<table class="table table-bordered">';
                html += '<thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th></tr></thead>';
                html += '<tbody>';
                dados.movimentos.slice(0, 10).forEach(function(mov) {
                    html += '<tr>';
                    html += '<td>' + (mov.dataHora ? mov.dataHora.substring(0, 10) : '-') + '</td>';
                    html += '<td>' + (mov.nome || mov.tipo || '-') + '</td>';
                    html += '<td>' + (mov.descricao || '-') + '</td>';
                    html += '</tr>';
                });
                if (dados.movimentos.length > 10) {
                    html += '<tr><td colspan="3"><em>Mostrando 10 de ' + dados.movimentos.length + ' movimentações</em></td></tr>';
                }
                html += '</tbody></table>';
            }
            
            html += '</div>';
            
            $('#resultadoContent').html(html);
            $('#resultado').show();
        }
    });
</script>

