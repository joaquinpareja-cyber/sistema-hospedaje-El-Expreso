@extends('layouts.app')
@section('title', 'Tarifas de Temporada')
@section('page-title', 'Tarifas de Temporada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('habitaciones.index') }}">Habitaciones</a></li>
    <li class="breadcrumb-item active">Tarifas</li>
@endsection

@section('content')
<div class="callout callout-info">
    <p class="mb-0">
        <i class="fas fa-info-circle mr-1"></i>
        Las tarifas activas reemplazan el precio base del tipo de habitación en las noches que cubren.
        Si varias coinciden, gana la de mayor prioridad (y a igual prioridad, la específica de un tipo).
        Las reservas ya creadas conservan su precio.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-day mr-2"></i>Tarifas registradas</h3>
        <div class="card-tools">
            <a href="{{ route('tarifas.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Nueva Tarifa
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Aplica a</th>
                    <th>Vigencia</th>
                    <th class="text-right">Precio</th>
                    <th class="text-center">Prioridad</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tarifas as $tarifa)
                <tr>
                    <td>
                        <strong>{{ $tarifa->nombre }}</strong>
                        @if($tarifa->descripcion)<br><small class="text-muted">{{ $tarifa->descripcion }}</small>@endif
                    </td>
                    <td>{{ $tarifa->tipoHabitacion->nombre ?? 'Todos los tipos' }}</td>
                    <td>{{ $tarifa->fecha_inicio->format('d/m/Y') }} → {{ $tarifa->fecha_fin->format('d/m/Y') }}</td>
                    <td class="text-right font-weight-bold">
                        @if($tarifa->tipo_precio === 'porcentaje')
                            <span class="{{ $tarifa->precio_noche < 0 ? 'text-success' : 'text-danger' }}">
                                {{ $tarifa->precio_noche > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($tarifa->precio_noche, 2), '0'), '.') }}%
                            </span>
                        @else
                            {{ $moneda }} {{ number_format($tarifa->precio_noche, 2) }}
                        @endif
                    </td>
                    <td class="text-center">{{ $tarifa->prioridad }}</td>
                    <td class="text-center">
                        <form action="{{ route('tarifas.toggle', $tarifa) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-{{ $tarifa->estado_badge === 'light' ? 'secondary' : $tarifa->estado_badge }}"
                                    title="Clic para {{ $tarifa->activa ? 'desactivar' : 'activar' }}">
                                {{ ucfirst($tarifa->estado) }}
                            </button>
                        </form>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('tarifas.edit', $tarifa) }}" class="btn btn-xs btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('tarifas.destroy', $tarifa) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar la tarifa «{{ $tarifa->nombre }}»?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No hay tarifas de temporada. Se usa el precio base de cada tipo de habitación.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
