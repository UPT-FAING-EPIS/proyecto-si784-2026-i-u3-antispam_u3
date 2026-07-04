<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cliente HTTP hacia el endpoint /api/integrations/check-spam de Aegis Filter.
 *
 * Usa la API de HTTP nativa de WordPress (wp_remote_post) en vez de cURL
 * directo, para respetar proxies/timeouts configurados a nivel de WordPress.
 */
class Aegis_Filter_Client
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeoutSeconds;

    public function __construct(string $apiUrl, string $apiKey, int $timeoutSeconds = 5)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Consulta si un contenido es spam.
     *
     * @return array{isSpam: bool, score: int, reason: ?string}|null
     *         null si la API no respondió correctamente (fallo de red,
     *         credenciales inválidas, error 5xx, etc.).
     */
    public function check_spam(string $content, string $author = ''): ?array
    {
        if (trim($content) === '') {
            return null;
        }

        $response = wp_remote_post($this->apiUrl . '/api/integrations/check-spam', [
            'timeout' => $this->timeoutSeconds,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Integration-Key' => $this->apiKey,
            ],
            'body' => wp_json_encode([
                'author' => $author !== '' ? $author : 'wordpress',
                'content' => $content,
            ]),
        ]);

        if (is_wp_error($response)) {
            error_log('Aegis Filter: error de conexión - ' . $response->get_error_message());

            return null;
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode !== 200) {
            error_log("Aegis Filter: respuesta inesperada de la API (HTTP {$statusCode})");

            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($data) || !array_key_exists('isSpam', $data)) {
            error_log('Aegis Filter: respuesta de la API con formato inesperado');

            return null;
        }

        return [
            'isSpam' => (bool) $data['isSpam'],
            'score' => (int) ($data['score'] ?? 0),
            'reason' => $data['reason'] ?? null,
        ];
    }
}
