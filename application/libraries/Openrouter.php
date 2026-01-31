<?php

defined('BASEPATH') or exit('No direct script access allowed');

use OpenAI\Client;

/**
 * Biblioteca OpenRouter - Cliente OpenAI configurado para OpenRouter
 *
 * Usa openai-php/client para acessar modelos LLM via OpenRouter.
 * Configuração via .env: OPENROUTER_API_KEY e OPENROUTER_BASE_URL
 *
 * Uso:
 *   $this->load->library('Openrouter');
 *   $response = $this->openrouter->chat('openai/gpt-4o-mini', [['role' => 'user', 'content' => 'Olá!']]);
 */
class Openrouter
{
    /** @var Client */
    private $client;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
        $this->baseUrl = $_ENV['OPENROUTER_BASE_URL'] ?? 'https://openrouter.ai/api/v1';

        if (empty($this->apiKey) || ! str_starts_with($this->apiKey, 'sk-or-')) {
            log_message('warning', 'Openrouter: OPENROUTER_API_KEY não configurada ou inválida no .env');
        }

        $this->client = $this->createClient();
    }

    /**
     * Cria o cliente OpenAI configurado para OpenRouter
     */
    private function createClient(): Client
    {
        $factory = \OpenAI::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUri(rtrim($this->baseUrl, '/'));

        $siteUrl = $_ENV['APP_BASEURL'] ?? '';
        if (! empty($siteUrl)) {
            $factory->withHttpHeader('HTTP-Referer', $siteUrl);
            $factory->withHttpHeader('X-Title', $_ENV['APP_NAME'] ?? 'Map-OS');
        }

        return $factory->make();
    }

    /**
     * Retorna o cliente OpenAI (para uso avançado)
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Envia mensagens para chat completion
     *
     * @param string $model Modelo OpenRouter (ex: openai/gpt-4o-mini, anthropic/claude-3-haiku)
     * @param array $messages Array de mensagens [['role' => 'user', 'content' => '...'], ...]
     * @param array $options Opções adicionais (temperature, max_tokens, etc)
     * @return string|null Conteúdo da resposta ou null em caso de erro
     */
    public function chat(string $model, array $messages, array $options = []): ?string
    {
        try {
            $params = array_merge([
                'model' => $model,
                'messages' => $messages,
            ], $options);

            $response = $this->client->chat()->create($params);

            $content = $response->choices[0]->message->content ?? null;

            return $content;
        } catch (Throwable $e) {
            log_message('error', 'Openrouter chat error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Chat com streaming (retorna iterador)
     *
     * @param string $model
     * @param array $messages
     * @param array $options
     * @return \OpenAI\Responses\StreamResponse|null
     */
    public function chatStreamed(string $model, array $messages, array $options = [])
    {
        try {
            $params = array_merge([
                'model' => $model,
                'messages' => $messages,
                'stream' => true,
            ], $options);

            return $this->client->chat()->createStreamed($params);
        } catch (Throwable $e) {
            log_message('error', 'Openrouter chatStreamed error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Verifica se a API está configurada
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && str_starts_with($this->apiKey, 'sk-or-');
    }
}
