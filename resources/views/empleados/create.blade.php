@extends('layouts.ap')
@can('crear empleados')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Nuevo Empleado</h2>
            <p class="text-muted small mb-0">Ingrese los datos personales y contractuales para dar de alta al nuevo personal.</p>
        </div>
        <a href="{{ route('empleados.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
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
            <form action="{{ route('empleados.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-id-card me-2"></i>Datos Personales
                        </h5>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-medium text-secondary">Nombre(s)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control px-3" placeholder="Ej. Carlos" value="{{ old('nombre') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label fw-medium text-secondary">Apellido(s)</label>
                            <input type="text" name="apellido" id="apellido" class="form-control px-3" placeholder="Ej. Mendoza" value="{{ old('apellido') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="ci" class="form-label fw-medium text-secondary">Cédula de Identidad (CI)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-address-card"></i></span>
                                <input type="text" name="ci" id="ci" class="form-control px-3" placeholder="Ej. 1234567" value="{{ old('ci') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-briefcase me-2"></i>Información Laboral
                        </h5>

                        <div class="mb-3">
                            <label for="fecha_ingreso" class="form-label fw-medium text-secondary">Fecha de Ingreso</label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control px-3" value="{{ old('fecha_ingreso') }}">
                        </div>

                        <div class="mb-3">
                            <label for="cargo_empleado_id" class="form-label fw-medium text-secondary">Cargo Asignado</label>
                            <select name="cargo_empleado_id" id="cargo_empleado_id" class="form-select px-3" required>
                                <option value="" disabled selected>-- Seleccionar Cargo --</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}" {{ old('cargo_empleado_id') == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->cargo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary mb-2">Estado Inicial</label>
                            <div class="p-2 border rounded bg-light d-flex gap-3">
                                <div class="form-check form-check-inline mb-0 ms-2">
                                    <input class="form-check-input cursor-pointer" type="radio" name="estado" id="estado_activo" value="1" {{ old('estado', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark fw-medium cursor-pointer" for="estado_activo">Activo</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input cursor-pointer" type="radio" name="estado" id="estado_inactivo" value="0" {{ old('estado') == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark fw-medium cursor-pointer" for="estado_inactivo">Inactivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('empleados.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Registrar Empleado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@endcan