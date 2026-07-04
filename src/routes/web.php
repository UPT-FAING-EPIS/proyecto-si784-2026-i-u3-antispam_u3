<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\IntegrationKeyController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Aegis Filter – Web Routes
|--------------------------------------------------------------------------
*/

// Landing page pública tipo Akismet.
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ─── Rutas del Formulario Público ────────────────────

Route::get('/comentarios', [CommentController::class, 'showForm'])
    ->name('comments.form');

Route::post('/comentarios', [CommentController::class, 'store'])
    ->name('comments.store');

// ─── Autenticación ────────────────────────────────────

Route::get('/login', [AuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Rutas del Dashboard de Administración ───────────

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CommentController::class, 'dashboard'])
        ->name('dashboard');

    Route::patch('/dashboard/{id}/approve', [CommentController::class, 'approve'])
        ->name('comments.approve');

    Route::delete('/dashboard/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
        Route::post('/blacklist', [BlacklistController::class, 'store'])->name('blacklist.store');
        Route::patch('/blacklist/{blacklistWord}/toggle', [BlacklistController::class, 'toggle'])->name('blacklist.toggle');
        Route::delete('/blacklist/{blacklistWord}', [BlacklistController::class, 'destroy'])->name('blacklist.destroy');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/integration-keys', [IntegrationKeyController::class, 'index'])->name('integration-keys.index');
        Route::post('/integration-keys', [IntegrationKeyController::class, 'store'])->name('integration-keys.store');
        Route::patch('/integration-keys/{integrationKey}/revoke', [IntegrationKeyController::class, 'revoke'])->name('integration-keys.revoke');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::get('/metrics', [AuditLogController::class, 'metrics'])->name('audit-log.metrics');
    });
});

