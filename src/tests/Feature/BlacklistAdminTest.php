<?php

namespace Tests\Feature;

use App\Models\BlacklistWord;
use App\Models\User;
use App\Services\SpamFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BlacklistAdminTest – CRUD de la lista negra desde el panel admin.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class BlacklistAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO un administrador autenticado
     *   CUANDO agrega una palabra nueva a la lista negra
     *   ENTONCES la palabra debe persistir y afectar inmediatamente a SpamFilterService
     */
    public function test_agregar_palabra_afecta_inmediatamente_al_filtro(): void
    {
        $admin = User::factory()->create();

        // Antes de agregarla, un mensaje con la palabra debe pasar limpio.
        $filterAntes = new SpamFilterService();
        $resultadoAntes = $filterAntes->analyze('Este mensaje habla de zorglubia y nada más');
        $this->assertFalse($resultadoAntes['isSpam']);

        $response = $this->actingAs($admin)->post('/admin/blacklist', [
            'word' => 'zorglubia',
        ]);

        $response->assertRedirect(route('admin.blacklist.index'));
        $this->assertDatabaseHas('blacklist_words', ['word' => 'zorglubia', 'is_active' => true]);

        // Después de agregarla (sin reiniciar nada), una nueva instancia del servicio debe detectarla.
        $filterDespues = new SpamFilterService();
        $resultadoDespues = $filterDespues->analyze('Este mensaje habla de zorglubia y nada más');
        $this->assertTrue($resultadoDespues['isSpam']);
        $this->assertEquals('blacklisted_word', $resultadoDespues['reason']);
    }

    /**
     *
     * Gherkin:
     *   DADO una palabra activa en la lista negra
     *   CUANDO el admin la desactiva
     *   ENTONCES debe dejar de afectar al filtro pero seguir existiendo en BD
     */
    public function test_desactivar_palabra_la_excluye_del_filtro(): void
    {
        $admin = User::factory()->create();
        $word = BlacklistWord::factory()->create(['word' => 'palabraprueba']);

        $response = $this->actingAs($admin)->patch("/admin/blacklist/{$word->id}/toggle");

        $response->assertRedirect(route('admin.blacklist.index'));
        $this->assertDatabaseHas('blacklist_words', ['id' => $word->id, 'is_active' => false]);

        $filter = new SpamFilterService();
        $resultado = $filter->analyze('Un mensaje con palabraprueba dentro');
        $this->assertFalse($resultado['isSpam']);
    }

    /**
     *
     * Gherkin:
     *   DADO una palabra en la lista negra
     *   CUANDO el admin la elimina
     *   ENTONCES debe desaparecer de la base de datos
     */
    public function test_eliminar_palabra_la_borra_de_bd(): void
    {
        $admin = User::factory()->create();
        $word = BlacklistWord::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/blacklist/{$word->id}");

        $response->assertRedirect(route('admin.blacklist.index'));
        $this->assertDatabaseMissing('blacklist_words', ['id' => $word->id]);
    }

    /**
     *
     * Gherkin:
     *   DADO un visitante sin sesión
     *   CUANDO intenta acceder a /admin/blacklist
     *   ENTONCES debe ser redirigido a /login
     */
    public function test_blacklist_admin_requiere_autenticacion(): void
    {
        $response = $this->get('/admin/blacklist');

        $response->assertRedirect(route('login'));
    }
}
