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
        
        // Mapeamento de segmentos e tribunais para endpoints
        $endpoints = [
            // Tribunais Superiores (segmento 1, 2, 3, 4, 5, 6, 10)
            '90' => 'api_publica_stf',
            
            // Justiça Federal (segmento 1)
            '01' => 'api_publica_trf1',
            '02' => 'api_publica_trf2',
            '03' => 'api_publica_trf3',
            '04' => 'api_publica_trf4',
            '05' => 'api_publica_trf5',
            '06' => 'api_publica_trf6',
            
            // Justiça Estadual (segmento 8)
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
            
            // Justiça do Trabalho (segmento 1)
            '01' => 'api_publica_trt1',
            '02' => 'api_publica_trt2',
            '03' => 'api_publica_trt3',
            '04' => 'api_publica_trt4',
            '05' => 'api_publica_trt5',
            '06' => 'api_publica_trt6',
            '07' => 'api_publica_trt7',
            '08' => 'api_publica_trt8',
            '09' => 'api_publica_trt9',
            '10' => 'api_publica_trt10',
            '11' => 'api_publica_trt11',
            '12' => 'api_publica_trt12',
            '13' => 'api_publica_trt13',
            '14' => 'api_publica_trt14',
            '15' => 'api_publica_trt15',
            '16' => 'api_publica_trt16',
            '17' => 'api_publica_trt17',
            '18' => 'api_publica_trt18',
            '19' => 'api_publica_trt19',
            '20' => 'api_publica_trt20',
            '21' => 'api_publica_trt21',
            '22' => 'api_publica_trt22',
            '23' => 'api_publica_trt23',
            '24' => 'api_publica_trt24',
        ];
        
        // Determina endpoint baseado no segmento
        $endpoint = null;
        
        if ($segmento == '1' || $segmento == '2' || $segmento == '3' || $segmento == '4' || $segmento == '5' || $segmento == '6' || $segmento == '10') {
            // Tribunais Superiores
            $endpoint = 'api_publica_stf';
        } elseif ($segmento == '8') {
            // Justiça Estadual - usa mapeamento específico
            $endpoint = obter_endpoint_justica_estadual($tribunal);
        } elseif ($segmento == '1' && in_array($tribunal, ['01', '02', '03', '04', '05', '06'])) {
            // Justiça Federal
            $endpoint = 'api_publica_trf' . $tribunal;
        } elseif ($segmento == '1' && intval($tribunal) >= 1 && intval($tribunal) <= 24) {
            // Justiça do Trabalho
            $endpoint = 'api_publica_trt' . $tribunal;
        }
        
        if (!$endpoint) {
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

