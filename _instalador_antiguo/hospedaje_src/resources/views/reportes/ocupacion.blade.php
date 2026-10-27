@extends('layouts.app')
@section('title', 'Reporte de Ocupación')
@section('page-title', 'Reporte de Ocupación')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Ocupación</li>
@endsection

@section('content')
{{-- Filtro --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2">Desde:</label>
            <input type="date" name="desde" class="form-control form-control-sm mr-3" value="{{ $desde->format('Y-m-d') }}">
            <label class="mr-2">Hasta:</label>
            <input type="date" name="hasta" class="form-control form-control-sm mr-3" value="{{ $hasta->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Filtrar</button>
        </form>
    </div>
</div>

{{-- Resumen --}}
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-hotel"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Habitaciones</span>
                <span class="info-box-number">{{ $totalHabitaciones }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Reservas del Período</span>
                <span class="info-box-number">{{ $totalReservas }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Canceladas</span>
                <span class="info-box-number">{{ $reservasCanceladas }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-percent"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ocupación Promedio</span>
                <span class="info-box-number">{{ number_format($promOcupacion, 1) }}%</span>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico --}}
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-area mr-2"></i>Ocupación Diaria</h3></div>
    <div class="card-body">
        <canvas id="chartOcupacion" height="60"></canvas>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Detalle por Día</h3></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0" id="tablaOcupacion">
            <thead class="thead-light">
                <tr><th>Fecha</th><th>Habitaciones Ocupadas</th><th>% Ocupación</th><th>Disponibles</th></tr>
            </thead>
            <tbody>
                @foreach($ocupacionDiaria as $d)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($d['fecha'])->format('d/m/Y') }}</td>
                    <td>{{ $d['ocupadas'] }} / {{ $totalHabitaciones }}</td>
                    <td>
                        <div class="progress" style="height:16px">
                            <div class="progress-bar {{ $d['porcentaje'] >= 80 ? 'bg-danger' : ($d['porcentaje'] >= 50 ? 'bg-warning' : 'bg-success') }}"
                                 style="width:{{ $d['porcentaje'] }}%">{{ $d['porcentaje'] }}%</div>
                        </div>
                    </td>
                    <td>{{ $totalHabitaciones - $d['ocupadas'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const data = @json($ocupacionDiaria);
new Chart(document.getElementById('chartOcupacion'), {
    type: 'line',
    data: {
        labels: data.map(d => d.label),
        datasets: [{
            label: 'Habitaciones Ocupadas',
            data: data.map(d => d.ocupadas),
            fill: true,
            backgroundColor: 'rgba(0,123,255,0.1)',
            borderColor: '#007bff',
            tension: 0.3,
        }, {
            label: '% Ocupación',
            data: data.map(d => d.porcentaje),
            fill: false,
            borderColor: '#dc3545',
            borderDash: [5,5],
            yAxisID: 'y1',
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, max: {{ $totalHabitaciones }}, title: { display: true, text: 'Habitaciones' } },
            y1: { beginAtZero: true, max: 100, position: 'right', title: { display: true, text: '%' }, grid: { drawOnChartArea: false } }
        }
    }
});
$(document).ready(function(){ $('#tablaOcupacion').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }, order: [[0,'desc']] }); });
</script>
@endpush
