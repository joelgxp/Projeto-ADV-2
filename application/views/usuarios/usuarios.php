<style>
    select {
        width: 70px;
    }
    .situacao-ativo {
        background-color: #00cd00;
        color: white;
    }
    .situacao-inativo {
        background-color: #ff0000;
        color: white;
    }
</style>

<div class="new122">
    <div class="widget-title" style="margin:-15px -10px 0">
        <h5>Usuários</h5>
    </div>
    <a href="<?= base_url('index.php/usuarios/adicionar') ?>" class="button btn btn-success" style="max-width: 160px">
        <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar Usuário</span>
    </a>

    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-cash-register"></i>
            </span>
            <h5 style="padding: 3px 0"></h5>
        </div>
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Nível</th>
                        <th>Situação</th>
                        <th>Validade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Com server-side processing, o tbody fica vazio - DataTables preenche via AJAX
                    // Mantém dados iniciais apenas para fallback se JS estiver desabilitado
                    if (isset($results) && $results && !$this->input->is_ajax_request()) {
                        foreach ($results as $r) {
                            $situacao = (isset($r->situacao) && $r->situacao == 1) ? 'Ativo' : 'Inativo';
                            $situacaoClasse = (isset($r->situacao) && $r->situacao == 1) ? 'situacao-ativo' : 'situacao-inativo';
                            ?>
                            <tr>
                                <td><?= $r->idUsuarios ?></td>
                                <td><?= $r->nome ?></td>
                                <td><?= $r->cpf ?></td>
                                <td><?= $r->permissao ?></td>
                                <td><span class="badge <?= $situacaoClasse ?>"><?= ucfirst($situacao) ?></span></td>
                                <td><?= $r->dataExpiracao ?></td>
                                <td>
                                    <a href="<?= base_url('index.php/usuarios/editar/' . $r->idUsuarios) ?>" class="btn-nwe3" title="Editar Usuário"><i class="bx bx-edit"></i></a>
                                </td>
                            </tr>
                            <?php
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
