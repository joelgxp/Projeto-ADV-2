<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-tags"></i>
        </span>
        <h5>Planos</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/planos/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Adicionar Plano
                    </span>
                </a>
            </div>
        <?php } ?>
        <form class="span9" method="get" action="<?= base_url() ?>index.php/planos"
            style="display: flex; justify-content: flex-end;">
            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa"
                    placeholder="Buscar por Nome ou Descrição..." class="span12"
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
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Valor Mensal</th>
                        <th>Limite Processos</th>
                        <th>Limite Prazos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                    <td colspan="8">Nenhum Plano Cadastrado</td>
                  </tr>';
                    }
                    foreach ($results as $r) {
                        echo '<tr>';
                        echo '<td>' . $r->idPlanos . '</td>';
                        echo '<td><a href="' . base_url() . 'index.php/planos/visualizar/' . $r->idPlanos . '" style="margin-right: 1%">' . $r->nome . '</a></td>';
                        echo '<td>' . ($r->descricao ? substr($r->descricao, 0, 50) . '...' : '-') . '</td>';
                        echo '<td>R$ ' . number_format($r->valor_mensal, 2, ',', '.') . '</td>';
                        echo '<td>' . ($r->limite_processos == 0 ? 'Ilimitado' : $r->limite_processos) . '</td>';
                        echo '<td>' . ($r->limite_prazos == 0 ? 'Ilimitado' : $r->limite_prazos) . '</td>';
                        
                        if ($r->status == 1) {
                            echo '<td><span class="label label-success">Ativo</span></td>';
                        } else {
                            echo '<td><span class="label label-danger">Inativo</span></td>';
                        }

                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                            echo '<a href="' . base_url() . 'index.php/planos/visualizar/' . $r->idPlanos . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                            echo '<a href="' . base_url() . 'index.php/planos/editar/' . $r->idPlanos . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Plano"><i class="bx bx-edit bx-xs"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
                            echo '<a href="#modal-excluir-plano" role="button" data-toggle="modal" plano="' . $r->idPlanos . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Plano"><i class="bx bx-trash-alt bx-xs"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal Excluir Plano -->
<div id="modal-excluir-plano" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <form action="<?php echo base_url() ?>index.php/planos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Plano</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idPlano" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este plano?</h5>
            <p style="text-align: center; color: #d9534f;"><strong>Atenção:</strong> Não é possível excluir planos que possuem clientes vinculados.</p>
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
        $(document).on('click', 'a[plano]', function (event) {
            var plano = $(this).attr('plano');
            $('#idPlano').val(plano);
        });
    });
</script>

