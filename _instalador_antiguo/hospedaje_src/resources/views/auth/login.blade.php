<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema Hospedaje</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a2035 0%, #2c3e6b 100%); min-height: 100vh; }
        .login-box { width: 380px; }
        .login-logo a { color: #fff !important; font-size: 1.6rem; font-weight: 700; }
        .login-logo small { display: block; color: rgba(255,255,255,.6); font-size:.85rem; font-weight:300; margin-top:-4px; }
        .card { border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .card-body { padding: 2rem; }
        .btn-primary { background: #1a2035; border-color: #1a2035; }
        .btn-primary:hover { background: #2c3e6b; border-color: #2c3e6b; }
        .hotel-icon { font-size: 3rem; color: rgba(255,255,255,.8); }
        .input-group-text { background: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #ced4da; }
    </style>
</head>
<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo text-center mb-4">
        <i class="fas fa-hotel hotel-icon mb-2 d-block"></i>
        <a href="#"><b>Sistema</b> Hospedaje</a>
        <small>Panel de Administración</small>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="login-box-msg text-muted mb-4 text-center">
                Ingresa tus credenciales para continuar
            </p>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="Correo electrónico"
                           value="{{ old('email') }}"
                           autofocus required>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" name="password"
                           class="form-control"
                           placeholder="Contraseña"
                           required>
                </div>

                <div class="row mb-3">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Recordarme</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                            Ingresar
                        </button>
                    </div>
                </div>
            </form>

            @if(Route::has('password.request'))
            <p class="text-center text-muted mb-0">
                <a href="{{ route('password.request') }}" class="text-secondary">
                    <i class="fas fa-key mr-1"></i>¿Olvidaste tu contraseña?
                </a>
            </p>
            @endif
        </div>
    </div>

    <p class="text-center mt-3" style="color:rgba(255,255,255,.4); font-size:.8rem">
        &copy; {{ date('Y') }} Sistema de Hospedaje
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
