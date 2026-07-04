<?php

namespace Tests\Feature;

use App\Models\AnalysisLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AuditLogTest – Registro y métricas de análisis por canal.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO que se envía un comentario limpio desde el foro web
     *   CUANDO se procesa la request
     *   ENTONCES debe crearse un AnalysisLog con channel="web"
     */
    public function test_comentario_web_genera_log_con_canal_correcto(): void
    {
        $this->post('/comentarios', [
            'author' => 'Usuario Normal',
            'content' => 'Este es un comentario completamente normal del foro',
        ]);

        $this->assertDatabaseHas('analysis_logs', [
            'channel' => 'web',
            'author' => 'Usuario Normal',
            'is_spam' => false,
        ]);
    }

    /**
     *
     * Gherkin:
     *   DADO que el endpoint simplificado de Alexa recibe un texto spam
     *   CUANDO se procesa la request
     *   ENTONCES debe crearse un AnalysisLog con channel="alexa" e is_spam=true
     */
    public function test_analisis_via_alexa_genera_log_con_canal_correcto(): void
    {
        $this->postJson('/api/analyze', [
            'text' => 'Compra ahora esta oferta increíble',
        ]);

        $this->assertDatabaseHas('analysis_logs', [
            'channel' => 'alexa',
            'is_spam' => true,
        ]);
    }

    /**
     *
     * Gherkin:
     *   DADO que el widget de demo de la landing llama a /api/check-spam
     *   CUANDO se procesa la request
     *   ENTONCES NO debe crearse ningún AnalysisLog (no contamina métricas)
     */
    public function test_demo_de_landing_no_genera_log(): void
    {
        $this->postJson('/api/check-spam', [
            'author' => 'Visitante Demo',
            'content' => 'Mensaje de prueba del demo de la landing page',
        ]);

        $this->assertDatabaseCount('analysis_logs', 0);
    }

    /**
     *
     * Gherkin:
     *   DADO registros de análisis en varios canales
     *   CUANDO el admin visita /admin/metrics
     *   ENTONCES debe ver los conteos agregados correctamente por canal
     */
    public function test_metrics_agrega_correctamente_por_canal(): void
    {
        $admin = User::factory()->create();

        AnalysisLog::record(['isSpam' => true, 'reason' => 'blacklisted_word', 'score' => 100], 'telegram', 'a', 'x');
        AnalysisLog::record(['isSpam' => false, 'reason' => null, 'score' => 0], 'telegram', 'b', 'y');
        AnalysisLog::record(['isSpam' => true, 'reason' => 'too_many_urls', 'score' => 80], 'alexa', 'c', 'z');

        $response = $this->actingAs($admin)->get('/admin/metrics');

        $response->assertOk();
        $response->assertSee('telegram');
        $response->assertSee('alexa');
    }

    /**
     *
     * Gherkin:
     *   DADO un visitante sin sesión
     *   CUANDO intenta acceder a /admin/audit-log
     *   ENTONCES debe ser redirigido a /login
     */
    public function test_audit_log_requiere_autenticacion(): void
    {
        $response = $this->get('/admin/audit-log');

        $response->assertRedirect(route('login'));
    }
}
