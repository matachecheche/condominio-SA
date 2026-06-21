<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Condominios</title>

    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #141e30, #243b55);
            height: 100vh;
        }

        .card-login {
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .card-login .card-header {
            border-bottom: none;
            background: transparent;
        }

        .custom-input {
            border-radius: 10px;
            border: none;
            padding: 12px;
        }

        .custom-input:focus {
            box-shadow: 0 0 0 2px #00c6ff;
        }

        .btn-login {
            border-radius: 10px;
            padding: 10px;
            font-weight: bold;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            border: none;
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: none;
        }

        .form-control {
            border-radius: 0 10px 10px 0 !important;
        }

        .forgot-link {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #00c6ff;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card card-login text-white p-4" style="width: 100%; max-width: 400px;">

        <div class="text-center mb-3">
            <h3 class="fw-bold">Condominio San Diego</h3>
            <p class="text-light small">Acceso al sistema</p>
        </div>

        <div class="card-body">

            @if ($errors->any())
                @foreach ($errors->all() as $item)
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ $item }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            @endif

            <form action="/login" method="POST">
                @csrf

                <!-- EMAIL -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email"
                        class="form-control custom-input"
                        placeholder="Correo electrónico"
                        value="{{ old('email') }}">
                </div>

                <!-- PASSWORD -->
                <div class="input-group mb-4">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password"
                        class="form-control custom-input"
                        placeholder="Contraseña">
                </div>

                <!-- BOTÓN -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login text-white">
                        Iniciar sesión
                    </button>
                </div>

                <!-- ¿OLVIDASTE TU CONTRASEÑA? -->
                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        <i class="fas fa-key me-1"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>

            </form>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>