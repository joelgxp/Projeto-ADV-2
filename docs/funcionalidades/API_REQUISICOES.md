# 📡 API - Requisições e Endpoints

## 🎯 Visão Geral

Documentação completa das requisições da API CNJ/DataJud e dos endpoints internos do sistema.

## 🔗 API Externa - CNJ/DataJud

### **Endpoint Base**
```
https://api-publica.datajud.cnj.jus.br/api/v1
```

### **1. Consultar Processo**

#### **Requisição**
```http
GET /api/v1/processo/{tribunal}/{numero}
Authorization: APIKey {sua_chave_api}
Accept: application/json
```

#### **Parâmetros**
- `tribunal` (string, obrigatório): Código do tribunal (2 dígitos)
  - Exemplo: `13` (Minas Gerais), `26` (São Paulo)
- `numero` (string, obrigatório): Número do processo (20 dígitos, sem formatação)
  - Exemplo: `50012348520238130139`

#### **Exemplo de Requisição**
```bash
curl -X GET "https://api-publica.datajud.cnj.jus.br/api/v1/processo/13/50012348520238130139" \
  -H "Authorization: APIKey sua_chave_aqui" \
  -H "Accept: application/json"
```

#### **Resposta de Sucesso (200)**
```json
{
  "numero": "5001234-85.2023.8.13.0139",
  "classe": "Ação de Cobrança",
  "assunto": "Cobrança",
  "situacao": "Em andamento",
  "valor": 1000.00,
  "partes": [
    {
      "tipo": "Autor",
      "nome": "João Silva",
      "documento": "12345678900"
    },
    {
      "tipo": "Réu",
      "nome": "Empresa XYZ Ltda",
      "documento": "12345678000190"
    }
  ],
  "movimentos": [
    {
      "id": "12345",
      "dataHora": "2023-01-15T10:30:00",
      "nome": "Juntada de Petição",
      "descricao": "Petição inicial juntada aos autos"
    }
  ],
  "vara": "1ª Vara Cível",
  "comarca": "Belo Horizonte"
}
```

#### **Respostas de Erro**

**400 - Bad Request**
```json
{
  "error": "Número de processo inválido"
}
```

**401 - Unauthorized**
```json
{
  "error": "Chave de API inválida ou ausente"
}
```

**404 - Not Found**
```json
{
  "error": "Processo não encontrado"
}
```

**429 - Too Many Requests**
```json
{
  "error": "Limite de requisições excedido"
}
```

## 🔧 API Interna - Sistema

### **Endpoint Base**
```
/api/v1
```

### **1. Consultar Processo (Interno)**

#### **Requisição**
```http
POST /api/v1/consulta-processual
Content-Type: application/json
```

#### **Body**
```json
{
  "numero_processo": "5001234-85.2023.8.13.0139",
  "tribunal": "13"
}
```

#### **Parâmetros**
- `numero_processo` (string, obrigatório): Número do processo (formatado ou não)
- `tribunal` (string, opcional): Código do tribunal (será extraído automaticamente se não informado)

#### **Exemplo de Requisição**
```bash
curl -X POST "http://localhost/api/v1/consulta-processual" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_processo": "5001234-85.2023.8.13.0139",
    "tribunal": "13"
  }'
```

#### **Resposta de Sucesso (200)**
```json
{
  "success": true,
  "data": {
    "numero": "5001234-85.2023.8.13.0139",
    "classe": "Ação de Cobrança",
    "assunto": "Cobrança",
    "situacao": "Em andamento",
    "valor": 1000.00,
    "partes": [...],
    "movimentos": [...],
    "numero_formatado": "5001234-85.2023.8.13.0139",
    "partes_processo": {
      "numero_sequencial": "5001234",
      "digito_verificador": "85",
      "ano": "2023",
      "segmento": "8",
      "tribunal": "13",
      "origem": "0139"
    }
  },
  "partes": {
    "numero_sequencial": "5001234",
    "digito_verificador": "85",
    "ano": "2023",
    "segmento": "8",
    "tribunal": "13",
    "origem": "0139",
    "numero_formatado": "5001234-85.2023.8.13.0139",
    "numero_limpo": "50012348520238130139"
  }
}
```

#### **Resposta de Erro (400)**
```json
{
  "success": false,
  "message": "Número de processo inválido: Formato inválido. Use o padrão: NNNNNNN-DD.AAAA.J.TR.OOOO",
  "erros": [
    "Formato inválido. Use o padrão: NNNNNNN-DD.AAAA.J.TR.OOOO"
  ]
}
```

### **2. Detalhes do Processo**

#### **Requisição**
```http
GET /api/v1/processos/{numero}/detalhes
```

#### **Parâmetros**
- `numero` (string, obrigatório): Número do processo (formatado ou não)

#### **Exemplo de Requisição**
```bash
curl -X GET "http://localhost/api/v1/processos/5001234-85.2023.8.13.0139/detalhes"
```

#### **Resposta**
Similar à resposta de consulta, mas com informações adicionais do processo cadastrado no sistema.

### **3. Histórico de Movimentações**

#### **Requisição**
```http
GET /api/v1/processos/{numero}/historico
```

#### **Parâmetros**
- `numero` (string, obrigatório): Número do processo

#### **Exemplo de Requisição**
```bash
curl -X GET "http://localhost/api/v1/processos/5001234-85.2023.8.13.0139/historico"
```

#### **Resposta**
```json
{
  "success": true,
  "movimentacoes": [
    {
      "id": "12345",
      "dataHora": "2023-01-15T10:30:00",
      "nome": "Juntada de Petição",
      "descricao": "Petição inicial juntada aos autos"
    }
  ]
}
```

## 🔐 Autenticação

### **API Externa (CNJ/DataJud)**
- **Tipo:** API Key
- **Header:** `Authorization: APIKey {sua_chave}`
- **Onde obter:** https://api-publica.datajud.cnj.jus.br/

### **API Interna**
- **Tipo:** Sanctum (para endpoints protegidos)
- **Header:** `Authorization: Bearer {token}`
- **Endpoints públicos:** Consulta processual básica

## 📋 Códigos de Status HTTP

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 400 | Requisição inválida |
| 401 | Não autorizado |
| 404 | Não encontrado |
| 429 | Muitas requisições |
| 500 | Erro interno do servidor |

## 🔄 Formato do Número de Processo

### **Padrão CNJ**
```
NNNNNNN-DD.AAAA.J.TR.OOOO
```

### **Exemplo**
```
5001234-85.2023.8.13.0139
```

### **Partes**
- `NNNNNNN`: Número sequencial (7 dígitos)
- `DD`: Dígito verificador (2 dígitos)
- `AAAA`: Ano (4 dígitos)
- `J`: Segmento da justiça (1 dígito)
- `TR`: Tribunal (2 dígitos)
- `OOOO`: Origem/Comarca (4 dígitos)

### **Aceita**
- Com formatação: `5001234-85.2023.8.13.0139`
- Sem formatação: `50012348520238130139`

## 🧪 Exemplos de Uso

### **JavaScript (Fetch)**
```javascript
// Consultar processo
fetch('/api/v1/consulta-processual', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    numero_processo: '5001234-85.2023.8.13.0139',
    tribunal: '13'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### **PHP (Guzzle)**
```php
use Illuminate\Support\Facades\Http;

$response = Http::post('/api/v1/consulta-processual', [
    'numero_processo' => '5001234-85.2023.8.13.0139',
    'tribunal' => '13'
]);

$data = $response->json();
```

### **Python (Requests)**
```python
import requests

response = requests.post('http://localhost/api/v1/consulta-processual', json={
    'numero_processo': '5001234-85.2023.8.13.0139',
    'tribunal': '13'
})

data = response.json()
```

## ⚠️ Limitações e Rate Limiting

### **API Externa (CNJ/DataJud)**
- Limite de requisições por minuto (verificar documentação oficial)
- Cache implementado (1 hora) para reduzir requisições
- Timeout: 30 segundos

### **API Interna**
- Rate limiting configurável via middleware
- Cache de consultas (1 hora)

## 📝 Notas Importantes

1. **Validação:** Todos os números de processo são validados antes da consulta
2. **Cache:** Consultas são cacheadas por 1 hora para otimização
3. **Dígito Verificador:** Validado automaticamente antes da consulta
4. **Tribunal:** Extraído automaticamente do número se não informado
5. **Formato:** Aceita números com ou sem formatação CNJ

## 🔗 Links Úteis

- [API DataJud CNJ](https://api-publica.datajud.cnj.jus.br/)
- [Documentação CNJ](https://www.cnj.jus.br/)
- [Formato Número CNJ](https://www.cnj.jus.br/numero-unico-processo/)







