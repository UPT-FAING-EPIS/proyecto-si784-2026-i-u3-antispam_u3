<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo AnalysisLog – Auditoría de cada análisis de spam realizado,
 * sin importar el canal de origen (web/telegram/alexa/wordpress).
 */
class AnalysisLog extends Model
{
    protected $fillable = [
        'channel',
        'author',
        'content',
        'is_spam',
        'reason',
        'score',
        'ip_address',
    ];

    protected $casts = [
        'is_spam' => 'boolean',
    ];

    public static function record(array $result, string $channel, string $author, string $content, ?string $ip = null): self
    {
        return self::create([
            'channel' => $channel,
            'author' => $author,
            'content' => $content,
            'is_spam' => $result['isSpam'] ?? false,
            'reason' => $result['reason'] ?? null,
            'score' => $result['score'] ?? 0,
            'ip_address' => $ip,
        ]);
    }

    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeSpam($query)
    {
        return $query->where('is_spam', true);
    }

    public function scopeClean($query)
    {
        return $query->where('is_spam', false);
    }
}
