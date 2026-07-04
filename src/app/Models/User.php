<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo User – Administradores de Aegis Filter.
 *
 * No hay registro público: los usuarios se crean vía el comando
 * `php artisan aegis:create-admin`.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function blacklistWords()
    {
        return $this->hasMany(BlacklistWord::class, 'created_by');
    }

    public function integrationKeys()
    {
        return $this->hasMany(IntegrationKey::class, 'created_by');
    }
}
