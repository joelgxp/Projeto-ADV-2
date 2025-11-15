@echo off
echo ========================================
echo Executando banco.sql no MySQL
echo ========================================
echo.

REM Ajuste estas variaveis conforme seu ambiente
set DB_HOST=localhost
set DB_USER=root
set DB_PASS=
set DB_NAME=mapos

echo Criando banco de dados %DB_NAME%...
mysql -h %DB_HOST% -u %DB_USER% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% NEQ 0 (
    echo ERRO: Nao foi possivel criar o banco de dados!
    echo Verifique se o MySQL esta rodando e se as credenciais estao corretas.
    pause
    exit /b 1
)

echo.
echo Importando banco.sql...
mysql -h %DB_HOST% -u %DB_USER% %DB_NAME% < banco.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Banco de dados criado com sucesso!
    echo ========================================
    echo.
    echo IMPORTANTE: Antes de usar, voce precisa:
    echo 1. Substituir os placeholders no banco.sql:
    echo    - admin_name
    echo    - admin_email  
    echo    - admin_password (use password_hash)
    echo    - admin_created_at
    echo.
    echo 2. Ou executar manualmente via phpMyAdmin
    echo    e editar o usuario admin depois.
    echo.
) else (
    echo.
    echo ERRO: Falha ao importar banco.sql!
    echo Verifique se o arquivo existe e se ha erros no SQL.
    echo.
)

pause

