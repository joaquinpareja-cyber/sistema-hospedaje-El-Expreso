@extends('layouts.app')
@section('title', "Editar Reserva {$reserva->codigo}")
@section('page-title', "Editar Reserva")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reservas.show', $reserva) }}">{{ $reserva->codigo }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
<div class="col-md-8">
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit mr-2"></i>{{ $reserva->codigo }}
            <span class="badge badge-{{ $reserva->estado_badge }} ml-2">{{ ucfirst($reserva->estado) }}</span>
        </h3>
    </div>
    <form action="{{ route('reservas.update', $reserva) }}" method="POST" id="formEditReserva">
        @csrf @method('PUT')
        <div class="card-body">

            {{-- Huésped (solo lectura) --}}
            <div class="form-group">
                <label>Huésped</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light"
                           value="{{ $reserva->huesped->nombre_completo }} ({{ $reserva->huesped->tipo_documento }}: {{ $reserva->huesped->num_documento }})"
                           readonly>
                </div>
                <small class="text-muted">El huésped no puede cambiarse. Cancela y crea una nueva reserva si es necesario.</small>
            </div>

            {{-- Habitación --}}
            <div class="form-group">
                <label>Habitación <span class="text-danger">*</span></label>
                <select name="habitacion_id" id="habitacion_id" class="form-control select2" required>
                    @foreach($habitaciones as $hab)
                    <option value="{{ $hab->id }}"
                            data-precio="{{ $hab->tipoHabitacion->precio_base }}"
                            {{ old('habitacion_id', $reserva->habitacion_id) == $hab->id ? 'selected' : '' }}>
                        Hab. {{ $hab->numero }} — {{ $hab->tipoHabitacion->nombre }}
                        (S/ {{ number_format($hab->tipoHabitacion->precio_base, 2) }}/noche)
                        — {{ ucfirst($hab->estado) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Fechas --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_entrada" id="fecha_entrada"
                               class="form-control @error('fecha_entrada') is-invalid @enderror"
                               value="{{ old('fecha_entrada', $reserva->fecha_entrada->format('Y-m-d')) }}"
                               required>
                        @error('fecha_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fecha de Salida <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_salida" id="fecha_salida"
                               class="form-control @error('fecha_salida') is-invalid @enderror"
                               value="{{ old('fecha_salida', $reserva->fecha_salida->format('Y-m-d')) }}"
                               required>
                        @error('fecha_salida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nro. Personas <span class="text-danger">*</span></label>
                        <input type="number" name="num_personas" class="form-control"
                               value="{{ old('num_personas', $reserva->num_personas) }}"
                               min="1" max="10" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Origen</label>
                        <select name="origen" class="form-control select2">
                            @foreach(['presencial','telefono','web','agencia'] as $o)
                            <option value="{{ $o }}"
                                    {{ old('origen', $reserva->origen) == $o ? 'selected' : '' }}>
                                {{ ucfirst($o) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Descuento (S/)</label>
                        <input type="number" name="descuento" id="descuento" class="form-control"
                               value="{{ old('descuento', $reserva->descuento) }}"
                               min="0" step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $reserva->observaciones) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="{{ route('reservas.show', $reserva) }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>

{{-- Panel de cálculo --}}
<div class="col-md-4">
    <div class="card" id="panelCalculo">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Recálculo de Costo</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                <tr><td>Precio/noche:</td>
                    <td class="text-right font-weight-bold" id="precioNoche">
                        S/ {{ number_format($reserva->precio_noche, 2) }}
                    </td></tr>
                <tr><td>Noches:</td>
                    <td class="text-right font-weight-bold" id="numNoches">
                        {{ $reserva->num_noches }}
                    </td></tr>
                <tr><td>Subtotal:</td>
                    <td class="text-right font-weight-bold" id="subtotal">
                        S/ {{ number_format($reserva->subtotal, 2) }}
                    </td></tr>
                <tr class="text-danger"><td>Descuento:</td>
                    <td class="text-right" id="descuentoVal">
                        - S/ {{ number_format($reserva->descuento, 2) }}
                    </td></tr>
                <tr class="font-weight-bold text-primary border-top">
                    <td>NUEVO TOTAL:</td>
                    <td class="text-right h5" id="totalCalc">
                        S/ {{ number_format($reserva->total, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card" id="alertaDisp" style="display:none">
        <div class="card-body" id="alertaDispBody"></div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function calcular() {
    const habId   = $('#habitacion_id').val();
    const entrada = $('#fecha_entrada').val();
    const salida  = $('#fecha_salida').val();
    const desc    = parseFloat($('#descuento').val()) || 0;

    if (!habId || !entrada || !salida || entrada >= salida) return;

    $.get("{{ route('reservas.disponibilidad') }}", {
        habitacion_id: habId,
        fecha_entrada: entrada,
        fecha_salida: salida,
        excluir_reserva_id: {{ $reserva->id }}
    }, function(data) {
        if (data.disponible) {
            $('#alertaDisp').hide();
            const total = data.subtotal - desc;
            $('#precioNoche').text('S/ ' + parseFloat(data.precio_noche).toFixed(2));
            $('#numNoches').text(data.num_noches + ' noches');
            $('#subtotal').text('S/ ' + parseFloat(data.subtotal).toFixed(2));
            $('#descuentoVal').text('- S/ ' + desc.toFixed(2));
            $('#totalCalc').text('S/ ' + Math.max(0, total).toFixed(2));
        } else {
            $('#alertaDispBody').html(
                '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle mr-2"></i>Habitación no disponible para esas fechas.</div>'
            );
            $('#alertaDisp').show();
        }
    });
}

$('#habitacion_id, #fecha_entrada, #fecha_salida, #descuento').on('change input', calcular);
$(document).ready(calcular);
</script>
@endpush
