@extends('plantilla')

@section('title', 'Editar Tipo de Cuota')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Tipo de Cuota: #{{ $tipo->id }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tipos-cuotas.index') }}" class="text-decoration-none">Tipos de Cuotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('tipos-cuotas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-edit text-primary"></i>
                <span>Modificar Parámetros de Configuración</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <!-- Alertas de Errores de Validación -->
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

            <form action="{{ route('tipos-cuotas.update', $tipo) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- SECCIÓN 1: PROPIEDADES DEL TIPO -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-sliders-h me-2"></i>Atributos del Elemento de Cobro
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="nombre" class="form-label fw-medium text-secondary">Nombre del Tipo de Cuota <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control px-3" value="{{ old('nombre', $tipo->nombre) }}" required>
                    </div>
                    
                    <div class="col-12 col-md-6">
                        <label for="frecuencia" class="form-label fw-medium text-secondary">Frecuencia de Pago <span class="text-danger">*</span></label>
                        <select name="frecuencia" id="frecuencia" class="form-select px-3" required>
                            <option value="mensual" {{ old('frecuencia', $tipo->frecuencia) == 'mensual' ? 'selected' : '' }}>Mensual</option>
                            <option value="anual" {{ old('frecuencia', $tipo->frecuencia) == 'anual' ? 'selected' : '' }}>Anual</option>
                            <option value="puntual" {{ old('frecuencia', $tipo->frecuencia) == 'puntual' ? 'selected' : '' }}>Puntual</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 2: PERMISOS Y RESTRICCIONES -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-user-shield me-2"></i>Permisos del Sistema
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card bg-light border-0 p-3 rounded-3">
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="editable" value="0">
                                <input class="form-check-input" type="checkbox" name="editable" value="1" id="editable" {{ old('editable', $tipo->editable) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium text-dark ms-2" for="editable">
                                    ¿Habilitar edición para el administrador?
                                </label>
                                <div class="form-text ms-2 mt-1 text-muted small">
                                    Si se desactiva, los usuarios con rol administrativo no podrán alterar los valores monetarios fijos asociados a esta categoría en futuras asignaciones.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('tipos-cuotas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection