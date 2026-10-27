@extends('layouts.app')
@section('title', 'Nuevo Tipo de Habitación')
@section('page-title', 'Nuevo Tipo de Habitación')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tipo-habitaciones.index') }}">Tipos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Registrar Tipo de Habitación</h3>
    </div>
    <form action="{{ route('tipo-habitaciones.store') }}" method="POST">
        @csrf
        <div class="card-body">

            <div class="form-group">
                <label>Nombre del Tipo <span class="text-danger">*</span></label>
                <input type="text" name="nombre"
                       class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre') }}"
                       placeholder="Ej: Suite Presidencial"
                       required>
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"
                          placeholder="Descripción de las comodidades y características...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacidad (personas) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                            </div>
                            <input type="number" name="capacidad"
                                   class="form-control @error('capacidad') is-invalid @enderror"
                                   value="{{ old('capacidad', 2) }}"
                                   min="1" max="20" required>
                            @error('capacidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Precio Base por Noche <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">S/</span>
                            </div>
                            <input type="number" name="precio_base"
                                   class="form-control @error('precio_base') is-invalid @enderror"
                                   value="{{ old('precio_base') }}"
                                   min="0" step="0.01"
                                   placeholder="0.00" required>
                            @error('precio_base')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="text-muted">Precio por defecto al crear una reserva.</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activo"
                           name="activo" value="1"
                           {{ old('activo', '1') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activo">
                        Tipo activo (visible al crear habitaciones)
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Guardar Tipo
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
