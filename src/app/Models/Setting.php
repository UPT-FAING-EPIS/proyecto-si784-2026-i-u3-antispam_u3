<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modelo Setting – Configuración clave-valor editable desde el admin.
 *
 * Diseñado para crecer sin migraciones nuevas: cada fila es un setting
 * independiente con su propio tipo para castear el valor al leerlo.
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    private static function cacheKey(string $key): string
    {
        return "setting.{$key}";
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $raw = Cache::rememberForever(self::cacheKey($key), function () use ($key) {
            return self::query()->where('key', $key)->first();
        });

        if (!$raw) {
            return $default;
        }

        return match ($raw->type) {
            'int' => (int) $raw->value,
            'bool' => filter_var($raw->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($raw->value, true),
            default => $raw->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget(self::cacheKey($key));
    }
}
