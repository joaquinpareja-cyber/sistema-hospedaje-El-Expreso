@extends('layouts.app')
@section('title', "Editar Habitación {$habitacion->numero}")
@section('page-title', "Editar Habitación {$habitacion->numero}")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('habitaciones.index') }}">Habitaciones</a></li>
    <li class="breadcrumb-item active">Editar {{ $habitacion->numero }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Editar Habitación {{ $habitacion->numero }}</h3></div>
    <form action="{{ route('habitaciones.update', $habitacion) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número <span class="text-danger">*</span></label>
                        <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror"
                               value="{{ old('numero', $habitacion->numero) }}" required>
                        @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Piso</label>
                        <input type="text" name="piso" class="form-control"
                               value="{{ old('piso', $habitacion->piso) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-control select2" required>
                            @foreach(['disponible','ocupada','reservada','mantenimiento'] as $est)
                                <option value="{{ $est }}" {{ old('estado', $habitacion->estado) == $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Tipo de Habitación <span class="text-danger">*</span></label>
                <select name="tipo_habitacion_id" class="form-control select2" required>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_habitacion_id', $habitacion->tipo_habitacion_id) == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }} — S/ {{ number_format($tipo->precio_base, 2) }}/noche
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $habitacion->descripcion) }}</textarea>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activa" name="activa" value="1"
                           {{ old('activa', $habitacion->activa) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activa">Habitación activa</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-2"></i>Actualizar</button>
            <a href="{{ route('habitaciones.show', $habitacion) }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
