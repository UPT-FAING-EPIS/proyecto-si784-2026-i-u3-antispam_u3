<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AuthTest – Login del panel de administración.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO un usuario administrador registrado
     *   CUANDO envía credenciales correctas a /login
     *   ENTONCES debe ser autenticado y redirigido al dashboard
     */
    public function test_login_con_credenciales_validas_autentica_al_usuario(): void
    {
        $user = User::factory()->create(['password' => bcrypt('clave-segura-123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'clave-segura-123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     *
     * Gherkin:
     *   DADO un usuario administrador registrado
     *   CUANDO envía una contraseña incorrecta
     *   ENTONCES debe ser rechazado y permanecer como invitado
     */
    public function test_login_con_credenciales_invalidas_es_rechazado(): void
    {
        $user = User::factory()->create(['password' => bcrypt('clave-correcta')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'clave-incorrecta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     *
     * Gherkin:
     *   DADO un visitante sin sesión iniciada
     *   CUANDO intenta acceder a /dashboard
     *   ENTONCES debe ser redirigido a /login
     */
    public function test_dashboard_redirige_a_login_sin_sesion(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     *
     * Gherkin:
     *   DADO un administrador con sesión activa
     *   CUANDO hace logout
     *   ENTONCES la sesión debe invalidarse y volver a redirigir a login
     */
    public function test_logout_invalida_la_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
