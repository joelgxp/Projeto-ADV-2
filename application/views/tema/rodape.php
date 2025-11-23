<div class="row-fluid">
    <div id="footer" class="span12">
        <a class="pecolor" href="#" target="_blank">
            <?= date('Y') ?> &copy; Adv - Sistema de Gestão Jurídica - Versão: <?= $this->config->item('app_version') ?>
        </a>
    </div>
</div>
<!--end-Footer-part-->
<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
<script src="<?= base_url() ?>assets/js/matrix.js"></script>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        // DataTables com server-side processing para melhor performance
        var table = $('#tabela');
        
        // Verificar se a tabela existe e se tem dados
        if (table.length) {
            // Detectar qual controller está sendo usado pela URL
            var currentUrl = window.location.pathname;
            var ajaxUrl = '';
            
            if (currentUrl.indexOf('/processos') !== -1) {
                ajaxUrl = '<?= site_url("processos/gerenciar") ?>';
            } else if (currentUrl.indexOf('/clientes') !== -1) {
                ajaxUrl = '<?= site_url("clientes/gerenciar") ?>';
            } else if (currentUrl.indexOf('/prazos') !== -1) {
                ajaxUrl = '<?= site_url("prazos/gerenciar") ?>';
            } else if (currentUrl.indexOf('/audiencias') !== -1) {
                ajaxUrl = '<?= site_url("audiencias/gerenciar") ?>';
            } else if (currentUrl.indexOf('/servicos') !== -1) {
                ajaxUrl = '<?= site_url("servicos/gerenciar") ?>';
            } else if (currentUrl.indexOf('/usuarios') !== -1) {
                ajaxUrl = '<?= site_url("usuarios/gerenciar") ?>';
            } else if (currentUrl.indexOf('/cobrancas') !== -1) {
                ajaxUrl = '<?= site_url("cobrancas/gerenciar") ?>';
            } else if (currentUrl.indexOf('/planos') !== -1) {
                ajaxUrl = '<?= site_url("planos/gerenciar") ?>';
            }
            
            // Se encontrou URL, usar server-side processing
            if (ajaxUrl) {
                // Verificar se DataTables já foi inicializado
                if ($.fn.dataTable.isDataTable('#tabela')) {
                    $('#tabela').dataTable().fnDestroy();
                }
                
                table.dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": ajaxUrl,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "iDisplayLength": <?= $configuration['per_page'] ?? 20 ?>,
                    "bSort": false,
                    "bInfo": true,
                    "sPaginationType": "full_numbers",
                    "sDom": '<"top"f>rt<"bottom"ip><"clear">',
                    "oLanguage": {
                        "sUrl": "<?= base_url() ?>assets/js/dataTable_pt-br.json",
                        "sProcessing": "Processando...",
                        "sSearch": "Pesquisa rápida:",
                        "sInfo": "Mostrando _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Nenhum registro encontrado",
                        "sInfoFiltered": "(filtrado de _MAX_ registros)",
                        "sZeroRecords": "Nenhum registro encontrado",
                        "oPaginate": {
                            "sFirst": "Primeira",
                            "sLast": "Última",
                            "sNext": "Próxima",
                            "sPrevious": "Anterior"
                        }
                    },
                    "fnServerData": function(sSource, aoData, fnCallback) {
                        $.ajax({
                            "dataType": 'json',
                            "type": "GET",
                            "url": sSource,
                            "data": aoData,
                            "success": function(json) {
                                // Verificar se a resposta está no formato correto
                                if (json && (json.aaData !== undefined || json.data !== undefined)) {
                                    fnCallback(json);
                                } else {
                                    console.error("Formato de resposta inválido:", json);
                                    alert("Erro: Formato de resposta inválido do servidor.");
                                }
                            },
                            "error": function(xhr, error, thrown) {
                                console.error("Erro DataTables:", error, thrown);
                                console.error("Status:", xhr.status);
                                console.error("Resposta:", xhr.responseText);
                                // Mostrar mensagem de erro ao usuário
                                alert("Erro ao carregar dados. Verifique o console para mais detalhes.");
                            }
                        });
                    }
                });
            } else {
                // Fallback: client-side processing para outras tabelas
                table.dataTable({
                    "ordering": false,
                    "info": false,
                    "language": {
                        "url": "<?= base_url() ?>assets/js/dataTable_pt-br.json",
                    },
                    "oLanguage": {
                        "sSearch": "Pesquisa rápida na tabela abaixo:"
                    }
                });
            }
        }
    } );
</script>
</html>
