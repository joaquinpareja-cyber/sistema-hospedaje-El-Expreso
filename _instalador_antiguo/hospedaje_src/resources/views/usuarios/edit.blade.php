@extends('layouts.app')
@section('title', "Editar: {$usuario->name}")
@section('page-title', "Editar Usuario")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-edit mr-2"></i>Editar: {{ $usuario->name }}
            <span class="badge badge-{{ $usuario->role_badge }} ml-2">{{ $usuario->role_label }}</span>
        </h3>
    </div>
    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $usuario->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono', $usuario->telefono) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $usuario->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control select2">
                    @foreach(['recepcionista' => 'Recepcionista', 'supervisor' => 'Supervisor', 'admin' => 'Administrador'] as $val => $label)
                    <option value="{{ $val }}" {{ old('role', $usuario->role) == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            <hr>
            <p class="text-muted"><i class="fas fa-lock mr-1"></i>Cambiar contraseña (dejar en blanco para mantener la actual)</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo"
                           name="activo" value="1"
                           {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activo">Usuario activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
