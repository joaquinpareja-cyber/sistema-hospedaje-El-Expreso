@extends('layouts.app')
@section('title', 'Reporte de Huéspedes')
@section('page-title', 'Reporte de Huéspedes')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Huéspedes</li>
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
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nuevos Huéspedes</span>
                <span class="info-box-number">{{ $nuevosHuespedes }}</span>
                <span class="progress-description">En el período seleccionado</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-globe-americas mr-2"></i>Por Nacionalidad (Top 10)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>Nacionalidad</th><th class="text-center">Huéspedes</th></tr></thead>
                    <tbody>
                        @forelse($porNacionalidad as $n)
                        <tr>
                            <td>{{ $n->nacionalidad }}</td>
                            <td class="text-center"><span class="badge badge-primary">{{ $n->total }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center py-3">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-star mr-2 text-warning"></i>Huéspedes Frecuentes</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light"><tr><th>Huésped</th><th>Documento</th><th class="text-center">Estancias</th></tr></thead>
                    <tbody>
                        @forelse($frecuentes as $h)
                        <tr>
                            <td><a href="{{ route('huespedes.show', $h) }}">{{ $h->nombre_completo }}</a></td>
                            <td><small>{{ $h->num_documento }}</small></td>
                            <td class="text-center"><span class="badge badge-success">{{ $h->total_estancias }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center py-3">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
