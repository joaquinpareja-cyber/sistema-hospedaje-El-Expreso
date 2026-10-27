@extends('layouts.app')
@section('title', 'Disponibilidad por Habitación')
@section('page-title', 'Disponibilidad — ' . $inicio->locale('es')->isoFormat('MMMM YYYY'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('calendario.index') }}">Calendario</a></li>
    <li class="breadcrumb-item active">Disponibilidad</li>
@endsection

@push('styles')
<style>
    .tabla-disp th, .tabla-disp td { padding: 4px 6px; font-size: .75rem; white-space: nowrap; }
    .tabla-disp thead th { background: #343a40; color: #fff; text-align: center; }
    .tabla-disp td.dia-header { background: #f8f9fa; font-weight: 600; }
    .tabla-disp td { text-align: center; }
    .celda-disponible  { background: #d4edda; }
    .celda-ocupada     { background: #f8d7da; font-weight: 700; color: #721c24; }
    .celda-reservada   { background: #cce5ff; font-weight: 700; color: #004085; }
    .celda-mant        { background: #fff3cd; color: #856404; }
    .celda-hoy         { outline: 2px solid #007bff; outline-offset: -2px; }
    .hab-info          { text-align: left; min-width: 140px; }
    .nav-mes .btn      { min-width: 100px; }
</style>
@endpush

@section('content')

{{-- Navegación de mes --}}
<div class="d-flex justify-content-between align-items-center mb-3 nav-mes">
    @php
        $mesPrev = \Carbon\Carbon::createFromDate($anio, $mes, 1)->subMonth();
        $mesSig  = \Carbon\Carbon::createFromDate($anio, $mes, 1)->addMonth();
    @endphp
    <a href="{{ route('calendario.disponibilidad', ['mes' => $mesPrev->month, 'anio' => $mesPrev->year]) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-chevron-left mr-1"></i>{{ $mesPrev->locale('es')->isoFormat('MMM YYYY') }}
    </a>
    <h5 class="mb-0 font-weight-bold">
        {{ $inicio->locale('es')->isoFormat('MMMM YYYY') | ucfirst }}
    </h5>
    <a href="{{ route('calendario.disponibilidad', ['mes' => $mesSig->month, 'anio' => $mesSig->year]) }}"
       class="btn btn-outline-secondary btn-sm">
        {{ $mesSig->locale('es')->isoFormat('MMM YYYY') }}<i class="fas fa-chevron-right ml-1"></i>
    </a>
</div>

{{-- Leyenda --}}
<div class="mb-3 d-flex flex-wrap">
    <span class="badge mr-2 px-3 py-2" style="background:#d4edda;color:#155724;border:1px solid #c3e6cb">Disponible</span>
    <span class="badge mr-2 px-3 py-2" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb">Ocupada</span>
    <span class="badge mr-2 px-3 py-2" style="background:#cce5ff;color:#004085;border:1px solid #b8daff">Reservada</span>
    <span class="badge mr-2 px-3 py-2" style="background:#fff3cd;color:#856404;border:1px solid #ffeeba">Mantenimiento</span>
    <span class="badge px-3 py-2" style="background:#e2e3e5;color:#383d41;border:1px solid #d6d8db">Sin servicio</span>
</div>

{{-- Tabla principal --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered tabla-disp mb-0">
            <thead>
                <tr>
                    <th class="hab-info">Habitación</th>
                    @for($d = 0; $d < $dias; $d++)
                        @php $dia = $inicio->copy()->addDays($d); @endphp
                        <th class="{{ $dia->isToday() ? 'table-primary' : '' }}">
                            <div>{{ $dia->format('D') }}</div>
                            <div>{{ $dia->format('d') }}</div>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($habitaciones as $hab)
                <tr>
                    <td class="hab-info">
                        <strong>Hab. {{ $hab->numero }}</strong><br>
                        <small class="text-muted">{{ $hab->tipoHabitacion->nombre }}</small><br>
                        <small class="text-success">S/ {{ number_format($hab->tipoHabitacion->precio_base, 2) }}</small>
                    </td>
                    @for($d = 0; $d < $dias; $d++)
                        @php
                            $dia = $inicio->copy()->addDays($d);
                            $reservaDelDia = null;
                            foreach($hab->reservas as $r) {
                                if ($dia->between($r->fecha_entrada, $r->fecha_salida->subDay())) {
                                    $reservaDelDia = $r;
                                    break;
                                }
                            }
                            $claseHoy = $dia->isToday() ? ' celda-hoy' : '';
                            if (!$hab->activa) {
                                $clase = 'celda-mant';
                                $texto = '✕';
                                $title = 'Sin servicio';
                            } elseif ($reservaDelDia) {
                                if ($reservaDelDia->estado === 'checkin') {
                                    $clase = 'celda-ocupada'; $texto = 'OC'; $title = $reservaDelDia->huesped->apellido;
                                } else {
                                    $clase = 'celda-reservada'; $texto = 'RES'; $title = $reservaDelDia->codigo;
                                }
                            } elseif ($hab->estado === 'mantenimiento') {
                                $clase = 'celda-mant'; $texto = 'MT'; $title = 'Mantenimiento';
                            } else {
                                $clase = 'celda-disponible'; $texto = '✓'; $title = 'Disponible';
                            }
                        @endphp
                        <td class="{{ $clase }}{{ $claseHoy }}"
                            title="{{ $title }}"
                            @if($reservaDelDia)
                                style="cursor:pointer"
                                onclick="window.location='{{ route('reservas.show', $reservaDelDia) }}'"
                            @endif>
                            {{ $texto }}
                        </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="text-right mt-2">
    <a href="{{ route('calendario.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-calendar-alt mr-1"></i>Vista Calendario
    </a>
</div>

@endsection
