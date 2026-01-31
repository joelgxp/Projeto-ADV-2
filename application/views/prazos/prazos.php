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
            <form class="span9" method="get" action="<?= base_url() ?>index.php/prazos"
                style="display: flex; justify-content: flex-end;">
                <div class="span2">
                    <select name="status" class="span12">
                        <option value="">Todos os Status</option>
                        <option value="pendente" <?= strtolower($status) == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="proximo_vencer" <?= strtolower($status) == 'proximo_vencer' ? 'selected' : '' ?>>Próximo a Vencer</option>
                        <option value="vencendo_hoje" <?= strtolower($status) == 'vencendo_hoje' ? 'selected' : '' ?>>Vencendo Hoje</option>
                        <option value="vencido" <?= strtolower($status) == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="cumprido" <?= strtolower($status) == 'cumprido' ? 'selected' : '' ?>>Cumprido</option>
                        <option value="prorrogado" <?= strtolower($status) == 'prorrogado' ? 'selected' : '' ?>>Prorrogado</option>
                        <option value="cancelado" <?= strtolower($status) == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
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
        <?php } else { ?>
            <form class="span12" method="get" action="<?= base_url() ?>index.php/prazos"
                style="display: flex; justify-content: flex-end;">
                <div class="span2">
                    <select name="status" class="span12">
                        <option value="">Todos os Status</option>
                        <option value="pendente" <?= strtolower($status) == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="proximo_vencer" <?= strtolower($status) == 'proximo_vencer' ? 'selected' : '' ?>>Próximo a Vencer</option>
                        <option value="vencendo_hoje" <?= strtolower($status) == 'vencendo_hoje' ? 'selected' : '' ?>>Vencendo Hoje</option>
                        <option value="vencido" <?= strtolower($status) == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="cumprido" <?= strtolower($status) == 'cumprido' ? 'selected' : '' ?>>Cumprido</option>
                        <option value="prorrogado" <?= strtolower($status) == 'prorrogado' ? 'selected' : '' ?>>Prorrogado</option>
                        <option value="cancelado" <?= strtolower($status) == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
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
                    // Com server-side processing, o tbody fica vazio - DataTables preenche via AJAX
                    // Mantém dados iniciais apenas para fallback se JS estiver desabilitado
                    if (isset($results) && $results && !$this->input->is_ajax_request()) {
                        foreach ($results as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idPrazos . '</td>';
                            echo '<td><a href="' . base_url() . 'index.php/processos/visualizar/' . ($r->processos_id ?? '') . '">' . (isset($r->numeroProcesso) ? $r->numeroProcesso : '-') . '</a></td>';
                            echo '<td>' . ($r->tipo ?? '-') . '</td>';
                            echo '<td>' . ($r->descricao ?? '-') . '</td>';
                            echo '<td>' . (isset($r->dataPrazo) ? date('d/m/Y', strtotime($r->dataPrazo)) : '-') . '</td>';
                            
                            // Data de vencimento
                            $dataVencimento = isset($r->dataVencimento) ? date('d/m/Y', strtotime($r->dataVencimento)) : '-';
                            echo '<td>' . $dataVencimento . '</td>';
                            
                            // Status com cores e badges
                            $status_atual = strtolower($r->status ?? 'pendente');
                            $status_labels = [
                                'pendente' => ['label' => 'Pendente', 'class' => 'label-info'],
                                'proximo_vencer' => ['label' => 'Próximo a Vencer', 'class' => 'label-warning'],
                                'vencendo_hoje' => ['label' => 'Vencendo Hoje', 'class' => 'label-warning'],
                                'vencido' => ['label' => 'Vencido', 'class' => 'label-important'],
                                'cumprido' => ['label' => 'Cumprido', 'class' => 'label-success'],
                                'prorrogado' => ['label' => 'Prorrogado', 'class' => 'label-inverse'],
                                'cancelado' => ['label' => 'Cancelado', 'class' => 'label-default'],
                            ];
                            $status_info = $status_labels[$status_atual] ?? ['label' => ucfirst($status_atual), 'class' => 'label-default'];
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
                    } else {
                        echo '<tr><td colspan="9" class="dataTables_empty">Carregando dados...</td></tr>';
                    }
                    ?>
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
            <button type="button" class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
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

