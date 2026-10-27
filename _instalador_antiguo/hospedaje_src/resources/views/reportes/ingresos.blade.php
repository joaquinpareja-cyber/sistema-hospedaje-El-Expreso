@extends('layouts.app')
@section('title', 'Reporte de Ingresos')
@section('page-title', 'Reporte de Ingresos')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Ingresos</li>
@endsection

@section('content')
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

<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ingresos Totales</span>
                <span class="info-box-number">S/ {{ number_format($totalIngresos, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Facturas Emitidas</span>
                <span class="info-box-number">{{ $facturas->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">IGV Recaudado</span>
                <span class="info-box-number">S/ {{ number_format($totalIGV, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Ingresos Diarios</h3></div>
            <div class="card-body"><canvas id="chartIngresos" height="80"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Por Método de Pago</h3></div>
            <div class="card-body"><canvas id="chartMetodos" height="160"></canvas></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-2"></i>Facturas del Período</h3></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0" id="tablaFacturas">
            <thead class="thead-light">
                <tr><th>Número</th><th>Huésped</th><th>Hab.</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @foreach($facturas as $f)
                <tr>
                    <td><a href="{{ route('facturas.show', $f) }}">{{ $f->numero }}</a></td>
                    <td>{{ $f->huesped->nombre_completo }}</td>
                    <td>{{ $f->reserva->habitacion->numero }}</td>
                    <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($f->tipo_comprobante) }}</td>
                    <td>S/ {{ number_format($f->total, 2) }}</td>
                    <td><span class="badge badge-{{ $f->estado_badge }}">{{ ucfirst($f->estado) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ingDiarios = @json($ingresosDiarios);
new Chart(document.getElementById('chartIngresos'), {
    type: 'bar',
    data: {
        labels: ingDiarios.map(d => d.dia),
        datasets: [{ label: 'S/', data: ingDiarios.map(d => parseFloat(d.total)), backgroundColor: 'rgba(40,167,69,0.7)', borderColor:'#28a745', borderRadius:4 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ '+v } } }
    }
});

const metodos = @json($ingresosPorMetodo);
new Chart(document.getElementById('chartMetodos'), {
    type: 'doughnut',
    data: {
        labels: metodos.map(m => m.metodo_pago.replace('_',' ')),
        datasets: [{ data: metodos.map(m => parseFloat(m.total)), backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#17a2b8','#fd7e14'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
$(document).ready(function(){ $('#tablaFacturas').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' } }); });
</script>
@endpush
