@extends('plantilla')

@section('title', 'Nuevo Tipo de Cuota')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Nuevo Tipo de Cuota</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tipos-cuotas.index') }}" class="text-decoration-none">Tipos de Cuotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Crear</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('tipos-cuotas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-plus-circle text-primary"></i>
                <span>Registrar Categoría de Cobro</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
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

            <form action="{{ route('tipos-cuotas.store') }}" method="POST">
                @csrf
                
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-sliders-h me-2"></i>Atributos de la Nueva Categoría
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="nombre" class="form-label fw-medium text-secondary">Nombre del Tipo de Cuota <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control px-3" placeholder="Ej: Expensas Extraordinarias" value="{{ old('nombre') }}" required>
                    </div>
                    
                    <div class="col-12 col-md-6">
                        <label for="frecuencia" class="form-label fw-medium text-secondary">Frecuencia de Facturación <span class="text-danger">*</span></label>
                        <select name="frecuencia" id="frecuencia" class="form-select px-3" required>
                            <option value="">— Selecciona una frecuencia —</option>
                            <option value="mensual" {{ old('frecuencia') == 'mensual' ? 'selected' : '' }}>Mensual</option>
                            <option value="anual" {{ old('frecuencia') == 'anual' ? 'selected' : '' }}>Anual</option>
                            <option value="puntual" {{ old('frecuencia') == 'puntual' ? 'selected' : '' }}>Puntual</option>
                        </select>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-user-shield me-2"></i>Permisos del Sistema
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card bg-light border-0 p-3 rounded-3">
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="editable" value="0">
                                <input class="form-check-input" type="checkbox" name="editable" value="1" id="editable" {{ old('editable', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium text-dark ms-2" for="editable">
                                    ¿Permitir que el administrador edite estos cargos?
                                </label>
                                <div class="form-text ms-2 mt-1 text-muted small">
                                    Al habilitarse, los operadores administrativos podrán modificar de forma manual los valores o asignaciones derivados de esta plantilla en el módulo general.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('tipos-cuotas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Guardar Tipo de Cuota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection