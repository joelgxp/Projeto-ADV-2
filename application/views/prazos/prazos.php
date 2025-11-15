<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-calendar-check"></i>
        </span>
        <h5>Prazos Processuais</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aPrazo')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/prazos/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Novo Prazo
                    </span>
                </a>
            </div>
        <?php } ?>
        <form class="span9" method="get" action="<?= base_url() ?>index.php/prazos"
            style="display: flex; justify-content: flex-end;">
            <div class="span2">
                <select name="status" class="span12">
                    <option value="">Todos os Status</option>
                    <option value="Pendente" <?= $status == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Cumprido" <?= $status == 'Cumprido' ? 'selected' : '' ?>>Cumprido</option>
                    <option value="Vencido" <?= $status == 'Vencido' ? 'selected' : '' ?>>Vencido</option>
                </select>
            </div>
            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa"
                    placeholder="Buscar por Descrição, Tipo, Processo..." class="span12"
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
                        <th>Processo</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Data Prazo</th>
                        <th>Data Vencimento</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                    <td colspan="9">Nenhum Prazo Cadastrado</td>
                  </tr>';
                    } else {
                        foreach ($results as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idPrazos . '</td>';
                            echo '<td><a href="' . base_url() . 'index.php/processos/visualizar/' . ($r->processos_id ?? '') . '">' . (isset($r->numeroProcesso) ? $r->numeroProcesso : '-') . '</a></td>';
                            echo '<td>' . ($r->tipo ?? '-') . '</td>';
                            echo '<td>' . ($r->descricao ?? '-') . '</td>';
                            echo '<td>' . (isset($r->dataPrazo) ? date('d/m/Y', strtotime($r->dataPrazo)) : '-') . '</td>';
                            
                            // Data de vencimento com destaque se vencido
                            $dataVencimento = isset($r->dataVencimento) ? date('d/m/Y', strtotime($r->dataVencimento)) : '-';
                            $vencido = isset($r->dataVencimento) && strtotime($r->dataVencimento) < strtotime('today') && ($r->status ?? '') == 'Pendente';
                            $vencendo = isset($r->dataVencimento) && strtotime($r->dataVencimento) <= strtotime('+3 days') && strtotime($r->dataVencimento) >= strtotime('today') && ($r->status ?? '') == 'Pendente';
                            
                            if ($vencido) {
                                echo '<td><span class="label label-important">' . $dataVencimento . ' (Vencido)</span></td>';
                            } elseif ($vencendo) {
                                echo '<td><span class="label label-warning">' . $dataVencimento . ' (Vencendo)</span></td>';
                            } else {
                                echo '<td>' . $dataVencimento . '</td>';
                            }
                            
                            // Status com cores
                            $status_labels = [
                                'Pendente' => ['label' => 'Pendente', 'class' => 'label-warning'],
                                'Cumprido' => ['label' => 'Cumprido', 'class' => 'label-success'],
                                'Vencido' => ['label' => 'Vencido', 'class' => 'label-important'],
                            ];
                            $status = $r->status ?? 'Pendente';
                            $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => 'label-default'];
                            echo '<td><span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span></td>';
                            
                            // Prioridade
                            $prioridade_labels = [
                                'Baixa' => ['label' => 'Baixa', 'class' => 'label-default'],
                                'Normal' => ['label' => 'Normal', 'class' => 'label-info'],
                                'Alta' => ['label' => 'Alta', 'class' => 'label-warning'],
                                'Urgente' => ['label' => 'Urgente', 'class' => 'label-important'],
                            ];
                            $prioridade = $r->prioridade ?? 'Normal';
                            $prioridade_info = $prioridade_labels[$prioridade] ?? ['label' => $prioridade, 'class' => 'label-default'];
                            echo '<td><span class="label ' . $prioridade_info['class'] . '">' . $prioridade_info['label'] . '</span></td>';

                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPrazo')) {
                                echo '<a href="' . base_url() . 'index.php/prazos/visualizar/' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePrazo')) {
                                echo '<a href="' . base_url() . 'index.php/prazos/editar/' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Prazo"><i class="bx bx-edit bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dPrazo')) {
                                echo '<a href="#modal-excluir" role="button" data-toggle="modal" prazo="' . $r->idPrazos . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Prazo"><i class="bx bx-trash-alt bx-xs"></i></a>';
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

<!-- Modal Excluir -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/prazos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Prazo</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="prazoId" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este prazo?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a[href="#modal-excluir"]', function(event) {
            var prazo = $(this).attr('prazo');
            $('#prazoId').val(prazo);
        });
    });
</script>

