<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Hospedaje') | {{ $empresa['nombre'] }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        .sidebar-dark-primary { background: {{ $empresa['color_sidebar'] }}; }
        .brand-link { background: {{ $empresa['color_brand'] }}; }
        .nav-sidebar .nav-item .nav-link.active { background: rgba(255,255,255,.1); }
        .card-header { font-weight: 600; }
        .badge-estado { font-size: .8rem; }
        .table-hover tbody tr:hover { background-color: rgba(0,123,255,.05); }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ auth()->user()->isLimpieza() ? route('housekeeping.index') : route('dashboard') }}" class="nav-link">Inicio</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link text-muted">
                    <i class="fas fa-clock mr-1"></i>{{ now()->format('d/m/Y H:i') }}
                </span>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <img src="{{ auth()->user()->avatar_url }}" class="img-circle" style="width:26px;height:26px;object-fit:cover" alt="">
                    <span class="ml-1">{{ auth()->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('perfil.index') }}" class="dropdown-item"><i class="fas fa-user-cog mr-2"></i>Mi Perfil</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link text-center">
            @if($empresa['logo'])
                <img src="{{ asset($empresa['logo']) }}" alt="" style="max-height:30px;max-width:40px" class="mr-2">
            @else
                <i class="fas fa-hotel text-primary mr-2" style="font-size:1.4rem"></i>
            @endif
            <span class="brand-text font-weight-bold" style="font-size:1rem">{{ $empresa['nombre'] }}</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ auth()->user()->avatar_url }}" class="img-circle elevation-2" style="object-fit:cover" alt="">
                </div>
                <div class="info">
                    <a href="{{ route('perfil.index') }}" class="d-block text-white">{{ auth()->user()->name }}</a>
                    <small class="text-muted">{{ auth()->user()->role_label }}</small>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                    @if(auth()->user()->isLimpieza())
                    {{-- Personal de limpieza: solo su panel y su perfil --}}
                    <li class="nav-item">
                        <a href="{{ route('housekeeping.index') }}" class="nav-link {{ request()->routeIs('housekeeping.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-broom"></i>
                            <p>Limpieza</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('perfil.index') }}" class="nav-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Mi Perfil</p>
                        </a>
                    </li>
                    @else

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    {{-- Calendario --}}
                    <li class="nav-item {{ request()->routeIs('calendario.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Calendario <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('calendario.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Reservas (FullCalendar)</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('calendario.disponibilidad') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Por Habitación</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-header">OPERACIONES</li>

                    {{-- Registro directo: huésped que llega sin reserva --}}
                    <li class="nav-item">
                        <a href="{{ route('registro-directo.create') }}" class="nav-link {{ request()->routeIs('registro-directo.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-door-open"></i>
                            <p>Registro Directo</p>
                        </a>
                    </li>

                    {{-- Reservas --}}
                    <li class="nav-item {{ request()->routeIs('reservas.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reservas.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Reservas <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reservas.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Lista de Reservas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reservas.create') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Nueva Reserva</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Huéspedes --}}
                    <li class="nav-item {{ request()->routeIs('huespedes.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('huespedes.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Huéspedes <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('huespedes.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Lista de Huéspedes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('huespedes.create') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Nuevo Huésped</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Facturación --}}
                    <li class="nav-item {{ request()->routeIs('facturas.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('facturas.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>Facturación <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('facturas.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Facturas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Housekeeping --}}
                    <li class="nav-item">
                        <a href="{{ route('housekeeping.index') }}" class="nav-link {{ request()->routeIs('housekeeping.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-broom"></i>
                            <p>Limpieza</p>
                        </a>
                    </li>

                    <li class="nav-header">ADMINISTRACIÓN</li>

                    {{-- Habitaciones --}}
                    @php($esSupervisor = auth()->user()->isSupervisor())
                    <li class="nav-item {{ request()->routeIs('habitaciones.*', 'tipo-habitaciones.*', 'tarifas.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('habitaciones.*', 'tipo-habitaciones.*', 'tarifas.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>Habitaciones <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('habitaciones.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Ver Habitaciones</p>
                                </a>
                            </li>
                            @if($esSupervisor)
                            <li class="nav-item">
                                <a href="{{ route('habitaciones.create') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Nueva Habitación</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tipo-habitaciones.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Tipos de Habitación</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('tarifas.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Tarifas de Temporada</p>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>

                    {{-- Reportes --}}
                    @if($esSupervisor)
                    <li class="nav-item {{ request()->routeIs('reportes.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Reportes <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reportes.ocupacion') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Ocupación</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reportes.ingresos') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Ingresos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reportes.huespedes') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Huéspedes</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    {{-- Sección SISTEMA (solo admin) --}}
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="nav-header">SISTEMA</li>

                    {{-- Usuarios --}}
                    <li class="nav-item {{ request()->routeIs('usuarios.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Usuarios <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('usuarios.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Gestión de Usuarios</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('usuarios.create') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Nuevo Usuario</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Configuración --}}
                    <li class="nav-item">
                        <a href="{{ route('configuracion.index') }}"
                           class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>Configuración</p>
                        </a>
                    </li>

                    {{-- Backup & Sistema --}}
                    <li class="nav-item {{ request()->routeIs('sistema.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('sistema.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>Backup & Sistema <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('sistema.index') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Copias de Seguridad</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('sistema.index') }}#restaurar" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i><p>Restaurar / Resetear</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @endif {{-- fin menú completo (no limpieza) --}}
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                {{-- Alertas flash --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Corrige los siguientes errores:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                @yield('content')

            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>{{ $empresa['nombre'] }}</strong> &copy; {{ date('Y') }}
        <div class="float-right d-none d-sm-inline-block">
            <b>Versión</b> 1.0.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Inicializar Select2 globalmente
    $(document).ready(function() {
        $('select.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 5000);
    });
</script>

@stack('scripts')
</body>
</html>
