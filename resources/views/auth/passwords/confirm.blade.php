@extends('layouts.guest')
@section('title', 'Confirmar contraseña')

@section('content')
<p class="text-muted text-center mb-4">Por seguridad, confirma tu contraseña para continuar.</p>

<form method="POST" action="{{ route('password.confirm') }}">
    @csrf
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
        </div>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Contraseña" required autocomplete="current-password">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Confirmar</button>

    @if(Route::has('password.request'))
    <div class="text-center mt-3">
        <a href="{{ route('password.request') }}" class="text-secondary">¿Olvidaste tu contraseña?</a>
    </div>
    @endif
</form>
@endsection
