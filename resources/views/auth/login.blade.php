<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Condominio San Diego</title>

    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/condominio-theme.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #090d16, #111827);
            height: 100vh;
            overflow: hidden;
        }

        .card-login {
            border-radius: 20px;
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .card-login .card-header {
            border-bottom: none;
            background: transparent;
        }

        .custom-input {
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            background: rgba(17, 24, 39, 0.7) !important;
            color: #ffffff !important;
            padding: 12px;
        }

        .custom-input::placeholder {
            color: #94a3b8;
        }

        .custom-input:focus {
            box-shadow: 0 0 0 2px #38bdf8 !important;
            border-color: #38bdf8 !important;
        }

        .btn-login {
            border-radius: 10px;
            padding: 11px;
            font-weight: 600;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.3);
            background: linear-gradient(135deg, #0284c7, #0369a1);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: #1f2937 !important;
            color: #94a3b8;
            min-width: 46px;
            justify-content: center;
        }

        /* Estilo para el botón del ojo */
        .btn-toggle-password {
            border-radius: 0 10px 10px 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: #1f2937 !important;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .btn-toggle-password:hover {
            color: #38bdf8;
        }

        /* Ajuste para que el input del password no tenga esquinas redondeadas a la derecha */
        .input-password-field {
            border-radius: 0 !important;
        }

        .form-control:first-child {
            border-radius: 0 10px 10px 0 !important;
        }

        .forgot-link {
            color: #94a3b8;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #38bdf8;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card card-login text-white p-4 mx-3" style="width: 100%; max-width: 400px;">

        <div class="text-center mb-4">
            <h3 class="fw-bold tracking-wide mb-1">🏡 Condominio San Diego</h3>
            <p class="text-secondary small">Panel de Control Residencial</p>
        </div>

        <div class="card-body p-1">

            @if ($errors->any())
                @foreach ($errors->all() as $item)
                    <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger text-white small rounded-3 mb-3">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ $item }}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            @endif

            <form action="/login" method="POST">
                @csrf

                <!-- EMAIL -->
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email"
                        class="form-control custom-input"
                        placeholder="Correo electrónico"
                        value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>

                <!-- PASSWORD CON OJITO -->
                <div class="input-group mb-4">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password"
                        class="form-control custom-input input-password-field"
                        placeholder="Contraseña" required autocomplete="current-password">
                    <button class="input-group-text btn-toggle-password" type="button" id="togglePassword">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <!-- BOTÓN -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login text-white shadow-sm">
                        Iniciar Sesión
                    </button>
                </div>

                <!-- ENLACE: OLVIDÓ CONTRASEÑA -->
                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        <i class="fas fa-key me-1" style="font-size: 0.75rem;"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>

            </form>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript para alternar la visibilidad de la contraseña
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
        // Alternar el tipo de atributo entre password y text
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Alternar el icono del ojo abierto / ojo tachado
        if (type === 'password') {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    });
</script>

</body>
</html>