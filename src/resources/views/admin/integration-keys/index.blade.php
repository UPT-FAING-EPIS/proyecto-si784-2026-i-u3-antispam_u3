@extends('layouts.admin')

@section('title', 'Integration Keys')

@section('content')
<h1 class="page-title">🔑 Integration Keys</h1>
<p class="page-subtitle">Credenciales para que servicios externos (ej. WordPress) consuman <code>/api/integrations/check-spam</code>.</p>

<div class="card">
    <div class="card-header">
        <span class="card-title">Emitir nueva key</span>
    </div>
    <form action="{{ route('admin.integration-keys.store') }}" method="POST" class="form-row">
        @csrf
        <div class="form-group">
            <label for="channel">Canal</label>
            <select id="channel" name="channel" required>
                @foreach($channels as $channel)
                    <option value="{{ $channel->value }}">{{ $channel->value }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="label">Etiqueta (opcional)</label>
            <input type="text" id="label" name="label" maxlength="150" placeholder="ej: Plugin WordPress producción">
        </div>
        <div class="form-group" style="flex:0; align-self:flex-end;">
            <button type="submit" class="btn btn-primary">🔑 Generar key</button>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Keys emitidas</span>
        <span class="table-count">{{ $keys->total() }} resultado(s)</span>
    </div>

    @if($keys->isEmpty())
        <div class="empty-state">
            <span class="icon" aria-hidden="true">📭</span>
            <p>Aún no se ha emitido ninguna key.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table aria-label="Integration keys">
                <thead>
                    <tr>
                        <th>Canal</th>
                        <th>Etiqueta</th>
                        <th>Prefijo</th>
                        <th>Estado</th>
                        <th>Último uso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keys as $key)
                    <tr>
                        <td><span class="badge badge-channel">{{ $key->channel }}</span></td>
                        <td>{{ $key->label ?? '—' }}</td>
                        <td><code>{{ $key->key_prefix }}…</code></td>
                        <td>
                            @if($key->is_active)
                                <span class="badge badge-active">✅ Activa</span>
                            @else
                                <span class="badge badge-inactive">⛔ Revocada</span>
                            @endif
                        </td>
                        <td>{{ $key->last_used_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                        <td class="col-actions">
                            @if($key->is_active)
                                <form action="{{ route('admin.integration-keys.revoke', $key) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Revocar esta key?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-danger">🚫 Revocar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($keys->hasPages())
        <div class="pagination-wrap">
            {{ $keys->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
