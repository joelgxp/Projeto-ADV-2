<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico de Estrutura do Banco de Dados</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #666; margin-top: 30px; }
        .sucesso { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .erro { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .tipo-tabela { font-weight: bold; color: #d9534f; }
        .tipo-coluna { font-weight: bold; color: #f0ad4e; }
        .tipo-tipo { font-weight: bold; color: #5bc0de; }
        .resumo { display: flex; gap: 20px; margin: 20px 0; }
        .card { flex: 1; padding: 20px; border-radius: 5px; text-align: center; }
        .card.sucesso { background: #d4edda; color: #155724; }
        .card.erro { background: #f8d7da; color: #721c24; }
        .card h3 { margin: 0 0 10px 0; font-size: 24px; }
        .card p { margin: 0; font-size: 18px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Estrutura do Banco de Dados</h1>
        <p><strong>Data/Hora:</strong> <?= date('d/m/Y H:i:s') ?></p>
        <p><strong>Banco de Dados:</strong> <?= htmlspecialchars($database) ?></p>
        
        <div class="resumo">
            <div class="card <?= empty($problemas) ? 'sucesso' : 'erro' ?>">
                <h3><?= count($problemas) ?></h3>
                <p>Problemas Encontrados</p>
            </div>
            <div class="card sucesso">
                <h3><?= count($sucesso) ?></h3>
                <p>Colunas Verificadas</p>
            </div>
        </div>
        
        <?php if (empty($problemas)): ?>
            <div class="sucesso">
                <h2>✅ Tudo OK!</h2>
                <p>Todas as tabelas e colunas necessárias estão presentes no banco de dados.</p>
            </div>
        <?php else: ?>
            <div class="erro">
                <h2>❌ Problemas Encontrados</h2>
                <p>Foram encontrados <?= count($problemas) ?> problema(s) na estrutura do banco de dados.</p>
            </div>
            
            <h2>Detalhes dos Problemas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Tabela</th>
                        <th>Coluna</th>
                        <th>Detalhes</th>
                        <th>Mensagem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($problemas as $problema): ?>
                        <tr>
                            <td>
                                <?php
                                $icones = [
                                    'tabela_ausente' => '📋',
                                    'coluna_ausente' => '📝',
                                    'tipo_incompativel' => '⚠️'
                                ];
                                echo $icones[$problema['tipo']] ?? '❓';
                                ?>
                                <span class="tipo-<?= $problema['tipo'] ?>"><?= ucfirst(str_replace('_', ' ', $problema['tipo'])) ?></span>
                            </td>
                            <td><strong><?= htmlspecialchars($problema['tabela']) ?></strong></td>
                            <td><?= isset($problema['coluna']) ? htmlspecialchars($problema['coluna']) : '-' ?></td>
                            <td>
                                <?php if (isset($problema['tipo_esperado'])): ?>
                                    <strong>Esperado:</strong> <?= htmlspecialchars($problema['tipo_esperado']) ?><br>
                                <?php endif; ?>
                                <?php if (isset($problema['tipo_existente'])): ?>
                                    <strong>Existente:</strong> <?= htmlspecialchars($problema['tipo_existente']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($problema['mensagem']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="info">
                <h3>💡 Como Corrigir</h3>
                <ol>
                    <li><strong>Opção 1 (Recomendado):</strong> Execute o <code>banco_limpo.sql</code> completo para recriar o banco com a estrutura correta</li>
                    <li><strong>Opção 2:</strong> Execute manualmente os comandos SQL necessários para adicionar as colunas/tabelas faltantes</li>
                    <li><strong>Opção 3:</strong> Compare a estrutura atual com o <code>banco_limpo.sql</code> e aplique as diferenças</li>
                </ol>
                <p><strong>⚠️ Importante:</strong> Faça backup do banco antes de aplicar alterações!</p>
            </div>
        <?php endif; ?>
        
        <h2>Colunas Verificadas com Sucesso</h2>
        <table>
            <thead>
                <tr>
                    <th>Tabela</th>
                    <th>Coluna</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sucesso as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['tabela']) ?></td>
                        <td><?= htmlspecialchars($item['coluna']) ?></td>
                        <td><?= htmlspecialchars($item['tipo']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="info" style="margin-top: 30px;">
            <p><strong>⚠️ Importante:</strong> Este é um script de diagnóstico. Use apenas em ambiente de desenvolvimento.</p>
            <p>Após corrigir os problemas, execute este diagnóstico novamente para verificar.</p>
            <p><a href="<?= base_url() ?>">← Voltar para o sistema</a></p>
        </div>
    </div>
</body>
</html>

