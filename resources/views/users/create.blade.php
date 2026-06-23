@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Usuario</h2>
            <p class="text-muted small mb-0">Crea una nueva cuenta de usuario y asigna sus respectivos roles y accesos.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
        <i class="fas fa-exclamation-circle text-danger mt-1 fs-5"></i>
        <div>
            <span class="fw-bold d-block mb-1">Por favor corrige los siguientes errores:</span>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-user-shield me-2"></i>Datos de Cuenta
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary"><i class="fas fa-user me-1.5 opacity-75"></i>Nombre completo</label>
                            <input type="text" name="name" class="form-control px-3" placeholder="Ej. Juan Pérez" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary"><i class="fas fa-envelope me-1.5 opacity-75"></i>Correo Electrónico</label>
                            <input type="email" name="email" class="form-control px-3" placeholder="ejemplo@correo.com" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium text-secondary"><i class="fas fa-lock me-1.5 opacity-75"></i>Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control px-3" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small mt-1.5">
                                <i class="fas fa-info-circle me-1"></i>Mínimo 8 caracteres (mayúsculas, números y símbolos).
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-medium text-secondary"><i class="fas fa-lock me-1.5 opacity-75"></i>Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control px-3" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-id-card-alt me-2"></i>Roles y Vínculos
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary"><i class="fas fa-user-tag me-1.5 opacity-75"></i>Rol de Usuario</label>
                            <select name="role" class="form-select px-3" required>
                                <option value="" disabled selected>-- Seleccionar un rol --</option>
                                @foreach($roles as $rol)
                                <option value="{{ $rol->name }}" {{ old('role') == $rol->name ? 'selected' : '' }}>
                                    {{ ucfirst($rol->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary"><i class="fas fa-briefcase me-1.5 opacity-75"></i>Empleado vinculado <span class="text-muted fw-normal">(opcional)</span></label>
                            <select name="empleado_id" class="form-select px-3">
                                <option value="">-- No asignar empleado --</option>
                                @foreach($empleados as $empleado)
                                <option value="{{ $empleado->id }}" {{ old('empleado_id') == $empleado->id ? 'selected' : '' }}>
                                    {{ $empleado->nombre }} {{ $empleado->apellido }} - CI: {{ $empleado->ci }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary"><i class="fas fa-home me-1.5 opacity-75"></i>Residente vinculado <span class="text-muted fw-normal">(opcional)</span></label>
                            <select name="residente_id" class="form-select px-3">
                                <option value="">-- No asignar residente --</option>
                                @foreach($residentes as $residente)
                                <option value="{{ $residente->id }}" {{ old('residente_id') == $residente->id ? 'selected' : '' }}>
                                    {{ $residente->nombre }} {{ $residente->apellido }} - CI: {{ $residente->ci }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('users.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Registrar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Función JS simple para alternar la visibilidad de las contraseñas --}}
<script>
    function togglePasswordVisibility(fieldId, button) {
        const passwordInput = document.getElementById(fieldId);
        const icon = button.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection