@extends('layouts.app')
@section('title', 'Limpieza')
@section('page-title', 'Limpieza')
@section('breadcrumb')
    <li class="breadcrumb-item active">Limpieza</li>
@endsection

@section('content')
{{-- Resumen --}}
<div class="row">
    @foreach($estados as $clave => $e)
    <div class="col-6 col-md-3">
        <a href="{{ route('housekeeping.index', request('estado_limpieza') === $clave ? [] : ['estado_limpieza' => $clave]) }}" class="text-reset">
            <div class="info-box {{ request('estado_limpieza') === $clave ? 'shadow' : '' }}">
                <span class="info-box-icon bg-{{ $e['badge'] }}"><i class="fas {{ $e['icono'] }}"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ $e['label'] }}</span>
                    <span class="info-box-number">{{ $resumen[$clave] }} <small class="text-muted">/ {{ $resumen['total'] }}</small></span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

@if(request()->filled('estado_limpieza'))
<p><a href="{{ route('housekeeping.index') }}"><i class="fas fa-times mr-1"></i>Quitar filtro</a></p>
@endif

@forelse($habitaciones as $piso => $lista)
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Piso {{ $piso ?: '—' }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($lista as $hab)
            @php($e = $estados[$hab->estado_limpieza] ?? $estados['limpia'])
            @php($huesped = $hab->reservas->first()?->huesped)
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                <div class="card card-outline card-{{ $e['badge'] }} h-100 mb-0">
                    <div class="card-header py-2">
                        <strong>Hab. {{ $hab->numero }}</strong>
                        <span class="badge badge-{{ $hab->estado_badge }} float-right">{{ ucfirst($hab->estado) }}</span>
                    </div>
                    <div class="card-body py-2">
                        <small class="text-muted d-block">{{ $hab->tipoHabitacion->nombre ?? '' }}</small>
                        @if($huesped)
                            <small class="d-block"><i class="fas fa-user mr-1"></i>{{ $huesped->nombre_completo }}</small>
                        @endif
                        <span class="badge badge-{{ $e['badge'] }} mt-1"><i class="fas {{ $e['icono'] }} mr-1"></i>{{ $e['label'] }}</span>
                        @if($hab->limpieza_actualizado)
                            <small class="text-muted d-block mt-1">Actualizado {{ $hab->limpieza_actualizado->diffForHumans() }}</small>
                        @endif
                        @if($hab->limpieza_notas)
                            <small class="d-block font-italic mt-1">“{{ $hab->limpieza_notas }}”</small>
                        @endif
                    </div>
                    <div class="card-footer py-2">
                        {{-- Un toque en un estado lo guarda junto con la nota (la nota se conserva si no se cambia) --}}
                        <form action="{{ route('housekeeping.estado', $hab) }}" method="POST">
                            @csrf
                            <input type="text" name="notas" class="form-control form-control-sm mb-1" maxlength="300"
                                   value="{{ $hab->limpieza_notas }}" placeholder="Nota (ej: falta jabón)">
                            <div class="btn-group btn-group-sm d-flex">
                                @foreach($estados as $clave => $op)
                                <button type="submit" name="estado_limpieza" value="{{ $clave }}"
                                        class="btn {{ $hab->estado_limpieza === $clave ? 'btn-' . $op['badge'] : 'btn-outline-' . $op['badge'] }} flex-fill px-1"
                                        title="{{ $op['label'] }}">
                                    <i class="fas {{ $op['icono'] }}"></i>
                                    <span class="d-block" style="font-size:.68rem;line-height:1.1">{{ $op['corto'] }}</span>
                                </button>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@empty
<div class="card"><div class="card-body text-center text-muted py-4">No hay habitaciones para mostrar.</div></div>
@endforelse
@endsection
