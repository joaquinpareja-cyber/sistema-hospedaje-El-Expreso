@extends('layouts.app')
@section('title', 'Configuración del Sistema')
@section('page-title', 'Configuración')
@section('breadcrumb')
    <li class="breadcrumb-item active">Configuración</li>
@endsection

@push('styles')
<style>
    .config-tab-nav .nav-link        { color: #6c757d; font-weight: 500; padding: .6rem 1.1rem; border-radius: 8px 8px 0 0; }
    .config-tab-nav .nav-link.active { color: #fff; background: #1a2035; }
    .config-tab-nav .nav-link i      { width: 20px; text-align: center; }
    .tab-section-title               { font-size: .7rem; font-weight: 700; text-transform: uppercase;
                                       letter-spacing: .08em; color: #adb5bd; margin: 1.5rem 0 .5rem; }
    .logo-drop-area                  { border: 2px dashed #ced4da; border-radius: 12px;
                                       padding: 2.5rem; text-align: center; cursor: pointer;
                                       transition: all .25s; background: #f8f9fa; }
    .logo-drop-area:hover,
    .logo-drop-area.drag-over        { border-color: #007bff; background: #e8f0fe; }
    .logo-preview                    { max-height: 120px; max-width: 300px; object-fit: contain; }
    .color-swatch                    { display: inline-block; width: 32px; height: 32px;
                                       border-radius: 6px; border: 2px solid #dee2e6;
                                       cursor: pointer; vertical-align: middle; }
    .field-desc                      { font-size: .75rem; color: #6c757d; margin-top: .2rem; }
    .save-bar                        { position: sticky; bottom: 0; background: #fff;
                                       padding: 1rem 0; border-top: 1px solid #dee2e6;
                                       margin-top: 1.5rem; z-index: 10; }
</style>
@endpush

@section('content')

<form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" id="formConfig">
@csrf
@method('PUT')

<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs config-tab-nav" id="configTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-empresa" role="tab">
                    <i class="fas fa-building"></i> Empresa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-logo" role="tab">
                    <i class="fas fa-image"></i> Logo & Apariencia
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-facturacion" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i> Facturación
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-sistema" role="tab">
                    <i class="fas fa-cog"></i> Sistema
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content pt-2">

            {{-- ══════════════════════════════════════════════════════
                 TAB 1 — EMPRESA
            ═══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab-empresa" role="tabpanel">

                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1 text-info"></i>
                    Esta información aparecerá en los comprobantes, reportes y documentos generados por el sistema.
                </p>

                <div class="tab-section-title"><i class="fas fa-id-card mr-1"></i> Identidad Legal</div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><i class="fas fa-hotel text-primary mr-1"></i> Nombre del Hotel <span class="text-danger">*</span></label>
                        <input type="text" name="empresa_nombre" class="form-control"
                               value="{{ $config['empresa_nombre']->valor ?? '' }}"
                               placeholder="Ej: Hotel Los Girasoles" required>
                        <small class="field-desc">Nombre comercial que aparece en encabezados</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fas fa-file-alt text-secondary mr-1"></i> Razón Social</label>
                        <input type="text" name="empresa_razon_social" class="form-control"
                               value="{{ $config['empresa_razon_social']->valor ?? '' }}"
                               placeholder="Ej: Inversiones Girasol S.A.C.">
                        <small class="field-desc">Nombre legal registrado ante SUNAT</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-hashtag text-warning mr-1"></i> RUC</label>
                        <input type="text" name="empresa_ruc" class="form-control"
                               value="{{ $config['empresa_ruc']->valor ?? '' }}"
                               placeholder="20XXXXXXXXX" maxlength="11" pattern="\d{11}">
                        <small class="field-desc">11 dígitos, solo para facturación</small>
                    </div>
                    <div class="col-md-8 form-group">
                        <label><i class="fas fa-quote-right text-info mr-1"></i> Eslogan</label>
                        <input type="text" name="empresa_eslogan" class="form-control"
                               value="{{ $config['empresa_eslogan']->valor ?? '' }}"
                               placeholder="Ej: Tu hogar lejos de casa">
                        <small class="field-desc">Frase que aparece bajo el nombre en documentos</small>
                    </div>
                </div>

                <div class="tab-section-title"><i class="fas fa-map-marker-alt mr-1"></i> Contacto y Ubicación</div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label><i class="fas fa-map-pin text-danger mr-1"></i> Dirección</label>
                        <textarea name="empresa_direccion" class="form-control" rows="2"
                                  placeholder="Av. Los Álamos 1234, Miraflores, Lima">{{ $config['empresa_direccion']->valor ?? '' }}</textarea>
                        <small class="field-desc">Dirección completa del establecimiento</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-phone text-success mr-1"></i> Teléfono</label>
                        <input type="text" name="empresa_telefono" class="form-control"
                               value="{{ $config['empresa_telefono']->valor ?? '' }}"
                               placeholder="+51 1 234-5678">
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-envelope text-info mr-1"></i> Correo Electrónico</label>
                        <input type="email" name="empresa_email" class="form-control"
                               value="{{ $config['empresa_email']->valor ?? '' }}"
                               placeholder="reservas@hotel.com">
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-globe text-primary mr-1"></i> Sitio Web</label>
                        <input type="url" name="empresa_web" class="form-control"
                               value="{{ $config['empresa_web']->valor ?? '' }}"
                               placeholder="https://www.mihotel.com">
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 TAB 2 — LOGO & APARIENCIA
            ═══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-logo" role="tabpanel">

                <div class="tab-section-title"><i class="fas fa-image mr-1"></i> Logotipo del Hotel</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="logo-drop-area" id="logoDropArea" onclick="document.getElementById('inputLogo').click()">
                            @php $logoActual = $config['empresa_logo']->valor ?? null; @endphp
                            @if($logoActual)
                                <img src="{{ asset($logoActual) }}" class="logo-preview mb-2" id="logoPreview" alt="Logo actual">
                                <p class="text-muted mb-0" id="logoHint">Clic para cambiar el logo</p>
                            @else
                                <div id="logoPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="mb-1 font-weight-bold">Arrastra tu logo aquí</p>
                                    <p class="text-muted small mb-0">o haz clic para seleccionar</p>
                                </div>
                                <img src="" class="logo-preview mb-2 d-none" id="logoPreview" alt="Preview">
                            @endif
                            <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle mr-1"></i>PNG, JPG o SVG · Máx. 2MB · Recomendado: fondo transparente</p>
                        </div>
                        <input type="file" name="empresa_logo" id="inputLogo" accept="image/*" class="d-none">

                        @if($logoActual)
                        <div class="mt-2 text-right">
                            <form action="{{ route('configuracion.logo.delete') }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar el logo actual?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash mr-1"></i>Eliminar logo
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3"><i class="fas fa-eye mr-1 text-primary"></i>Vista previa en Sidebar</h6>
                                <div style="background:#1a2035;padding:12px 16px;border-radius:8px;display:flex;align-items:center;gap:10px">
                                    <div style="width:36px;height:36px;border-radius:50%;background:#2c3e6b;display:flex;align-items:center;justify-content:center">
                                        @if($logoActual)
                                            <img src="{{ asset($logoActual) }}" style="width:32px;height:32px;object-fit:contain;border-radius:50%">
                                        @else
                                            <i class="fas fa-hotel text-white"></i>
                                        @endif
                                    </div>
                                    <span style="color:#fff;font-weight:700;font-size:.9rem">{{ $config['empresa_nombre']->valor ?? 'Mi Hotel' }}</span>
                                </div>
                                <small class="text-muted mt-2 d-block">Así aparecerá en la barra lateral del sistema</small>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mt-2">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-lightbulb mr-1 text-warning"></i>Recomendaciones</h6>
                                <ul class="small text-muted pl-3 mb-0">
                                    <li>Usa fondo transparente (PNG) para mejor resultado</li>
                                    <li>Dimensiones ideales: 200×80 px o ratio 5:2</li>
                                    <li>Resolución mínima: 72 DPI</li>
                                    <li>Tamaño máximo: 2 MB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-section-title mt-3"><i class="fas fa-palette mr-1"></i> Colores del Sistema</div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Color del Sidebar</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text p-1">
                                    <input type="color" name="sistema_color_sidebar"
                                           id="colorSidebar"
                                           value="{{ $config['sistema_color_sidebar']->valor ?? '#1a2035' }}"
                                           style="width:32px;height:28px;border:none;cursor:pointer;padding:0">
                                </span>
                            </div>
                            <input type="text" class="form-control" id="hexSidebar"
                                   value="{{ $config['sistema_color_sidebar']->valor ?? '#1a2035' }}"
                                   placeholder="#1a2035" maxlength="7">
                        </div>
                        <small class="field-desc">Color de fondo del menú lateral</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Color de Marca (Brand)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text p-1">
                                    <input type="color" name="sistema_color_brand"
                                           id="colorBrand"
                                           value="{{ $config['sistema_color_brand']->valor ?? '#141d2e' }}"
                                           style="width:32px;height:28px;border:none;cursor:pointer;padding:0">
                                </span>
                            </div>
                            <input type="text" class="form-control" id="hexBrand"
                                   value="{{ $config['sistema_color_brand']->valor ?? '#141d2e' }}"
                                   placeholder="#141d2e" maxlength="7">
                        </div>
                        <small class="field-desc">Color del área del logo/marca</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="w-100 mt-2">
                            <label class="d-block small text-muted mb-1">Vista Previa</label>
                            <div id="colorPreview" style="border-radius:8px;overflow:hidden;border:1px solid #dee2e6">
                                <div id="prevBrand" style="height:12px;background:{{ $config['sistema_color_brand']->valor ?? '#141d2e' }}"></div>
                                <div id="prevSidebar" style="height:40px;background:{{ $config['sistema_color_sidebar']->valor ?? '#1a2035' }};
                                     display:flex;align-items:center;padding:0 12px">
                                    <i class="fas fa-hotel text-white mr-2"></i>
                                    <span style="color:#fff;font-size:.8rem;font-weight:600">Sistema Hospedaje</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 TAB 3 — FACTURACIÓN
            ═══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-facturacion" role="tabpanel">

                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1 text-info"></i>
                    Configura la moneda, impuestos y series de numeración para los comprobantes de pago.
                </p>

                <div class="tab-section-title"><i class="fas fa-coins mr-1"></i> Moneda</div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Símbolo de Moneda <span class="text-danger">*</span></label>
                        <input type="text" name="facturacion_moneda_simbolo" class="form-control font-weight-bold"
                               value="{{ $config['facturacion_moneda_simbolo']->valor ?? 'S/' }}"
                               placeholder="S/" maxlength="5" required>
                        <small class="field-desc">Ej: S/, $, €, £</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Nombre de la Moneda</label>
                        <input type="text" name="facturacion_moneda_nombre" class="form-control"
                               value="{{ $config['facturacion_moneda_nombre']->valor ?? 'Soles' }}"
                               placeholder="Soles">
                        <small class="field-desc">Nombre completo (Soles, Dólares...)</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label><i class="fas fa-percentage text-warning mr-1"></i> IGV / Impuesto (%)</label>
                        <div class="input-group">
                            <input type="number" name="facturacion_igv" class="form-control"
                                   value="{{ $config['facturacion_igv']->valor ?? '18' }}"
                                   min="0" max="100" step="0.01" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                        <small class="field-desc">0 = sin impuesto</small>
                    </div>
                </div>

                <div class="tab-section-title"><i class="fas fa-receipt mr-1"></i> Series de Comprobantes</div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-file-alt mr-1 text-success"></i> Serie — Boleta</label>
                        <input type="text" name="facturacion_serie_boleta" class="form-control"
                               value="{{ $config['facturacion_serie_boleta']->valor ?? 'B001' }}"
                               placeholder="B001" maxlength="10">
                        <small class="field-desc">Prefijo para boletas de venta</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-file-invoice mr-1 text-primary"></i> Serie — Factura</label>
                        <input type="text" name="facturacion_serie_factura" class="form-control"
                               value="{{ $config['facturacion_serie_factura']->valor ?? 'F001' }}"
                               placeholder="F001" maxlength="10">
                        <small class="field-desc">Prefijo para facturas</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label><i class="fas fa-file mr-1 text-secondary"></i> Serie — Recibo</label>
                        <input type="text" name="facturacion_serie_recibo" class="form-control"
                               value="{{ $config['facturacion_serie_recibo']->valor ?? 'R001' }}"
                               placeholder="R001" maxlength="10">
                        <small class="field-desc">Prefijo para recibos</small>
                    </div>
                </div>

                <div class="tab-section-title"><i class="fas fa-align-left mr-1"></i> Texto Adicional</div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Pie de Comprobante</label>
                        <textarea name="facturacion_pie_factura" class="form-control" rows="2"
                                  placeholder="Ej: Gracias por su preferencia. Este documento no tiene validez tributaria.">{{ $config['facturacion_pie_factura']->valor ?? '' }}</textarea>
                        <small class="field-desc">Aparece al pie de todos los comprobantes generados en PDF</small>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 TAB 4 — SISTEMA
            ═══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-sistema" role="tabpanel">

                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1 text-info"></i>
                    Ajustes generales del sistema como zona horaria y formato de fechas.
                </p>

                <div class="tab-section-title"><i class="fas fa-clock mr-1"></i> Fechas y Hora</div>
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label>Zona Horaria</label>
                        <select name="sistema_zona_horaria" class="form-control select2">
                            @php
                                $zonas = [
                                    'America/Lima'       => 'América/Lima (GMT-5)',
                                    'America/Bogota'     => 'América/Bogotá (GMT-5)',
                                    'America/Santiago'   => 'América/Santiago (GMT-4)',
                                    'America/Guayaquil'  => 'América/Guayaquil (GMT-5)',
                                    'America/La_Paz'     => 'América/La Paz (GMT-4)',
                                    'America/Buenos_Aires'=> 'América/Buenos Aires (GMT-3)',
                                    'America/Mexico_City'=> 'América/Ciudad México (GMT-6)',
                                    'America/New_York'   => 'América/Nueva York (GMT-5)',
                                    'Europe/Madrid'      => 'Europa/Madrid (GMT+1)',
                                ];
                                $zonaActual = $config['sistema_zona_horaria']->valor ?? 'America/Lima';
                            @endphp
                            @foreach($zonas as $val => $label)
                                <option value="{{ $val }}" {{ $zonaActual === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Formato de Fecha</label>
                        <select name="sistema_formato_fecha" class="form-control select2">
                            @php
                                $formatos = [
                                    'd/m/Y'   => 'DD/MM/AAAA (31/12/2025)',
                                    'm/d/Y'   => 'MM/DD/AAAA (12/31/2025)',
                                    'Y-m-d'   => 'AAAA-MM-DD (2025-12-31)',
                                    'd-m-Y'   => 'DD-MM-AAAA (31-12-2025)',
                                    'd M Y'   => 'DD Mes AAAA (31 Dic 2025)',
                                ];
                                $fmtActual = $config['sistema_formato_fecha']->valor ?? 'd/m/Y';
                            @endphp
                            @foreach($formatos as $val => $label)
                                <option value="{{ $val }}" {{ $fmtActual === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-3">
                        <div class="alert alert-light border mb-0 py-2 w-100">
                            <small class="text-muted">Hora actual del servidor:</small><br>
                            <strong>{{ now()->format('d/m/Y H:i:s') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="tab-section-title"><i class="fas fa-info-circle mr-1"></i> Información del Sistema</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr><th width="30%">Versión de PHP</th><td>{{ PHP_VERSION }}</td></tr>
                                    <tr><th>Versión de Laravel</th><td>{{ app()->version() }}</td></tr>
                                    <tr><th>Entorno</th><td><span class="badge badge-{{ config('app.env') === 'production' ? 'success' : 'warning' }}">{{ config('app.env') }}</span></td></tr>
                                    <tr><th>Base de Datos</th><td>{{ config('database.connections.mysql.database') }} (MySQL)</td></tr>
                                    <tr><th>Zona Horaria del Servidor</th><td>{{ config('app.timezone') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>

    {{-- ── Barra de guardado sticky ── --}}
    <div class="card-footer save-bar">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small"><i class="fas fa-info-circle mr-1"></i>Los cambios se aplican inmediatamente al guardar.</span>
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save mr-2"></i>Guardar Configuración
            </button>
        </div>
    </div>

</div>{{-- /card --}}
</form>

@endsection

@push('scripts')
<script>
// ── Previsualización de logo ──────────────────────────────────────────────
const inputLogo   = document.getElementById('inputLogo');
const logoPreview = document.getElementById('logoPreview');
const logoDrop    = document.getElementById('logoDropArea');
const logoPlaceh  = document.getElementById('logoPlaceholder');

inputLogo?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        logoPreview.src = e.target.result;
        logoPreview.classList.remove('d-none');
        if (logoPlaceh) logoPlaceh.classList.add('d-none');
    };
    reader.readAsDataURL(file);
});

['dragover', 'dragleave', 'drop'].forEach(evt => {
    logoDrop?.addEventListener(evt, e => {
        e.preventDefault();
        if (evt === 'dragover') logoDrop.classList.add('drag-over');
        else logoDrop.classList.remove('drag-over');
        if (evt === 'drop' && e.dataTransfer.files[0]) {
            inputLogo.files = e.dataTransfer.files;
            inputLogo.dispatchEvent(new Event('change'));
        }
    });
});

// ── Sincronizar color picker ↔ text input ────────────────────────────────
function syncColor(pickerId, hexId, prevId) {
    const picker = document.getElementById(pickerId);
    const hex    = document.getElementById(hexId);
    const prev   = document.getElementById(prevId);

    picker?.addEventListener('input', () => {
        hex.value = picker.value;
        if (prev) prev.style.background = picker.value;
    });
    hex?.addEventListener('input', () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(hex.value)) {
            picker.value = hex.value;
            if (prev) prev.style.background = hex.value;
        }
    });
}

syncColor('colorSidebar', 'hexSidebar', 'prevSidebar');
syncColor('colorBrand',   'hexBrand',   'prevBrand');

// ── Inicializar Select2 ──────────────────────────────────────────────────
$(document).ready(function () {
    $('select.select2').select2({ theme: 'bootstrap4', width: '100%' });
});
</script>
@endpush
