<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla 'comments'
 *
 * Tabla principal del sistema Aegis Filter.
 * Almacena los comentarios/mensajes del foro Tech Hub
 * junto con su estado de moderación antispam.
 *
 * Campos:
 *  - id       : Clave primaria autoincremental
 *  - author   : Nombre del autor del comentario
 *  - content  : Contenido del comentario
 *  - status   : Estado del filtro ('approved' | 'spam' | 'pending')
 *  - ip_address: IP del remitente (para análisis forense)
 *  - spam_reason: Razón por la que fue marcado como spam
 *  - timestamps: created_at y updated_at
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            // Clave primaria
            $table->id();

            // Autor del comentario (requerido)
            $table->string('author', 100)->comment('Nombre del autor del comentario');

            // Email del autor (opcional, para notificaciones)
            $table->string('email', 150)->nullable()->comment('Email del autor');

            // Contenido principal del comentario
            $table->text('content')->comment('Contenido del comentario enviado');

            // Estado de moderación del filtro antispam
            // Valores: 'pending' (inicial), 'approved' (limpio), 'spam' (bloqueado)
            $table->enum('status', ['pending', 'approved', 'spam'])
                  ->default('pending')
                  ->comment('Estado del filtro antispam: pending|approved|spam');

            // Razón por la cual fue marcado como spam (nulo si aprobado)
            $table->string('spam_reason', 255)
                  ->nullable()
                  ->comment('Motivo del bloqueo: blacklisted_word|too_many_urls');

            // IP del remitente para auditoría
            $table->string('ip_address', 45)
                  ->nullable()
                  ->comment('Dirección IP del remitente');

            // Timestamps automáticos (created_at, updated_at)
            $table->timestamps();

            // Índices para optimizar consultas del dashboard
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');
            $table->index(['status', 'created_at'], 'idx_status_date');
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
