#!/bin/bash

# Script para testar migrations no servidor
# Execute: bash testar_migrations_servidor.sh

echo "=========================================="
echo "Testando Migrations no Servidor"
echo "=========================================="
echo ""

# Ir para o diretório do projeto
cd /home2/hotel631/adv.joelsouza.com.br

echo "1. Verificando diretório atual..."
pwd
echo ""

echo "2. Verificando se os arquivos foram atualizados..."
echo "   - Tools.php:"
if [ -f "application/controllers/Tools.php" ]; then
    grep -n "class_exists('Faker" application/controllers/Tools.php && echo "   ✅ Tools.php corrigido" || echo "   ❌ Tools.php não foi corrigido"
else
    echo "   ❌ Arquivo não encontrado"
fi
echo ""

echo "   - Mapos_model.php:"
if [ -f "application/models/Mapos_model.php" ]; then
    grep -n "if (\$query === false)" application/models/Mapos_model.php && echo "   ✅ Mapos_model.php corrigido" || echo "   ❌ Mapos_model.php não foi corrigido"
else
    echo "   ❌ Arquivo não encontrado"
fi
echo ""

echo "3. Testando comando tools help..."
php index.php tools help
echo ""

echo "4. Verificando migrations pendentes..."
php index.php tools migrate
echo ""

echo "5. Verificando se a migration foi criada..."
if [ -f "application/database/migrations/20251114182314_fix_check_credentials_error.php" ]; then
    echo "   ✅ Migration encontrada"
    echo "   Conteúdo da migration:"
    cat application/database/migrations/20251114182314_fix_check_credentials_error.php | head -20
else
    echo "   ⚠️  Migration não encontrada (pode não ter sido enviada)"
fi
echo ""

echo "6. Verificando logs de erro (últimas 10 linhas)..."
if [ -f "application/logs/log-$(date +%Y-%m-%d).php" ]; then
    tail -10 application/logs/log-$(date +%Y-%m-%d).php
else
    echo "   Nenhum log encontrado para hoje"
fi
echo ""

echo "=========================================="
echo "Teste concluído!"
echo "=========================================="

