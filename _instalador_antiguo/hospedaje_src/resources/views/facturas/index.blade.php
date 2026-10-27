@extends('layouts.app')
@section('title', 'Facturación')
@section('page-title', 'Facturación y Pagos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Facturas</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Facturas Emitidas</h3>
    </div>

    <div class="card-body border-bottom pb-3">
        <form method="GET" class="form-inline flex-wrap">
            <input type="text" name="buscar" class="form-control form-control-sm mr-2 mb-2"
                   placeholder="Nro. factura o huésped..." value="{{ request('buscar') }}" style="min-width:200px">
            <select name="estado" class="form-control form-control-sm mr-2 mb-2">
                <option value="">Todos</option>
                @foreach(['pendiente','pagada','anulada'] as $est)
                    <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_desde" class="form-control form-control-sm mr-2 mb-2" value="{{ request('fecha_desde') }}">
            <input type="date" name="fecha_hasta" class="form-control form-control-sm mr-2 mb-2" value="{{ request('fecha_hasta') }}">
            <button type="submit" class="btn btn-secondary btn-sm mr-1 mb-2"><i class="fas fa-search mr-1"></i>Filtrar</button>
            <a href="{{ route('facturas.index') }}" class="btn btn-outline-secondary btn-sm mb-2">Limpiar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Número</th>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Fecha Emisión</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td><a href="{{ route('facturas.show', $f) }}" class="font-weight-bold">{{ $f->numero }}</a></td>
                    <td>{{ $f->huesped->nombre_completo }}</td>
                    <td>{{ $f->reserva->habitacion->numero }}</td>
                    <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                    <td><span class="badge badge-secondary">{{ ucfirst($f->tipo_comprobante) }}</span></td>
                    <td class="font-weight-bold">S/ {{ number_format($f->total, 2) }}</td>
                    <td><span class="badge badge-{{ $f->estado_badge }}">{{ ucfirst($f->estado) }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('facturas.show', $f) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('facturas.pdf', $f) }}" class="btn btn-xs btn-secondary" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                        @if($f->estado !== 'anulada')
                        <form action="{{ route('facturas.anular', $f) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Anular factura {{ $f->numero }}?')">
                            @csrf
                            <button class="btn btn-xs btn-danger" title="Anular"><i class="fas fa-ban"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron facturas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $facturas->withQueryString()->links() }}
    </div>
</div>
@endsection
