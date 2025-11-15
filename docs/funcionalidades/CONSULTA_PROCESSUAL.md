# 📋 Consulta Processual - Documentação

## 🎯 Visão Geral

O sistema de Consulta Processual permite consultar processos diretamente na API pública do CNJ/DataJud, importar movimentações e sincronizar dados automaticamente.

## 🔧 Como Funciona

### 1. **Integração com API CNJ/DataJud**

O sistema utiliza a API pública do CNJ (Conselho Nacional de Justiça) através do DataJud para consultar processos de todos os tribunais do Brasil.

**Endpoint da API:**
```
https://api-publica.datajud.cnj.jus.br/api/v1/processo/{tribunal}/{numero}
```

### 2. **Fluxo de Consulta**

```
1. Usuário informa número do processo e tribunal
2. Sistema limpa o número (remove formatação)
3. Faz requisição para API do CNJ
4. Retorna dados do processo (movimentações, partes, etc.)
5. Opcionalmente importa movimentações para o banco
```

### 3. **Componentes do Sistema**

#### **Service: `ConsultaProcessualService`**
- Gerencia todas as consultas à API
- Implementa cache (1 hora)
- Importa movimentações automaticamente
- Trata erros e validações

#### **Controller: `ConsultaProcessualController`**
- Recebe requisições do usuário
- Valida dados de entrada
- Chama o service para consultar
- Retorna resultados formatados

#### **Model: `MovimentacaoProcessual`**
- Armazena movimentações importadas
- Campo `importado_api` identifica origem
- Campo `dados_api` armazena JSON completo

## 📝 Como Usar

### **Consulta Manual**

1. Acesse: **Admin > Consulta Processual**
2. Informe o número do processo (com ou sem formatação)
3. Selecione o tribunal (ou deixe auto-detectar)
4. Clique em "Consultar"
5. Visualize os detalhes e movimentações

### **Sincronização Automática**

Para sincronizar um processo já cadastrado:

1. Acesse o processo em **Admin > Processos**
2. Clique em "Sincronizar com API"
3. O sistema importará novas movimentações automaticamente

### **Formato do Número do Processo**

O sistema aceita números com ou sem formatação:

- **Com formatação:** `0000123-45.2023.8.26.0100`
- **Sem formatação:** `00001234520238260100`

O sistema remove automaticamente caracteres não numéricos.

## 🔑 Configuração

### **Chave da API**

Configure a chave da API no arquivo `.env`:

```env
API_CNJ_KEY=sua_chave_aqui
```

**Como obter a chave:**
1. Acesse: https://api-publica.datajud.cnj.jus.br/
2. Faça cadastro/login
3. Gere uma chave de API
4. Adicione no `.env`

### **Códigos dos Tribunais**

| Código | Tribunal |
|--------|----------|
| 01 | STF - Supremo Tribunal Federal |
| 02 | STJ - Superior Tribunal de Justiça |
| 03 | STM - Superior Tribunal Militar |
| 04 | TST - Tribunal Superior do Trabalho |
| 05 | TSE - Tribunal Superior Eleitoral |
| 07 | Justiça Federal |
| 08 | Justiça do Trabalho |
| 09 | Justiça Eleitoral |
| 10 | Justiça Militar |
| 11 | Justiça Estadual |

## 🚀 Funcionalidades

### **1. Consulta de Processo**
- Busca dados completos do processo
- Exibe movimentações, partes, valores
- Cache de 1 hora para otimização

### **2. Importação de Movimentações**
- Importa movimentações automaticamente
- Evita duplicatas
- Armazena dados completos da API

### **3. Sincronização**
- Sincroniza processos cadastrados
- Atualiza última movimentação
- Importa apenas movimentações novas

### **4. Auto-detecção de Tribunal**
- Detecta tribunal automaticamente do número
- Facilita consultas rápidas

## 📊 Estrutura de Dados

### **Resposta da API**

```json
{
  "numero": "0000123-45.2023.8.26.0100",
  "classe": "Ação de Cobrança",
  "assunto": "Cobrança",
  "partes": [...],
  "movimentos": [...],
  "valor": 1000.00,
  "situacao": "Em andamento"
}
```

### **Movimentação Importada**

```php
[
    'processo_id' => 1,
    'user_id' => 1,
    'titulo' => 'Juntada de Petição',
    'descricao' => 'Descrição da movimentação',
    'data' => '2023-01-15',
    'origem' => 'api_cnj',
    'dados_api' => [...], // JSON completo
    'importado_api' => true,
]
```

## ⚙️ Otimizações

### **Cache**
- Consultas são cacheadas por 1 hora
- Reduz chamadas à API
- Melhora performance

### **Tratamento de Erros**
- Validação de dados
- Mensagens de erro claras
- Logs detalhados

### **Rate Limiting**
- Respeita limites da API
- Timeout de 30 segundos
- Retry automático (futuro)

## 🔄 Agendamento Automático

Para sincronizar processos automaticamente, configure no `app/Console/Kernel.php`:

```php
$schedule->command('processos:consultar-tribunais')->daily();
```

## 📝 Logs

Todos os erros e consultas são registrados em:
- `storage/logs/laravel.log`

## 🐛 Troubleshooting

### **Erro 401 - Não Autorizado**
- Verifique se `API_CNJ_KEY` está configurada no `.env`
- Confirme se a chave está válida

### **Erro 404 - Processo Não Encontrado**
- Verifique o número do processo
- Confirme se o tribunal está correto
- Processo pode não estar disponível na API

### **Erro 429 - Muitas Requisições**
- Aguarde alguns minutos
- O cache ajuda a reduzir requisições

### **Timeout**
- Verifique conexão com internet
- API pode estar lenta
- Tente novamente mais tarde

## 🔐 Segurança

- Chave da API armazenada em `.env`
- Validação de dados de entrada
- Sanitização de números de processo
- Logs de auditoria

## 📚 Referências

- [API DataJud](https://api-publica.datajud.cnj.jus.br/)
- [Documentação CNJ](https://www.cnj.jus.br/)
- [Formato Número CNJ](https://www.cnj.jus.br/numero-unico-processo/)

