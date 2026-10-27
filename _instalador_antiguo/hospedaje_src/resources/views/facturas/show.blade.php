@extends('layouts.app')
@section('title', "Factura {$factura->numero}")
@section('page-title', "Factura {$factura->numero}")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('facturas.index') }}">Facturas</a></li>
    <li class="breadcrumb-item active">{{ $factura->numero }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">

        {{-- Encabezado Factura --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>{{ strtoupper($factura->tipo_comprobante) }}: {{ $factura->numero }}
                        <span class="badge badge-{{ $factura->estado_badge }} ml-2">{{ ucfirst($factura->estado) }}</span>
                    </h3>
                    <div>
                        <a href="{{ route('facturas.pdf', $factura) }}" class="btn btn-sm btn-secondary" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">DATOS DEL ESTABLECIMIENTO</h6>
                        <strong>{{ config('app.name') }}</strong><br>
                        <small>Sistema de Gestión Hotelera</small>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <h6 class="text-muted">DATOS DEL CLIENTE</h6>
                        <strong>{{ $factura->huesped->nombre_completo }}</strong><br>
                        <small>{{ $factura->huesped->tipo_documento }}: {{ $factura->huesped->num_documento }}</small>
                        @if($factura->razon_social)
                        <br><small>{{ $factura->razon_social }} (RUC: {{ $factura->ruc_cliente }})</small>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted">Fecha Emisión</small><br>
                        <strong>{{ $factura->fecha_emision->format('d/m/Y') }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Reserva</small><br>
                        <a href="{{ route('reservas.show', $factura->reserva) }}">{{ $factura->reserva->codigo }}</a>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Habitación</small><br>
                        <strong>{{ $factura->reserva->habitacion->numero }}</strong> — {{ $factura->reserva->habitacion->tipoHabitacion->nombre }}
                    </div>
                </div>

                <hr>

                {{-- Detalle de items --}}
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr><th>Descripción</th><th class="text-right">P. Unit.</th><th class="text-center">Cant.</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Alojamiento — {{ $factura->reserva->num_noches }} noches ({{ $factura->reserva->fecha_entrada->format('d/m') }} → {{ $factura->reserva->fecha_salida->format('d/m/Y') }})</td>
                            <td class="text-right">S/ {{ number_format($factura->reserva->precio_noche, 2) }}</td>
                            <td class="text-center">{{ $factura->reserva->num_noches }}</td>
                            <td class="text-right">S/ {{ number_format($factura->reserva->subtotal, 2) }}</td>
                        </tr>
                        @foreach($factura->reserva->cargosAdicionales as $c)
                        <tr>
                            <td>{{ $c->concepto }} ({{ ucfirst($c->categoria) }})</td>
                            <td class="text-right">S/ {{ number_format($c->precio_unitario, 2) }}</td>
                            <td class="text-center">{{ $c->cantidad }}</td>
                            <td class="text-right">S/ {{ number_format($c->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-right">Subtotal:</td><td class="text-right">S/ {{ number_format($factura->subtotal, 2) }}</td></tr>
                        @if($factura->descuento > 0)
                        <tr class="text-danger"><td colspan="3" class="text-right">Descuento:</td><td class="text-right">- S/ {{ number_format($factura->descuento, 2) }}</td></tr>
                        @endif
                        @if($factura->igv > 0)
                        <tr><td colspan="3" class="text-right">IGV (18%):</td><td class="text-right">S/ {{ number_format($factura->igv, 2) }}</td></tr>
                        @endif
                        <tr class="font-weight-bold bg-light h5">
                            <td colspan="3" class="text-right">TOTAL:</td>
                            <td class="text-right">S/ {{ number_format($factura->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Historial de Pagos --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Pagos Registrados</h3>
                @if($factura->estado !== 'anulada' && $factura->saldo_pendiente > 0)
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalPago">
                        <i class="fas fa-plus mr-1"></i>Registrar Pago
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Fecha</th><th>Método</th><th>Referencia</th><th>Monto</th><th>Usuario</th></tr>
                    </thead>
                    <tbody>
                        @forelse($factura->pagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td><i class="{{ $pago->metodo_icono }} mr-1"></i>{{ ucfirst(str_replace('_',' ',$pago->metodo_pago)) }}</td>
                            <td>{{ $pago->referencia ?? '—' }}</td>
                            <td class="font-weight-bold text-success">S/ {{ number_format($pago->monto, 2) }}</td>
                            <td>{{ $pago->usuario->name }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Sin pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Estado de Pago --}}
        <div class="card {{ $factura->saldo_pendiente <= 0 ? 'card-success' : 'card-warning' }}">
            <div class="card-header"><h3 class="card-title">Estado de Pago</h3></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td>Total:</td><td class="text-right font-weight-bold">S/ {{ number_format($factura->total, 2) }}</td></tr>
                    <tr><td>Pagado:</td><td class="text-right text-success font-weight-bold">S/ {{ number_format($factura->monto_pagado, 2) }}</td></tr>
                    <tr class="border-top {{ $factura->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                        <td>Saldo:</td>
                        <td class="text-right h5">S/ {{ number_format($factura->saldo_pendiente, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Pago --}}
@if($factura->estado !== 'anulada' && $factura->saldo_pendiente > 0)
<div class="modal fade" id="modalPago">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Pago — Saldo: S/ {{ number_format($factura->saldo_pendiente, 2) }}</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('facturas.pago', $factura) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Monto a Pagar <span class="text-danger">*</span></label>
                        <input type="number" name="monto" class="form-control"
                               value="{{ $factura->saldo_pendiente }}" min="0.01"
                               max="{{ $factura->saldo_pendiente }}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" class="form-control select2">
                            @foreach(['efectivo','tarjeta_credito','tarjeta_debito','transferencia','yape','plin','otro'] as $m)
                                <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Referencia / Nro. Operación</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="form-group">
                        <label>Fecha de Pago</label>
                        <input type="date" name="fecha_pago" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Registrar Pago</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
