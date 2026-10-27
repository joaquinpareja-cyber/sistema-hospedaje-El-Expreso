<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Sistema Hospedaje</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Colores de botones del sistema (editar aquí: public/css/tema.css) -->
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}">
    <style>
        body { background: linear-gradient(135deg, #1a2035 0%, #2c3e6b 100%); min-height: 100vh; }
        .login-box { width: 380px; }
        .login-logo a { color: #fff !important; font-size: 1.6rem; font-weight: 700; }
        .card { border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .card-body { padding: 2rem; }
        .btn-primary { background: #007bff; border-color: #007bff; }
        .btn-primary:hover, .btn-primary:focus { background: #0062cc; border-color: #005cbf; }
        .hotel-icon { font-size: 3rem; color: rgba(255,255,255,.8); }
    </style>
</head>
<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo text-center mb-4">
        <i class="fas fa-hotel hotel-icon mb-2 d-block"></i>
        <a href="{{ route('login') }}"><b>Sistema</b> Hospedaje</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('status'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
