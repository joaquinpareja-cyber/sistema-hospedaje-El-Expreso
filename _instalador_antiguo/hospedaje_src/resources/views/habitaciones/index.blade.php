@extends('layouts.app')
@section('title', 'Habitaciones')
@section('page-title', 'Gestión de Habitaciones')
@section('breadcrumb')
    <li class="breadcrumb-item active">Habitaciones</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bed mr-2"></i>Habitaciones</h3>
        <div class="card-tools">
            <a href="{{ route('habitaciones.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Nueva Habitación
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" class="form-inline flex-wrap gap-2">
            <select name="estado" class="form-control form-control-sm mr-2 mb-2">
                <option value="">Todos los estados</option>
                @foreach(['disponible','ocupada','reservada','mantenimiento'] as $est)
                    <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                @endforeach
            </select>
            <select name="tipo" class="form-control form-control-sm mr-2 mb-2">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ request('tipo') == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
            <select name="piso" class="form-control form-control-sm mr-2 mb-2">
                <option value="">Todos los pisos</option>
                @foreach(['1','2','3','4','5'] as $p)
                    <option value="{{ $p }}" {{ request('piso') == $p ? 'selected' : '' }}>Piso {{ $p }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm mb-2"><i class="fas fa-search mr-1"></i>Filtrar</button>
            <a href="{{ route('habitaciones.index') }}" class="btn btn-outline-secondary btn-sm mb-2">Limpiar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="row p-3">
            @forelse($habitaciones as $hab)
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card h-100 shadow-sm border-{{ $hab->estado_badge }}">
                    <div class="card-body text-center p-3">
                        <div class="mb-2">
                            <span class="badge badge-{{ $hab->estado_badge }} badge-estado px-2 py-1">{{ ucfirst($hab->estado) }}</span>
                        </div>
                        <h3 class="font-weight-bold mb-0">{{ $hab->numero }}</h3>
                        <small class="text-muted">Piso {{ $hab->piso }}</small>
                        <hr class="my-2">
                        <small class="text-primary font-weight-bold">{{ $hab->tipoHabitacion->nombre }}</small><br>
                        <small class="text-success">S/ {{ number_format($hab->tipoHabitacion->precio_base, 2) }}/noche</small>
                    </div>
                    <div class="card-footer p-1 text-center">
                        <a href="{{ route('habitaciones.show', $hab) }}" class="btn btn-xs btn-info mr-1" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('habitaciones.edit', $hab) }}" class="btn btn-xs btn-warning mr-1" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('habitaciones.destroy', $hab) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar habitación {{ $hab->numero }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-bed fa-3x mb-3"></i><br>No hay habitaciones registradas.
            </div>
            @endforelse
        </div>
    </div>

    <div class="card-footer">
        {{ $habitaciones->withQueryString()->links() }}
    </div>
</div>
@endsection
