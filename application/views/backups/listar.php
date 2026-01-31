<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-database"></i>
                </span>
                <h5>Gerenciamento de Backups</h5>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" style="padding: 15px;">
                    <a href="<?php echo base_url('backups/executar'); ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Executar Backup Agora
                    </a>
                </div>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Arquivo</th>
                            <th>Tamanho</th>
                            <th>Data Backup</th>
                            <th>Status</th>
                            <th>Criptografado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <p>Nenhum backup encontrado.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?php echo $backup->id; ?></td>
                                    <td>
                                        <span class="label label-info"><?php echo ucfirst($backup->tipo); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($backup->arquivo); ?></td>
                                    <td><?php echo number_format($backup->tamanho / 1024 / 1024, 2); ?> MB</td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($backup->data_backup)); ?></td>
                                    <td>
                                        <?php
                                        $status_class = [
                                            'sucesso' => 'label-success',
                                            'erro' => 'label-danger',
                                            'em_andamento' => 'label-warning'
                                        ];
                                        $class = $status_class[$backup->status] ?? 'label-default';
                                        ?>
                                        <span class="label <?php echo $class; ?>">
                                            <?php echo ucfirst($backup->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($backup->criptografado): ?>
                                            <span class="label label-info"><i class="fas fa-lock"></i> Sim</span>
                                        <?php else: ?>
                                            <span class="label label-default">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($backup->status == 'sucesso'): ?>
                                            <a href="<?php echo base_url('backups/download/' . $backup->id); ?>" class="btn btn-mini btn-info">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo base_url('backups/excluir/' . $backup->id); ?>" class="btn btn-mini btn-danger" onclick="return confirm('Deseja realmente excluir este backup?');">
                                            <i class="fas fa-trash"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

