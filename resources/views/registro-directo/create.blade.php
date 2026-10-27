@extends('layouts.app')
@section('title', 'Registro Directo')
@section('page-title', 'Registro Directo (sin reserva)')
@section('breadcrumb')
    <li class="breadcrumb-item active">Registro Directo</li>
@endsection

@section('content')
@php
    $numPersonas = (int) old('num_personas', 1);
    $iva = rtrim(rtrim(number_format($ivaPorcentaje, 2), '0'), '.');
@endphp

@if($habitaciones->isEmpty())
<div class="alert alert-warning">
    <i class="fas fa-bed mr-2"></i>No hay habitaciones libres en este momento.
</div>
@endif

<form action="{{ route('registro-directo.store') }}" method="POST" id="formWalkin">
@csrf
<div class="row">
<div class="col-lg-8">

    {{-- 1. Habitación y estancia --}}
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-bed mr-2"></i>1. Habitación y Estancia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label>Habitación libre <span class="text-danger">*</span></label>
                <select name="habitacion_id" id="habitacion_id" class="form-control select2 @error('habitacion_id') is-invalid @enderror" required>
                    <option value="">Seleccionar habitación...</option>
                    @foreach($habitaciones as $hab)
                        <option value="{{ $hab->id }}" data-capacidad="{{ $hab->tipoHabitacion->capacidad }}"
                                {{ old('habitacion_id', request('habitacion_id')) == $hab->id ? 'selected' : '' }}>
                            Hab. {{ $hab->numero }} — {{ $hab->tipoHabitacion->nombre }}
                            (cap. {{ $hab->tipoHabitacion->capacidad }}) — {{ $moneda }} {{ number_format($hab->tipoHabitacion->precio_base, 2) }}/noche
                            @if($hab->estado_limpieza && $hab->estado_limpieza !== 'limpia') — ⚠ {{ str_replace('_', ' ', $hab->estado_limpieza) }} @endif
                        </option>
                    @endforeach
                </select>
                @error('habitacion_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nro. de Personas <span class="text-danger">*</span></label>
                        <select name="num_personas" id="num_personas" class="form-control font-weight-bold @error('num_personas') is-invalid @enderror" required>
                            @for($i = 1; $i <= $maxPersonas; $i++)
                                <option value="{{ $i }}" {{ $numPersonas === $i ? 'selected' : '' }}>{{ $i }} {{ $i === 1 ? 'persona' : 'personas' }}</option>
                            @endfor
                        </select>
                        @error('num_personas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Noches <span class="text-danger">*</span></label>
                        <input type="number" name="num_noches" id="num_noches" class="form-control @error('num_noches') is-invalid @enderror"
                               value="{{ old('num_noches', 1) }}" min="1" max="90" required>
                        @error('num_noches')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sale el</label>
                        <input type="text" id="fecha_salida_txt" class="form-control" readonly tabindex="-1">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Descuento ({{ $moneda }})</label>
                        <input type="number" name="descuento" id="descuento" class="form-control @error('descuento') is-invalid @enderror"
                               value="{{ old('descuento', 0) }}" min="0" step="0.01">
                        @error('descuento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group mb-0">
                <label>Observaciones de la estancia</label>
                <textarea name="observaciones_reserva" class="form-control" rows="2" placeholder="Notas para recepción...">{{ old('observaciones_reserva') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 2. Una ficha por persona: se muestran tantas como "Nro. de Personas".
         Un fieldset deshabilitado no se valida en el navegador ni se envía. --}}
    @for($i = 1; $i <= $maxPersonas; $i++)
    @php
        $modo     = old("personas.{$i}.modo", 'nuevo');
        $previo   = $huespedesPrevios->get(old("personas.{$i}.huesped_id"));
        $visible  = $i <= $numPersonas;
    @endphp
    <fieldset class="js-persona" data-n="{{ $i }}" {{ $visible ? '' : 'disabled' }} style="{{ $visible ? '' : 'display:none' }}">
    <div class="card {{ $i === 1 ? 'card-outline card-primary' : 'card-outline card-info' }}">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas {{ $i === 1 ? 'fa-user' : 'fa-user-friends' }} mr-2"></i>
                Persona {{ $i }} <small class="text-muted">— {{ $i === 1 ? 'Titular (a su nombre sale el comprobante)' : 'Acompañante' }}</small>
            </h3>
            <div class="card-tools">
                <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
                    <label class="btn btn-outline-primary {{ $modo === 'nuevo' ? 'active' : '' }}">
                        <input type="radio" class="js-modo" name="personas[{{ $i }}][modo]" value="nuevo" {{ $modo === 'nuevo' ? 'checked' : '' }}> Nuevo
                    </label>
                    <label class="btn btn-outline-primary {{ $modo === 'existente' ? 'active' : '' }}">
                        <input type="radio" class="js-modo" name="personas[{{ $i }}][modo]" value="existente" {{ $modo === 'existente' ? 'checked' : '' }}> Ya registrado
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <fieldset class="js-bloque-existente" {{ $modo === 'existente' ? '' : 'disabled' }} style="{{ $modo === 'existente' ? '' : 'display:none' }}">
                <div class="form-group mb-0">
                    <label>Buscar huésped registrado <span class="text-danger">*</span></label>
                    <select name="personas[{{ $i }}][huesped_id]" class="form-control js-buscar-huesped @error("personas.{$i}.huesped_id") is-invalid @enderror">
                        @if($previo)
                            <option value="{{ $previo->id }}" selected>{{ $previo->nombre_completo }} ({{ $previo->tipo_documento }}: {{ $previo->num_documento }})</option>
                        @endif
                    </select>
                    @error("personas.{$i}.huesped_id")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </fieldset>
            <fieldset class="js-bloque-nuevo" {{ $modo === 'nuevo' ? '' : 'disabled' }} style="{{ $modo === 'nuevo' ? '' : 'display:none' }}">
                @include('huespedes._form', ['huesped' => null, 'prefijo' => "personas[{$i}]"])
            </fieldset>
        </div>
    </div>
    </fieldset>
    @endfor

    {{-- 3. Comprobante y pago --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-cash-register mr-2"></i>Comprobante y Pago</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Tipo de Comprobante <span class="text-danger">*</span></label>
                        <select name="tipo_comprobante" id="tipo_comprobante" class="form-control" required>
                            <option value="recibo"  {{ old('tipo_comprobante', 'recibo') == 'recibo' ? 'selected' : '' }}>Recibo</option>
                            <option value="factura" {{ old('tipo_comprobante') == 'factura' ? 'selected' : '' }}>Factura</option>
                            <option value="boleta"  {{ old('tipo_comprobante') == 'boleta' ? 'selected' : '' }}>Boleta de Venta</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="custom-control custom-switch mt-md-4 pt-md-2">
                        <input type="checkbox" class="custom-control-input" id="iva_aplicado" name="iva_aplicado" value="1" {{ old('iva_aplicado') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="iva_aplicado">Aplicar IVA ({{ $iva }}%)</label>
                    </div>
                </div>
            </div>
            <div class="row" id="datosFiscales" style="display:none;">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>NIT / CI <span class="text-danger">*</span></label>
                        <input type="text" name="nit_cliente" id="nit_cliente" class="form-control @error('nit_cliente') is-invalid @enderror" maxlength="20"
                               value="{{ old('nit_cliente') }}" placeholder="Ej: 1023456029">
                        @error('nit_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="form-group">
                        <label>Razón Social <span class="text-danger">*</span></label>
                        <input type="text" name="razon_social" id="razon_social" class="form-control @error('razon_social') is-invalid @enderror" maxlength="150"
                               value="{{ old('razon_social') }}" placeholder="Nombre o empresa">
                        @error('razon_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Monto que paga ahora ({{ $moneda }})</label>
                        <input type="number" name="monto_pagado" id="monto_pagado" class="form-control font-weight-bold @error('monto_pagado') is-invalid @enderror"
                               value="{{ old('monto_pagado', 0) }}" min="0" step="0.01" required>
                        @error('monto_pagado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">0 = paga al salir</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" class="form-control @error('metodo_pago') is-invalid @enderror">
                            @foreach(\App\Models\Pago::METODOS as $m => $mLabel)
                                <option value="{{ $m }}" {{ old('metodo_pago', 'efectivo') == $m ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                        @error('metodo_pago')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text" name="referencia" class="form-control" maxlength="100"
                               value="{{ old('referencia') }}" placeholder="Nro. operación (opcional)">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Resumen --}}
<div class="col-lg-4">
    <div class="card card-success sticky-top" style="top: 70px; z-index: 1;">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumen</h3></div>
        <div class="card-body">
            <div id="alertaHab" class="alert alert-warning py-2" style="display:none;"></div>
            <table class="table table-sm table-borderless mb-0">
                <tr><td>Entrada</td><td class="text-right">{{ now()->format('d/m/Y') }} (hoy)</td></tr>
                <tr><td>Personas</td><td class="text-right" id="rPersonas">—</td></tr>
                <tr><td>Precio por noche</td><td class="text-right" id="rPrecio">—</td></tr>
                <tr><td>Noches</td><td class="text-right" id="rNoches">—</td></tr>
                <tr><td>Subtotal</td><td class="text-right" id="rSubtotal">—</td></tr>
                <tr class="text-danger"><td>Descuento</td><td class="text-right" id="rDescuento">—</td></tr>
                <tr id="filaIva" style="display:none;"><td>IVA ({{ $iva }}%)</td><td class="text-right" id="rIva">—</td></tr>
                <tr class="border-top font-weight-bold text-primary"><td class="h5 mb-0">TOTAL</td><td class="text-right h5 mb-0" id="rTotal">—</td></tr>
                <tr><td>Paga ahora</td><td class="text-right" id="rPaga">—</td></tr>
                <tr class="font-weight-bold"><td>Saldo</td><td class="text-right" id="rSaldo">—</td></tr>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success btn-block btn-lg" {{ $habitaciones->isEmpty() ? 'disabled' : '' }}>
                <i class="fas fa-door-open mr-2"></i>Registrar y Emitir Comprobante
            </button>
            <small class="text-muted d-block mt-2 text-center">Hace el check-in, ocupa la habitación y genera el comprobante.</small>
        </div>
    </div>
</div>
</div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const MONEDA = @json($moneda);
    const IVA    = {{ (float) $ivaPorcentaje }};
    const fmt    = n => MONEDA + ' ' + (Math.round(n * 100) / 100).toFixed(2);
    let subtotal = 0, montoEditado = {{ old('monto_pagado') !== null ? 'true' : 'false' }};

    // ── Fichas visibles según el Nro. de Personas ────────────────
    function mostrarPersonas() {
        const n = parseInt($('#num_personas').val()) || 1;
        $('.js-persona').each(function () {
            const visible = parseInt(this.dataset.n) <= n;
            $(this).prop('disabled', !visible).toggle(visible);
        });
        $('#rPersonas').text(n);
    }

    // La habitación limita cuántas personas se pueden elegir
    function limitarPersonas() {
        const cap = parseInt($('#habitacion_id option:selected').data('capacidad')) || {{ $maxPersonas }};
        $('#num_personas option').each(function () { this.disabled = parseInt(this.value) > cap; });
        if (parseInt($('#num_personas').val()) > cap) $('#num_personas').val(cap);
        mostrarPersonas();
    }

    // ── Cada ficha: huésped nuevo o ya registrado ────────────────
    $(document).on('change', '.js-modo', function () {
        const card  = $(this).closest('.js-persona');
        const nuevo = this.value === 'nuevo';
        card.find('.js-bloque-nuevo').prop('disabled', !nuevo).toggle(nuevo);
        card.find('.js-bloque-existente').prop('disabled', nuevo).toggle(!nuevo);
        datosFiscales();
    });

    $('.js-buscar-huesped').select2({
        theme: 'bootstrap4', width: '100%', placeholder: 'Buscar por nombre o documento...', minimumInputLength: 2,
        language: { inputTooShort: () => 'Escribe al menos 2 caracteres', noResults: () => 'Sin resultados', searching: () => 'Buscando...' },
        ajax: { url: "{{ route('huespedes.buscar') }}", dataType: 'json', delay: 250, data: p => ({ q: p.term }) }
    });

    // ── Precio (tarifas de temporada incluidas) ──────────────────
    function fechaSalida() {
        const d = new Date();
        d.setDate(d.getDate() + (parseInt($('#num_noches').val()) || 1));
        return d;
    }
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

    function cotizar() {
        const sal = fechaSalida();
        $('#fecha_salida_txt').val(sal.toLocaleDateString('es-BO'));
        const habId = $('#habitacion_id').val();
        if (!habId) { subtotal = 0; $('#rPrecio, #rNoches').text('—'); $('#alertaHab').hide(); totales(); return; }

        $.get("{{ route('reservas.disponibilidad') }}", {
            habitacion_id: habId, fecha_entrada: iso(new Date()), fecha_salida: iso(sal)
        }, function (data) {
            subtotal = parseFloat(data.subtotal) || 0;
            $('#rPrecio').text(fmt(parseFloat(data.precio_noche)));
            $('#rNoches').text(data.num_noches);
            $('#alertaHab').toggle(!data.disponible)
                .html('<i class="fas fa-calendar-times mr-1"></i>La habitación tiene una reserva dentro de esas noches. Reduce las noches o elige otra.');
            totales();
        });
    }

    function totales() {
        const descuento = Math.min(parseFloat($('#descuento').val()) || 0, subtotal);
        const base  = subtotal - descuento;
        const iva   = $('#iva_aplicado').is(':checked') ? Math.round(base * IVA) / 100 : 0;
        const total = Math.round((base + iva) * 100) / 100;
        if (!montoEditado) $('#monto_pagado').val(total.toFixed(2));
        const paga  = Math.min(parseFloat($('#monto_pagado').val()) || 0, total);

        $('#rSubtotal').text(subtotal ? fmt(subtotal) : '—');
        $('#rDescuento').text('- ' + fmt(descuento));
        $('#filaIva').toggle(iva > 0); $('#rIva').text(fmt(iva));
        $('#rTotal').text(subtotal ? fmt(total) : '—');
        $('#rPaga').text(fmt(paga));
        $('#rSaldo').text(fmt(total - paga)).toggleClass('text-danger', total - paga > 0);
    }

    // ── Comprobante: datos fiscales solo para factura (se proponen los del titular) ──
    function datosFiscales() {
        const factura = $('#tipo_comprobante').val() === 'factura';
        $('#datosFiscales').toggle(factura);
        if (!factura || $('#razon_social').val()) return;

        const titular = $('.js-persona[data-n="1"]');
        let nombre = '', doc = '';
        if (titular.find('.js-modo:checked').val() === 'existente') {
            const txt = titular.find('.js-buscar-huesped option:selected').text();   // "Nombre (CI: 123)"
            nombre = txt.replace(/\s*\(.*\)\s*$/, '');
            doc    = (txt.match(/:\s*([^)]+)\)/) || [])[1] || '';
        } else {
            nombre = titular.find('input[name="personas[1][nombre_completo]"]').val();
            doc    = titular.find('input[name="personas[1][num_documento]"]').val();
        }
        if (nombre) $('#razon_social').val(nombre.trim());
        if (doc && !$('#nit_cliente').val()) $('#nit_cliente').val(doc.trim());
    }

    $('#habitacion_id').on('change', function () { limitarPersonas(); cotizar(); });
    $('#num_noches').on('change input', cotizar);
    $('#num_personas').on('change', mostrarPersonas);
    $('#descuento, #iva_aplicado').on('change input', totales);
    $('#monto_pagado').on('input', function () { montoEditado = true; totales(); });
    $('#tipo_comprobante').on('change', datosFiscales);
    $(function () { limitarPersonas(); cotizar(); datosFiscales(); });
})();
</script>
@endpush
