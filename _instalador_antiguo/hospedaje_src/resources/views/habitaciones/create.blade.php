@extends('layouts.app')
@section('title', 'Nueva Habitación')
@section('page-title', 'Nueva Habitación')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('habitaciones.index') }}">Habitaciones</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-2"></i>Registrar Habitación</h3></div>
    <form action="{{ route('habitaciones.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número de Habitación <span class="text-danger">*</span></label>
                        <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror"
                               value="{{ old('numero') }}" placeholder="Ej: 101" maxlength="10" required>
                        @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Piso</label>
                        <input type="text" name="piso" class="form-control @error('piso') is-invalid @enderror"
                               value="{{ old('piso') }}" placeholder="Ej: 1">
                        @error('piso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-control select2 @error('estado') is-invalid @enderror" required>
                            @foreach(['disponible','ocupada','reservada','mantenimiento'] as $est)
                                <option value="{{ $est }}" {{ old('estado') == $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                            @endforeach
                        </select>
                        @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Tipo de Habitación <span class="text-danger">*</span></label>
                <select name="tipo_habitacion_id" class="form-control select2 @error('tipo_habitacion_id') is-invalid @enderror" required>
                    <option value="">Selecciona un tipo...</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}"
                                data-precio="{{ $tipo->precio_base }}"
                                {{ old('tipo_habitacion_id') == $tipo->id ? 'selected' : '' }}>
                            {{ $tipo->nombre }} — S/ {{ number_format($tipo->precio_base, 2) }}/noche (cap. {{ $tipo->capacidad }} personas)
                        </option>
                    @endforeach
                </select>
                @error('tipo_habitacion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"
                          placeholder="Descripción adicional de la habitación...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="activa" name="activa" value="1"
                           {{ old('activa', '1') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activa">Habitación activa en el sistema</label>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Guardar Habitación</button>
            <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
