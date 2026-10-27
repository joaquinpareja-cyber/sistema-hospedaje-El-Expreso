@extends('layouts.app')
@section('title', 'Tipos de Habitación')
@section('page-title', 'Tipos de Habitación')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('habitaciones.index') }}">Habitaciones</a></li>
    <li class="breadcrumb-item active">Tipos</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Tipos de Habitación</h3>
        <div class="card-tools">
            <a href="{{ route('tipo-habitaciones.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Nuevo Tipo
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Capacidad</th>
                    <th class="text-right">Precio/noche</th>
                    <th class="text-center">Habitaciones</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tipos as $tipo)
                <tr>
                    <td><strong>{{ $tipo->nombre }}</strong></td>
                    <td class="text-muted"><small>{{ $tipo->descripcion ?? '—' }}</small></td>
                    <td class="text-center">
                        <i class="fas fa-user mr-1 text-muted"></i>{{ $tipo->capacidad }}
                    </td>
                    <td class="text-right font-weight-bold text-success">
                        S/ {{ number_format($tipo->precio_base, 2) }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $tipo->habitaciones_count }}</span>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('tipo-habitaciones.toggle', $tipo) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-xs {{ $tipo->activo ? 'btn-success' : 'btn-secondary' }}"
                                    title="{{ $tipo->activo ? 'Activo — clic para desactivar' : 'Inactivo — clic para activar' }}">
                                <i class="fas fa-{{ $tipo->activo ? 'check' : 'times' }}"></i>
                                {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('tipo-habitaciones.edit', $tipo) }}"
                           class="btn btn-xs btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('tipo-habitaciones.destroy', $tipo) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar tipo «{{ $tipo->nombre }}»?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No hay tipos de habitación registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        <small>{{ $tipos->count() }} tipo(s) registrado(s)</small>
    </div>
</div>
@endsection
