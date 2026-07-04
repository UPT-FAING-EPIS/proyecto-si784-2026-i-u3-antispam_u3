<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * RateLimitTest – Límite de peticiones en rutas públicas de la API.
 *
 * Curso: SI784 – Calidad y Pruebas de Software
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     *
     * Gherkin:
     *   DADO el limitador "public-api" (30 req/min por IP)
     *   CUANDO se superan las 30 peticiones a /api/check-spam desde la misma IP
     *   ENTONCES la petición 31 debe rechazarse con 429
     */
    public function test_check_spam_responde_429_tras_superar_el_limite(): void
    {
        RateLimiter::clear('public-api:127.0.0.1');

        $payload = [
            'author' => 'Tester',
            'content' => 'Mensaje normal repetido para probar el rate limit',
        ];

        for ($i = 0; $i < 30; $i++) {
            $response = $this->postJson('/api/check-spam', $payload);
            $response->assertStatus(200);
        }

        $response = $this->postJson('/api/check-spam', $payload);
        $response->assertStatus(429);
    }

    /**
     *
     * Gherkin:
     *   DADO la ruta /api/check-spam
     *   CUANDO se inspecciona su configuración de middleware
     *   ENTONCES debe incluir el limitador "throttle:public-api"
     */
    public function test_check_spam_tiene_el_middleware_de_throttle_asignado(): void
    {
        $route = app('router')->getRoutes()->getByName('api.check-spam');

        $this->assertContains('throttle:public-api', $route->gatherMiddleware());
    }
}
