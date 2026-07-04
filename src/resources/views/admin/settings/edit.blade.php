@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<h1 class="page-title">⚙️ Configuración del Filtro</h1>
<p class="page-subtitle">Ajustes globales del motor antispam, aplicados en tiempo real.</p>

<div class="card">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($settings as $setting)
            <div class="form-group">
                <label for="setting-{{ $setting->key }}">
                    {{ $setting->description ?? $setting->key }}
                </label>

                @if($setting->type === 'bool')
                    <select id="setting-{{ $setting->key }}" name="{{ $setting->key }}">
                        <option value="1" {{ $setting->value ? 'selected' : '' }}>Activado</option>
                        <option value="0" {{ !$setting->value ? 'selected' : '' }}>Desactivado</option>
                    </select>
                @elseif($setting->type === 'int')
                    <input type="number" id="setting-{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}" min="0">
                @else
                    <input type="text" id="setting-{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @endif
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
    </form>
</div>
@endsection
