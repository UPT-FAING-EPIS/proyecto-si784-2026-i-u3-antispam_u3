@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
<h1 class="page-title">📋 Audit Log</h1>
<p class="page-subtitle">Historial de todos los análisis de spam, sin importar el canal de origen.</p>

<div class="filter-bar" style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.25rem;">
    <span class="filter-label" style="font-size:.84rem; color:var(--muted); font-weight:500;">Canal:</span>
    <a href="{{ route('admin.audit-log.index') }}" class="filter-btn {{ !$channel ? 'active' : '' }}">Todos</a>
    @foreach($channels as $c)
        <a href="{{ route('admin.audit-log.index', ['channel' => $c->value, 'spam' => $spamFilter]) }}"
           class="filter-btn {{ $channel === $c->value ? 'active' : '' }}">{{ $c->value }}</a>
    @endforeach

    <span class="filter-label" style="font-size:.84rem; color:var(--muted); font-weight:500; margin-left:1rem;">Resultado:</span>
    <a href="{{ route('admin.audit-log.index', ['channel' => $channel]) }}" class="filter-btn {{ !$spamFilter ? 'active' : '' }}">Todos</a>
    <a href="{{ route('admin.audit-log.index', ['channel' => $channel, 'spam' => 'spam']) }}" class="filter-btn {{ $spamFilter === 'spam' ? 'active' : '' }}">🚫 Spam</a>
    <a href="{{ route('admin.audit-log.index', ['channel' => $channel, 'spam' => 'clean']) }}" class="filter-btn {{ $spamFilter === 'clean' ? 'active' : '' }}">✅ Limpios</a>
</div>

<style>
.filter-btn {
    padding:.4rem .9rem; border-radius:999px;
    font-size:.82rem; font-weight:500; cursor:pointer;
    text-decoration:none; border:1px solid var(--border);
    color:var(--sub); background:transparent; transition:var(--transition);
}
.filter-btn:hover, .filter-btn.active {
    background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.4); color:var(--text); font-weight:600;
}
</style>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Registros</span>
        <span class="table-count">{{ $logs->total() }} resultado(s)</span>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <span class="icon" aria-hidden="true">📭</span>
            <p>No hay registros con estos filtros.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table aria-label="Audit log">
                <thead>
                    <tr>
                        <th>Canal</th>
                        <th>Autor</th>
                        <th>Contenido</th>
                        <th>Resultado</th>
                        <th>Razón</th>
                        <th>Score</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td><span class="badge badge-channel">{{ $log->channel }}</span></td>
                        <td>{{ $log->author ?? '—' }}</td>
                        <td style="max-width:320px; color:var(--sub);">
                            <span title="{{ $log->content }}" style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $log->content }}
                            </span>
                        </td>
                        <td>
                            @if($log->is_spam)
                                <span class="badge badge-spam">🚫 Spam</span>
                            @else
                                <span class="badge badge-approved">✅ Limpio</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem; color:var(--muted); font-family:monospace;">{{ $log->reason ?? '—' }}</td>
                        <td>{{ $log->score }}</td>
                        <td style="color:var(--muted); font-size:.8rem; white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="pagination-wrap">
            {{ $logs->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
