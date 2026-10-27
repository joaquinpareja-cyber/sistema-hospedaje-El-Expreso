@extends('layouts.app')
@section('title', 'Huéspedes')
@section('page-title', 'Gestión de Huéspedes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Huéspedes</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Huéspedes Registrados</h3>
        <div class="card-tools">
            <a href="{{ route('huespedes.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i>Nuevo Huésped
            </a>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" class="form-inline">
            <input type="text" name="buscar" class="form-control form-control-sm mr-2"
                   placeholder="Buscar por nombre, documento o email..."
                   value="{{ request('buscar') }}" style="min-width:280px">
            <select name="tipo_documento" class="form-control form-control-sm mr-2">
                <option value="">Todos los documentos</option>
                @foreach(['DNI','Pasaporte','CE','RUC'] as $td)
                    <option value="{{ $td }}" {{ request('tipo_documento') == $td ? 'selected' : '' }}>{{ $td }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm mr-1"><i class="fas fa-search mr-1"></i>Buscar</button>
            <a href="{{ route('huespedes.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Nacionalidad</th>
                    <th>Registrado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($huespedes as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td><strong>{{ $h->nombre_completo }}</strong></td>
                    <td><span class="badge badge-secondary">{{ $h->tipo_documento }}</span> {{ $h->num_documento }}</td>
                    <td>{{ $h->telefono ?? '—' }}</td>
                    <td>{{ $h->email ?? '—' }}</td>
                    <td>{{ $h->nacionalidad ?? '—' }}</td>
                    <td>{{ $h->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('huespedes.show', $h) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('huespedes.edit', $h) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('reservas.create', ['huesped_id' => $h->id]) }}" class="btn btn-xs btn-success" title="Nueva reserva">
                            <i class="fas fa-calendar-plus"></i>
                        </a>
                        <form action="{{ route('huespedes.destroy', $h) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar a {{ $h->nombre_completo }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron huéspedes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $huespedes->withQueryString()->links() }}
    </div>
</div>
@endsection
