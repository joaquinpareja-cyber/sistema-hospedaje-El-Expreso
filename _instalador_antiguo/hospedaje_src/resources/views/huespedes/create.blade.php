@extends('layouts.app')
@section('title', 'Nuevo Huésped')
@section('page-title', 'Registrar Nuevo Huésped')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('huespedes.index') }}">Huéspedes</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Datos del Huésped</h3></div>
    <form action="{{ route('huespedes.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre(s) <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}" placeholder="Nombre(s)" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Apellido(s) <span class="text-danger">*</span></label>
                        <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror"
                               value="{{ old('apellido') }}" placeholder="Apellidos" required>
                        @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" class="form-control select2 @error('tipo_documento') is-invalid @enderror" required>
                            @foreach(['DNI','Pasaporte','CE','RUC'] as $td)
                                <option value="{{ $td }}" {{ old('tipo_documento') == $td ? 'selected' : '' }}>{{ $td }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número de Documento <span class="text-danger">*</span></label>
                        <input type="text" name="num_documento" class="form-control @error('num_documento') is-invalid @enderror"
                               value="{{ old('num_documento') }}" placeholder="Nro. Documento" required>
                        @error('num_documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Género</label>
                        <select name="genero" class="form-control select2">
                            <option value="">Sin especificar</option>
                            <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('genero') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="{{ old('fecha_nacimiento') }}" max="{{ now()->subYears(5)->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nacionalidad</label>
                        <input type="text" name="nacionalidad" class="form-control"
                               value="{{ old('nacionalidad') }}" placeholder="Ej: Peruana">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono / Celular</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono') }}" placeholder="+51 999 999 999">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion') }}" placeholder="Dirección completa">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2"
                          placeholder="Alergias, preferencias especiales, etc.">{{ old('observaciones') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Registrar Huésped</button>
            <a href="{{ route('huespedes.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
