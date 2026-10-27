@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- ===== TARJETAS DE HABITACIONES ===== --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $habitacionesDisp }}</h3>
                <p>Habitaciones Disponibles</p>
            </div>
            <div class="icon"><i class="fas fa-bed"></i></div>
            <a href="{{ route('habitaciones.index', ['estado' => 'disponible']) }}" class="small-box-footer">
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
            <a href="{{ route('habitaciones.index', ['estado' => 'ocupada']) }}" class="small-box-footer">
                Ver <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $habitacionesMant }}</h3>
                <p>En Mantenimiento</p>
            </div>
            <div class="icon"><i class="fas fa-tools"></i></div>
            <a href="{{ route('habitaciones.index', ['estado' => 'mantenimiento']) }}" class="small-box-footer">
                Ver <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $porcentajeOcupacion }}%</h3>
                <p>% Ocupación ({{ $habitacionesOcupadas }}/{{ $totalHabitaciones }})</p>
            </div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <a href="{{ route('reportes.ocupacion') }}" class="small-box-footer">
                Reporte <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ===== ACTIVIDAD DE HOY ===== --}}
<div class="row">
    <div class="col-md-8">

        {{-- Tarjetas actividad hoy --}}
        <div class="row">
            <div class="col-sm-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-sign-in-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Check-ins Hoy</span>
                        <span class="info-box-number">{{ $checkinsHoy }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-sign-out-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Check-outs Hoy</span>
                        <span class="info-box-number">{{ $checkoutsHoy }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-plane-arrival"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Llegadas Hoy</span>
                        <span class="info-box-number">{{ $llegadasHoy }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-plane-departure"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Salidas Hoy</span>
                        <span class="info-box-number">{{ $salidasHoy }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico ocupación semanal --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Ocupación Últimos 7 Días</h3>
            </div>
            <div class="card-body">
                <canvas id="chartOcupacion" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Ingresos del mes --}}
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-white">Ingresos del Mes</h5>
                        <h2 class="text-white font-weight-bold">S/ {{ number_format($ingresosMes, 2) }}</h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x text-white opacity-50"></i>
                </div>
                <a href="{{ route('reportes.ingresos') }}" class="text-white">Ver reporte <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        {{-- Facturas pendientes --}}
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-white">Facturas Pendientes</h5>
                        <h2 class="text-white font-weight-bold">{{ $facturasPendientes }}</h2>
                    </div>
                    <i class="fas fa-file-invoice fa-3x text-white opacity-50"></i>
                </div>
                <a href="{{ route('facturas.index', ['estado' => 'pendiente']) }}" class="text-white">Ver facturas <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Acciones Rápidas</h3></div>
            <div class="card-body p-2">
                <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-plus mr-2"></i>Nueva Reserva
                </a>
                <a href="{{ route('huespedes.create') }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-user-plus mr-2"></i>Nuevo Huésped
                </a>
                <a href="{{ route('reservas.index', ['estado' => 'confirmada']) }}" class="btn btn-info btn-block">
                    <i class="fas fa-list mr-2"></i>Reservas Confirmadas
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ===== GRÁFICO INGRESOS MENSUALES ===== --}}
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Ingresos Últimos 6 Meses</h3>
            </div>
            <div class="card-body">
                <canvas id="chartIngresos" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        {{-- Reservas recientes --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Últimas Reservas</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($reservasRecientes as $r)
                    <li class="list-group-item py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $r->huesped->nombre_completo }}</strong><br>
                                <small class="text-muted">Hab. {{ $r->habitacion->numero }} · {{ $r->fecha_entrada->format('d/m') }} → {{ $r->fecha_salida->format('d/m') }}</small>
                            </div>
                            <span class="badge badge-{{ $r->estado_badge }}">{{ ucfirst($r->estado) }}</span>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-muted text-center py-3">Sin reservas registradas</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('reservas.index') }}">Ver todas las reservas</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Gráfico Ocupación semanal
const ocupData = @json($ocupacionSemanal);
new Chart(document.getElementById('chartOcupacion'), {
    type: 'bar',
    data: {
        labels: ocupData.map(d => d.dia),
        datasets: [{
            label: 'Habitaciones Ocupadas',
            data: ocupData.map(d => d.ocupadas),
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: '#28a745',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Gráfico Ingresos mensuales
const ingData = @json($ingresosMensuales);
new Chart(document.getElementById('chartIngresos'), {
    type: 'line',
    data: {
        labels: ingData.map(d => d.mes),
        datasets: [{
            label: 'Ingresos S/',
            data: ingData.map(d => parseFloat(d.total)),
            fill: true,
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderColor: '#007bff',
            borderWidth: 2,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#007bff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' S/ ' + ctx.raw.toFixed(2)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: val => 'S/ ' + val.toLocaleString() }
            }
        }
    }
});
</script>
@endpush
