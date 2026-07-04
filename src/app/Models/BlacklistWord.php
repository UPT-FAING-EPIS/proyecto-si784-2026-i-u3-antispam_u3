<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo BlacklistWord – Palabras prohibidas configurables por el admin.
 *
 * Reemplaza el array hardcodeado que antes vivía en SpamFilterService.
 * El caché ('spamfilter.blacklist_words') se invalida automáticamente
 * en cada save/delete, así ningún controlador necesita acordarse de
 * limpiarlo manualmente.
 */
class BlacklistWord extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'spamfilter.blacklist_words';

    protected $fillable = [
        'word',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function setWordAttribute(string $value): void
    {
        $this->attributes['word'] = mb_strtolower(trim($value), 'UTF-8');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
