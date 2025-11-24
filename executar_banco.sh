#!/bin/bash

echo "========================================"
echo "Executando banco.sql no MySQL/MariaDB"
echo "========================================"
echo ""

# Ajuste estas variáveis conforme seu ambiente
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="adv"

echo "Criando banco de dados $DB_NAME..."
mysql -h "$DB_HOST" -u "$DB_USER" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if [ $? -ne 0 ]; then
    echo "ERRO: Não foi possível criar o banco de dados!"
    echo "Verifique se o MySQL está rodando e se as credenciais estão corretas."
    exit 1
fi

echo ""
echo "Importando banco.sql..."
mysql -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" < banco.sql

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "Banco de dados criado com sucesso!"
    echo "========================================"
    echo ""
    echo "IMPORTANTE: Antes de usar, você precisa:"
    echo "1. Substituir os placeholders no banco.sql:"
    echo "   - admin_name"
    echo "   - admin_email"
    echo "   - admin_password (use password_hash)"
    echo "   - admin_created_at"
    echo ""
    echo "2. Ou executar manualmente via phpMyAdmin"
    echo "   e editar o usuário admin depois."
    echo ""
else
    echo ""
    echo "ERRO: Falha ao importar banco.sql!"
    echo "Verifique se o arquivo existe e se há erros no SQL."
    echo ""
fi

