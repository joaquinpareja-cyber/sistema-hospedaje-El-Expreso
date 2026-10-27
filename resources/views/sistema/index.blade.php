@extends('layouts.app')
@section('title', 'Sistema — Backup & Restauración')
@section('page-title', 'Sistema')
@section('breadcrumb')
    <li class="breadcrumb-item active">Sistema</li>
@endsection

@push('styles')
<style>
    .sys-card         { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .sys-card-header  { border-radius: 12px 12px 0 0; padding: 1rem 1.5rem; }
    .backup-row       { transition: background .15s; }
    .backup-row:hover { background: #f8f9fa; }
    .danger-zone      { border: 2px solid #dc3545; border-radius: 12px; }
    .danger-zone .dz-header { background: linear-gradient(135deg, #dc3545, #c82333);
                              border-radius: 10px 10px 0 0; padding: 1rem 1.5rem; color: #fff; }
    .warning-zone     { border: 2px solid #ffc107; border-radius: 12px; }
    .warning-zone .wz-header { background: linear-gradient(135deg, #ffc107, #e0a800);
                                border-radius: 10px 10px 0 0; padding: 1rem 1.5rem; color: #212529; }
    .stat-card        { border-radius: 10px; padding: 1.1rem 1.3rem;
                        display: flex; align-items: center; gap: 1rem; }
    .stat-card .stat-icon { width: 52px; height: 52px; border-radius: 10px;
                             display: flex; align-items: center; justify-content: center;
                             font-size: 1.4rem; flex-shrink: 0; }
    .stat-card .stat-num  { font-size: 1.6rem; font-weight: 700; line-height: 1; }
    .stat-card .stat-lbl  { font-size: .75rem; color: #6c757d; }
    .drop-backup      { border: 2px dashed #ced4da; border-radius: 10px;
                        padding: 2rem; text-align: center; cursor: pointer;
                        transition: all .2s; background: #fefefe; }
    .drop-backup:hover { border-color: #fd7e14; background: #fff8f0; }
    .step-badge       { display: inline-flex; align-items: center; justify-content: center;
                        width: 26px; height: 26px; border-radius: 50%; font-size: .8rem;
                        font-weight: 700; flex-shrink: 0; }
    .confirm-input:focus { border-color: #dc3545 !important; box-shadow: 0 0 0 .2rem rgba(220,53,69,.25) !important; }
</style>
@endpush

@section('content')

{{-- ═══════ TARJETAS DE RESUMEN ═══════ --}}
<div class="row mb-3">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card bg-white border">
            <div class="stat-icon" style="background:#e8f4fd">
                <i class="fas fa-database" style="color:#007bff"></i>
            </div>
            <div>
                <div class="stat-num text-primary">{{ $dbName }}</div>
                <div class="stat-lbl">Base de Datos Activa</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card bg-white border">
            <div class="stat-icon" style="background:#e8fdf0">
                <i class="fas fa-table" style="color:#28a745"></i>
            </div>
            <div>
                <div class="stat-num text-success">{{ $tablas }}</div>
                <div class="stat-lbl">Tablas en la BD · {{ $dbSize }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card bg-white border">
            <div class="stat-icon" style="background:#fdf8e8">
                <i class="fas fa-save" style="color:#ffc107"></i>
            </div>
            <div>
                <div class="stat-num text-warning">{{ $archivos->count() }}</div>
                <div class="stat-lbl">
                    Backups guardados
                    @if($ultimoBackup)
                        · Último: {{ $ultimoBackup['fecha'] }}
                    @else
                        · Sin backups aún
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    {{-- ═══════ COLUMNA IZQUIERDA: BACKUP ═══════ --}}
    <div class="col-lg-7">

        {{-- Card: Crear backup --}}
        <div class="sys-card card mb-4">
            <div class="sys-card-header" style="background: linear-gradient(135deg, #1a73e8, #0d47a1); color:#fff">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-cloud-download-alt fa-lg mr-2"></i>
                    <div>
                        <h5 class="mb-0 font-weight-bold">Copia de Seguridad</h5>
                        <small style="opacity:.85">Genera y descarga un respaldo completo de la base de datos</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <p class="mb-2">El backup incluye <strong>todas las tablas</strong> y datos del sistema en formato SQL estándar.</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(['Huéspedes','Reservas','Facturas','Pagos','Habitaciones','Usuarios','Configuración'] as $t)
                            <span class="badge badge-light border mr-1 mb-1 px-2 py-1">
                                <i class="fas fa-table mr-1 text-primary"></i>{{ $t }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4 text-center mt-3 mt-md-0">
                        <form action="{{ route('sistema.backup') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-4"
                                    onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>Generando...'; this.form.submit()">
                                <i class="fas fa-download mr-2"></i>Crear Backup Ahora
                            </button>
                        </form>
                        <small class="text-muted d-block mt-1">Se guarda en el servidor</small>
                    </div>
                </div>

                {{-- Lista de backups --}}
                <h6 class="font-weight-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-history mr-1 text-secondary"></i>Historial de Backups
                </h6>

                @if($archivos->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                    No hay backups guardados aún. ¡Crea el primero!
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th><i class="fas fa-file-code mr-1"></i>Archivo</th>
                                <th>Fecha</th>
                                <th>Tamaño</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($archivos as $i => $bk)
                        <tr class="backup-row">
                            <td>
                                <i class="fas fa-file-alt text-primary mr-1"></i>
                                <span class="small font-weight-bold">{{ $bk['nombre'] }}</span>
                                @if($i === 0)<span class="badge badge-success ml-1">Último</span>@endif
                            </td>
                            <td><small>{{ $bk['fecha'] }}</small></td>
                            <td><span class="badge badge-secondary">{{ $bk['tamanio'] }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('sistema.backup.descargar', $bk['nombre']) }}"
                                   class="btn btn-xs btn-outline-primary mr-1" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('sistema.backup.eliminar', $bk['nombre']) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este backup?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ═══════ COLUMNA DERECHA: RESTAURAR + RESET ═══════ --}}
    <div class="col-lg-5">

        {{-- Card: Restaurar --}}
        <div class="warning-zone mb-4">
            <div class="wz-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-upload fa-lg mr-2"></i>
                    <div>
                        <h5 class="mb-0 font-weight-bold">Restaurar Sistema</h5>
                        <small>Carga un archivo .sql para restaurar los datos</small>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Advertencia:</strong> La restauración <strong>reemplaza todos los datos actuales</strong> con los del backup. Esta acción no se puede deshacer.
                </div>

                <form action="{{ route('sistema.restaurar') }}" method="POST"
                      enctype="multipart/form-data" id="formRestaurar">
                    @csrf

                    {{-- Paso 1 --}}
                    <div class="d-flex align-items-start mb-2">
                        <span class="step-badge bg-warning text-dark mr-2 mt-1">1</span>
                        <div class="w-100">
                            <label class="font-weight-bold mb-1">Selecciona el archivo de backup</label>
                            <div class="drop-backup" id="dropRestaurar" onclick="document.getElementById('inputRestaurar').click()">
                                <i class="fas fa-file-upload fa-2x text-muted mb-1"></i>
                                <p class="mb-0 small" id="dropLabel">Arrastra el archivo .sql aquí o haz clic</p>
                            </div>
                            <input type="file" name="archivo_backup" id="inputRestaurar" accept=".sql,.txt" class="d-none">
                        </div>
                    </div>

                    {{-- Paso 2 --}}
                    <div class="d-flex align-items-start mb-3 mt-3">
                        <span class="step-badge bg-warning text-dark mr-2 mt-1">2</span>
                        <div class="w-100">
                            <label class="font-weight-bold mb-1">Escribe <code>RESTAURAR</code> para confirmar</label>
                            <input type="text" name="confirmacion" class="form-control confirm-input"
                                   placeholder="Escribe RESTAURAR aquí" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-block font-weight-bold"
                            onclick="return validarRestaurar()">
                        <i class="fas fa-undo mr-2"></i>Restaurar Sistema
                    </button>
                </form>
            </div>
        </div>

        {{-- Card: Reset --}}
        <div class="danger-zone">
            <div class="dz-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fa-lg mr-2"></i>
                    <div>
                        <h5 class="mb-0 font-weight-bold">Zona de Peligro — Resetear Sistema</h5>
                        <small style="opacity:.9">Para iniciar con una empresa nueva desde cero</small>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <div class="alert alert-danger py-2 mb-3">
                    <i class="fas fa-skull-crossbones mr-1"></i>
                    <strong>¡CUIDADO!</strong> Esto <strong>elimina permanentemente</strong> todos los datos:
                    huéspedes, reservas, facturas, pagos, reportes. Solo quedarán los datos base del sistema.
                </div>

                <form action="{{ route('sistema.resetear') }}" method="POST" id="formReset">
                    @csrf

                    {{-- Paso 1 --}}
                    <div class="d-flex align-items-start mb-2">
                        <span class="step-badge bg-danger text-white mr-2 mt-1">1</span>
                        <div class="w-100">
                            <label class="font-weight-bold mb-1 small">Contraseña del Administrador</label>
                            <input type="password" name="password_admin" class="form-control form-control-sm confirm-input"
                                   placeholder="Tu contraseña actual" autocomplete="off">
                        </div>
                    </div>

                    {{-- Paso 2 --}}
                    <div class="d-flex align-items-start mb-3 mt-2">
                        <span class="step-badge bg-danger text-white mr-2 mt-1">2</span>
                        <div class="w-100">
                            <label class="font-weight-bold mb-1 small">Escribe <code>RESETEAR</code> para confirmar</label>
                            <input type="text" name="confirmacion_reset" class="form-control form-control-sm confirm-input"
                                   placeholder="Escribe RESETEAR aquí" autocomplete="off">
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger btn-block font-weight-bold"
                            onclick="confirmarReset()">
                        <i class="fas fa-trash-alt mr-2"></i>Resetear Sistema Completamente
                    </button>
                </form>

                <p class="text-muted small mt-2 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tras el reset podrás ingresar con <strong>admin@hospedaje.com</strong> / <strong>password</strong>
                </p>
            </div>
        </div>

    </div>
</div>

{{-- Modal confirmación reset --}}
<div class="modal fade" id="modalReset" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-circle mr-2"></i>Confirmación Final</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-skull-crossbones fa-3x text-danger mb-3"></i>
                <h5 class="font-weight-bold">¿Estás absolutamente seguro?</h5>
                <p class="text-muted">Se borrarán <strong>todos los datos</strong> de la empresa.<br>Esta acción es <strong class="text-danger">irreversible</strong>.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger px-4" onclick="document.getElementById('formReset').submit()">
                    <i class="fas fa-trash-alt mr-1"></i>Sí, Resetear Todo
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Drag & drop para restaurar ───────────────────────────────────────────
const inputRest = document.getElementById('inputRestaurar');
const dropRest  = document.getElementById('dropRestaurar');
const dropLabel = document.getElementById('dropLabel');

inputRest?.addEventListener('change', function () {
    if (this.files[0]) {
        dropLabel.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i>' + this.files[0].name;
        dropRest.style.borderColor = '#28a745';
    }
});

['dragover','dragleave','drop'].forEach(evt => {
    dropRest?.addEventListener(evt, e => {
        e.preventDefault();
        dropRest.style.borderColor = evt === 'dragover' ? '#fd7e14' : '#ced4da';
        if (evt === 'drop' && e.dataTransfer.files[0]) {
            inputRest.files = e.dataTransfer.files;
            inputRest.dispatchEvent(new Event('change'));
        }
    });
});

// ── Validar antes de restaurar ───────────────────────────────────────────
function validarRestaurar() {
    const file = document.getElementById('inputRestaurar').files[0];
    const conf = document.querySelector('[name="confirmacion"]').value;
    if (!file) { alert('Selecciona un archivo de backup.'); return false; }
    if (conf !== 'RESTAURAR') { alert('Debes escribir exactamente: RESTAURAR'); return false; }
    return confirm('¿Confirmas la restauración? Todos los datos actuales serán reemplazados.');
}

// ── Confirmar reset ──────────────────────────────────────────────────────
function confirmarReset() {
    const pwd  = document.querySelector('[name="password_admin"]').value;
    const conf = document.querySelector('[name="confirmacion_reset"]').value;
    if (!pwd)  { alert('Ingresa tu contraseña de administrador.'); return; }
    if (conf !== 'RESETEAR') { alert('Debes escribir exactamente: RESETEAR'); return; }
    $('#modalReset').modal('show');
}
</script>
@endpush
