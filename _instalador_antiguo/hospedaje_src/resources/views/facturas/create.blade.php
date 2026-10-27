@extends('layouts.app')
@section('title', 'Generar Factura')
@section('page-title', 'Generar Factura')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('facturas.index') }}">Facturas</a></li>
    <li class="breadcrumb-item active">Nueva Factura</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">

@if($reserva)
{{-- Resumen de la reserva --}}
<div class="card card-outline card-info mb-3">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Reserva: {{ $reserva->codigo }}</h3></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Huésped:</strong> {{ $reserva->huesped->nombre_completo }}</p>
                <p><strong>Documento:</strong> {{ $reserva->huesped->tipo_documento }}: {{ $reserva->huesped->num_documento }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Habitación:</strong> {{ $reserva->habitacion->numero }} — {{ $reserva->habitacion->tipoHabitacion->nombre }}</p>
                <p><strong>Estancia:</strong> {{ $reserva->fecha_entrada->format('d/m/Y') }} → {{ $reserva->fecha_salida->format('d/m/Y') }} ({{ $reserva->num_noches }} noches)</p>
            </div>
        </div>
        <table class="table table-sm table-bordered mt-2 mb-0">
            <tr><th>Concepto</th><th class="text-right">Monto</th></tr>
            <tr><td>Alojamiento ({{ $reserva->num_noches }} noches × S/ {{ number_format($reserva->precio_noche, 2) }})</td>
                <td class="text-right">S/ {{ number_format($reserva->subtotal, 2) }}</td></tr>
            @foreach($reserva->cargosAdicionales as $c)
            <tr><td>{{ $c->concepto }} ({{ ucfirst($c->categoria) }}) × {{ $c->cantidad }}</td>
                <td class="text-right">S/ {{ number_format($c->subtotal, 2) }}</td></tr>
            @endforeach
            @if($reserva->descuento > 0)
            <tr class="text-danger"><td>Descuento</td><td class="text-right">- S/ {{ number_format($reserva->descuento, 2) }}</td></tr>
            @endif
            <tr class="font-weight-bold bg-light"><td>Subtotal</td>
                <td class="text-right">S/ {{ number_format($reserva->total + $reserva->cargosAdicionales->sum('subtotal'), 2) }}</td></tr>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Datos del Comprobante</h3></div>
    <form action="{{ route('facturas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="reserva_id" value="{{ $reserva?->id ?? old('reserva_id') }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tipo de Comprobante <span class="text-danger">*</span></label>
                        <select name="tipo_comprobante" id="tipo_comprobante" class="form-control select2" required>
                            <option value="boleta" {{ old('tipo_comprobante') == 'boleta' ? 'selected' : '' }}>Boleta de Venta</option>
                            <option value="factura" {{ old('tipo_comprobante') == 'factura' ? 'selected' : '' }}>Factura</option>
                            <option value="recibo" {{ old('tipo_comprobante') == 'recibo' ? 'selected' : '' }}>Recibo Simple</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="custom-control custom-switch mt-4">
                            <input type="checkbox" class="custom-control-input" id="igv_aplicado" name="igv_aplicado" value="1"
                                   {{ old('igv_aplicado') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="igv_aplicado">Aplicar IGV (18%)</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos para factura empresarial --}}
            <div id="datosFiscales" style="display:none;">
                <hr>
                <h6 class="text-muted mb-3"><i class="fas fa-building mr-2"></i>Datos Fiscales (Empresa)</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>RUC del Cliente</label>
                            <input type="text" name="ruc_cliente" class="form-control" maxlength="11"
                                   value="{{ old('ruc_cliente') }}" placeholder="20XXXXXXXXX">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Razón Social</label>
                            <input type="text" name="razon_social" class="form-control"
                                   value="{{ old('razon_social') }}" placeholder="Nombre de la empresa">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice mr-2"></i>Emitir Comprobante</button>
            @if($reserva)
            <a href="{{ route('reservas.show', $reserva) }}" class="btn btn-secondary ml-2">Cancelar</a>
            @else
            <a href="{{ route('facturas.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
            @endif
        </div>
    </form>
</div>

</div>
</div>
@endsection

@push('scripts')
<script>
$('#tipo_comprobante').on('change', function() {
    if ($(this).val() === 'factura') {
        $('#datosFiscales').show();
    } else {
        $('#datosFiscales').hide();
    }
}).trigger('change');
</script>
@endpush
