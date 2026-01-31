<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon"><i class="fas fa-stethoscope"></i></span>
        <h5>Diagnóstico - Petições IA (Produção vs Local)</h5>
    </div>

    <p class="alert alert-info">Compare estes valores entre <strong>local</strong> e <strong>produção</strong>. 
    Se algo estiver diferente, pode ser a causa do problema.</p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Item</th><th>Valor</th><th>Observação</th></tr>
        </thead>
        <tbody>
            <?php foreach ($info ?? [] as $k => $v): ?>
            <tr>
                <td><code><?= htmlspecialchars($k) ?></code></td>
                <td><?= htmlspecialchars(is_bool($v) ? ($v ? 'true' : 'false') : (string)$v) ?></td>
                <td>
                    <?php if ($k === 'max_execution_time' && (int)$v < 60): ?>
                        <span class="label label-warning">Baixo! API pode demorar 60-90s</span>
                    <?php elseif ($k === 'logs_writable' && !$v): ?>
                        <span class="label label-danger">Pasta logs não gravável!</span>
                    <?php elseif ($k === 'vendor_autoload' && !$v): ?>
                        <span class="label label-danger">Composer não instalado! Rode composer install</span>
                    <?php elseif ($k === 'OpenAI_class' && $v === 'NAO'): ?>
                        <span class="label label-danger">Pacote openai-php não carregado</span>
                    <?php elseif ($k === 'OPENROUTER_API_KEY' && strpos($v, 'vazio') !== false): ?>
                        <span class="label label-warning">Configure no .env</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h5>Arquivo de debug (pecas_debug.log)</h5>
    <p>Após tentar gerar uma petição, verifique: <code>application/logs/pecas_debug.log</code></p>
    <p>Este arquivo é gravado diretamente (não depende do log do CodeIgniter). Se aparecer:</p>
    <ul>
        <li><strong>INICIO executar_geracao</strong> → A requisição chegou ao controller</li>
        <li><strong>Chamando OpenRouter chat</strong> → Chegou à chamada da API</li>
        <li><strong>Resposta recebida: OK</strong> → API retornou com sucesso</li>
        <li><strong>FATAL:</strong> → Erro fatal do PHP (timeout, memory, etc.)</li>
    </ul>
    <p>Se o arquivo não existir ou estiver vazio após tentar gerar, a requisição pode estar sendo bloqueada antes de chegar ao PHP (ex: timeout do servidor web).</p>

    <div style="margin-top: 15px;">
        <a href="<?= site_url('pecas-geradas/listar') ?>" class="btn">Voltar</a>
    </div>
</div>
