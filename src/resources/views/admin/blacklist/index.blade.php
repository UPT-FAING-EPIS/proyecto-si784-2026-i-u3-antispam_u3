@extends('layouts.admin')

@section('title', 'Blacklist')

@section('content')
<h1 class="page-title">⛔ Lista Negra de Palabras</h1>
<p class="page-subtitle">Palabras y frases que activan el bloqueo automático de spam.</p>

<div class="card">
    <div class="card-header">
        <span class="card-title">Agregar palabra</span>
    </div>
    <form action="{{ route('admin.blacklist.store') }}" method="POST" class="form-row">
        @csrf
        <div class="form-group">
            <label for="word">Palabra o frase</label>
            <input type="text" id="word" name="word" value="{{ old('word') }}" required minlength="2" maxlength="191" placeholder="ej: gana dinero rápido">
            @error('word')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>
        <div class="form-group" style="flex:0; align-self:flex-end;">
            <button type="submit" class="btn btn-primary">➕ Agregar</button>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Palabras registradas</span>
        <span class="table-count">{{ $words->total() }} resultado(s)</span>
    </div>

    @if($words->isEmpty())
        <div class="empty-state">
            <span class="icon" aria-hidden="true">📭</span>
            <p>Aún no hay palabras en la lista negra.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table aria-label="Lista negra de palabras">
                <thead>
                    <tr>
                        <th>Palabra</th>
                        <th>Estado</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($words as $word)
                    <tr>
                        <td>{{ $word->word }}</td>
                        <td>
                            @if($word->is_active)
                                <span class="badge badge-active">✅ Activa</span>
                            @else
                                <span class="badge badge-inactive">⛔ Inactiva</span>
                            @endif
                        </td>
                        <td>{{ $word->created_at->format('d/m/Y H:i') }}</td>
                        <td class="col-actions">
                            <form action="{{ route('admin.blacklist.toggle', $word) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost">
                                    {{ $word->is_active ? '⏸️ Desactivar' : '▶️ Activar' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.blacklist.destroy', $word) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta palabra?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($words->hasPages())
        <div class="pagination-wrap">
            {{ $words->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
