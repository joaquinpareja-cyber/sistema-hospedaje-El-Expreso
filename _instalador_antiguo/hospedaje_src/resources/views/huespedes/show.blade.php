@extends('layouts.app')
@section('title', $huesped->nombre_completo)
@section('page-title', 'Ficha del Huésped')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('huespedes.index') }}">Huéspedes</a></li>
    <li class="breadcrumb-item active">{{ $huesped->nombre_completo }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <i class="fas fa-user-circle fa-5x text-secondary mb-3"></i>
                <h3 class="profile-username">{{ $huesped->nombre_completo }}</h3>
                <p class="text-muted">{{ $huesped->nacionalidad ?? 'Nacionalidad no registrada' }}</p>
                <div class="text-left mt-3">
                    <p><i class="fas fa-id-card mr-2 text-primary"></i><strong>{{ $huesped->tipo_documento }}:</strong> {{ $huesped->num_documento }}</p>
                    @if($huesped->telefono)
                    <p><i class="fas fa-phone mr-2 text-success"></i>{{ $huesped->telefono }}</p>
                    @endif
                    @if($huesped->email)
                    <p><i class="fas fa-envelope mr-2 text-info"></i>{{ $huesped->email }}</p>
                    @endif
                    @if($huesped->fecha_nacimiento)
                    <p><i class="fas fa-birthday-cake mr-2 text-warning"></i>{{ $huesped->fecha_nacimiento->format('d/m/Y') }} ({{ $huesped->edad }} años)</p>
                    @endif
                    @if($huesped->direccion)
                    <p><i class="fas fa-map-marker-alt mr-2 text-danger"></i>{{ $huesped->direccion }}</p>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <div class="row text-center">
                    <div class="col-6 border-right">
                        <h4 class="font-weight-bold">{{ $totalEstancias }}</h4>
                        <small>Estancias</small>
                    </div>
                    <div class="col-6">
                        <h4 class="font-weight-bold text-success">S/ {{ number_format($totalGastado, 2) }}</h4>
                        <small>Total Gastado</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer text-center">
                <a href="{{ route('huespedes.edit', $huesped) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit mr-2"></i>Editar Datos
                </a>
                <a href="{{ route('reservas.create', ['huesped_id' => $huesped->id]) }}" class="btn btn-success btn-block">
                    <i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Historial de Reservas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Historial de Reservas</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Código</th><th>Habitación</th><th>Entrada</th><th>Salida</th><th>Total</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @forelse($huesped->reservas as $r)
                        <tr>
                            <td><a href="{{ route('reservas.show', $r) }}">{{ $r->codigo }}</a></td>
                            <td>{{ $r->habitacion->numero }} ({{ $r->habitacion->tipoHabitacion->nombre }})</td>
                            <td>{{ $r->fecha_entrada->format('d/m/Y') }}</td>
                            <td>{{ $r->fecha_salida->format('d/m/Y') }}</td>
                            <td>S/ {{ number_format($r->total, 2) }}</td>
                            <td><span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin reservas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Observaciones --}}
        @if($huesped->observaciones)
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Observaciones</h3></div>
            <div class="card-body">{{ $huesped->observaciones }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
