# 🛠️ Ferramentas de Diagnóstico

Este diretório contém ferramentas de diagnóstico para verificar a estrutura do banco de dados e identificar problemas.

## 📋 Verificação de Estrutura do Banco

### Controller: `Diagnostico::verificar_estrutura()`

Este controller verifica se todas as tabelas e colunas necessárias estão presentes no banco de dados.

#### Como Usar

**Via Navegador:**
```
http://seu-dominio/diagnostico/verificar_estrutura
```

**Nota:** Este controller só funciona em ambiente de desenvolvimento. Em produção, retorna 404.

#### O que o script verifica:

- ✅ Existência de tabelas essenciais
- ✅ Existência de colunas em cada tabela
- ✅ Compatibilidade de tipos de dados
- ✅ Estrutura esperada vs. estrutura real

#### Exemplo de Saída:

- **Sucesso**: Todas as tabelas e colunas estão presentes
- **Problemas**: Lista detalhada de tabelas/colunas faltantes ou incompatíveis

---

## 🔍 Outras Formas de Verificar

### 1. Verificar Logs de Erro

Quando uma coluna ou tabela está faltando, o sistema gera erros nos logs:

**Localização dos logs:**
```
application/logs/log-YYYY-MM-DD.php
```

**Procurar por:**
- `Unknown column`
- `Table doesn't exist`
- `Field 'xxx' doesn't have a default value`

### 2. Verificar Diretamente no Banco

**MySQL/MariaDB:**
```sql
-- Verificar se uma tabela existe
SHOW TABLES LIKE 'email_queue';

-- Verificar colunas de uma tabela
DESCRIBE email_queue;
-- ou
SHOW COLUMNS FROM email_queue;

-- Comparar com banco_limpo.sql
-- Execute o banco_limpo.sql e compare a estrutura
```

### 3. Verificar Erros em Tempo de Execução

**No navegador (modo desenvolvimento):**
- Ative `display_errors` no `index.php`
- Os erros aparecerão diretamente na tela

**Via console do navegador:**
- Abra o DevTools (F12)
- Verifique a aba "Console" para erros JavaScript
- Verifique a aba "Network" para erros de requisições AJAX

### 4. Comparar com banco_limpo.sql

**Método manual:**
1. Abra `banco_limpo.sql`
2. Verifique a estrutura esperada
3. Compare com o banco atual usando:
   ```sql
   SHOW CREATE TABLE nome_da_tabela;
   ```

---

## ⚠️ Importante

- **NÃO** adicione validações de colunas/tabelas no código de produção
- **NÃO** use `table_exists()` ou `list_fields()` no código principal
- **SIM** use este script de diagnóstico quando necessário
- **SIM** corrija o `banco_limpo.sql` se encontrar problemas estruturais

---

## 📝 Estrutura Esperada

O script verifica as seguintes tabelas e colunas:

### Tabelas Principais:
- `email_queue` - Fila de e-mails
- `confirmacoes_email` - Confirmações de e-mail
- `tentativas_login` - Tentativas de login
- `bloqueios_conta` - Bloqueios de conta
- `logs` - Logs do sistema
- `usuarios` - Usuários do sistema

Para ver a lista completa, consulte `banco_limpo.sql`.

---

## 🚀 Próximos Passos

Se o script encontrar problemas:

1. **Identifique o problema** na saída do script
2. **Verifique se existe um script SQL** em `updates/` para corrigir
3. **Execute o script SQL** apropriado
4. **Ou atualize o `banco_limpo.sql`** e recrie o banco
5. **Execute o script novamente** para verificar se foi corrigido

---

## 🔒 Segurança

⚠️ **IMPORTANTE**: Este script expõe informações sobre a estrutura do banco de dados.

**Recomendações:**
- Use apenas em ambiente de desenvolvimento
- Remova ou proteja este diretório em produção
- Adicione autenticação se necessário
- Não exponha este script publicamente

