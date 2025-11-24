<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-calendar-event"></i>
        </span>
        <h5>Audiências</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aAudiencia')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/audiencias/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Nova Audiência
                    </span>
                </a>
            </div>
            <form class="span9" method="get" action="<?= base_url() ?>index.php/audiencias"
                style="display: flex; justify-content: flex-end;">
                <div class="span2">
                    <select name="status" class="span12">
                        <option value="">Todos os Status</option>
                        <option value="Agendada" <?= $status == 'Agendada' ? 'selected' : '' ?>>Agendada</option>
                        <option value="Realizada" <?= $status == 'Realizada' ? 'selected' : '' ?>>Realizada</option>
                        <option value="Cancelada" <?= $status == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        <option value="Adiada" <?= $status == 'Adiada' ? 'selected' : '' ?>>Adiada</option>
                    </select>
                </div>
                <div class="span2">
                    <input type="date" name="data_inicio" class="span12" value="<?= $data_inicio ?>" placeholder="Data Início">
                </div>
                <div class="span2">
                    <input type="date" name="data_fim" class="span12" value="<?= $data_fim ?>" placeholder="Data Fim">
                </div>
                <div class="span3">
                    <input type="text" name="pesquisa" id="pesquisa"
                        placeholder="Buscar por Tipo, Local, Processo..." class="span12"
                        value="<?= $this->input->get('pesquisa') ?>">
                </div>
                <div class="span1">
                    <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                        <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
                </div>
            </form>
        <?php } else { ?>
            <form class="span12" method="get" action="<?= base_url() ?>index.php/audiencias"
                style="display: flex; justify-content: flex-end;">
                <div class="span2">
                    <select name="status" class="span12">
                        <option value="">Todos os Status</option>
                        <option value="Agendada" <?= $status == 'Agendada' ? 'selected' : '' ?>>Agendada</option>
                        <option value="Realizada" <?= $status == 'Realizada' ? 'selected' : '' ?>>Realizada</option>
                        <option value="Cancelada" <?= $status == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        <option value="Adiada" <?= $status == 'Adiada' ? 'selected' : '' ?>>Adiada</option>
                    </select>
                </div>
                <div class="span2">
                    <input type="date" name="data_inicio" class="span12" value="<?= $data_inicio ?>" placeholder="Data Início">
                </div>
                <div class="span2">
                    <input type="date" name="data_fim" class="span12" value="<?= $data_fim ?>" placeholder="Data Fim">
                </div>
                <div class="span3">
                    <input type="text" name="pesquisa" id="pesquisa"
                        placeholder="Buscar por Tipo, Local, Processo..." class="span12"
                        value="<?= $this->input->get('pesquisa') ?>">
                </div>
                <div class="span1">
                    <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                        <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
                </div>
            </form>
        <?php } ?>
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
                        <th>Data e Hora</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Com server-side processing, o tbody fica vazio - DataTables preenche via AJAX
                    // Mantém dados iniciais apenas para fallback se JS estiver desabilitado
                    if (isset($results) && $results && !$this->input->is_ajax_request()) {
                        foreach ($results as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idAudiencias . '</td>';
                            echo '<td><a href="' . base_url() . 'index.php/processos/visualizar/' . ($r->processos_id ?? '') . '">' . (isset($r->numeroProcesso) ? $r->numeroProcesso : '-') . '</a></td>';
                            echo '<td>' . ($r->tipo ?? '-') . '</td>';
                            
                            // Data e hora formatada
                            if (isset($r->dataHora)) {
                                $dataHora = date('d/m/Y H:i', strtotime($r->dataHora));
                                $hoje = strtotime('today');
                                $dataAud = strtotime(date('Y-m-d', strtotime($r->dataHora)));
                                
                                if ($dataAud == $hoje) {
                                    echo '<td><span class="label label-info">' . $dataHora . ' (Hoje)</span></td>';
                                } elseif ($dataAud < $hoje) {
                                    echo '<td><span class="label label-default">' . $dataHora . ' (Passada)</span></td>';
                                } else {
                                    echo '<td>' . $dataHora . '</td>';
                                }
                            } else {
                                echo '<td>-</td>';
                            }
                            
                            echo '<td>' . ($r->local ?? '-') . '</td>';
                            
                            // Status com cores
                            $status_labels = [
                                'Agendada' => ['label' => 'Agendada', 'class' => 'label-info'],
                                'Realizada' => ['label' => 'Realizada', 'class' => 'label-success'],
                                'Cancelada' => ['label' => 'Cancelada', 'class' => 'label-important'],
                                'Adiada' => ['label' => 'Adiada', 'class' => 'label-warning'],
                            ];
                            $status = $r->status ?? 'Agendada';
                            $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => 'label-default'];
                            echo '<td><span class="label ' . $status_info['class'] . '">' . $status_info['label'] . '</span></td>';

                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vAudiencia')) {
                                echo '<a href="' . base_url() . 'index.php/audiencias/visualizar/' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eAudiencia')) {
                                echo '<a href="' . base_url() . 'index.php/audiencias/editar/' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Audiência"><i class="bx bx-edit bx-xs"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dAudiencia')) {
                                echo '<a href="#modal-excluir" role="button" data-toggle="modal" audiencia="' . $r->idAudiencias . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Audiência"><i class="bx bx-trash-alt bx-xs"></i></a>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="dataTables_empty">Carregando dados...</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/audiencias/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Audiência</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="audienciaId" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta audiência?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a[href="#modal-excluir"]', function(event) {
            var audiencia = $(this).attr('audiencia');
            $('#audienciaId').val(audiencia);
        });
    });
</script>

