<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlacklistWord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlacklistController extends Controller
{
    public function index(): View
    {
        $words = BlacklistWord::latest()->paginate(20);

        return view('admin.blacklist.index', compact('words'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'min:2', 'max:191', 'unique:blacklist_words,word'],
        ], [
            'word.unique' => 'Esa palabra ya está en la lista negra.',
        ]);

        BlacklistWord::create([
            'word' => $validated['word'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.blacklist.index')
            ->with('success', "Palabra '{$validated['word']}' agregada a la lista negra.");
    }

    public function toggle(BlacklistWord $blacklistWord): RedirectResponse
    {
        $blacklistWord->update(['is_active' => !$blacklistWord->is_active]);

        return redirect()
            ->route('admin.blacklist.index')
            ->with('success', "Palabra '{$blacklistWord->word}' actualizada.");
    }

    public function destroy(BlacklistWord $blacklistWord): RedirectResponse
    {
        $word = $blacklistWord->word;
        $blacklistWord->delete();

        return redirect()
            ->route('admin.blacklist.index')
            ->with('success', "Palabra '{$word}' eliminada de la lista negra.");
    }
}
