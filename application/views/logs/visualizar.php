<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-eye"></i>
        </span>
        <h5>Visualizar Log: <?= htmlspecialchars($arquivo) ?></h5>
    </div>

    <div class="widget-box">
        <div class="widget-content nopadding">
            <div class="span12" style="padding: 20px;">
                
                <!-- Informações do Arquivo -->
                <div class="span12" style="margin-left: 0; margin-bottom: 20px;">
                    <div class="alert alert-info">
                        <strong>Informações do Arquivo:</strong><br>
                        <i class="fas fa-file"></i> <strong>Arquivo:</strong> <?= htmlspecialchars($arquivo) ?><br>
                        <i class="fas fa-hdd"></i> <strong>Tamanho:</strong> <?= formatarTamanho($tamanho) ?><br>
                        <i class="fas fa-calendar"></i> <strong>Última Modificação:</strong> <?= $data_modificacao ?><br>
                        <i class="fas fa-list"></i> <strong>Total de Linhas:</strong> <?= number_format($total_linhas, 0, ',', '.') ?>
                        <?php if (isset($aviso)): ?>
                            <br><i class="fas fa-exclamation-triangle"></i> <strong>Aviso:</strong> <?= $aviso ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações -->
                <div class="span12" style="margin-left: 0; margin-bottom: 20px;">
                    <a href="<?= base_url() ?>index.php/logs" class="button btn btn-mini">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="<?= base_url() ?>index.php/logs/download/<?= $tipo ?>/<?= urlencode($arquivo) ?>" 
                       class="button btn btn-mini btn-success">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <?php 
                    $permissao = $this->session->userdata('permissao');
                    $isAdmin = is_string($permissao) && (strtolower($permissao) === 'admin' || strtolower($permissao) === 'administrador');
                    if ($isAdmin || $this->permission->checkPermission($permissao, 'eLog')): 
                    ?>
                        <a href="<?= base_url() ?>index.php/logs/limpar/<?= $tipo ?>/<?= urlencode($arquivo) ?>" 
                           class="button btn btn-mini btn-danger"
                           onclick="return confirm('Tem certeza que deseja limpar este log? Esta ação não pode ser desfeita.');">
                            <i class="fas fa-trash"></i> Limpar Log
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Conteúdo do Log -->
                <div class="span12" style="margin-left: 0;">
                    <div style="background-color: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 600px; overflow-y: auto; overflow-x: auto;">
                        <?php if (empty($linhas)): ?>
                            <div style="color: #888;">Log vazio</div>
                        <?php else: ?>
                            <?php foreach ($linhas as $num => $linha): ?>
                                <div style="margin-bottom: 2px;">
                                    <span style="color: #858585; margin-right: 10px; user-select: none;">
                                        <?= str_pad($num + 1, 6, '0', STR_PAD_LEFT) ?>
                                    </span>
                                    <span style="color: #d4d4d4;">
                                        <?php
                                        // Destaca diferentes tipos de log
                                        $linhaEscapada = htmlspecialchars($linha);
                                        
                                        // Destaque para ERROR
                                        if (stripos($linha, 'ERROR') !== false) {
                                            $linhaEscapada = preg_replace('/ERROR/i', '<span style="color: #f48771; font-weight: bold;">ERROR</span>', $linhaEscapada);
                                        }
                                        
                                        // Destaque para WARNING
                                        if (stripos($linha, 'WARNING') !== false || stripos($linha, 'WARN') !== false) {
                                            $linhaEscapada = preg_replace('/WARNING|WARN/i', '<span style="color: #dcdcaa; font-weight: bold;">$0</span>', $linhaEscapada);
                                        }
                                        
                                        // Destaque para INFO
                                        if (stripos($linha, 'INFO') !== false) {
                                            $linhaEscapada = preg_replace('/INFO/i', '<span style="color: #4ec9b0; font-weight: bold;">INFO</span>', $linhaEscapada);
                                        }
                                        
                                        // Destaque para DEBUG
                                        if (stripos($linha, 'DEBUG') !== false) {
                                            $linhaEscapada = preg_replace('/DEBUG/i', '<span style="color: #569cd6; font-weight: bold;">DEBUG</span>', $linhaEscapada);
                                        }
                                        
                                        // Destaque para URLs
                                        $linhaEscapada = preg_replace('/(https?:\/\/[^\s]+)/i', '<span style="color: #ce9178;">$1</span>', $linhaEscapada);
                                        
                                        // Destaque para JSON
                                        if (preg_match('/\{.*\}/', $linha)) {
                                            $linhaEscapada = preg_replace('/(\{[^}]+\})/i', '<span style="color: #ce9178;">$1</span>', $linhaEscapada);
                                        }
                                        
                                        echo $linhaEscapada;
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .button {
        margin-right: 5px;
    }
    div[style*="background-color: #1e1e1e"] {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
    }
</style>

