@extends('layouts.app')
@section('title', "Habitación {$habitacion->numero}")
@section('page-title', "Habitación {$habitacion->numero}")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('habitaciones.index') }}">Habitaciones</a></li>
    <li class="breadcrumb-item active">Nro. {{ $habitacion->numero }}</li>
@endsection

@section('content')
<div class="row">

    {{-- Panel izquierdo: info de habitación --}}
    <div class="col-md-4">
        <div class="card card-{{ $habitacion->estado_badge }} card-outline">
            <div class="card-body text-center py-4">
                <i class="fas fa-bed fa-4x text-{{ $habitacion->estado_badge }} mb-3"></i>
                <h2 class="font-weight-bold mb-0">Habitación {{ $habitacion->numero }}</h2>
                <p class="text-muted mb-2">Piso {{ $habitacion->piso }}</p>
                <span class="badge badge-{{ $habitacion->estado_badge }} badge-estado px-3 py-2 mb-3"
                      style="font-size:.9rem">
                    {{ ucfirst($habitacion->estado) }}
                </span>

                <table class="table table-sm table-borderless text-left mt-2 mb-0">
                    <tr>
                        <th class="text-muted">Tipo</th>
                        <td><strong>{{ $habitacion->tipoHabitacion->nombre }}</strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Capacidad</th>
                        <td>{{ $habitacion->tipoHabitacion->capacidad }} personas</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Precio/noche</th>
                        <td class="text-success font-weight-bold">
                            S/ {{ number_format($habitacion->tipoHabitacion->precio_base, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Estado sistema</th>
                        <td>
                            <span class="badge badge-{{ $habitacion->activa ? 'success' : 'secondary' }}">
                                {{ $habitacion->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer text-center">
                @if($habitacion->descripcion)
                    <p class="text-muted mb-0"><small>{{ $habitacion->descripcion }}</small></p>
                @endif
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Cambiar Estado</h3></div>
            <div class="card-body p-2">
                @foreach(['disponible' => ['success','check-circle'],
                          'mantenimiento' => ['warning','tools'],
                          'reservada' => ['primary','bookmark']] as $est => [$color, $icon])
                    @if($habitacion->estado !== $est)
                    <form action="{{ route('habitaciones.estado', $habitacion) }}" method="POST" class="mb-1">
                        @csrf
                        <input type="hidden" name="estado" value="{{ $est }}">
                        <button type="submit" class="btn btn-{{ $color }} btn-block btn-sm">
                            <i class="fas fa-{{ $icon }} mr-2"></i>Marcar como {{ ucfirst($est) }}
                        </button>
                    </form>
                    @endif
                @endforeach
                <a href="{{ route('habitaciones.edit', $habitacion) }}" class="btn btn-warning btn-block btn-sm mt-2">
                    <i class="fas fa-edit mr-2"></i>Editar Habitación
                </a>
                <a href="{{ route('reservas.create', ['habitacion_id' => $habitacion->id]) }}"
                   class="btn btn-success btn-block btn-sm mt-1">
                    <i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva
                </a>
            </div>
        </div>
    </div>

    {{-- Panel derecho: reservas --}}
    <div class="col-md-8">

        {{-- Reservas activas --}}
        @if($reservasActivas->count())
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check mr-2 text-warning"></i>
                    Reservas Activas / Próximas ({{ $reservasActivas->count() }})
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Código</th><th>Huésped</th><th>Entrada</th><th>Salida</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($reservasActivas as $r)
                        <tr>
                            <td><a href="{{ route('reservas.show', $r) }}">{{ $r->codigo }}</a></td>
                            <td>{{ $r->huesped->nombre_completo }}</td>
                            <td>{{ $r->fecha_entrada->format('d/m/Y') }}</td>
                            <td>{{ $r->fecha_salida->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Historial de todas las reservas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Historial Completo</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" id="tablaHistorial">
                    <thead class="thead-light">
                        <tr><th>Código</th><th>Huésped</th><th>Entrada</th><th>Salida</th><th>Total</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @forelse($habitacion->reservas->sortByDesc('fecha_entrada') as $r)
                        <tr>
                            <td><a href="{{ route('reservas.show', $r) }}">{{ $r->codigo }}</a></td>
                            <td>{{ $r->huesped->nombre_completo }}</td>
                            <td>{{ $r->fecha_entrada->format('d/m/Y') }}</td>
                            <td>{{ $r->fecha_salida->format('d/m/Y') }}</td>
                            <td>S/ {{ number_format($r->total, 2) }}</td>
                            <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin historial de reservas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    $('#tablaHistorial').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        order: [[2, 'desc']],
        pageLength: 10
    });
});
</script>
@endpush
