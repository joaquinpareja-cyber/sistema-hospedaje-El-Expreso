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
                            <td>S/ {{ number_format($cargo->precio_unitario, 2) }}</td>
                            <td class="text-center">{{ $cargo->cantidad }}</td>
                            <td>S/ {{ number_format($cargo->subtotal, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Sin cargos adicionales.</td></tr>
                        @endforelse
                    </tbody>
                    @if($reserva->cargosAdicionales->count() > 0)
                    <tfoot class="font-weight-bold">
                        <tr>
                            <td colspan="5" class="text-right">Total Cargos Adicionales:</td>
                            <td>S/ {{ number_format($reserva->cargosAdicionales->sum('subtotal'), 2) }}</td>
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
                    <tr><td>Precio/noche</td><td class="text-right">S/ {{ number_format($reserva->precio_noche, 2) }}</td></tr>
                    <tr><td>{{ $reserva->num_noches }} noches</td><td class="text-right">S/ {{ number_format($reserva->subtotal, 2) }}</td></tr>
                    <tr><td>Cargos adicionales</td><td class="text-right">S/ {{ number_format($reserva->cargosAdicionales->sum('subtotal'), 2) }}</td></tr>
                    @if($reserva->descuento > 0)
                    <tr class="text-danger"><td>Descuento</td><td class="text-right">- S/ {{ number_format($reserva->descuento, 2) }}</td></tr>
                    @endif
                    <tr class="font-weight-bold border-top text-primary">
                        <td>TOTAL</td>
                        <td class="text-right h5">S/ {{ number_format($reserva->total + $reserva->cargosAdicionales->sum('subtotal'), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Acciones de la Reserva --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Acciones</h3></div>
            <div class="card-body">
                @if($reserva->estado === 'confirmada')
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

                @if(!in_array($reserva->estado, ['checkout','cancelada']))
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
