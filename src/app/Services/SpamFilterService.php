<?php

namespace App\Services;
use App\Models\BlacklistWord;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SpamFilterService – Motor Antispam de Aegis Filter
 *
 * Servicio dedicado para el análisis y clasificación de mensajes.
 * Implementa el Principio de Responsabilidad Única (SRP):
 * TODA la lógica antispam reside aquí, NUNCA en los controladores.
 *
 * Reglas implementadas:
 *  1. Bloqueo por palabras negras (blacklist, cargada desde BD/caché)
 *  2. Bloqueo por exceso de URLs (umbral configurable en BD/caché)
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 * Proyecto: Aegis Filter | Tech Hub Forum
 */

class SpamFilterService
{
    /**
     * Overrides en memoria de la lista negra, usados por
     * addBlacklistedWord() (pruebas/configuración puntual de una request).
     *
     * @var array<string>
     */
    private array $extraBlacklistedWords = [];

    /**
     * Override en memoria del límite de URLs, usado por
     * setMaxAllowedUrls(). Null = usar el valor de BD/caché.
     */
    private ?int $maxAllowedUrlsOverride = null;

    // ══════════════════════════════════════════════════
    // MÉTODO PRINCIPAL
    // ══════════════════════════════════════════════════

    /**
     * Analizar un mensaje y determinar si es spam.
     *
     * Ejecuta todas las reglas en orden. Al primer match,
     * retorna el resultado sin continuar con las demás.
     *
     * @param  string $content   Contenido del mensaje a analizar
     * @param  string $author    Nombre del autor (para logging)
     * @param  string $channel   Canal de origen (web/telegram/alexa/wordpress), solo informativo
     * @return array{
     *     isSpam: bool,
     *     reason: string|null,
     *     score: int
     * }
     */
    public function analyze(string $content, string $author = 'anonymous', string $channel = 'web'): array
    {
        $normalizedContent = $this->normalizeContent($content);

        // Regla 1: Verificar palabras negras
        $blacklistResult = $this->checkBlacklistedWords($normalizedContent);
        if ($blacklistResult['found']) {
            Log::warning('SpamFilter: Palabra negra detectada', [
                'author'  => $author,
                'channel' => $channel,
                'word'    => $blacklistResult['word'],
            ]);

            return [
                'isSpam' => true,
                'reason' => 'blacklisted_word',
                'score'  => 100,
                'detail' => "Palabra prohibida encontrada: '{$blacklistResult['word']}'",
            ];
        }

        // Regla 2: Verificar exceso de URLs
        $urlResult = $this->checkExcessiveUrls($normalizedContent);
        if ($urlResult['exceeded']) {
            Log::warning('SpamFilter: Exceso de URLs detectado', [
                'author'    => $author,
                'channel'   => $channel,
                'url_count' => $urlResult['count'],
                'max'       => $this->getMaxAllowedUrls(),
            ]);

            return [
                'isSpam' => true,
                'reason' => 'too_many_urls',
                'score'  => 80,
                'detail' => "Se encontraron {$urlResult['count']} URLs (máximo permitido: {$this->getMaxAllowedUrls()})",
            ];
        }

        // Mensaje limpio
        return [
            'isSpam' => false,
            'reason' => null,
            'score'  => 0,
            'detail' => 'Mensaje aprobado por el filtro antispam',
        ];
    }

    // ══════════════════════════════════════════════════
    // REGLA 1: PALABRAS NEGRAS
    // ══════════════════════════════════════════════════

    /**
     * Verificar si el contenido contiene palabras de la lista negra.
     *
     * Normaliza el texto a minúsculas y elimina acentos para
     * evitar evasiones simples (ej. "GRÁTIS" → "gratis").
     *
     * @param  string $content Contenido normalizado
     * @return array{found: bool, word: string|null}
     */
    public function checkBlacklistedWords(string $content): array
    {
        foreach ($this->getBlacklistedWords() as $word) {
            // Usar strpos para búsqueda eficiente de subcadena
            if (str_contains($content, strtolower($word))) {
                return [
                    'found' => true,
                    'word'  => $word,
                ];
            }
        }

        return [
            'found' => false,
            'word'  => null,
        ];
    }

    // ══════════════════════════════════════════════════
    // REGLA 2: EXCESO DE URLs
    // ══════════════════════════════════════════════════

    /**
     * Verificar si el contenido contiene más URLs de las permitidas.
     *
     * Detecta URLs con protocolos http://, https://, ftp://
     * y URLs sin protocolo que comiencen con www.
     *
     * @param  string $content Contenido normalizado
     * @return array{exceeded: bool, count: int}
     */
    public function checkExcessiveUrls(string $content): array
    {
        // Patrón regex para detectar URLs
        // Cubre: http(s)://, ftp://, www.dominio.tld
        $urlPattern = '/\b(?:https?|ftp):\/\/[^\s<>"{}|\\^`\[\]]+|www\.[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}/i';

        $matches = [];
        $count = preg_match_all($urlPattern, $content, $matches);

        return [
            'exceeded' => $count > $this->getMaxAllowedUrls(),
            'count'    => $count,
        ];
    }

    // ══════════════════════════════════════════════════
    // HELPERS INTERNOS
    // ══════════════════════════════════════════════════

    /**
     * Normalizar el contenido para análisis uniforme.
     *
     * - Convierte a minúsculas
     * - Elimina acentos y caracteres especiales
     * - Elimina espacios múltiples
     *
     * @param  string $content Texto original
     * @return string          Texto normalizado
     */
    private function normalizeContent(string $content): string
    {
        // Convertir a minúsculas
        $normalized = mb_strtolower($content, 'UTF-8');

        // Transliterar caracteres acentuados (á→a, é→e, etc.)
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized) ?: $normalized;

        // Eliminar espacios múltiples
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Agregar una palabra a la lista negra en tiempo de ejecución.
     * Útil para pruebas unitarias y configuración dinámica.
     * Es un override en memoria: no persiste en BD ni afecta a otras requests.
     *
     * @param  string $word Palabra a agregar
     * @return void
     */
    public function addBlacklistedWord(string $word): void
    {
        $this->extraBlacklistedWords[] = strtolower($word);
    }

    /**
     * Obtener la lista negra actual (BD activa, cacheada, + overrides en memoria).
     *
     * @return array<string>
     */
    public function getBlacklistedWords(): array
    {
        $fromDb = Cache::rememberForever(BlacklistWord::CACHE_KEY, function () {
            return BlacklistWord::query()->active()->pluck('word')->all();
        });

        return array_merge($fromDb, $this->extraBlacklistedWords);
    }

    /**
     * Establecer el número máximo de URLs permitidas.
     * Es un override en memoria: no persiste en BD ni afecta a otras requests.
     *
     * @param  int  $max
     * @return void
     */
    public function setMaxAllowedUrls(int $max): void
    {
        $this->maxAllowedUrlsOverride = $max;
    }

    /**
     * Obtener el límite de URLs vigente (override en memoria si existe, sino BD/caché).
     */
    public function getMaxAllowedUrls(): int
    {
        return $this->maxAllowedUrlsOverride ?? Setting::get('max_allowed_urls', 2);
    }
}
