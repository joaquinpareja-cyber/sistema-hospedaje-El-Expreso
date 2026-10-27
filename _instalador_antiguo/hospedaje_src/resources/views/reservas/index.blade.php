@extends('layouts.app')
@section('title', 'Reservas')
@section('page-title', 'Gestión de Reservas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reservas</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Reservas</h3>
        <div class="card-tools">
            <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Nueva Reserva
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" class="form-inline flex-wrap">
            <input type="text" name="buscar" class="form-control form-control-sm mr-2 mb-2"
                   placeholder="Código, huésped o documento..." value="{{ request('buscar') }}" style="min-width:220px">
            <select name="estado" class="form-control form-control-sm mr-2 mb-2">
                <option value="">Todos los estados</option>
                @foreach(['pendiente','confirmada','checkin','checkout','cancelada','no_show'] as $est)
                    <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_desde" class="form-control form-control-sm mr-2 mb-2" value="{{ request('fecha_desde') }}">
            <input type="date" name="fecha_hasta" class="form-control form-control-sm mr-2 mb-2" value="{{ request('fecha_hasta') }}">
            <button type="submit" class="btn btn-secondary btn-sm mr-1 mb-2"><i class="fas fa-search mr-1"></i>Filtrar</button>
            <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary btn-sm mb-2">Limpiar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Código</th>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Noches</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $r)
                <tr>
                    <td><a href="{{ route('reservas.show', $r) }}" class="font-weight-bold">{{ $r->codigo }}</a></td>
                    <td>{{ $r->huesped->nombre_completo }}</td>
                    <td>{{ $r->habitacion->numero }} <small class="text-muted">({{ $r->habitacion->tipoHabitacion->nombre }})</small></td>
                    <td>{{ $r->fecha_entrada->format('d/m/Y') }}</td>
                    <td>{{ $r->fecha_salida->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $r->num_noches }}</td>
                    <td>S/ {{ number_format($r->total, 2) }}</td>
                    <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('reservas.show', $r) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                        @if(in_array($r->estado, ['pendiente','confirmada']))
                        <a href="{{ route('reservas.edit', $r) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                        @endif
                        @if($r->estado === 'confirmada')
                        <form action="{{ route('reservas.checkin', $r) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-success" title="Check-in" onclick="return confirm('¿Realizar check-in?')">
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>
                        @endif
                        @if($r->estado === 'checkin')
                        <form action="{{ route('reservas.checkout', $r) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-danger" title="Check-out" onclick="return confirm('¿Realizar check-out?')">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">No se encontraron reservas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $reservas->withQueryString()->links() }}
    </div>
</div>
@endsection
