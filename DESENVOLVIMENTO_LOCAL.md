# Desenvolvimento Local - Alterações em Tempo Real

## ✅ Sim, as alterações refletem automaticamente!

Quando você altera um arquivo PHP no XAMPP, as mudanças são aplicadas **imediatamente** na próxima requisição, sem precisar reiniciar o servidor.

---

## 🔄 Como Funciona

### Arquivos PHP
- ✅ **Alterações refletem imediatamente**
- Não precisa reiniciar Apache
- Basta recarregar a página no navegador (F5)

### Arquivos de Configuração
Alguns arquivos podem precisar de recarregamento:

#### 1. `application/.env`
- ⚠️ **Pode precisar limpar cache**
- O CodeIgniter pode cachear variáveis de ambiente
- **Solução:** Limpe o cache ou reinicie o Apache

#### 2. `application/config/*.php`
- ⚠️ **Pode precisar limpar cache**
- Arquivos de configuração podem ser cacheados
- **Solução:** Limpe o cache

#### 3. `php.ini`
- ❌ **Precisa reiniciar Apache**
- Alterações em `php.ini` só são aplicadas após reiniciar

---

## 🧪 Testando Alterações

### Método 1: Recarregar Página
```
1. Faça alteração no arquivo
2. Salve o arquivo (Ctrl+S)
3. Recarregue a página no navegador (F5 ou Ctrl+R)
4. As alterações devem aparecer
```

### Método 2: Hard Refresh (Limpar Cache do Navegador)
```
Windows/Linux: Ctrl + Shift + R
Mac: Cmd + Shift + R
```

### Método 3: Limpar Cache do CodeIgniter
```php
// Acesse via terminal
php index.php tools clear_cache

// Ou delete manualmente
rm -rf application/cache/*
```

---

## 🔍 Verificando se a Alteração Foi Aplicada

### 1. Adicione um Debug Temporário
```php
// No arquivo que você alterou
echo "<!-- ARQUIVO ALTERADO EM: " . date('Y-m-d H:i:s') . " -->";
```

### 2. Verifique o Source da Página
- Clique com botão direito → "Ver código-fonte"
- Procure pelo comentário que você adicionou

### 3. Use var_dump() ou print_r()
```php
// Adicione temporariamente
var_dump("Teste de alteração");
die();
```

---

## ⚠️ Quando Pode NÃO Funcionar

### 1. Cache do Navegador
- **Problema:** Navegador está mostrando versão antiga
- **Solução:** Hard refresh (Ctrl+Shift+R) ou limpar cache

### 2. Cache do CodeIgniter
- **Problema:** CodeIgniter está usando cache antigo
- **Solução:** Limpar pasta `application/cache/`

### 3. Opcache do PHP (se habilitado)
- **Problema:** PHP está usando versão compilada antiga
- **Solução:** Reiniciar Apache ou desabilitar opcache em desenvolvimento

### 4. Erro de Sintaxe
- **Problema:** Erro PHP impede o arquivo de ser executado
- **Solução:** Verificar logs de erro (`application/logs/`)

---

## 🛠️ Dicas para Desenvolvimento

### 1. Desabilitar Cache em Desenvolvimento

**No `application/.env`:**
```env
APP_ENVIRONMENT=development
APP_COMPRESS_OUTPUT=false
```

### 2. Limpar Cache Automaticamente

Crie um script `limpar_cache.bat`:
```batch
@echo off
echo Limpando cache...
del /Q application\cache\*.*
echo Cache limpo!
pause
```

### 3. Verificar Logs de Erro

Sempre verifique os logs quando algo não funcionar:
```
application/logs/log-YYYY-MM-DD.php
```

### 4. Usar Modo Debug

No `application/.env`:
```env
APP_ENVIRONMENT=development
WHOOPS_ERROR_PAGE_ENABLED=true
```

---

## 🔄 Fluxo de Desenvolvimento Recomendado

```
1. Faça alteração no arquivo
   ↓
2. Salve o arquivo (Ctrl+S)
   ↓
3. Recarregue a página (F5)
   ↓
4. Se não aparecer:
   - Tente Hard Refresh (Ctrl+Shift+R)
   - Limpe cache do CodeIgniter
   - Verifique logs de erro
   - Verifique se não há erro de sintaxe
```

---

## 📝 Exemplo Prático

### Testando uma Alteração

**1. Altere um arquivo:**
```php
// application/controllers/Mapos.php
public function index()
{
    echo "TESTE DE ALTERAÇÃO - " . date('H:i:s');
    // ... resto do código
}
```

**2. Salve o arquivo**

**3. Recarregue a página:**
- Acesse: `http://localhost/mapos`
- Você deve ver "TESTE DE ALTERAÇÃO" imediatamente

**4. Se não aparecer:**
- Verifique se salvou o arquivo
- Tente Ctrl+Shift+R (hard refresh)
- Verifique se não há erro de sintaxe
- Verifique logs: `application/logs/`

---

## 🚨 Problemas Comuns

### "A alteração não aparece"
1. ✅ Verifique se salvou o arquivo
2. ✅ Tente hard refresh (Ctrl+Shift+R)
3. ✅ Limpe cache: `application/cache/`
4. ✅ Verifique logs de erro
5. ✅ Verifique se não há erro de sintaxe

### "Erro 500 após alteração"
1. ✅ Verifique sintaxe do PHP
2. ✅ Verifique logs: `application/logs/`
3. ✅ Verifique se não quebrou alguma dependência
4. ✅ Desfaça a alteração e teste novamente

### "Cache não limpa"
1. ✅ Delete manualmente: `application/cache/*`
2. ✅ Reinicie o Apache
3. ✅ Verifique permissões da pasta cache

---

## ✅ Checklist Rápido

Antes de testar uma alteração:

- [ ] Arquivo foi salvo?
- [ ] Não há erro de sintaxe?
- [ ] Cache foi limpo (se necessário)?
- [ ] Navegador foi recarregado (F5 ou Ctrl+Shift+R)?
- [ ] Logs foram verificados (se houver erro)?

---

## 🎯 Resumo

**SIM, as alterações refletem automaticamente!**

- ✅ Arquivos PHP: Refletem imediatamente
- ✅ Arquivos de view: Refletem imediatamente
- ⚠️ Arquivos de config: Podem precisar limpar cache
- ❌ php.ini: Precisa reiniciar Apache

**Dica:** Se não aparecer, sempre tente:
1. Hard refresh (Ctrl+Shift+R)
2. Limpar cache do CodeIgniter
3. Verificar logs de erro

