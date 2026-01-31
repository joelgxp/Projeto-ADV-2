<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Biblioteca PeticaoGenerator - Orquestra geração de petições com IA
 *
 * Fluxo: contexto do ADV + modelo base + jurisprudência (RAG) + LLM
 * Nunca inventa dados - usa apenas informações do banco e templates.
 */
class PeticaoGenerator
{
    /** @var object */
    private $ci;

    /** @var string */
    private $modelo;

    /** @var bool */
    private $chamadaLocal;

    public function __construct()
    {
        $ci = get_instance();
        $this->ci = $ci;
        $this->modelo = $_ENV['PETICAO_IA_MODELO'] ?? 'openai/gpt-4o-mini';
        $this->chamadaLocal = (bool) ($_ENV['PETICAO_IA_CHAMADA_LOCAL'] ?? 0);
    }

    /**
     * Monta o contexto completo para geração
     *
     * @param array $params [processos_id, prazos_id, contratos_id, clientes_id, contexto_manual, anexos_ids]
     * @return array [contexto_texto, contexto_ids]
     */
    public function montarContexto(array $params): array
    {
        $partes = [];
        $processo = null;
        $contexto = [];

        if (!empty($params['processos_id'])) {
            $this->ci->load->model('processos_model');
            $this->ci->load->model('partes_processo_model');
            $this->ci->load->model('movimentacoes_processuais_model');

            $processo = $this->ci->processos_model->getById($params['processos_id']);
            if ($processo) {
                $contexto[] = "=== DADOS DO PROCESSO ===";
                $contexto[] = "Número: " . ($processo->numeroProcesso ?? '-');
                $contexto[] = "Classe: " . ($processo->classe ?? '-');
                $contexto[] = "Assunto: " . ($processo->assunto ?? '-');
                $contexto[] = "Vara: " . ($processo->vara ?? '-');
                $contexto[] = "Comarca: " . ($processo->comarca ?? '-');
                $contexto[] = "Tribunal: " . ($processo->tribunal ?? '-');
                $contexto[] = "Valor da causa: R$ " . (isset($processo->valorCausa) ? number_format($processo->valorCausa, 2, ',', '.') : '-');
                $contexto[] = "Cliente: " . ($processo->nomeCliente ?? '-');

                $partes = $this->ci->partes_processo_model->getByProcesso($params['processos_id']);
                if ($partes) {
                    $contexto[] = "\n=== PARTES ===";
                    foreach ($partes as $p) {
                        $contexto[] = ucfirst($p->tipo ?? 'parte') . ": " . ($p->nome ?? '-') . " | Doc: " . ($p->documento ?? '-') . " | Endereço: " . ($p->endereco ?? '-');
                    }
                }

                $movs = $this->ci->movimentacoes_processuais_model->getByProcesso($params['processos_id']);
                if ($movs && (!isset($params['incluir_movimentacoes']) || $params['incluir_movimentacoes'])) {
                    $contexto[] = "\n=== MOVIMENTAÇÕES (últimas 10) ===";
                    $limite = min(10, count($movs));
                    for ($i = 0; $i < $limite; $i++) {
                        $m = $movs[$i];
                        $data = isset($m->data) ? date('d/m/Y', strtotime($m->data)) : '-';
                        $contexto[] = $data . " - " . ($m->tipo ?? '') . ": " . mb_substr(strip_tags($m->descricao ?? ''), 0, 500);
                    }
                }
            }
        }

        if (!empty($params['prazos_id'])) {
            $this->ci->load->model('prazos_model');
            $prazo = $this->ci->prazos_model->getById($params['prazos_id']);
            if ($prazo) {
                $contexto[] = "\n=== PRAZO VINCULADO ===";
                $contexto[] = "Tipo: " . ($prazo->tipo ?? '-');
                $contexto[] = "Descrição: " . ($prazo->descricao ?? '-');
                $contexto[] = "Vencimento: " . (isset($prazo->dataVencimento) ? date('d/m/Y', strtotime($prazo->dataVencimento)) : '-');
            }
        }

        if (!empty($params['contratos_id'])) {
            $this->ci->load->model('Contratos_model');
            $contrato = $this->ci->Contratos_model->getById($params['contratos_id']);
            if ($contrato) {
                $contexto[] = "\n=== CONTRATO ===";
                $contexto[] = "Cliente: " . ($contrato->nomeCliente ?? '-');
                $contexto[] = "Tipo: " . ($contrato->tipo ?? '-');
            }
        }

        if (!empty($params['clientes_id']) && empty($processo)) {
            $this->ci->load->model('clientes_model');
            $cliente = $this->ci->clientes_model->getById($params['clientes_id']);
            if ($cliente) {
                $contexto[] = "\n=== CLIENTE ===";
                $contexto[] = "Nome: " . ($cliente->nomeCliente ?? '-');
                $contexto[] = "Documento: " . ($cliente->documento ?? '-');
            }
        }

        if (!empty($params['contexto_manual'])) {
            $contexto[] = "\n=== CONTEXTO ADICIONAL ===";
            $contexto[] = $params['contexto_manual'];
        }

        if (!empty($params['anexos_ids']) && is_array($params['anexos_ids'])) {
            $this->ci->db->where_in('idDocumentos', $params['anexos_ids']);
            $docs = $this->ci->db->get('documentos_processuais')->result();
            if ($docs) {
                $contexto[] = "\n=== DOCUMENTOS SELECIONADOS ===";
                foreach ($docs as $d) {
                    $contexto[] = "- " . ($d->nome ?? 'Documento') . " (" . ($d->tipo ?? '') . "): " . ($d->descricao ?? 'Sem descrição');
                }
            }
        }

        $contextoIds = [
            'processos_id' => $params['processos_id'] ?? null,
            'prazos_id' => $params['prazos_id'] ?? null,
            'contratos_id' => $params['contratos_id'] ?? null,
            'clientes_id' => $params['clientes_id'] ?? null,
            'anexos_ids' => $params['anexos_ids'] ?? [],
        ];

        return [
            'contexto_texto' => implode("\n", $contexto),
            'contexto_ids' => $contextoIds,
            'partes' => $partes,
            'processo' => $processo,
        ];
    }

    /**
     * Substitui placeholders no template
     *
     * @param string $corpo Template com {{NOME_AUTOR}}, {{NOME_REU}}, etc.
     * @param array $dados Dados para substituição
     * @return string
     */
    public function substituirPlaceholders(string $corpo, array $dados): string
    {
        $placeholders = [
            'NOME_AUTOR' => $dados['nome_autor'] ?? '',
            'NOME_REU' => $dados['nome_reu'] ?? '',
            'NUMERO_PROCESSO' => $dados['numero_processo'] ?? '',
            'VARA' => $dados['vara'] ?? '',
            'COMARCA' => $dados['comarca'] ?? '',
            'TRIBUNAL' => $dados['tribunal'] ?? '',
            'CLASSE' => $dados['classe'] ?? '',
            'ASSUNTO' => $dados['assunto'] ?? '',
            'VALOR_CAUSA' => $dados['valor_causa'] ?? '',
        ];

        foreach ($placeholders as $key => $value) {
            $corpo = str_replace('{{' . $key . '}}', $value, $corpo);
        }

        return $corpo;
    }

    /**
     * Busca jurisprudência na base interna (RAG)
     *
     * @param string|null $area
     * @param string|null $assunto
     * @param int $limite
     * @return array
     */
    public function buscarJurisprudencia(?string $area = null, ?string $assunto = null, int $limite = 5): array
    {
        if (!$this->ci->db->table_exists('jurisprudencia_base')) {
            return [];
        }

        $this->ci->db->select('*');
        $this->ci->db->from('jurisprudencia_base');
        $this->ci->db->limit($limite);

        if ($area) {
            $this->ci->db->like('area', $area);
        }
        if ($assunto) {
            $this->ci->db->like('assunto', $assunto);
        }

        $result = $this->ci->db->get()->result();

        return $result ?: [];
    }

    /**
     * Gera a petição
     *
     * @param array $params [tipo_peca, tese_principal, pontos_enfatizar, tom, modelos_pecas_id, ...]
     * @return array [sucesso, conteudo, erro]
     */
    public function gerar(array $params): array
    {
        $this->ci->load->library('Openrouter');

        if (!$this->ci->openrouter->isConfigured()) {
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'API de IA não configurada. Configure OPENROUTER_API_KEY no .env.'];
        }

        $contextoData = $this->montarContexto($params);
        $contextoTexto = $contextoData['contexto_texto'];

        if (empty($contextoTexto) && empty($params['contexto_manual'])) {
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'Contexto vazio. Informe pelo menos cliente e contexto textual ou vincule a um processo.'];
        }

        $modeloBase = '';
        $dadosPlaceholders = [];

        if (!empty($params['modelos_pecas_id'])) {
            $this->ci->load->model('Modelos_pecas_model');
            $modelo = $this->ci->Modelos_pecas_model->getById($params['modelos_pecas_id']);
            if ($modelo && !empty($modelo->corpo)) {
                $processo = $contextoData['processo'] ?? null;
                $partes = $contextoData['partes'] ?? [];

                $autor = '';
                $reu = '';
                foreach ($partes as $p) {
                    $t = strtolower($p->tipo ?? '');
                    if (in_array($t, ['autor', 'ativo'])) {
                        $autor = $p->nome ?? '';
                    } elseif (in_array($t, ['réu', 'reu', 'passivo'])) {
                        $reu = $p->nome ?? '';
                    }
                }

                $dadosPlaceholders = [
                    'nome_autor' => $autor,
                    'nome_reu' => $reu,
                    'numero_processo' => $processo ? ($processo->numeroProcesso ?? '') : '',
                    'vara' => $processo ? ($processo->vara ?? '') : '',
                    'comarca' => $processo ? ($processo->comarca ?? '') : '',
                    'tribunal' => $processo ? ($processo->tribunal ?? '') : '',
                    'classe' => $processo ? ($processo->classe ?? '') : '',
                    'assunto' => $processo ? ($processo->assunto ?? '') : '',
                    'valor_causa' => ($processo && isset($processo->valorCausa)) ? 'R$ ' . number_format($processo->valorCausa, 2, ',', '.') : '',
                ];

                $modeloBase = $this->substituirPlaceholders($modelo->corpo, $dadosPlaceholders);
            }
        }

        $processo = $contextoData['processo'] ?? null;
        $jurisprudencia = $this->buscarJurisprudencia(
            $params['area'] ?? ($processo ? ($processo->tipo_processo ?? null) : null),
            $params['assunto'] ?? ($processo ? ($processo->assunto ?? null) : null)
        );

        $instrucaoJuris = '';
        if (empty($jurisprudencia)) {
            $instrucaoJuris = "IMPORTANTE: NÃO invente números de artigos de lei ou números de processos/jurisprudência. "
                . "Se precisar citar jurisprudência, escreva: 'Sem precedentes vinculados disponíveis na base interna do escritório.'";
        } else {
            $trechos = [];
            foreach ($jurisprudencia as $j) {
                $trechos[] = "- " . ($j->tribunal ?? '') . ", Processo " . ($j->numero_processo ?? '') . ", " . ($j->data ?? '') . ": " . mb_substr($j->trecho ?? '', 0, 300);
            }
            $instrucaoJuris = "Use APENAS as seguintes jurisprudências verificadas (não invente outras):\n" . implode("\n", $trechos);
        }

        $tom = $params['tom'] ?? 'tecnico';
        $tomInstrucao = [
            'tecnico' => 'Use linguagem técnica e formal, adequada ao meio jurídico.',
            'didatico' => 'Use linguagem mais didática e acessível, explicando conceitos quando necessário.',
            'conciso' => 'Seja objetivo e conciso, evitando prolixidade.',
        ];
        $tomTexto = $tomInstrucao[$tom] ?? $tomInstrucao['tecnico'];

        $tiposPeca = [
            'peticao_inicial' => 'Petição Inicial',
            'contestacao' => 'Contestação',
            'replica' => 'Réplica',
            'recurso' => 'Recurso (Apelação ou Agravo)',
            'peticao_simples' => 'Petição Simples (manifestação, juntada de documentos, pedido de prazo)',
        ];
        $tipoLabel = $tiposPeca[$params['tipo_peca'] ?? ''] ?? $params['tipo_peca'];

        $systemPrompt = "Você é um assistente jurídico especializado em redação de peças processuais. "
            . "Sua tarefa é gerar peças com estrutura mínima obrigatória:\n"
            . "1. Endereçamento (Juízo, Vara, Comarca)\n"
            . "2. Qualificação das partes (autor, réu)\n"
            . "3. Exposição dos fatos\n"
            . "4. Fundamentos jurídicos (dispositivos legais + jurisprudência quando disponível)\n"
            . "5. Pedidos claros e numerados\n"
            . "6. Local, data, nome do advogado, OAB\n\n"
            . "Use APENAS os dados fornecidos no contexto. NUNCA invente nomes, números de processo ou dados das partes.\n"
            . $instrucaoJuris . "\n\n"
            . $tomTexto;

        $userContent = "Gere uma peça do tipo: " . $tipoLabel . "\n\n";
        $userContent .= "TESE PRINCIPAL (obrigatória):\n" . ($params['tese_principal'] ?? '') . "\n\n";

        if (!empty($params['pontos_enfatizar'])) {
            $userContent .= "PONTOS A ENFATIZAR:\n" . $params['pontos_enfatizar'] . "\n\n";
        }

        $userContent .= "CONTEXTO DO CASO:\n" . $contextoTexto . "\n\n";

        if (!empty($modeloBase)) {
            $userContent .= "MODELO BASE DO ESCRITÓRIO (adapte ao caso, mantendo a estrutura):\n" . $modeloBase . "\n\n";
        }

        $userContent .= "Gere a peça completa, usando os dados do contexto. Inclua ao final: Local e data atual, e espaço para nome e OAB do advogado.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
        ];

        try {
            $resposta = $this->ci->openrouter->chat($this->modelo, $messages, [
                'temperature' => 0.4,
                'max_tokens' => 4096,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'PeticaoGenerator gerar: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'Erro na API de IA: ' . $e->getMessage()];
        }

        if ($resposta === null) {
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'Erro ao comunicar com o serviço de IA. Verifique OPENROUTER_API_KEY no .env e application/logs/'];
        }

        return [
            'sucesso' => true,
            'conteudo' => $resposta,
            'erro' => null,
            'prompt_system' => $systemPrompt,
            'prompt_user' => $userContent,
        ];
    }

    /**
     * Refina texto existente (ex: simplificar, aumentar fundamentação)
     *
     * @param string $conteudoAtual
     * @param string $instrucao Ex: "Simplifique a linguagem" ou "Aumente a fundamentação jurídica"
     * @return array [sucesso, conteudo, erro]
     */
    public function refinar(string $conteudoAtual, string $instrucao): array
    {
        $this->ci->load->library('Openrouter');

        if (!$this->ci->openrouter->isConfigured()) {
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'API de IA não configurada.'];
        }

        $systemPrompt = "Você é um assistente jurídico. Receberá um texto de peça processual e uma instrução de modificação. "
            . "Aplique a modificação mantendo a estrutura e os dados factuais. Não invente informações.";

        $userContent = "INSTRUÇÃO: " . $instrucao . "\n\nTEXTO ATUAL:\n" . $conteudoAtual;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
        ];

        $resposta = $this->ci->openrouter->chat($this->modelo, $messages, [
            'temperature' => 0.3,
            'max_tokens' => 4096,
        ]);

        if ($resposta === null) {
            return ['sucesso' => false, 'conteudo' => null, 'erro' => 'Erro ao comunicar com o serviço de IA.'];
        }

        return ['sucesso' => true, 'conteudo' => $resposta, 'erro' => null];
    }

    /**
     * Retorna o modelo LLM configurado
     */
    public function getModelo(): string
    {
        return $this->modelo;
    }

    /**
     * Retorna se a chamada é local (para log)
     */
    public function isChamadaLocal(): bool
    {
        return $this->chamadaLocal;
    }
}
