<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-file-alt"></i>
        </span>
        <h5>Logs do Sistema</h5>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <div class="span12" style="padding: 20px;">
                
                <!-- Logs Gerais do CodeIgniter -->
                <div class="span12" style="margin-left: 0; margin-bottom: 30px;">
                    <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">
                        <i class="fas fa-list"></i> Logs Gerais do Sistema
                    </h4>
                    
                    <?php if (empty($logs['gerais'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum log geral encontrado.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Arquivo</th>
                                    <th>Tamanho</th>
                                    <th>Última Modificação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs['gerais'] as $log): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-code"></i> 
                                            <strong><?= htmlspecialchars($log['nome']) ?></strong>
                                        </td>
                                        <td><?= formatarTamanho($log['tamanho']) ?></td>
                                        <td><?= date('d/m/Y H:i:s', $log['data_modificacao']) ?></td>
                                        <td>
                                            <a href="<?= base_url() ?>index.php/logs/visualizar/geral/<?= urlencode($log['nome']) ?>" 
                                               class="button btn btn-mini btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </a>
                                            <a href="<?= base_url() ?>index.php/logs/download/geral/<?= urlencode($log['nome']) ?>" 
                                               class="button btn btn-mini btn-success" title="Download">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <?php 
                                            $permissao = $this->session->userdata('permissao');
                                            $isAdmin = is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador');
                                            if ($isAdmin || $this->permission->checkPermission($permissao, 'eLog')): 
                                            ?>
                                                <a href="<?= base_url() ?>index.php/logs/limpar/geral/<?= urlencode($log['nome']) ?>" 
                                                   class="button btn btn-mini btn-danger" 
                                                   title="Limpar"
                                                   onclick="return confirm('Tem certeza que deseja limpar este log? Esta ação não pode ser desfeita.');">
                                                    <i class="fas fa-trash"></i> Limpar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Logs de Debug da API CNJ -->
                <div class="span12" style="margin-left: 0; margin-bottom: 30px;">
                    <h4 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">
                        <i class="fas fa-code"></i> Logs de Debug - API CNJ/DataJud
                    </h4>
                    
                    <?php if (empty($logs['cnj_api'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum log de debug da API CNJ encontrado.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Arquivo</th>
                                    <th>Tamanho</th>
                                    <th>Última Modificação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs['cnj_api'] as $log): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-code"></i> 
                                            <strong><?= htmlspecialchars($log['nome']) ?></strong>
                                        </td>
                                        <td><?= formatarTamanho($log['tamanho']) ?></td>
                                        <td><?= date('d/m/Y H:i:s', $log['data_modificacao']) ?></td>
                                        <td>
                                            <a href="<?= base_url() ?>index.php/logs/visualizar/cnj_api/<?= urlencode($log['nome']) ?>" 
                                               class="button btn btn-mini btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </a>
                                            <a href="<?= base_url() ?>index.php/logs/download/cnj_api/<?= urlencode($log['nome']) ?>" 
                                               class="button btn btn-mini btn-success" title="Download">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <?php 
                                            $permissao = $this->session->userdata('permissao');
                                            $isAdmin = is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador');
                                            if ($isAdmin || $this->permission->checkPermission($permissao, 'eLog')): 
                                            ?>
                                                <a href="<?= base_url() ?>index.php/logs/limpar/cnj_api/<?= urlencode($log['nome']) ?>" 
                                                   class="button btn btn-mini btn-danger" 
                                                   title="Limpar"
                                                   onclick="return confirm('Tem certeza que deseja limpar este log? Esta ação não pode ser desfeita.');">
                                                    <i class="fas fa-trash"></i> Limpar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        background-color: #f5f5f5;
        font-weight: bold;
    }
    .button {
        margin-right: 5px;
    }
</style>

