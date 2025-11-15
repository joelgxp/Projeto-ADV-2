# Como Executar o banco.sql

Este guia explica as diferentes formas de executar o arquivo `banco.sql` para criar o banco de dados do MapOS.

## 📋 Pré-requisitos

- MySQL ou MariaDB instalado e rodando
- Banco de dados criado (ou será criado automaticamente)
- Acesso ao servidor MySQL (via phpMyAdmin, linha de comando ou script)

---

## 🎯 Método 1: Via Instalador Web (Recomendado)

**Esta é a forma mais fácil e recomendada!**

1. Acesse: `http://localhost/mapos/install`
2. Preencha o formulário com:
   - Dados do banco de dados
   - Dados do administrador
3. O sistema executará o `banco.sql` automaticamente
4. Pronto! O banco será criado com todas as substituições necessárias

**Vantagens:**
- ✅ Substitui automaticamente os placeholders
- ✅ Cria o arquivo `.env` automaticamente
- ✅ Interface amigável
- ✅ Validações automáticas

---

## 🖥️ Método 2: Via phpMyAdmin (Manual)

### Passo 1: Criar o Banco de Dados

1. Acesse: `http://localhost/phpmyadmin`
2. Clique em **"Novo"** ou **"New"** no menu lateral
3. Nome do banco: `mapos`
4. Collation: `utf8mb4_unicode_ci`
5. Clique em **"Criar"**

### Passo 2: Editar o banco.sql (IMPORTANTE!)

Antes de importar, você precisa substituir os placeholders no arquivo `banco.sql`:

**Opção A: Editar manualmente**
1. Abra `banco.sql` em um editor de texto
2. Procure pela linha 658 (INSERT INTO usuarios)
3. Substitua:
   - `admin_name` → Seu nome completo
   - `admin_email` → Seu email
   - `admin_password` → Hash da senha (veja abaixo)
   - `admin_created_at` → Data atual (ex: `2025-11-15 10:30:00`)

**Opção B: Gerar hash da senha**
```php
<?php
echo password_hash('sua_senha_aqui', PASSWORD_DEFAULT);
?>
```

### Passo 3: Importar o SQL

1. Selecione o banco `mapos` criado
2. Clique na aba **"Importar"** ou **"Import"**
3. Clique em **"Escolher arquivo"** e selecione `banco.sql`
4. Clique em **"Executar"** ou **"Go"**
5. Aguarde a importação terminar

---

## 💻 Método 3: Via Linha de Comando (Windows)

### Usando o script criado:

```batch
executar_banco.bat
```

### Ou manualmente:

```batch
REM Criar banco
mysql -u root -e "CREATE DATABASE IF NOT EXISTS mapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Importar SQL
mysql -u root mapos < banco.sql
```

**⚠️ IMPORTANTE:** Antes de executar, edite o `banco.sql` e substitua os placeholders!

---

## 🐧 Método 4: Via Linha de Comando (Linux/Mac)

### Usando o script criado:

```bash
chmod +x executar_banco.sh
./executar_banco.sh
```

### Ou manualmente:

```bash
# Criar banco
mysql -u root -e "CREATE DATABASE IF NOT EXISTS mapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar SQL
mysql -u root mapos < banco.sql
```

**⚠️ IMPORTANTE:** Antes de executar, edite o `banco.sql` e substitua os placeholders!

---

## 🚀 Método 5: Via Script PHP (Automático)

**Este é o método mais completo! Faz tudo automaticamente.**

### Executar:

```bash
php executar_banco.php
```

### O que o script faz:

1. ✅ Conecta ao MySQL
2. ✅ Cria o banco de dados automaticamente
3. ✅ Lê o arquivo `banco.sql`
4. ✅ Substitui os placeholders automaticamente:
   - `admin_name` → Nome configurado
   - `admin_email` → Email configurado
   - `admin_password` → Hash gerado automaticamente
   - `admin_created_at` → Data atual
5. ✅ Executa todas as queries
6. ✅ Verifica se tudo foi criado corretamente

### Configurar o script:

Edite o arquivo `executar_banco.php` e ajuste as variáveis:

```php
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '', // Sua senha do MySQL
    'database' => 'mapos',
    
    // Dados do administrador
    'admin_name' => 'Seu Nome',
    'admin_email' => 'seu@email.com',
    'admin_password' => 'sua_senha',
    'admin_created_at' => date('Y-m-d H:i:s'),
];
```

---

## 📝 Placeholders que Precisam ser Substituídos

No arquivo `banco.sql`, linha 658, você encontrará:

```sql
INSERT INTO `usuarios` VALUES
(1, 'admin_name', ..., 'admin_email', 'admin_password', ..., 'admin_created_at', ...);
```

**Substitua:**
- `admin_name` → Nome completo do administrador
- `admin_email` → Email do administrador (será usado para login)
- `admin_password` → Hash da senha (use `password_hash()`)
- `admin_created_at` → Data/hora atual (formato: `YYYY-MM-DD HH:MM:SS`)

---

## ✅ Verificação Pós-Instalação

Após executar o banco, verifique:

1. **Tabelas criadas:**
   ```sql
   SHOW TABLES;
   ```
   Deve mostrar todas as tabelas do sistema.

2. **Usuário admin:**
   ```sql
   SELECT * FROM usuarios WHERE idUsuarios = 1;
   ```
   Deve mostrar o usuário administrador criado.

3. **Permissões:**
   ```sql
   SELECT * FROM permissoes WHERE idPermissao = 1;
   ```
   Deve mostrar a permissão de Administrador.

---

## 🔧 Solução de Problemas

### Erro: "Unknown database"
- **Solução:** Crie o banco primeiro ou use o script PHP que cria automaticamente

### Erro: "Access denied"
- **Solução:** Verifique usuário e senha do MySQL

### Erro: "Table already exists"
- **Solução:** O banco já foi criado. Use `DROP DATABASE mapos;` para recriar (cuidado: apaga tudo!)

### Erro: "Foreign key constraint"
- **Solução:** Certifique-se de executar o SQL completo, não apenas partes

### Usuário admin não funciona
- **Solução:** Verifique se os placeholders foram substituídos corretamente
- Verifique se o hash da senha está correto

---

## 📚 Próximos Passos

Após criar o banco:

1. **Configure o `.env`:**
   - Copie `application/.env.example` para `application/.env`
   - Configure as credenciais do banco

2. **Acesse o sistema:**
   - URL: `http://localhost/mapos`
   - Email: O que você configurou
   - Senha: A senha que você definiu

3. **Faça login e configure:**
   - Vá em Configurações > Sistema
   - Configure emitente, email, etc.

---

## 🎯 Recomendação

**Para iniciantes:** Use o **Método 1 (Instalador Web)** ou **Método 5 (Script PHP)**

**Para desenvolvedores:** Use o **Método 5 (Script PHP)** para automação

**Para produção:** Use o **Método 1 (Instalador Web)** para garantir todas as validações

