@extends('layouts.app')
@section('title', 'Calendario de Disponibilidad')
@section('page-title', 'Calendario de Reservas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Calendario</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css">
<style>
    #calendar { min-height: 600px; }
    .fc-event { cursor: pointer; border-radius: 4px !important; font-size: .8rem !important; padding: 2px 4px !important; }
    .fc-toolbar-title { font-size: 1.2rem !important; }
    .leyenda-dot { width: 14px; height: 14px; border-radius: 3px; display: inline-block; }
    #modalReserva .modal-header { color: #fff; }
</style>
@endpush

@section('content')

{{-- Filtros y leyenda --}}
<div class="row mb-3">
    <div class="col-md-5">
        <div class="card mb-0">
            <div class="card-body py-2">
                <div class="form-inline">
                    <label class="mr-2 text-muted">Habitación:</label>
                    <select id="filtroHabitacion" class="form-control form-control-sm select2 mr-2" style="min-width:220px">
                        <option value="">Todas las habitaciones</option>
                        @foreach($habitaciones as $hab)
                        <option value="{{ $hab->id }}">
                            Hab. {{ $hab->numero }} — {{ $hab->tipoHabitacion->nombre }}
                        </option>
                        @endforeach
                    </select>
                    <button id="btnFiltrar" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter mr-1"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    @foreach([
                        ['#6c757d','Pendiente'],
                        ['#007bff','Confirmada'],
                        ['#28a745','Check-in'],
                        ['#17a2b8','Check-out'],
                    ] as [$color, $label])
                    <span class="mr-3">
                        <span class="leyenda-dot mr-1" style="background:{{ $color }}"></span>
                        <small>{{ $label }}</small>
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 text-right">
        <a href="{{ route('calendario.disponibilidad') }}" class="btn btn-info btn-sm">
            <i class="fas fa-table mr-1"></i>Vista por Habitación
        </a>
        <a href="{{ route('reservas.create') }}" class="btn btn-success btn-sm ml-1">
            <i class="fas fa-plus mr-1"></i>Nueva Reserva
        </a>
    </div>
</div>

{{-- Calendario --}}
<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

{{-- Modal detalle reserva --}}
<div class="modal fade" id="modalReserva">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader" style="background:#007bff">
                <h5 class="modal-title text-white" id="modalTitulo">Reserva</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted">Código</th>  <td id="mCodigo"></td></tr>
                    <tr><th class="text-muted">Huésped</th> <td id="mHuesped"></td></tr>
                    <tr><th class="text-muted">Habitación</th><td id="mHabitacion"></td></tr>
                    <tr><th class="text-muted">Estado</th>  <td id="mEstado"></td></tr>
                    <tr><th class="text-muted">Noches</th>  <td id="mNoches"></td></tr>
                    <tr><th class="text-muted">Total</th>   <td id="mTotal"></td></tr>
                </table>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="btnVerReserva" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye mr-1"></i>Ver detalle completo
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/es.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 680,
        navLinks: true,
        editable: false,
        dayMaxEvents: 4,
        events: function(info, successCb, failureCb) {
            const habId = $('#filtroHabitacion').val();
            $.get("{{ route('calendario.eventos') }}", {
                start: info.startStr,
                end:   info.endStr,
                habitacion_id: habId
            })
            .done(successCb)
            .fail(failureCb);
        },
        eventClick: function(info) {
            const p = info.event.extendedProps;
            $('#modalTitulo').text(p.codigo);
            $('#modalHeader').css('background', info.event.backgroundColor);
            $('#mCodigo').text(p.codigo);
            $('#mHuesped').text(p.huesped);
            $('#mHabitacion').text(p.habitacion);
            $('#mEstado').html('<span class="badge badge-secondary">' + p.estado + '</span>');
            $('#mNoches').text(p.noches + ' noches');
            $('#mTotal').text(p.total);
            $('#btnVerReserva').attr('href', p.url_detalle);
            $('#modalReserva').modal('show');
        },
        // Clic en día vacío => nueva reserva
        dateClick: function(info) {
            window.location.href = "{{ route('reservas.create') }}?fecha=" + info.dateStr;
        }
    });

    calendar.render();

    $('#btnFiltrar').on('click', function () {
        calendar.refetchEvents();
    });
});
</script>
@endpush
