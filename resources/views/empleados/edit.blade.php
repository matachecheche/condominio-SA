{{-- resources/views/empleados/edit.blade.php --}}
@extends('layouts.ap')
@can('editar empleados')
@section('content')
<div class="container py-4">
    <!-- Encabezado y Regreso -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Empleado</h2>
            <p class="text-muted small mb-0">Modifique los datos personales y de identidad del empleado seleccionado.</p>
        </div>
        <a href="{{ route('empleados.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Alertas de Errores de Validación (Agregado por consistencia UI) -->
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

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-user-edit text-primary"></i>
                <span>Información de Identidad Personal</span>
            </div>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('empleados.update', $empleado->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Columna Izquierda: Nombres y Apellidos -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-medium text-secondary">Nombre(s)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control px-3" placeholder="Ej. Carlos" value="{{ old('nombre', $empleado->nombre) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label fw-medium text-secondary">Apellido(s)</label>
                            <input type="text" name="apellido" id="apellido" class="form-control px-3" placeholder="Ej. Mendoza" value="{{ old('apellido', $empleado->apellido) }}" required>
                        </div>
                    </div>

                    <!-- Columna Derecha: Documentos de Identidad -->
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label for="ci" class="form-label fw-medium text-secondary">Cédula de Identidad (CI)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-address-card"></i></span>
                                <input type="text" name="ci" id="ci" class="form-control px-3" placeholder="Ej. 1234567" value="{{ old('ci', $empleado->ci) }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción inferior -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('empleados.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Actualizar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@endcan