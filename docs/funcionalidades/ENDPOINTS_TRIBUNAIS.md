# 🔗 Endpoints dos Tribunais - API DataJud

## 📋 Visão Geral

Documentação completa dos endpoints da API DataJud organizados por segmento da justiça.

## 🌐 Base URL
```
https://api-publica.datajud.cnj.jus.br
```

## 📊 Formato dos Endpoints

Todos os endpoints seguem o padrão:
```
/{endpoint}/_search
```

Onde `{endpoint}` varia conforme o tribunal.

## 🏛️ Tribunais Superiores

| Tribunal | Código TR | Endpoint |
|----------|-----------|----------|
| STF/STJ/TSE/TST/STM/CJF/CNJ/CSJT | 90 | `api_publica_stf` |

**Nota:** O código TR 90 é usado genericamente para todos os Tribunais Superiores (STF, STJ, TSE, TST, STM, CJF, CNJ, CSJT). A identificação específica do tribunal pode requerer análise adicional do número do processo.

**URL Completa:**
- Tribunais Superiores: `https://api-publica.datajud.cnj.jus.br/api_publica_stf/_search`

## ⚖️ Justiça Federal

| Tribunal | Código | Endpoint |
|----------|--------|----------|
| TRF 1ª Região | 01 | `api_publica_trf1` |
| TRF 2ª Região | 02 | `api_publica_trf2` |
| TRF 3ª Região | 03 | `api_publica_trf3` |
| TRF 4ª Região | 04 | `api_publica_trf4` |
| TRF 5ª Região | 05 | `api_publica_trf5` |
| TRF 6ª Região | 06 | `api_publica_trf6` |

**URL Completa:**
- TRF1: `https://api-publica.datajud.cnj.jus.br/api_publica_trf1/_search`
- TRF2: `https://api-publica.datajud.cnj.jus.br/api_publica_trf2/_search`
- TRF3: `https://api-publica.datajud.cnj.jus.br/api_publica_trf3/_search`
- TRF4: `https://api-publica.datajud.cnj.jus.br/api_publica_trf4/_search`
- TRF5: `https://api-publica.datajud.cnj.jus.br/api_publica_trf5/_search`
- TRF6: `https://api-publica.datajud.cnj.jus.br/api_publica_trf6/_search`

## 🏛️ Justiça Estadual

| Tribunal | Código TR | Endpoint |
|----------|-----------|----------|
| TJAC - Acre | 01 | `api_publica_tjac` |
| TJAL - Alagoas | 02 | `api_publica_tjal` |
| TJAP - Amapá | 03 | `api_publica_tjap` |
| TJAM - Amazonas | 04 | `api_publica_tjam` |
| TJBA - Bahia | 05 | `api_publica_tjba` |
| TJCE - Ceará | 06 | `api_publica_tjce` |
| TJDFT - Distrito Federal | 07 | `api_publica_tjdft` |
| TJES - Espírito Santo | 08 | `api_publica_tjes` |
| TJGO - Goiás | 09 | `api_publica_tjgo` |
| TJMA - Maranhão | 10 | `api_publica_tjma` |
| TJMT - Mato Grosso | 11 | `api_publica_tjmt` |
| TJMS - Mato Grosso do Sul | 12 | `api_publica_tjms` |
| TJMG - Minas Gerais | 13 | `api_publica_tjmg` |
| TJPA - Pará | 14 | `api_publica_tjpa` |
| TJPB - Paraíba | 15 | `api_publica_tjpb` |
| TJPR - Paraná | 16 | `api_publica_tjpr` |
| TJPE - Pernambuco | 17 | `api_publica_tjpe` |
| TJPI - Piauí | 18 | `api_publica_tjpi` |
| TJRJ - Rio de Janeiro | 19 | `api_publica_tjrj` |
| TJRN - Rio Grande do Norte | 20 | `api_publica_tjrn` |
| TJRS - Rio Grande do Sul | 21 | `api_publica_tjrs` |
| TJRO - Rondônia | 22 | `api_publica_tjro` |
| TJRR - Roraima | 23 | `api_publica_tjrr` |
| TJSC - Santa Catarina | 24 | `api_publica_tjsc` |
| TJSE - Sergipe | 25 | `api_publica_tjse` |
| TJSP - São Paulo | 26 | `api_publica_tjsp` |
| TJTO - Tocantins | 27 | `api_publica_tjto` |

## 💼 Justiça do Trabalho

| Tribunal | Código | Endpoint |
|----------|--------|----------|
| TRT 1ª Região (RJ) | 01 | `api_publica_trt1` |
| TRT 2ª Região (SP) | 02 | `api_publica_trt2` |
| TRT 3ª Região (MG) | 03 | `api_publica_trt3` |
| TRT 4ª Região (RS) | 04 | `api_publica_trt4` |
| TRT 5ª Região (BA) | 05 | `api_publica_trt5` |
| TRT 6ª Região (PE) | 06 | `api_publica_trt6` |
| TRT 7ª Região (CE) | 07 | `api_publica_trt7` |
| TRT 8ª Região (PA/AP) | 08 | `api_publica_trt8` |
| TRT 9ª Região (PR) | 09 | `api_publica_trt9` |
| TRT 10ª Região (DF/TO) | 10 | `api_publica_trt10` |
| TRT 11ª Região (AM/RR) | 11 | `api_publica_trt11` |
| TRT 12ª Região (SC) | 12 | `api_publica_trt12` |
| TRT 13ª Região (PB) | 13 | `api_publica_trt13` |
| TRT 14ª Região (RO/AC) | 14 | `api_publica_trt14` |
| TRT 15ª Região (SP - Campinas) | 15 | `api_publica_trt15` |
| TRT 16ª Região (MA) | 16 | `api_publica_trt16` |
| TRT 17ª Região (ES) | 17 | `api_publica_trt17` |
| TRT 18ª Região (GO) | 18 | `api_publica_trt18` |
| TRT 19ª Região (AL) | 19 | `api_publica_trt19` |
| TRT 20ª Região (SE) | 20 | `api_publica_trt20` |
| TRT 21ª Região (RN) | 21 | `api_publica_trt21` |
| TRT 22ª Região (PI) | 22 | `api_publica_trt22` |
| TRT 23ª Região (MT) | 23 | `api_publica_trt23` |
| TRT 24ª Região (MS) | 24 | `api_publica_trt24` |

## 🗳️ Justiça Eleitoral

| Tribunal | Código TR | Endpoint |
|----------|-----------|----------|
| TRE-AC - Acre | 01 | `api_publica_tre-ac` |
| TRE-AL - Alagoas | 02 | `api_publica_tre-al` |
| TRE-AP - Amapá | 03 | `api_publica_tre-ap` |
| TRE-AM - Amazonas | 04 | `api_publica_tre-am` |
| TRE-BA - Bahia | 05 | `api_publica_tre-ba` |
| TRE-CE - Ceará | 06 | `api_publica_tre-ce` |
| TRE-DF - Distrito Federal | 07 | `api_publica_tre-dft` |
| TRE-ES - Espírito Santo | 08 | `api_publica_tre-es` |
| TRE-GO - Goiás | 09 | `api_publica_tre-go` |
| TRE-MA - Maranhão | 10 | `api_publica_tre-ma` |
| TRE-MT - Mato Grosso | 11 | `api_publica_tre-mt` |
| TRE-MS - Mato Grosso do Sul | 12 | `api_publica_tre-ms` |
| TRE-MG - Minas Gerais | 13 | `api_publica_tre-mg` |
| TRE-PA - Pará | 14 | `api_publica_tre-pa` |
| TRE-PB - Paraíba | 15 | `api_publica_tre-pb` |
| TRE-PR - Paraná | 16 | `api_publica_tre-pr` |
| TRE-PE - Pernambuco | 17 | `api_publica_tre-pe` |
| TRE-PI - Piauí | 18 | `api_publica_tre-pi` |
| TRE-RJ - Rio de Janeiro | 19 | `api_publica_tre-rj` |
| TRE-RN - Rio Grande do Norte | 20 | `api_publica_tre-rn` |
| TRE-RS - Rio Grande do Sul | 21 | `api_publica_tre-rs` |
| TRE-RO - Rondônia | 22 | `api_publica_tre-ro` |
| TRE-RR - Roraima | 23 | `api_publica_tre-rr` |
| TRE-SC - Santa Catarina | 24 | `api_publica_tre-sc` |
| TRE-SE - Sergipe | 25 | `api_publica_tre-se` |
| TRE-SP - São Paulo | 26 | `api_publica_tre-sp` |
| TRE-TO - Tocantins | 27 | `api_publica_tre-to` |

## ⚔️ Justiça Militar

| Tribunal | Código TR | Endpoint |
|----------|-----------|----------|
| STM - Superior Tribunal Militar | 10 | `api_publica_stm` |
| TJM-MG - Tribunal de Justiça Militar de Minas Gerais | 13 | `api_publica_tjmmg` |
| TJM-RS - Tribunal de Justiça Militar do Rio Grande do Sul | 21 | `api_publica_tjmrs` |
| TJM-SP - Tribunal de Justiça Militar de São Paulo | 26 | `api_publica_tjmsp` |

## 🔧 Uso no Sistema

O sistema detecta automaticamente o segmento e tribunal do número do processo CNJ e usa o endpoint correto.

### Exemplo de Requisição

```php
use App\Helpers\TribunaisEndpointsHelper;

// Obter URL completa
$url = TribunaisEndpointsHelper::getUrlCompleta('8', '13'); 
// Retorna: https://api-publica.datajud.cnj.jus.br/api_publica_tjmg/_search

// Requisição POST
$response = Http::post($url, [
    'query' => [
        'match' => [
            'numeroProcesso' => '50012348520238130139'
        ]
    ]
]);
```

## 📝 Notas Importantes

1. **Formato da Requisição:** A API usa formato Elasticsearch com requisições POST
2. **Body da Requisição:** Deve conter query de busca com o número do processo
3. **Autenticação:** Requer API Key no header `Authorization: APIKey {chave}`
4. **Detecção Automática:** O sistema detecta segmento e tribunal do número CNJ

## 🔗 Referências

- [API DataJud CNJ](https://api-publica.datajud.cnj.jus.br/)
- [Documentação CNJ](https://www.cnj.jus.br/)

