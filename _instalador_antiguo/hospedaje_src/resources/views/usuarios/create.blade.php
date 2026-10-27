@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('page-title', 'Nuevo Usuario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Crear Usuario</h3>
    </div>
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="card-body">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono') }}" placeholder="+51 999 ...">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Rol en el Sistema <span class="text-danger">*</span></label>
                <select name="role" class="form-control select2 @error('role') is-invalid @enderror" required>
                    <option value="recepcionista" {{ old('role') == 'recepcionista' ? 'selected' : '' }}>
                        Recepcionista — Puede gestionar reservas y huéspedes
                    </option>
                    <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>
                        Supervisor — Acceso a reportes y configuración
                    </option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                        Administrador — Acceso total al sistema
                    </option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo"
                           name="activo" value="1" checked>
                    <label class="custom-control-label" for="activo">
                        Usuario activo (puede iniciar sesión)
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Crear Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
