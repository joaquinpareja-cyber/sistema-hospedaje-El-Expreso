@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- ===== TARJETAS PRINCIPALES ===== --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $habitacionesDisp }}</h3>
                <p>Habitaciones Disponibles</p>
            </div>
            <div class="icon"><i class="fas fa-bed"></i></div>
            <a href="{{ route('habitaciones.index') }}" class="small-box-footer">
                Ver <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $habitacionesOcupadas }}</h3>
                <p>Habitaciones Ocupadas</p>
            </div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <a href="{{ route('habitaciones.index') }}" class="small-box-footer">
                Ver <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $porcentajeOcupacion }}%</h3>
                <p>Ocupación ({{ $habitacionesOcupadas }}/{{ $totalHabitaciones }})</p>
            </div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            @if(auth()->user()->isSupervisor())
            <a href="{{ route('reportes.ocupacion') }}" class="small-box-footer">
                Reporte <i class="fas fa-arrow-circle-right"></i>
            </a>
            @else
            <a href="{{ route('habitaciones.index') }}" class="small-box-footer">
                Ver habitaciones <i class="fas fa-arrow-circle-right"></i>
            </a>
            @endif
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $habitacionesMant }}</h3>
                <p>En Mantenimiento</p>
            </div>
            <div class="icon"><i class="fas fa-tools"></i></div>
            <a href="{{ route('habitaciones.index') }}" class="small-box-footer">
                Ver <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ===== FILA DE ESTADÍSTICAS ===== --}}
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-sign-in-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Check-ins Hoy</span>
                <span class="info-box-number">{{ $checkinsHoy }}</span>
                <span class="progress-description text-muted">Llegadas del día</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-sign-out-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Check-outs Hoy</span>
                <span class="info-box-number">{{ $checkoutsHoy }}</span>
                <span class="progress-description text-muted">Salidas del día</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Huéspedes</span>
                <span class="info-box-number">{{ $totalHuespedes }}</span>
                <span class="progress-description text-muted">Registrados en el sistema</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Reservas este Mes</span>
                <span class="info-box-number">{{ $reservasMes }}</span>
                <span class="progress-description text-muted">{{ now()->locale('es')->isoFormat('MMMM') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== GRÁFICOS + SIDEBAR ===== --}}
<div class="row">
    {{-- Columna izquierda: gráficos --}}
    <div class="col-md-8">

        {{-- Gráfico ocupación semanal --}}
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2 text-success"></i>Ocupación Últimos 7 Días</h3>
                <div class="card-tools">
                    <span class="text-muted small">Habitaciones ocupadas por día</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <canvas id="chartOcupacion" height="90"></canvas>
            </div>
        </div>

        {{-- Gráfico ingresos 6 meses --}}
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2 text-primary"></i>Ingresos Últimos 6 Meses</h3>
                <div class="card-tools">
                    <span class="badge badge-success px-3 py-1">Total {{ $moneda }} {{ number_format($ingresosTotales, 0) }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <canvas id="chartIngresos" height="90"></canvas>
            </div>
        </div>

    </div>

    {{-- Columna derecha: resumen y acciones --}}
    <div class="col-md-4">

        {{-- Ingresos del mes --}}
        <div class="card" style="background: linear-gradient(135deg,#1e7e34,#28a745); color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:.85rem;opacity:.85">Ingresos del Mes</div>
                        <div style="font-size:1.9rem;font-weight:700">{{ $moneda }} {{ number_format($ingresosMes, 2) }}</div>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x" style="opacity:.5"></i>
                </div>
                <hr style="border-color:rgba(255,255,255,.3);margin:10px 0">
                @if(auth()->user()->isSupervisor())
                <a href="{{ route('reportes.ingresos') }}" class="text-white small">
                    <i class="fas fa-chart-line mr-1"></i>Ver reporte completo
                </a>
                @endif
            </div>
        </div>

        {{-- Facturas pendientes --}}
        <div class="card" style="background: linear-gradient(135deg,#b55e00,#fd7e14); color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:.85rem;opacity:.85">Facturas Pendientes</div>
                        <div style="font-size:1.9rem;font-weight:700">{{ $facturasPendientes }}</div>
                    </div>
                    <i class="fas fa-file-invoice-dollar fa-2x" style="opacity:.5"></i>
                </div>
                <hr style="border-color:rgba(255,255,255,.3);margin:10px 0">
                <a href="{{ route('facturas.index') }}" class="text-white small">
                    <i class="fas fa-arrow-right mr-1"></i>Ver facturas pendientes
                </a>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-bolt mr-1 text-warning"></i>Acciones Rápidas</h3>
            </div>
            <div class="card-body p-2">
                <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva
                </a>
                <a href="{{ route('huespedes.create') }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-user-plus mr-2"></i>Nuevo Huésped
                </a>
                <a href="{{ route('calendario.disponibilidad') }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-th mr-2"></i>Ver Disponibilidad
                </a>
                @if(auth()->user()->isSupervisor())
                <a href="{{ route('reportes.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-chart-bar mr-2"></i>Reportes
                </a>
                @else
                <a href="{{ route('housekeeping.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-broom mr-2"></i>Limpieza
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ===== RESERVAS RECIENTES ===== --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-clock mr-2 text-info"></i>Reservas Recientes</h3>
                <div class="card-tools">
                    <a href="{{ route('reservas.index') }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Código</th>
                                <th>Huésped</th>
                                <th>Habitación</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservasRecientes as $r)
                            <tr>
                                <td><small class="text-muted">{{ $r->codigo }}</small></td>
                                <td>
                                    <strong>{{ $r->huesped->nombre_completo }}</strong>
                                </td>
                                <td><span class="badge badge-secondary">Hab. {{ $r->habitacion->numero }}</span></td>
                                <td>{{ $r->fecha_entrada->format('d/m/Y') }}</td>
                                <td>{{ $r->fecha_salida->format('d/m/Y') }}</td>
                                <td><strong>{{ $moneda }} {{ number_format($r->total, 2) }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('reservas.show', $r) }}" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Sin reservas registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Gráfico Ocupación 7 días ──────────────────────────────────────────────
const ocupData = @json($ocupacionSemanal);
new Chart(document.getElementById('chartOcupacion'), {
    type: 'bar',
    data: {
        labels: ocupData.map(d => d.fecha),
        datasets: [{
            label: 'Habitaciones Ocupadas',
            data: ocupData.map(d => d.ocupadas),
            backgroundColor: ocupData.map((d, i) =>
                i === ocupData.length - 1 ? 'rgba(40,167,69,0.9)' : 'rgba(40,167,69,0.55)'
            ),
            borderColor: '#28a745',
            borderWidth: 1,
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: ctx => ' ' + ctx.raw + ' habitaciones' }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, max: {{ $totalHabitaciones }} },
            x: { grid: { display: false } }
        }
    }
});

// ── Gráfico Ingresos 6 meses ──────────────────────────────────────────────
const ingData = @json($ingresosMensuales);
new Chart(document.getElementById('chartIngresos'), {
    type: 'line',
    data: {
        labels: ingData.map(d => d.mes),
        datasets: [{
            label: 'Ingresos {{ $moneda }}',
            data: ingData.map(d => parseFloat(d.total)),
            fill: true,
            backgroundColor: 'rgba(0,123,255,0.1)',
            borderColor: '#007bff',
            borderWidth: 2.5,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#007bff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' {{ $moneda }} ' + parseFloat(ctx.raw).toLocaleString('es-PE', {minimumFractionDigits: 2})
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: val => '{{ $moneda }} ' + val.toLocaleString() }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
