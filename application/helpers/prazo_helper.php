<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper para cálculos de prazos processuais
 * 
 * Conforme RN 4.1 - Cálculo de Prazos
 * Considera feriados nacionais e municipais, dias úteis (exclui sábados e domingos)
 */

/**
 * Verifica se uma data é dia útil (não é sábado, domingo ou feriado)
 * 
 * @param string $data Data no formato Y-m-d
 * @param int|null $municipio_id ID do município para verificar feriados municipais (opcional)
 * @return bool True se é dia útil, False caso contrário
 */
if (!function_exists('isDiaUtil')) {
    function isDiaUtil($data, $municipio_id = null)
    {
        $timestamp = strtotime($data);
        $diaSemana = date('w', $timestamp); // 0 = domingo, 6 = sábado
        
        // Sábados e domingos não são dias úteis
        if ($diaSemana == 0 || $diaSemana == 6) {
            return false;
        }
        
        // Verificar se é feriado
        if (isFeriado($data, $municipio_id)) {
            return false;
        }
        
        return true;
    }
}

/**
 * Verifica se uma data é feriado
 * 
 * @param string $data Data no formato Y-m-d
 * @param int|null $municipio_id ID do município para verificar feriados municipais (opcional)
 * @return bool True se é feriado, False caso contrário
 */
if (!function_exists('isFeriado')) {
    function isFeriado($data, $municipio_id = null)
    {
        $CI = &get_instance();
        
        if (!$CI->db->table_exists('feriados')) {
            return false;
        }
        
        // Resetar query builder
        $CI->db->reset_query();
        
        // Lógica: (data exata AND (nacional OR (municipal AND municipio_id))) OR (recorrente AND mesmo dia/mês)
        $CI->db->group_start();
        
        // Verificar data exata E tipo (nacional ou municipal)
        $CI->db->where('data', $data);
        $CI->db->where('ativo', 1);
        $CI->db->group_start();
        $CI->db->where('tipo', 'nacional');
        if ($municipio_id !== null) {
            $CI->db->or_group_start();
            $CI->db->where('tipo', 'municipal');
            $CI->db->where('municipio_id', $municipio_id);
            $CI->db->group_end();
        }
        $CI->db->group_end();
        
        $CI->db->group_end();
        
        // OU feriado recorrente (mesmo dia/mês independente do ano)
        $CI->db->or_group_start();
        $CI->db->where('recorrente', 1);
        $CI->db->where('ativo', 1);
        // Usar where com FALSE para permitir query raw e garantir operador =
        $CI->db->where("DATE_FORMAT(data, '%m-%d') = '" . date('m-d', strtotime($data)) . "'", null, false);
        $CI->db->group_start();
        $CI->db->where('tipo', 'nacional');
        if ($municipio_id !== null) {
            $CI->db->or_group_start();
            $CI->db->where('tipo', 'municipal');
            $CI->db->where('municipio_id', $municipio_id);
            $CI->db->group_end();
        }
        $CI->db->group_end();
        $CI->db->group_end();
        
        $query = $CI->db->get('feriados');
        
        return $query->num_rows() > 0;
    }
}

/**
 * Adiciona dias úteis a uma data
 * 
 * @param string $dataInicio Data inicial no formato Y-m-d
 * @param int $diasUteis Número de dias úteis a adicionar
 * @param int|null $municipio_id ID do município para considerar feriados municipais (opcional)
 * @return string Data final no formato Y-m-d
 */
if (!function_exists('adicionarDiasUteis')) {
    function adicionarDiasUteis($dataInicio, $diasUteis, $municipio_id = null)
    {
        if ($diasUteis <= 0) {
            return $dataInicio;
        }
        
        $data = $dataInicio;
        $diasAdicionados = 0;
        
        while ($diasAdicionados < $diasUteis) {
            $data = date('Y-m-d', strtotime($data . ' +1 day'));
            
            if (isDiaUtil($data, $municipio_id)) {
                $diasAdicionados++;
            }
        }
        
        return $data;
    }
}

/**
 * Calcula data de vencimento de prazo conforme legislação
 * 
 * @param string $dataInicio Data inicial (intimação/publicação) no formato Y-m-d
 * @param int $diasUteis Número de dias úteis do prazo
 * @param string $legislacao Tipo de legislação (CPC, CLT, tributario) - usado para regras específicas
 * @param int|null $municipio_id ID do município para considerar feriados municipais (opcional)
 * @return string Data de vencimento no formato Y-m-d
 */
if (!function_exists('calcularPrazo')) {
    function calcularPrazo($dataInicio, $diasUteis, $legislacao = 'CPC', $municipio_id = null)
    {
        // RN 4.1: Prazos contam-se a partir do dia seguinte da intimação/publicação (dia 0)
        // Então começamos a contar do dia seguinte
        $dataContagem = date('Y-m-d', strtotime($dataInicio . ' +1 day'));
        
        // Adicionar dias úteis
        $dataVencimento = adicionarDiasUteis($dataContagem, $diasUteis, $municipio_id);
        
        // RN 4.1: Se último dia for domingo/feriado, prazo vai até próximo dia útil
        // Já está coberto pela função adicionarDiasUteis, mas garantimos aqui também
        while (!isDiaUtil($dataVencimento, $municipio_id)) {
            $dataVencimento = date('Y-m-d', strtotime($dataVencimento . ' +1 day'));
        }
        
        return $dataVencimento;
    }
}

/**
 * Conta quantos dias úteis existem entre duas datas
 * 
 * @param string $dataInicio Data inicial no formato Y-m-d
 * @param string $dataFim Data final no formato Y-m-d
 * @param int|null $municipio_id ID do município para considerar feriados municipais (opcional)
 * @return int Número de dias úteis
 */
if (!function_exists('contarDiasUteis')) {
    function contarDiasUteis($dataInicio, $dataFim, $municipio_id = null)
    {
        $inicio = strtotime($dataInicio);
        $fim = strtotime($dataFim);
        
        if ($inicio > $fim) {
            return 0;
        }
        
        $diasUteis = 0;
        $dataAtual = $dataInicio;
        
        while (strtotime($dataAtual) <= $fim) {
            if (isDiaUtil($dataAtual, $municipio_id)) {
                $diasUteis++;
            }
            $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 day'));
        }
        
        return $diasUteis;
    }
}

/**
 * Sugere data de vencimento baseada no tipo de prazo
 * 
 * @param string $tipoPrazo Tipo do prazo (recurso, contestacao, parecer, etc)
 * @param string $legislacao Tipo de legislação (CPC, CLT, tributario)
 * @param string $dataInicio Data inicial (opcional, usa hoje se não informada)
 * @param int|null $municipio_id ID do município (opcional)
 * @return string Data sugerida no formato Y-m-d
 */
if (!function_exists('sugerirDataPrazo')) {
    function sugerirDataPrazo($tipoPrazo, $legislacao = 'CPC', $dataInicio = null, $municipio_id = null)
    {
        if ($dataInicio === null) {
            $dataInicio = date('Y-m-d');
        }
        
        // Mapeamento de tipos de prazo para dias úteis conforme legislação
        $prazosLegais = [
            'CPC' => [
                'recurso' => 15,
                'contestacao' => 15,
                'resposta' => 15,
                'impugnacao' => 15,
                'manifestacao' => 15,
                'parecer' => 15,
                'embargos' => 15,
                'agravo' => 15,
                'apelação' => 15,
                'defesa' => 15,
                'resposta_acao' => 15,
            ],
            'CLT' => [
                'recurso' => 8,
                'contestacao' => 8,
                'defesa' => 8,
                'impugnacao' => 5,
                'parecer' => 10,
            ],
            'tributario' => [
                'recurso' => 30,
                'defesa' => 30,
                'impugnacao' => 30,
            ],
        ];
        
        // Obter dias úteis para o tipo de prazo
        $diasUteis = null;
        if (isset($prazosLegais[$legislacao]) && isset($prazosLegais[$legislacao][strtolower($tipoPrazo)])) {
            $diasUteis = $prazosLegais[$legislacao][strtolower($tipoPrazo)];
        } elseif (isset($prazosLegais['CPC'][strtolower($tipoPrazo)])) {
            // Fallback para CPC se não encontrado na legislação especificada
            $diasUteis = $prazosLegais['CPC'][strtolower($tipoPrazo)];
        }
        
        // Se não encontrou, usar padrão de 15 dias úteis
        if ($diasUteis === null) {
            $diasUteis = 15;
        }
        
        return calcularPrazo($dataInicio, $diasUteis, $legislacao, $municipio_id);
    }
}

/**
 * Formata data de prazo para exibição
 * 
 * @param string $data Data no formato Y-m-d
 * @param bool $incluirDiaSemana Se deve incluir dia da semana
 * @return string Data formatada
 */
if (!function_exists('formatarDataPrazo')) {
    function formatarDataPrazo($data, $incluirDiaSemana = false)
    {
        $diasSemana = [
            'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira',
            'Quinta-feira', 'Sexta-feira', 'Sábado'
        ];
        
        $timestamp = strtotime($data);
        $diaSemana = $incluirDiaSemana ? $diasSemana[date('w', $timestamp)] . ', ' : '';
        
        return $diaSemana . date('d/m/Y', $timestamp);
    }
}

