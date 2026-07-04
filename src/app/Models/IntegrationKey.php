<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modelo IntegrationKey – Credenciales por canal externo (ej. WordPress).
 *
 * El texto plano de la key solo se conoce en el momento de generarla
 * (devuelto una sola vez); en BD solo se guarda su hash SHA-256.
 */
class IntegrationKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'label',
        'key_hash',
        'key_prefix',
        'is_active',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Genera una nueva key, la persiste (hasheada) y devuelve el texto
     * plano para mostrarlo una sola vez al admin.
     *
     * @return array{model: self, plainKey: string}
     */
    public static function generate(string $channel, ?string $label = null, ?int $createdBy = null): array
    {
        $plainKey = 'afk_' . Str::random(40);

        $model = self::create([
            'channel' => $channel,
            'label' => $label,
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12),
            'is_active' => true,
            'created_by' => $createdBy,
        ]);

        return ['model' => $model, 'plainKey' => $plainKey];
    }

    public static function findActiveByPlainKey(string $plainKey): ?self
    {
        return self::query()
            ->where('key_hash', hash('sha256', $plainKey))
            ->where('is_active', true)
            ->first();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}
