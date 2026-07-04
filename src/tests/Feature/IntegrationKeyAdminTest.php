<?php

namespace Tests\Feature;

use App\Models\IntegrationKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IntegrationKeyAdminTest – Emisión/revocación de keys y el endpoint protegido.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class IntegrationKeyAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO un administrador autenticado
     *   CUANDO emite una key para el canal "wordpress"
     *   ENTONCES debe poder usarla para llamar /api/integrations/check-spam con éxito (200)
     */
    public function test_key_emitida_permite_llamar_al_endpoint_protegido(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/integration-keys', [
            'channel' => 'wordpress',
            'label' => 'Test plugin',
        ]);

        $key = IntegrationKey::first();
        $this->assertNotNull($key);

        // La key en texto plano solo viaja en el mensaje flash de éxito (se muestra una sola vez).
        $plainKey = $this->extractPlainKeyFromSession();

        $response = $this->withHeaders(['X-Integration-Key' => $plainKey])
            ->postJson('/api/integrations/check-spam', [
                'content' => 'Mensaje normal sin spam para probar el canal',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['isSpam', 'score', 'reason', 'detail']);
        $this->assertDatabaseHas('analysis_logs', ['channel' => 'wordpress']);
    }

    /**
     *
     * Gherkin:
     *   DADO que no se envía ninguna integration key
     *   CUANDO se llama a /api/integrations/check-spam
     *   ENTONCES debe rechazarse con 401
     */
    public function test_endpoint_protegido_rechaza_sin_key(): void
    {
        $response = $this->postJson('/api/integrations/check-spam', [
            'content' => 'Mensaje de prueba',
        ]);

        $response->assertStatus(401);
    }

    /**
     *
     * Gherkin:
     *   DADO una key revocada
     *   CUANDO se usa para llamar al endpoint protegido
     *   ENTONCES debe rechazarse con 403
     */
    public function test_endpoint_protegido_rechaza_key_revocada(): void
    {
        $generated = IntegrationKey::generate('wordpress', 'Revocada');
        $generated['model']->update(['is_active' => false]);

        $response = $this->withHeaders(['X-Integration-Key' => $generated['plainKey']])
            ->postJson('/api/integrations/check-spam', [
                'content' => 'Mensaje de prueba',
            ]);

        $response->assertStatus(403);
    }

    /**
     *
     * Gherkin:
     *   DADO una key activa
     *   CUANDO el admin la revoca
     *   ENTONCES debe dejar de funcionar para el endpoint protegido
     */
    public function test_revocar_key_desde_el_admin_la_invalida(): void
    {
        $admin = User::factory()->create();
        $generated = IntegrationKey::generate('wordpress', 'A revocar');

        $this->actingAs($admin)->patch("/admin/integration-keys/{$generated['model']->id}/revoke");

        $this->assertDatabaseHas('integration_keys', ['id' => $generated['model']->id, 'is_active' => false]);

        $response = $this->withHeaders(['X-Integration-Key' => $generated['plainKey']])
            ->postJson('/api/integrations/check-spam', [
                'content' => 'Mensaje de prueba',
            ]);

        $response->assertStatus(403);
    }

    private function extractPlainKeyFromSession(): string
    {
        $message = session('success');
        preg_match('/: (afk_\S+)$/', $message, $matches);

        return $matches[1];
    }
}
