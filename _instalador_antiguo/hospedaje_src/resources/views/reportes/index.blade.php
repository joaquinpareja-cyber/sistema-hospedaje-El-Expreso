@extends('layouts.app')
@section('title', 'Reportes')
@section('page-title', 'Centro de Reportes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary h-100">
            <div class="card-body text-center py-5">
                <i class="fas fa-hotel fa-4x text-primary mb-3"></i>
                <h5>Reporte de Ocupación</h5>
                <p class="text-muted">Porcentaje de ocupación, habitaciones disponibles y tendencias por período.</p>
                <a href="{{ route('reportes.ocupacion') }}" class="btn btn-primary">
                    <i class="fas fa-chart-bar mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success h-100">
            <div class="card-body text-center py-5">
                <i class="fas fa-dollar-sign fa-4x text-success mb-3"></i>
                <h5>Reporte de Ingresos</h5>
                <p class="text-muted">Ingresos por período, métodos de pago y facturación emitida.</p>
                <a href="{{ route('reportes.ingresos') }}" class="btn btn-success">
                    <i class="fas fa-chart-line mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-info h-100">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-4x text-info mb-3"></i>
                <h5>Reporte de Huéspedes</h5>
                <p class="text-muted">Huéspedes frecuentes, nacionalidades y análisis de clientes.</p>
                <a href="{{ route('reportes.huespedes') }}" class="btn btn-info">
                    <i class="fas fa-users mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
