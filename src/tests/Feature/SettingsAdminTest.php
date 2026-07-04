<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SpamFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SettingsAdminTest – Configuración del umbral de URLs desde el panel admin.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class SettingsAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO que max_allowed_urls vale 2 (seed de la migración)
     *   CUANDO el admin lo actualiza a 5 desde /admin/settings
     *   ENTONCES SpamFilterService debe respetar el nuevo límite sin reiniciar nada
     */
    public function test_actualizar_umbral_de_urls_afecta_inmediatamente_al_filtro(): void
    {
        $admin = User::factory()->create();

        $mensajeConCuatroUrls = 'Links: https://a.com https://b.com https://c.com https://d.com';

        $filterAntes = new SpamFilterService();
        $resultadoAntes = $filterAntes->analyze($mensajeConCuatroUrls);
        $this->assertTrue($resultadoAntes['isSpam'], 'Con el límite de 2, 4 URLs debe ser spam');

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'max_allowed_urls' => 5,
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $this->assertEquals(5, Setting::get('max_allowed_urls'));

        $filterDespues = new SpamFilterService();
        $resultadoDespues = $filterDespues->analyze($mensajeConCuatroUrls);
        $this->assertFalse($resultadoDespues['isSpam'], 'Con el límite ampliado a 5, 4 URLs no debe ser spam');
    }

    /**
     *
     * Gherkin:
     *   DADO un visitante sin sesión
     *   CUANDO intenta acceder a /admin/settings
     *   ENTONCES debe ser redirigido a /login
     */
    public function test_settings_admin_requiere_autenticacion(): void
    {
        $response = $this->get('/admin/settings');

        $response->assertRedirect(route('login'));
    }
}
