<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema Residencial el Expreso</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Colores de botones del sistema (editar aquí: public/css/tema.css) -->
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}">
    <style>
        html, body { height: 100%; }
        body { margin: 0; background: #f4f6f9; font-family: 'Source Sans Pro', sans-serif; }

        .login-split { display: flex; min-height: 100vh; }

        /* ── Lado izquierdo: carrusel de imágenes ────────────────── */
        /* Pon las fotos en public/img/login/ (jpg, png o webp). Se muestran en orden alfabético. */
        .login-imagen {
            flex: 1 1 55%;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: linear-gradient(135deg, #4f68c4 0%, #2c3e6b 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
        }
        .slide {
            position: absolute;
            inset: 0;
            z-index: -2;
            overflow: hidden;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }
        .slide.activa { opacity: 1; }
        /* Capa de abajo: la misma foto ampliada, desenfocada y oscurecida (rellena los costados) */
        .slide::before {
            content: '';
            position: absolute;
            inset: -40px;
            background: var(--img) center / cover no-repeat;
            filter: blur(28px) brightness(.55);
        }
        /* Capa de arriba: la foto nítida y completa ("contain", sin recortes) */
        .slide::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--img) center / contain no-repeat;
        }
        /* Sombra solo arriba y abajo para que se lean los textos */
        .login-imagen::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(0,0,0,.35) 0%, rgba(0,0,0,0) 22%, rgba(0,0,0,0) 55%, rgba(0,0,0,.65) 100%);
        }
        .puntos { position: absolute; right: 3rem; bottom: 3.2rem; display: flex; gap: .5rem; }
        .puntos button {
            width: 10px; height: 10px; padding: 0; border: 0; border-radius: 50%;
            background: rgba(255,255,255,.45); cursor: pointer; transition: all .3s;
        }
        .puntos button.activa { background: #fff; width: 26px; border-radius: 5px; }
        .login-imagen h1 { font-weight: 700; font-size: 2.4rem; margin-bottom: .5rem; text-shadow: 0 2px 12px rgba(0,0,0,.3); }
        .login-imagen p  { font-size: 1.1rem; opacity: .95; max-width: 480px; margin: 0; text-shadow: 0 1px 6px rgba(0,0,0,.5); }
        .login-imagen .marca { position: absolute; top: 2.5rem; left: 3rem; font-size: 1.1rem; font-weight: 600; text-shadow: 0 1px 6px rgba(0,0,0,.5); }

        /* ── Lado derecho: formulario ───────────────────────────── */
        .login-form {
            flex: 1 1 45%;
            max-width: 560px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 4rem;
            background: #fff;
            box-shadow: -10px 0 40px rgba(0,0,0,.08);
        }
        .login-form .hotel-icon { font-size: 2.6rem; color: #2c3e6b; }
        .login-form h2 { font-weight: 700; color: #1a2035; font-size: 1.6rem; margin: .5rem 0 .25rem; }
        .login-form .sub { color: #6c757d; margin-bottom: 2rem; }

        .btn-primary { background: #007bff; border-color: #007bff; }
        .btn-primary:hover, .btn-primary:focus { background: #0062cc; border-color: #005cbf; }
        .input-group-text { background: #f8f9fa; border-right: none; }
        .form-control { border-left: none; height: calc(2.5rem + 2px); }
        .form-control:focus { box-shadow: none; border-color: #ced4da; }
        .pie { color: #adb5bd; font-size: .8rem; margin-top: 2.5rem; }

        /* En tablet y celular se oculta la imagen y el formulario ocupa todo */
        @media (max-width: 991.98px) {
            .login-imagen { display: none; }
            .login-form { max-width: none; padding: 2.5rem 1.5rem; box-shadow: none; }
            .login-form > div { width: 100%; max-width: 400px; margin: 0 auto; }
        }
    </style>
</head>
<body class="hold-transition">

<div class="login-split">

    {{-- Lado izquierdo: carrusel (toma todas las imágenes de public/img/login/) --}}
    @php
        $slides = collect(is_dir(public_path('img/login')) ? scandir(public_path('img/login')) : [])
            ->filter(fn($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn($f) => asset('img/login/' . rawurlencode($f)))
            ->values();
        if ($slides->isEmpty() && is_file(public_path('img/login.jpg'))) {
            $slides = collect([asset('img/login.jpg')]);
        }
    @endphp
    <div class="login-imagen">
        @foreach($slides as $i => $src)
            <div class="slide {{ $i === 0 ? 'activa' : '' }}" style="--img: url('{{ $src }}')"></div>
        @endforeach

        @if($slides->count() > 1)
        <div class="puntos">
            @foreach($slides as $i => $src)
                <button type="button" class="{{ $i === 0 ? 'activa' : '' }}" data-slide="{{ $i }}" aria-label="Imagen {{ $i + 1 }}"></button>
            @endforeach
        </div>
        @endif

        <div class="marca"><i class="fas fa-hotel mr-2"></i>Residencial el Expreso</div>
        <h1>Bienvenido</h1>
        <p>Gestiona reservas, huéspedes y facturación desde un solo lugar.</p>
    </div>

    {{-- Lado derecho: formulario --}}
    <div class="login-form">
        <div>
            <div class="text-center">
                <i class="fas fa-hotel hotel-icon"></i>
                <h2>Residencial el Expreso</h2>
                <p class="sub">Ingresa tus credenciales para continuar</p>
            </div>

            @if(session('success') || session('status'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') ?? session('status') }}
            </div>
            @endif

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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Recordarme</label>
                    </div>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-secondary small">
                        ¿Olvidaste tu contraseña?
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-login-ingresar btn-primary btn-block btn-lg font-weight-bold">
                    Ingresar
                </button>
            </form>

            <p class="pie text-center mb-0">
                &copy; {{ date('Y') }} Sistema Residencial el Expreso. Todos los derechos reservados.
            </p>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
// Carrusel del login: cambia de imagen cada SEGUNDOS; los puntos permiten elegir una.
(function () {
    const SEGUNDOS = 5;
    const slides = document.querySelectorAll('.slide');
    const puntos = document.querySelectorAll('.puntos button');
    if (slides.length < 2) return;

    let actual = 0, timer;
    function mostrar(n) {
        slides[actual].classList.remove('activa');
        puntos[actual]?.classList.remove('activa');
        actual = (n + slides.length) % slides.length;
        slides[actual].classList.add('activa');
        puntos[actual]?.classList.add('activa');
    }
    function iniciar() {
        clearInterval(timer);
        timer = setInterval(() => mostrar(actual + 1), SEGUNDOS * 1000);
    }
    puntos.forEach(p => p.addEventListener('click', () => { mostrar(+p.dataset.slide); iniciar(); }));
    iniciar();
})();
</script>
</body>
</html>
