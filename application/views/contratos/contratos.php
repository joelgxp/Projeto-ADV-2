<div class="new122">
    <div class="widget-title">
        <span class="icon"><i class="fas fa-file-contract"></i></span>
        <h5>Contratos</h5>
    </div>
    
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aContrato')) { ?>
        <div style="margin-bottom: 15px;">
            <a href="<?= base_url() ?>index.php/contratos/adicionar" class="button btn btn-mini btn-success">
                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                <span class="button__text2">Novo Contrato</span>
            </a>
        </div>
    <?php } ?>

    <form method="get" action="<?= base_url() ?>index.php/contratos" style="margin-bottom: 15px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div>
                <input type="text" name="cliente" placeholder="Buscar por cliente..." 
                    value="<?= $this->input->get('cliente') ?>" class="span12">
            </div>
            <div>
                <select name="tipo" class="span12">
                    <option value="">Todos os tipos</option>
                    <option value="fixo" <?= $this->input->get('tipo') == 'fixo' ? 'selected' : '' ?>>Fixo</option>
                    <option value="variavel" <?= $this->input->get('tipo') == 'variavel' ? 'selected' : '' ?>>Variável</option>
                    <option value="sucumbencia" <?= $this->input->get('tipo') == 'sucumbencia' ? 'selected' : '' ?>>Sucumbência</option>
                    <option value="misto" <?= $this->input->get('tipo') == 'misto' ? 'selected' : '' ?>>Misto</option>
                </select>
            </div>
            <div>
                <select name="ativo" class="span12">
                    <option value="">Todos</option>
                    <option value="1" <?= $this->input->get('ativo') === '1' ? 'selected' : '' ?>>Ativos</option>
                    <option value="0" <?= $this->input->get('ativo') === '0' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>
            <div>
                <button type="submit" class="button btn btn-mini btn-warning">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span>
                </button>
            </div>
        </div>
    </form>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th>Valor Fixo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($results) { ?>
                        <?php foreach ($results as $r) { ?>
                            <tr>
                                <td><?= $r->id ?></td>
                                <td><?= htmlspecialchars($r->nomeCliente) ?></td>
                                <td><?= ucfirst($r->tipo) ?></td>
                                <td><?= date('d/m/Y', strtotime($r->data_inicio)) ?></td>
                                <td><?= $r->data_fim ? date('d/m/Y', strtotime($r->data_fim)) : '-' ?></td>
                                <td>R$ <?= $r->valor_fixo ? number_format($r->valor_fixo, 2, ',', '.') : '-' ?></td>
                                <td>
                                    <?php if ($r->ativo) { ?>
                                        <span class="badge badge-success">Ativo</span>
                                    <?php } else { ?>
                                        <span class="badge badge-secondary">Inativo</span>
                                    <?php } ?>
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="<?= base_url() ?>index.php/contratos/visualizar/<?= $r->id ?>" 
                                        class="button btn btn-mini btn-info" title="Visualizar" style="display: inline-block; margin-right: 5px;">
                                        <i class='bx bx-show'></i>
                                    </a>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eContrato')) { ?>
                                        <a href="<?= base_url() ?>index.php/contratos/editar/<?= $r->id ?>" 
                                            class="button btn btn-mini btn-warning" title="Editar" style="display: inline-block; margin-right: 5px;">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <?php if (!$r->ativo) { ?>
                                            <a href="<?= base_url() ?>index.php/contratos/ativar/<?= $r->id ?>" 
                                                class="button btn btn-mini btn-success" title="Ativar" style="display: inline-block; margin-right: 5px;">
                                                <i class='bx bx-check'></i>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dContrato')) { ?>
                                        <a href="<?= base_url() ?>index.php/contratos/excluir/<?= $r->id ?>" 
                                            class="button btn btn-mini btn-danger" 
                                            onclick="return confirm('Deseja realmente excluir este contrato?')" title="Excluir" style="display: inline-block; margin-right: 5px;">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8">Nenhum contrato encontrado.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <?php if (isset($pagination)) { echo $pagination; } ?>
        </div>
    </div>
</div>

