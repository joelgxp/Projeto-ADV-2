<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-gavel"></i>
        </span>
        <h5>Processos</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aProcesso')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/processos/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Novo Processo
                    </span>
                </a>
            </div>
        <?php } ?>
        <form class="span9" method="get" action="<?= base_url() ?>index.php/processos"
            style="display: flex; justify-content: flex-end;">
            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa"
                    placeholder="Buscar por Número, Classe, Assunto, Cliente..." class="span12"
                    value="<?= $this->input->get('pesquisa') ?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <div class="widget-box">
        <h5 style="padding: 3px 0"></h5>
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Número de Processo</th>
                        <th>Classe</th>
                        <th>Assunto</th>
                        <th>Cliente</th>
                        <th>Advogado</th>
                        <th>Status</th>
                        <th>Comarca</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                    <td colspan="9">Nenhum Processo Cadastrado</td>
                  </tr>';
                    } else {
                        $this->load->model('processos_model');
                        foreach ($results as $r) {
                            $numeroFormatado = $this->processos_model->formatarNumeroProcesso($r->numeroProcesso);
                            echo '<tr>';
                            echo '<td>' . $r->idProcessos . '</td>';
                            echo '<td><a href="' . base_url() . 'index.php/processos/visualizar/' . $r->idProcessos . '" style="margin-right: 1%">' . $numeroFormatado . '</a></td>';
                            echo '<td>' . ($r->classe ?? '-') . '</td>';
                            echo '<td>' . ($r->assunto ?? '-') . '</td>';
                            echo '<td>' . (isset($r->nomeCliente) ? $r->nomeCliente : '-') . '</td>';
                            echo '<td>' . (isset($r->nomeAdvogado) ? $r->nomeAdvogado : '-') . '</td>';
                            
                            // Status com cores
                            $status_labels = [
                                'em_andamento' => ['label' => 'Em Andamento', 'class' => 'label-info'],
                                'suspenso' => ['label' => 'Suspenso', 'class' => 'label-warning'],
                                'arquivado' => ['label' => 'Arquivado', 'class' => 'label-default'],
                                'finalizado' => ['label' => 'Finalizado', 'class' => 'label-success'],
                            ];
                            $status = $r->status ?? 'em_andamento';
                            $status_info = $status_labels[$status] ?? ['label' => ucfirst($status), 'class' => 'label-default'];
                            echo '<td><span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span></td>';
                            
                            echo '<td>' . ($r->comarca ?? '-') . '</td>';

                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProcesso')) {
                                echo '<a href="' . base_url() . 'index.php/processos/visualizar/' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProcesso')) {
                                echo '<a href="' . base_url() . 'index.php/processos/editar/' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Processo"><i class="bx bx-edit bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dProcesso')) {
                                echo '<a href="#modal-excluir" role="button" data-toggle="modal" processo="' . $r->idProcessos . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Processo"><i class="bx bx-trash-alt bx-xs"></i></a>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/processos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Processo</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idProcesso" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este processo e os dados associados a ele (movimentações, prazos, audiências, documentos)?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i
                        class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span
                    class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', 'a', function (event) {
            var processo = $(this).attr('processo');
            if (processo) {
                $('#idProcesso').val(processo);
            }
        });
    });
</script>

