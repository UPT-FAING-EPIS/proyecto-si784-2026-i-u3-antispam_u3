<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Aegis Filter – API Routes
|--------------------------------------------------------------------------
| Endpoints JSON para integrar el antispam desde cualquier página web.
|
| Base URL: /api
|  POST /api/comments              → Enviar comentario (analiza + guarda)
|  GET  /api/comments              → Listar comentarios aprobados
|  POST /api/check-spam            → Solo verificar spam, sin guardar
|  POST /api/analyze                → Endpoint simplificado (Alexa Bridge)
|  POST /api/integrations/check-spam→ Canal externo autenticado por API key
|  POST /api/telegram/webhook      → Webhook de Telegram (bot moderador)
|  POST /api/telegram/setup-webhook→ Configurar webhook (requiere sesión admin)
|  GET  /api/telegram/status       → Estado del bot (requiere sesión admin)
*/

// ─── Rutas del Foro (Comentarios) ─────────────────────

// Enviar un comentario desde un sitio externo
Route::post('/comments', [CommentController::class, 'apiStore'])
    ->middleware('throttle:public-api')
    ->name('api.comments.store');

// Obtener comentarios aprobados (para mostrar en la página externa)
Route::get('/comments', [CommentController::class, 'apiIndex'])
    ->name('api.comments.index');

// Solo verificar si un texto es spam, sin guardarlo
Route::post('/check-spam', [CommentController::class, 'checkSpam'])
    ->middleware('throttle:public-api')
    ->name('api.check-spam');

// Endpoint simplificado para Alexa Bridge
// Recibe: {"text": "..."} → Devuelve: {"is_spam": true/false}
Route::post('/analyze', [CommentController::class, 'analyzeText'])
    ->middleware('throttle:public-api')
    ->name('api.analyze');

// Canal externo (ej. WordPress) autenticado por Integration Key
Route::post('/integrations/check-spam', [CommentController::class, 'integrationCheckSpam'])
    ->middleware(['throttle:public-api', 'integration_key'])
    ->name('api.integrations.check-spam');

// ─── Rutas del Bot de Telegram ────────────────────────

// Webhook: Telegram envía updates aquí automáticamente
Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook'])
    ->middleware('throttle:webhook')
    ->name('telegram.webhook');

// Configurar el webhook (ejecutar una vez) – requiere sesión de admin
Route::post('/telegram/setup-webhook', [TelegramController::class, 'setupWebhook'])
    ->middleware(['web', 'auth', 'throttle:public-api'])
    ->name('telegram.setup-webhook');

// Estado del bot y estadísticas – requiere sesión de admin
Route::get('/telegram/status', [TelegramController::class, 'status'])
    ->middleware(['web', 'auth', 'throttle:public-api'])
    ->name('telegram.status');

