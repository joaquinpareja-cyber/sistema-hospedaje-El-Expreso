@extends('layouts.app')
@section('title', 'Nueva Reserva')
@section('page-title', 'Nueva Reserva')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
<div class="row">
<div class="col-md-8">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>Crear Nueva Reserva</h3></div>
    <form action="{{ route('reservas.store') }}" method="POST" id="formReserva">
        @csrf
        <div class="card-body">

            {{-- Huésped --}}
            <div class="form-group">
                <label>Huésped <span class="text-danger">*</span></label>
                <select name="huesped_id" class="form-control select2 @error('huesped_id') is-invalid @enderror" required>
                    <option value="">Seleccionar huésped...</option>
                    @foreach($huespedes as $h)
                        <option value="{{ $h->id }}"
                            {{ (old('huesped_id', $huespedSeleccionado?->id) == $h->id) ? 'selected' : '' }}>
                            {{ $h->apellido }}, {{ $h->nombre }} ({{ $h->tipo_documento }}: {{ $h->num_documento }})
                        </option>
                    @endforeach
                </select>
                @error('huesped_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">
                    ¿Huésped nuevo? <a href="{{ route('huespedes.create') }}" target="_blank">Registrar aquí</a>
                </small>
            </div>

            {{-- Habitación --}}
            <div class="form-group">
                <label>Habitación <span class="text-danger">*</span></label>
                <select name="habitacion_id" id="habitacion_id" class="form-control select2 @error('habitacion_id') is-invalid @enderror" required>
                    <option value="">Seleccionar habitación...</option>
                    @foreach($habitaciones as $hab)
                        <option value="{{ $hab->id }}"
                            data-precio="{{ $hab->tipoHabitacion->precio_base }}"
                            {{ old('habitacion_id') == $hab->id ? 'selected' : '' }}>
                            Hab. {{ $hab->numero }} — {{ $hab->tipoHabitacion->nombre }}
                            (S/ {{ number_format($hab->tipoHabitacion->precio_base, 2) }}/noche)
                            — {{ ucfirst($hab->estado) }}
                        </option>
                    @endforeach
                </select>
                @error('habitacion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Fechas --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_entrada" id="fecha_entrada"
                               class="form-control @error('fecha_entrada') is-invalid @enderror"
                               value="{{ old('fecha_entrada', now()->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}" required>
                        @error('fecha_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fecha de Salida <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_salida" id="fecha_salida"
                               class="form-control @error('fecha_salida') is-invalid @enderror"
                               value="{{ old('fecha_salida', now()->addDay()->format('Y-m-d')) }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        @error('fecha_salida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nro. Personas <span class="text-danger">*</span></label>
                        <input type="number" name="num_personas" class="form-control"
                               value="{{ old('num_personas', 1) }}" min="1" max="10" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Origen</label>
                        <select name="origen" class="form-control select2">
                            @foreach(['presencial','telefono','web','agencia'] as $o)
                                <option value="{{ $o }}" {{ old('origen') == $o ? 'selected' : '' }}>{{ ucfirst($o) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descuento (S/)</label>
                        <input type="number" name="descuento" id="descuento" class="form-control"
                               value="{{ old('descuento', 0) }}" min="0" step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Confirmar Reserva</button>
            <a href="{{ route('reservas.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>

{{-- Panel de Cálculo --}}
<div class="col-md-4">
    <div class="card" id="panelCalculo" style="display:none;">
        <div class="card-header bg-success text-white">
            <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumen de Costo</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr><td>Precio por noche:</td><td class="text-right font-weight-bold" id="precioNoche">—</td></tr>
                <tr><td>Número de noches:</td><td class="text-right font-weight-bold" id="numNoches">—</td></tr>
                <tr><td>Subtotal:</td><td class="text-right font-weight-bold" id="subtotal">—</td></tr>
                <tr class="text-danger"><td>Descuento:</td><td class="text-right" id="descuentoVal">—</td></tr>
                <tr class="font-weight-bold text-primary border-top">
                    <td>TOTAL:</td><td class="text-right h5" id="totalCalc">—</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card" id="alertaDisp" style="display:none;">
        <div class="card-body" id="alertaDispBody"></div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function calcular() {
    const habId = $('#habitacion_id').val();
    const entrada = $('#fecha_entrada').val();
    const salida = $('#fecha_salida').val();
    const descuento = parseFloat($('#descuento').val()) || 0;

    if (!habId || !entrada || !salida || entrada >= salida) {
        $('#panelCalculo').hide();
        return;
    }

    $.get("{{ route('reservas.disponibilidad') }}", {
        habitacion_id: habId,
        fecha_entrada: entrada,
        fecha_salida: salida
    }, function(data) {
        const body = $('#alertaDispBody');
        if (data.disponible) {
            $('#alertaDisp').hide();
            const total = data.subtotal - descuento;
            $('#precioNoche').text('S/ ' + parseFloat(data.precio_noche).toFixed(2));
            $('#numNoches').text(data.num_noches + ' noches');
            $('#subtotal').text('S/ ' + parseFloat(data.subtotal).toFixed(2));
            $('#descuentoVal').text('- S/ ' + descuento.toFixed(2));
            $('#totalCalc').text('S/ ' + Math.max(0, total).toFixed(2));
            $('#panelCalculo').show();
        } else {
            $('#panelCalculo').hide();
            body.html('<div class="alert alert-danger mb-0"><i class="fas fa-times-circle mr-2"></i>Habitación no disponible para las fechas seleccionadas.</div>');
            $('#alertaDisp').show();
        }
    });
}

$('#habitacion_id, #fecha_entrada, #fecha_salida, #descuento').on('change input', calcular);
$(document).ready(calcular);
</script>
@endpush
