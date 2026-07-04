<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::all();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach (Setting::all() as $setting) {
            if (!$request->has($setting->key)) {
                continue;
            }

            $value = match ($setting->type) {
                'bool' => $request->boolean($setting->key) ? '1' : '0',
                default => $request->input($setting->key),
            };

            Setting::set($setting->key, $value);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Configuración actualizada correctamente.');
    }
}
