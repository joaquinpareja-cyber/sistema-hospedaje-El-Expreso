@extends('layouts.guest')
@section('title', 'Recuperar contraseña')

@section('content')
<p class="text-muted text-center mb-4">
    Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        </div>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="Correo electrónico" value="{{ old('email') }}" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-paper-plane mr-2"></i>Enviar enlace
    </button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('login') }}" class="text-secondary"><i class="fas fa-arrow-left mr-1"></i>Volver al inicio de sesión</a>
</div>
@endsection
