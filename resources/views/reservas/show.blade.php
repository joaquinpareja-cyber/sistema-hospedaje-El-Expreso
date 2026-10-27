@extends('layouts.app')
@section('title', "Reserva {$reserva->codigo}")
@section('page-title', "Reserva {$reserva->codigo}")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
    <li class="breadcrumb-item active">{{ $reserva->codigo }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">

        {{-- Info principal de la reserva --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check mr-2"></i>{{ $reserva->codigo }}
                    <span class="badge badge-{{ $reserva->estado_badge }} ml-2">{{ ucfirst($reserva->estado) }}</span>
                </h3>
                <div class="card-tools">
                    @if(in_array($reserva->estado, ['pendiente','confirmada']))
                    <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th class="text-muted" width="40%">Huésped</th>
                                <td><a href="{{ route('huespedes.show', $reserva->huesped) }}"><strong>{{ $reserva->huesped->nombre_completo }}</strong></a></td></tr>
                            <tr><th class="text-muted">Documento</th>
                                <td>{{ $reserva->huesped->tipo_documento }}: {{ $reserva->huesped->num_documento }}</td></tr>
                            <tr><th class="text-muted">Teléfono</th>
                                <td>{{ $reserva->huesped->telefono ?? '—' }}</td></tr>
                            <tr><th class="text-muted">Origen</th>
                                <td>{{ ucfirst($reserva->origen) }}</td></tr>
                            <tr><th class="text-muted">Registrado por</th>
                                <td>{{ $reserva->usuario->name }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th class="text-muted" width="40%">Habitación</th>
                                <td><strong>{{ $reserva->habitacion->numero }}</strong> — {{ $reserva->habitacion->tipoHabitacion->nombre }}</td></tr>
                            <tr><th class="text-muted">Entrada</th>
                                <td>{{ $reserva->fecha_entrada->format('d/m/Y') }}</td></tr>
                            <tr><th class="text-muted">Salida</th>
                                <td>{{ $reserva->fecha_salida->format('d/m/Y') }}</td></tr>
                            <tr><th class="text-muted">Noches</th>
                                <td>{{ $reserva->num_noches }} noches</td></tr>
                            <tr><th class="text-muted">Personas</th>
                                <td>{{ $reserva->num_personas }}</td></tr>
                        </table>
                    </div>
                </div>
                @if($reserva->observaciones)
                <div class="alert alert-light border-left border-info mt-2 mb-0 py-2">
                    <i class="fas fa-sticky-note mr-2 text-info"></i>{{ $reserva->observaciones }}
                </div>
                @endif
            </div>
        </div>

        {{-- Huéspedes alojados: titular + acompañantes --}}
        @php
            $capacidad = $reserva->habitacion->tipoHabitacion->capacidad;
            $ocupantes = collect([$reserva->huesped])->merge($reserva->acompanantes);
            $cuposLibres = $capacidad - $ocupantes->count();
        @endphp
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>Huéspedes en la Habitación
                    <span class="badge badge-{{ $cuposLibres > 0 ? 'secondary' : 'success' }} ml-2">{{ $ocupantes->count() }} de {{ $capacidad }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>#</th><th>Nombre completo</th><th>Documento</th><th class="text-center">Edad</th><th>Procedencia</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($ocupantes as $i => $o)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('huespedes.show', $o) }}">{{ $o->nombre_completo }}</a>
                                @if($i === 0)
                                    <span class="badge badge-primary ml-1">Titular</span>
                                @else
                                    <span class="badge badge-light border ml-1">Acompañante</span>
                                @endif
                            </td>
                            <td>{{ $o->tipo_documento }}: {{ $o->num_documento }}</td>
                            <td class="text-center">{{ $o->edad !== null ? $o->edad . ' años' : '—' }}</td>
                            <td>{{ $o->procedencia ?? '—' }}</td>
                            <td class="text-right">
                                @if($i > 0 && $reserva->admiteCambiosDeAcompanantes())
                                <form action="{{ route('reservas.acompanantes.quitar', [$reserva, $o]) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Quitar a {{ $o->nombre_completo }} de esta reserva?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Quitar de la reserva"><i class="fas fa-user-minus"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reserva->admiteCambiosDeAcompanantes() && $cuposLibres > 0)
            <div class="card-footer">
                <form action="{{ route('reservas.acompanantes.agregar', $reserva) }}" method="POST" class="mb-2">
                    @csrf
                    <label class="small text-muted mb-1">Agregar acompañante ya registrado ({{ $cuposLibres }} cupo(s) libre(s)):</label>
                    <div class="d-flex">
                        <div class="flex-grow-1 mr-2">
                            <select name="huesped_id" id="acompanante_id" class="form-control" required></select>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-user-plus mr-1"></i>Agregar</button>
                    </div>
                </form>
                <a href="{{ route('huespedes.create', ['reserva_id' => $reserva->id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-id-card mr-1"></i>Registrar acompañante nuevo
                </a>
            </div>
            @endif
        </div>

        {{-- Cargos Adicionales --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Cargos Adicionales</h3>
                @if($reserva->estado === 'checkin')
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalCargo">
                        <i class="fas fa-plus mr-1"></i>Agregar Cargo
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Concepto</th><th>Categoría</th><th>Fecha</th><th>P. Unit.</th><th>Cant.</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @forelse($reserva->cargosAdicionales as $cargo)
                        <tr>
                            <td>{{ $cargo->concepto }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($cargo->categoria) }}</span></td>
                            <td>{{ $cargo->fecha->format('d/m/Y') }}</td>
                            <td>{{ $moneda }} {{ number_format($cargo->precio_unitario, 2) }}</td>
                            <td class="text-center">{{ $cargo->cantidad }}</td>
                            <td>{{ $moneda }} {{ number_format($cargo->subtotal, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin cargos adicionales.</td></tr>
                        @endforelse
                    </tbody>
                    @if($reserva->cargosAdicionales->count() > 0)
                    <tfoot class="font-weight-bold">
                        <tr>
                            <td colspan="5" class="text-right">Total Cargos Adicionales:</td>
                            <td>{{ $moneda }} {{ number_format($reserva->cargosAdicionales->sum('subtotal'), 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Resumen de Costos --}}
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Resumen de Costos</h3></div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td>Precio/noche</td><td class="text-right">{{ $moneda }} {{ number_format($reserva->precio_noche, 2) }}</td></tr>
                    <tr><td>{{ $reserva->num_noches }} noches</td><td class="text-right">{{ $moneda }} {{ number_format($reserva->subtotal, 2) }}</td></tr>
                    <tr><td>Cargos adicionales</td><td class="text-right">{{ $moneda }} {{ number_format($reserva->cargosAdicionales->sum('subtotal'), 2) }}</td></tr>
                    @if($reserva->descuento > 0)
                    <tr class="text-danger"><td>Descuento</td><td class="text-right">- {{ $moneda }} {{ number_format($reserva->descuento, 2) }}</td></tr>
                    @endif
                    <tr class="font-weight-bold border-top text-primary">
                        <td>TOTAL</td>
                        <td class="text-right h5">{{ $moneda }} {{ number_format($reserva->total + $reserva->cargosAdicionales->sum('subtotal'), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Acciones de la Reserva --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Acciones</h3></div>
            <div class="card-body">
                @if(in_array($reserva->estado, ['pendiente','confirmada']))
                <form action="{{ route('reservas.checkin', $reserva) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block" onclick="return confirm('¿Confirmar check-in?')">
                        <i class="fas fa-sign-in-alt mr-2"></i>Realizar Check-in
                    </button>
                </form>
                @endif

                @if($reserva->estado === 'checkin')
                <form action="{{ route('reservas.checkout', $reserva) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('¿Confirmar check-out?')">
                        <i class="fas fa-sign-out-alt mr-2"></i>Realizar Check-out
                    </button>
                </form>
                @endif

                @if($reserva->estado === 'checkout' && !$reserva->factura)
                <a href="{{ route('facturas.create', ['reserva_id' => $reserva->id]) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-file-invoice mr-2"></i>Generar Factura
                </a>
                @endif

                @if($reserva->factura)
                <a href="{{ route('facturas.show', $reserva->factura) }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>Ver Factura {{ $reserva->factura->numero }}
                </a>
                @endif

                @if(in_array($reserva->estado, ['pendiente','confirmada']))
                <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('¿Cancelar esta reserva?')">
                        <i class="fas fa-times mr-2"></i>Cancelar Reserva
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Cargo Adicional --}}
@if($reserva->estado === 'checkin')
<div class="modal fade" id="modalCargo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Agregar Cargo Adicional</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('reservas.cargo', $reserva) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Concepto <span class="text-danger">*</span></label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Desayuno buffet" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Categoría</label>
                                <select name="categoria" class="form-control select2">
                                    @foreach(['restaurante','minibar','lavanderia','telefono','transporte','tours','spa','otros'] as $cat)
                                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Precio Unitario</label>
                                <input type="number" name="precio_unitario" class="form-control" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Agregar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$('#acompanante_id').select2({
    theme: 'bootstrap4',
    width: '100%',
    placeholder: 'Buscar por nombre o documento...',
    minimumInputLength: 2,
    language: { inputTooShort: () => 'Escribe al menos 2 caracteres', noResults: () => 'Sin resultados', searching: () => 'Buscando...' },
    ajax: {
        url: "{{ route('huespedes.buscar') }}",
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term })
    }
});
</script>
@endpush
