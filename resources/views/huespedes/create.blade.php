@extends('layouts.app')
@section('title', $reserva ? 'Nuevo Acompañante' : 'Nuevo Huésped')
@section('page-title', $reserva ? 'Registrar Acompañante' : 'Registrar Nuevo Huésped')
@section('breadcrumb')
    @if($reserva)
        <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservas.show', $reserva) }}">{{ $reserva->codigo }}</a></li>
        <li class="breadcrumb-item active">Nuevo acompañante</li>
    @else
        <li class="breadcrumb-item"><a href="{{ route('huespedes.index') }}">Huéspedes</a></li>
        <li class="breadcrumb-item active">Nuevo</li>
    @endif
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">
@if($reserva)
<div class="alert alert-info">
    <i class="fas fa-user-friends mr-2"></i>
    Esta persona se agregará como <strong>acompañante</strong> de la reserva
    <strong>{{ $reserva->codigo }}</strong> — Hab. {{ $reserva->habitacion->numero }}
    ({{ $reserva->habitacion->tipoHabitacion->nombre }}, capacidad {{ $reserva->habitacion->tipoHabitacion->capacidad }}).
</div>
@endif
@error('reserva_id')
<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}</div>
@enderror
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Datos del {{ $reserva ? 'Acompañante' : 'Huésped' }}</h3></div>
    <form action="{{ route('huespedes.store') }}" method="POST">
        @csrf
        @if($reserva)
            <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">
        @endif
        <div class="card-body">
            @include('huespedes._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>{{ $reserva ? 'Registrar y Agregar a la Reserva' : 'Registrar Huésped' }}
            </button>
            <a href="{{ $reserva ? route('reservas.show', $reserva) : route('huespedes.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
