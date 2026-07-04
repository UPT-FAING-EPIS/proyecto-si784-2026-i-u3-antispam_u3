<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\IntegrationKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationKeyController extends Controller
{
    public function index(): View
    {
        $keys = IntegrationKey::latest()->paginate(20);
        $channels = Channel::cases();

        return view('admin.integration-keys.index', compact('keys', 'channels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'max:30'],
            'label' => ['nullable', 'string', 'max:150'],
        ]);

        $generated = IntegrationKey::generate(
            channel: $validated['channel'],
            label: $validated['label'] ?? null,
            createdBy: $request->user()->id,
        );

        return redirect()
            ->route('admin.integration-keys.index')
            ->with('success', "Key generada para el canal '{$validated['channel']}'. Cópiala ahora, no se mostrará de nuevo: {$generated['plainKey']}");
    }

    public function revoke(IntegrationKey $integrationKey): RedirectResponse
    {
        $integrationKey->update(['is_active' => false]);

        return redirect()
            ->route('admin.integration-keys.index')
            ->with('success', "Key '{$integrationKey->key_prefix}…' revocada.");
    }
}
