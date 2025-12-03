<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Helper para detectar tribunal e obter endpoints da API CNJ/DataJud
 */
if (!function_exists('detectar_tribunal_cnj')) {
    /**
     * Detecta o tribunal a partir do número CNJ
     * 
     * @param string $numeroProcesso Número do processo (formatado ou limpo)
     * @return array Array com segmento, tribunal e origem
     */
    function detectar_tribunal_cnj($numeroProcesso)
    {
        // Remove formatação
        $numeroLimpo = preg_replace('/[^0-9]/', '', $numeroProcesso);
        
        if (strlen($numeroLimpo) != 20) {
            return false;
        }
        
        // Extrai partes do número CNJ: NNNNNNN-DD.AAAA.J.TR.OOOO
        $sequencial = substr($numeroLimpo, 0, 7);
        $digito = substr($numeroLimpo, 7, 2);
        $ano = substr($numeroLimpo, 9, 4);
        $segmento = substr($numeroLimpo, 13, 1);
        $tribunal = substr($numeroLimpo, 14, 2);
        $origem = substr($numeroLimpo, 16, 4);
        
        return [
            'sequencial' => $sequencial,
            'digito' => $digito,
            'ano' => $ano,
            'segmento' => $segmento,
            'tribunal' => $tribunal,
            'origem' => $origem,
            'numero_limpo' => $numeroLimpo,
            'numero_formatado' => formatar_numero_cnj($numeroLimpo)
        ];
    }
}

if (!function_exists('formatar_numero_cnj')) {
    /**
     * Formata número CNJ no padrão: NNNNNNN-DD.AAAA.J.TR.OOOO
     * 
     * @param string $numeroLimpo Número sem formatação (20 dígitos)
     * @return string Número formatado
     */
    function formatar_numero_cnj($numeroLimpo)
    {
        $numeroLimpo = preg_replace('/[^0-9]/', '', $numeroLimpo);
        
        if (strlen($numeroLimpo) != 20) {
            return $numeroLimpo;
        }
        
        return substr($numeroLimpo, 0, 7) . '-' . 
               substr($numeroLimpo, 7, 2) . '.' . 
               substr($numeroLimpo, 9, 4) . '.' . 
               substr($numeroLimpo, 13, 1) . '.' . 
               substr($numeroLimpo, 14, 2) . '.' . 
               substr($numeroLimpo, 16, 4);
    }
}

if (!function_exists('normalizar_numero_processo')) {
    /**
     * Normaliza número de processo removendo formatação
     * 
     * @param string $numeroProcesso Número com ou sem formatação
     * @return string Número limpo (20 dígitos)
     */
    function normalizar_numero_processo($numeroProcesso)
    {
        return preg_replace('/[^0-9]/', '', $numeroProcesso);
    }
}

if (!function_exists('obter_nome_segmento')) {
    /**
     * Obtém o nome do segmento a partir do código numérico
     * 
     * @param string $codigo Código do segmento (1-9)
     * @return string Nome do segmento
     */
    function obter_nome_segmento($codigo)
    {
        $segmentos = [
            '1' => 'Supremo Tribunal Federal (STF)',
            '2' => 'Conselho Nacional de Justiça (CNJ)',
            '3' => 'Superior Tribunal de Justiça (STJ)',
            '4' => 'Justiça Federal',
            '5' => 'Justiça do Trabalho',
            '6' => 'Justiça Eleitoral',
            '7' => 'Justiça Militar da União',
            '8' => 'Justiça dos Estados e do Distrito Federal e Territórios',
            '9' => 'Justiça Militar Estadual',
        ];
        
        $codigo = (string) $codigo;
        return $segmentos[$codigo] ?? 'Segmento ' . $codigo;
    }
}

if (!function_exists('obter_nome_tribunal')) {
    /**
     * Obtém o nome do tribunal a partir do código numérico
     * 
     * @param string $codigo Código do tribunal (2 dígitos)
     * @param string|null $segmento Código do segmento para melhor identificação
     * @return string Nome do tribunal
     */
    function obter_nome_tribunal($codigo, $segmento = null)
    {
        $codigo = str_pad((string) $codigo, 2, '0', STR_PAD_LEFT);
        
        // Mapeamento geral de tribunais
        $tribunais = [
            '00' => 'Supremo Tribunal Federal (STF)',
            '90' => 'Superior Tribunal de Justiça (STJ) / Tribunal Superior do Trabalho (TST) / Tribunal Superior Eleitoral (TSE)',
            '01' => 'Tribunal Regional Federal da 1ª Região (TRF1) / Tribunal de Justiça do Acre (TJ-AC) / Tribunal Regional do Trabalho da 1ª Região (TRT1) / Tribunal Regional Eleitoral do Acre (TRE-AC)',
            '02' => 'Tribunal Regional Federal da 2ª Região (TRF2) / Tribunal de Justiça de Alagoas (TJ-AL) / Tribunal Regional do Trabalho da 2ª Região (TRT2) / Tribunal Regional Eleitoral de Alagoas (TRE-AL)',
            '03' => 'Tribunal Regional Federal da 3ª Região (TRF3) / Tribunal de Justiça do Amapá (TJ-AP) / Tribunal Regional do Trabalho da 3ª Região (TRT3) / Tribunal Regional Eleitoral do Amapá (TRE-AP)',
            '04' => 'Tribunal Regional Federal da 4ª Região (TRF4) / Tribunal de Justiça do Amazonas (TJ-AM) / Tribunal Regional do Trabalho da 4ª Região (TRT4) / Tribunal Regional Eleitoral do Amazonas (TRE-AM)',
            '05' => 'Tribunal Regional Federal da 5ª Região (TRF5) / Tribunal de Justiça da Bahia (TJ-BA) / Tribunal Regional do Trabalho da 5ª Região (TRT5) / Tribunal Regional Eleitoral da Bahia (TRE-BA)',
            '06' => 'Tribunal Regional Federal da 6ª Região (TRF6) / Tribunal de Justiça do Ceará (TJ-CE) / Tribunal Regional do Trabalho da 6ª Região (TRT6) / Tribunal Regional Eleitoral do Ceará (TRE-CE)',
            '07' => 'Tribunal de Justiça do Distrito Federal e Territórios (TJ-DFT) / Tribunal Regional do Trabalho da 7ª Região (TRT7) / Tribunal Regional Eleitoral do Distrito Federal (TRE-DF)',
            '08' => 'Tribunal de Justiça do Espírito Santo (TJ-ES) / Tribunal Regional do Trabalho da 8ª Região (TRT8) / Tribunal Regional Eleitoral do Espírito Santo (TRE-ES)',
            '09' => 'Tribunal de Justiça de Goiás (TJ-GO) / Tribunal Regional do Trabalho da 9ª Região (TRT9) / Tribunal Regional Eleitoral de Goiás (TRE-GO)',
            '10' => 'Superior Tribunal Militar (STM) / Tribunal de Justiça do Maranhão (TJ-MA) / Tribunal Regional do Trabalho da 10ª Região (TRT10) / Tribunal Regional Eleitoral do Maranhão (TRE-MA)',
            '11' => 'Tribunal de Justiça de Mato Grosso (TJ-MT) / Tribunal Regional do Trabalho da 11ª Região (TRT11) / Tribunal Regional Eleitoral de Mato Grosso (TRE-MT)',
            '12' => 'Tribunal de Justiça de Mato Grosso do Sul (TJ-MS) / Tribunal Regional do Trabalho da 12ª Região (TRT12) / Tribunal Regional Eleitoral de Mato Grosso do Sul (TRE-MS)',
            '13' => 'Tribunal de Justiça de São Paulo (TJ-SP) / Tribunal Regional do Trabalho da 2ª Região (TRT2)',
            '14' => 'Tribunal de Justiça de Rondônia (TJ-RO) / Tribunal Regional do Trabalho da 14ª Região (TRT14) / Tribunal Regional Eleitoral de Rondônia (TRE-RO)',
            '15' => 'Tribunal de Justiça de Roraima (TJ-RR) / Tribunal Regional do Trabalho da 15ª Região (TRT15) / Tribunal Regional Eleitoral de Roraima (TRE-RR)',
            '16' => 'Tribunal de Justiça de Pernambuco (TJ-PE) / Tribunal Regional do Trabalho da 6ª Região (TRT6) / Tribunal Regional Eleitoral de Pernambuco (TRE-PE)',
            '17' => 'Tribunal de Justiça de Tocantins (TJ-TO) / Tribunal Regional do Trabalho da 10ª Região (TRT10) / Tribunal Regional Eleitoral de Tocantins (TRE-TO)',
            '18' => 'Tribunal de Justiça do Sergipe (TJ-SE) / Tribunal Regional do Trabalho da 5ª Região (TRT5) / Tribunal Regional Eleitoral de Sergipe (TRE-SE)',
            '19' => 'Tribunal de Justiça da Paraíba (TJ-PB) / Tribunal Regional do Trabalho da 13ª Região (TRT13) / Tribunal Regional Eleitoral da Paraíba (TRE-PB)',
            '20' => 'Tribunal de Justiça do Rio Grande do Norte (TJ-RN) / Tribunal Regional do Trabalho da 21ª Região (TRT21) / Tribunal Regional Eleitoral do Rio Grande do Norte (TRE-RN)',
            '21' => 'Tribunal de Justiça Militar de Minas Gerais (TJM-MG) / Tribunal de Justiça de Minas Gerais (TJ-MG) / Tribunal Regional do Trabalho da 3ª Região (TRT3) / Tribunal Regional Eleitoral de Minas Gerais (TRE-MG)',
            '22' => 'Tribunal de Justiça do Piauí (TJ-PI) / Tribunal Regional do Trabalho da 22ª Região (TRT22) / Tribunal Regional Eleitoral do Piauí (TRE-PI)',
            '23' => 'Tribunal de Justiça do Paraná (TJ-PR) / Tribunal Regional do Trabalho da 9ª Região (TRT9) / Tribunal Regional Eleitoral do Paraná (TRE-PR)',
            '24' => 'Tribunal de Justiça de Santa Catarina (TJ-SC) / Tribunal Regional do Trabalho da 12ª Região (TRT12) / Tribunal Regional Eleitoral de Santa Catarina (TRE-SC)',
            '25' => 'Tribunal de Justiça do Rio Grande do Sul (TJ-RS) / Tribunal Regional do Trabalho da 4ª Região (TRT4) / Tribunal Regional Eleitoral do Rio Grande do Sul (TRE-RS)',
            '26' => 'Tribunal de Justiça Militar de São Paulo (TJM-SP) / Tribunal de Justiça de São Paulo (TJ-SP) / Tribunal Regional do Trabalho da 15ª Região (TRT15)',
            '27' => 'Tribunal de Justiça do Rio de Janeiro (TJ-RJ) / Tribunal Regional do Trabalho da 1ª Região (TRT1) / Tribunal Regional Eleitoral do Rio de Janeiro (TRE-RJ)',
        ];
        
        // Se tiver segmento, tentar refinar a resposta
        if ($segmento && isset($tribunais[$codigo])) {
            $nome = $tribunais[$codigo];
            // Se o nome contém múltiplas opções, tentar identificar pelo segmento
            if (strpos($nome, '/') !== false) {
                $segmentos_tribunais = [
                    '4' => ['TRF'], // Justiça Federal
                    '5' => ['TRT'], // Justiça do Trabalho
                    '6' => ['TRE'], // Justiça Eleitoral
                    '8' => ['TJ'],  // Justiça Estadual
                ];
                
                if (isset($segmentos_tribunais[$segmento])) {
                    $prefixo = $segmentos_tribunais[$segmento][0];
                    $partes = explode('/', $nome);
                    foreach ($partes as $parte) {
                        if (strpos($parte, $prefixo) !== false) {
                            return trim($parte);
                        }
                    }
                }
            }
            return $nome;
        }
        
        return $tribunais[$codigo] ?? 'Tribunal ' . $codigo;
    }
}

if (!function_exists('validar_digito_verificador_cnj')) {
    /**
     * Valida dígito verificador do número CNJ
     * 
     * @param string $numeroProcesso Número do processo
     * @return bool True se válido
     */
    function validar_digito_verificador_cnj($numeroProcesso)
    {
        $numeroLimpo = normalizar_numero_processo($numeroProcesso);
        
        if (strlen($numeroLimpo) != 20) {
            return false;
        }
        
        // Extrai partes
        $sequencial = substr($numeroLimpo, 0, 7);
        $digito = substr($numeroLimpo, 7, 2);
        $ano = substr($numeroLimpo, 9, 4);
        $segmento = substr($numeroLimpo, 13, 1);
        $tribunal = substr($numeroLimpo, 14, 2);
        $origem = substr($numeroLimpo, 16, 4);
        
        // Calcula dígito verificador
        $numeroBase = $sequencial . $ano . $segmento . $tribunal . $origem;
        $soma = 0;
        $multiplicadores = [2, 3, 4, 5, 6, 7, 8, 9];
        
        for ($i = 0; $i < strlen($numeroBase); $i++) {
            $digitoAtual = intval($numeroBase[$i]);
            $multiplicador = $multiplicadores[$i % 8];
            $soma += $digitoAtual * $multiplicador;
        }
        
        $resto = $soma % 97;
        $digitoCalculado = 98 - $resto;
        
        return str_pad($digitoCalculado, 2, '0', STR_PAD_LEFT) == $digito;
    }
}

if (!function_exists('obter_endpoint_tribunal')) {
    /**
     * Obtém o endpoint da API CNJ baseado no segmento e tribunal
     * 
     * @param string $segmento Segmento da justiça (1 dígito)
     * @param string $tribunal Código do tribunal (2 dígitos)
     * @return string|false URL completa do endpoint ou false se não encontrado
     */
    function obter_endpoint_tribunal($segmento, $tribunal)
    {
        $baseUrl = 'https://api-publica.datajud.cnj.jus.br';
        $endpoint = null;
        
        // Segmento 1 = STF (Supremo Tribunal Federal)
        if ($segmento == '1') {
            $endpoint = 'api_publica_stf';
        }
        // Segmento 2 = CNJ (Conselho Nacional de Justiça)
        elseif ($segmento == '2') {
            $endpoint = 'api_publica_stf';
        }
        // Segmento 3 = STJ (Superior Tribunal de Justiça)
        elseif ($segmento == '3') {
            $endpoint = 'api_publica_stf';
        }
        // Segmento 4 = Justiça Federal
        elseif ($segmento == '4') {
            // Garante que o tribunal seja tratado como número inteiro (remove zeros à esquerda)
            $tribunalNum = intval($tribunal);
            if ($tribunalNum >= 1 && $tribunalNum <= 6) {
                // TRF (Tribunais Regionais Federais)
                // Remove zero à esquerda: 01 -> 1, 02 -> 2, etc.
                $endpoint = 'api_publica_trf' . $tribunalNum;
            } elseif ($tribunal == '90' || $tribunalNum == 90) {
                // STJ (Superior Tribunal de Justiça)
                $endpoint = 'api_publica_stf';
            } elseif ($tribunal == '00' || $tribunalNum == 0) {
                // STF (Supremo Tribunal Federal)
                $endpoint = 'api_publica_stf';
            }
        }
        // Segmento 5 = Justiça do Trabalho
        elseif ($segmento == '5') {
            // Garante que o tribunal seja tratado como número inteiro (remove zeros à esquerda)
            $tribunalNum = intval($tribunal);
            if ($tribunalNum >= 1 && $tribunalNum <= 24) {
                // TRT (Tribunais Regionais do Trabalho) - sem zero à esquerda
                $endpoint = 'api_publica_trt' . $tribunalNum;
                // Log para debug
                if (ENVIRONMENT === 'development') {
                    log_message('debug', "Tribunal TRT: original='{$tribunal}', convertido={$tribunalNum}, endpoint='{$endpoint}'");
                }
            } elseif ($tribunal == '90' || $tribunalNum == 90) {
                // TST (Tribunal Superior do Trabalho)
                $endpoint = 'api_publica_stf';
            }
        }
        // Segmento 6 = Justiça Eleitoral
        elseif ($segmento == '6') {
            if ($tribunal == '90') {
                // TSE (Tribunal Superior Eleitoral)
                $endpoint = 'api_publica_stf';
            } elseif (intval($tribunal) >= 1 && intval($tribunal) <= 27) {
                // TRE (Tribunais Regionais Eleitorais) - usar endpoint genérico
                $endpoint = 'api_publica_stf';
            }
        }
        // Segmento 7 = Justiça Militar da União
        elseif ($segmento == '7') {
            if ($tribunal == '10') {
                // STM (Superior Tribunal Militar)
                $endpoint = 'api_publica_stm';
            } else {
                $endpoint = 'api_publica_stf';
            }
        }
        // Segmento 8 = Justiça Estadual
        elseif ($segmento == '8') {
            $endpoint = obter_endpoint_justica_estadual($tribunal);
        }
        // Segmento 9 = Justiça Militar Estadual
        elseif ($segmento == '9') {
            if ($tribunal == '13') {
                $endpoint = 'api_publica_tjmmg';
            } elseif ($tribunal == '21') {
                $endpoint = 'api_publica_tjmrs';
            } elseif ($tribunal == '26') {
                $endpoint = 'api_publica_tjmsp';
            } else {
                $endpoint = 'api_publica_stf';
            }
        }
        // Tribunais Superiores (código 90 ou 00)
        elseif ($tribunal == '90' || $tribunal == '00') {
            $endpoint = 'api_publica_stf';
        }
        
        if (!$endpoint) {
            log_message('error', "Endpoint não encontrado para segmento {$segmento} e tribunal {$tribunal}");
            return false;
        }
        
        return $baseUrl . '/' . $endpoint . '/_search';
    }
}

if (!function_exists('obter_endpoint_justica_estadual')) {
    /**
     * Obtém endpoint para Justiça Estadual
     */
    function obter_endpoint_justica_estadual($tribunal)
    {
        $mapa = [
            '01' => 'api_publica_tjac',
            '02' => 'api_publica_tjal',
            '03' => 'api_publica_tjap',
            '04' => 'api_publica_tjam',
            '05' => 'api_publica_tjba',
            '06' => 'api_publica_tjce',
            '07' => 'api_publica_tjdft',
            '08' => 'api_publica_tjes',
            '09' => 'api_publica_tjgo',
            '10' => 'api_publica_tjma',
            '11' => 'api_publica_tjmt',
            '12' => 'api_publica_tjms',
            '13' => 'api_publica_tjmg',
            '14' => 'api_publica_tjpa',
            '15' => 'api_publica_tjpb',
            '16' => 'api_publica_tjpr',
            '17' => 'api_publica_tjpe',
            '18' => 'api_publica_tjpi',
            '19' => 'api_publica_tjrj',
            '20' => 'api_publica_tjrn',
            '21' => 'api_publica_tjrs',
            '22' => 'api_publica_tjro',
            '23' => 'api_publica_tjrr',
            '24' => 'api_publica_tjsc',
            '25' => 'api_publica_tjse',
            '26' => 'api_publica_tjsp',
            '27' => 'api_publica_tjto',
        ];
        
        return isset($mapa[$tribunal]) ? $mapa[$tribunal] : false;
    }
}

