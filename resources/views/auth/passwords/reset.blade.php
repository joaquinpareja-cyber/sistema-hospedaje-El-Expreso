@extends('layouts.guest')
@section('title', 'Restablecer contraseña')

@section('content')
<p class="text-muted text-center mb-4">Define tu nueva contraseña.</p>

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        </div>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="Correo electrónico" value="{{ $email ?? old('email') }}" required autofocus>
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
        </div>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Nueva contraseña" required autocomplete="new-password">
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
        </div>
        <input type="password" name="password_confirmation" class="form-control"
               placeholder="Confirmar contraseña" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-key mr-2"></i>Restablecer contraseña
    </button>
</form>
@endsection
