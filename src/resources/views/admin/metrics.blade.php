@extends('layouts.admin')

@section('title', 'Métricas')

@section('content')
<h1 class="page-title">📈 Métricas por Canal</h1>
<p class="page-subtitle">Volumen y tasa de spam detectado en cada canal de integración.</p>

@if($byChannel->isEmpty())
    <div class="empty-state">
        <span class="icon" aria-hidden="true">📭</span>
        <p>Aún no hay datos suficientes. Genera tráfico en algún canal para ver métricas aquí.</p>
    </div>
@else
    <div class="stats-grid">
        @foreach($byChannel as $row)
            @php
                $rate = $row->total > 0 ? round(($row->spam_count / $row->total) * 100, 1) : 0;
            @endphp
            <div class="stat-card">
                <div class="stat-icon">📡</div>
                <div class="stat-value">{{ $row->total }}</div>
                <div class="stat-label">{{ $row->channel }} – {{ $rate }}% spam</div>
            </div>
        @endforeach
    </div>

    <div class="table-card">
        <div class="table-header">
            <span class="table-title">Detalle por canal</span>
        </div>
        <div style="overflow-x:auto;">
            <table aria-label="Métricas por canal">
                <thead>
                    <tr>
                        <th>Canal</th>
                        <th>Total analizados</th>
                        <th>Spam detectado</th>
                        <th>Limpios</th>
                        <th>Tasa de spam</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byChannel as $row)
                        @php
                            $rate = $row->total > 0 ? round(($row->spam_count / $row->total) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><span class="badge badge-channel">{{ $row->channel }}</span></td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->spam_count }}</td>
                            <td>{{ $row->total - $row->spam_count }}</td>
                            <td>{{ $rate }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
