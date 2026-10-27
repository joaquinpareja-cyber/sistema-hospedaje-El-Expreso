@extends('layouts.app')
@section('title', "Editar: {$tipoHabitacion->nombre}")
@section('page-title', "Editar Tipo: {$tipoHabitacion->nombre}")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tipo-habitaciones.index') }}">Tipos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit mr-2"></i>Editar: {{ $tipoHabitacion->nombre }}
        </h3>
    </div>
    <form action="{{ route('tipo-habitaciones.update', $tipoHabitacion) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">

            <div class="form-group">
                <label>Nombre del Tipo <span class="text-danger">*</span></label>
                <input type="text" name="nombre"
                       class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $tipoHabitacion->nombre) }}"
                       required>
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $tipoHabitacion->descripcion) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacidad <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                            </div>
                            <input type="number" name="capacidad"
                                   class="form-control @error('capacidad') is-invalid @enderror"
                                   value="{{ old('capacidad', $tipoHabitacion->capacidad) }}"
                                   min="1" max="20" required>
                            @error('capacidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Base / Noche <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" name="precio_base"
                                   class="form-control @error('precio_base') is-invalid @enderror"
                                   value="{{ old('precio_base', $tipoHabitacion->precio_base) }}"
                                   min="0" step="0.01" required>
                            @error('precio_base')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Cambiar el precio no afecta reservas ya creadas.
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo"
                           name="activo" value="1"
                           {{ old('activo', $tipoHabitacion->activo) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activo">Tipo activo</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="{{ route('tipo-habitaciones.index') }}" class="btn btn-secondary ml-2">
                Cancelar
            </a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
