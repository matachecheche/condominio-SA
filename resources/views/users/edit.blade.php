@extends('plantilla')

@section('title', 'Editar usuario')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .btn {
            transition: all 0.2s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 0.85;
            cursor: not-allowed;
        }
        /* Ajuste de altura para Select2 con tema Bootstrap 5 */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Encabezado de la página -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mt-3 mb-4 gap-2">
            <div>
                <h1 class="fw-bold text-dark mb-1 fs-3">Editar Usuario</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-muted">Usuarios</a></li>
                        <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Editar usuario</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Atrás</span>
            </a>
        </div>

        <!-- Card Principal de Formulario -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                    <i class="fas fa-user-edit text-primary"></i>
                    <span>Modificar Credenciales y Perfil de Usuario</span>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('users.update', ['user' => $user]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row g-4 mb-4">
                        <!-- Columna de Datos de Acceso -->
                        <div class="col-12 col-md-6">
                            <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                                <i class="fas fa-key me-2"></i>Seguridad y Cuenta
                            </h5>

                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium text-secondary">
                                    <i class="fas fa-lock me-1.5 opacity-50"></i>Usuario <span class="text-muted small">(No editable)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                                    <input type="text" class="form-control border-start-0" name="name" id="name"
                                        placeholder="introduzca un nombre de usuario" value="{{ $user->name }}" readonly>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium text-secondary">
                                    <i class="fas fa-envelope me-1.5 opacity-75"></i>Correo electrónico
                                </label>
                                <input type="email" class="form-control px-3" name="email" id="email"
                                    placeholder="nombre@ejemplo.com" value="{{ $user->email }}">
                                @error('email')
                                    <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="inputPassword" class="form-label fw-medium text-secondary">
                                    <i class="fas fa-passkey me-1.5 opacity-75"></i>Contraseña
                                </label>
                                <input type="password" class="form-control px-3" name="password" id="inputPassword"
                                    placeholder="Dejar en blanco para conservar la actual">
                                <div class="form-text text-muted small mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Solo ingrese un valor si desea cambiar la clave actual.
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna de Asignación de Roles y Personal -->
                        <div class="col-12 col-md-6">
                            <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                                <i class="fas fa-id-badge me-2"></i>Asignaciones y Roles
                            </h5>

                            <div class="mb-4">
                                <label for="empleado_id" class="form-label fw-medium text-secondary">
                                    <i class="fas fa-briefcase me-1.5 opacity-75"></i>Empleado Vinculado
                                </label>
                                <select class="form-select" id="empleado_id" name="empleado_id" data-placeholder="Seleccione un empleado">
                                    <option value="">--Seleccione empleado--</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id }}" {{ $user->empleado_id == $empleado->id ? 'selected' : '' }}>
                                            {{ $empleado->nombre }}, CI: {{ $empleado->ci }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label fw-medium text-secondary">
                                    <i class="fas fa-user-tag me-1.5 opacity-75"></i>Seleccionar Rol del Sistema
                                </label>
                                <select name="role" id="role" class="form-select px-3">
                                    @foreach ($roles as $item)
                                        @if ( in_array($item->name, $user->roles->pluck('name')->toArray()) )
                                            <option selected value="{{$item->name}}" @selected(old('role')==$item->name)>{{ ucfirst($item->name) }}</option>
                                        @else
                                            <option value="{{$item->name}}" @selected(old('role')==$item->name)>{{ ucfirst($item->name) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small mt-1">
                                    Determina los niveles de permiso globales que poseerá este usuario.
                                </div>
                                @error('role')
                                    <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 pt-3 border-top border-light">
                        <button type="reset" class="btn btn-light border px-3">
                            <i class="fas fa-undo me-1.5 text-secondary"></i>Restaurar datos
                        </button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                            <i class="fas fa-save me-1.5"></i>Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#empleado_id').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: 'Seleccione empleado',
                allowClear: true
            });
        });
    </script>
@endpush