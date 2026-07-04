@extends('layouts.admin')

@section('title', 'Iniciar sesión')

@section('extra-style')
.login-wrap { max-width:420px; margin:4rem auto 0; }
.login-icon { text-align:center; font-size:2.5rem; margin-bottom:.5rem; }
.login-title { text-align:center; font-size:1.3rem; font-weight:800; margin-bottom:.25rem; }
.login-subtitle { text-align:center; color:var(--muted); font-size:.85rem; margin-bottom:1.5rem; }
.checkbox-row { display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; }
.checkbox-row input { width:auto; }
.checkbox-row label { margin:0; font-size:.85rem; }
@endsection

@section('content')
<div class="login-wrap">
    <div class="login-icon">🛡️</div>
    <h1 class="login-title">Aegis Filter</h1>
    <p class="login-subtitle">Acceso al panel de administración</p>

    <div class="card">
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                ❌ {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                Iniciar sesión
            </button>
        </form>
    </div>
</div>
@endsection
