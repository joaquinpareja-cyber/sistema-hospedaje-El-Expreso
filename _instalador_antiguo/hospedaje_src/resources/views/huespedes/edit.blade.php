@extends('layouts.app')
@section('title', "Editar: {$huesped->nombre_completo}")
@section('page-title', "Editar Huésped")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('huespedes.index') }}">Huéspedes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('huespedes.show', $huesped) }}">{{ $huesped->nombre_completo }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Editar: {{ $huesped->nombre_completo }}</h3></div>
    <form action="{{ route('huespedes.update', $huesped) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre(s) <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $huesped->nombre) }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Apellido(s) <span class="text-danger">*</span></label>
                        <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror"
                               value="{{ old('apellido', $huesped->apellido) }}" required>
                        @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" class="form-control select2" required>
                            @foreach(['DNI','Pasaporte','CE','RUC'] as $td)
                                <option value="{{ $td }}" {{ old('tipo_documento', $huesped->tipo_documento) == $td ? 'selected' : '' }}>{{ $td }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número de Documento <span class="text-danger">*</span></label>
                        <input type="text" name="num_documento" class="form-control @error('num_documento') is-invalid @enderror"
                               value="{{ old('num_documento', $huesped->num_documento) }}" required>
                        @error('num_documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Género</label>
                        <select name="genero" class="form-control select2">
                            <option value="">Sin especificar</option>
                            <option value="M" {{ old('genero', $huesped->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero', $huesped->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('genero', $huesped->genero) == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="{{ old('fecha_nacimiento', $huesped->fecha_nacimiento?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nacionalidad</label>
                        <input type="text" name="nacionalidad" class="form-control"
                               value="{{ old('nacionalidad', $huesped->nacionalidad) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono', $huesped->telefono) }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $huesped->email) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion', $huesped->direccion) }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $huesped->observaciones) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-2"></i>Guardar Cambios</button>
            <a href="{{ route('huespedes.show', $huesped) }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
