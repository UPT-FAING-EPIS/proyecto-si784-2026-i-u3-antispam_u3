<?php

namespace App\Http\Middleware;

use App\Models\IntegrationKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntegrationKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainKey = $request->header('X-Integration-Key');

        if (!$plainKey) {
            return response()->json(['error' => 'Falta el header X-Integration-Key.'], 401);
        }

        $integrationKey = IntegrationKey::findActiveByPlainKey($plainKey);

        if (!$integrationKey) {
            return response()->json(['error' => 'Integration key inválida o revocada.'], 403);
        }

        $integrationKey->update(['last_used_at' => now()]);

        $request->attributes->set('integration_channel', $integrationKey->channel);

        return $next($request);
    }
}
