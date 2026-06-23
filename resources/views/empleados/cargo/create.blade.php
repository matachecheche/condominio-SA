@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <!-- Encabezado y Regreso -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Cargo</h2>
            <p class="text-muted small mb-0">Defina un nuevo puesto o rol laboral para la estructura de la organización.</p>
        </div>
        <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Alertas de Errores de Validación (Agregado por consistencia del sistema) -->
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
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                        <i class="fas fa-plus-circle text-primary"></i>
                        <span>Información del Puesto Comercial</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('cargos.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="cargo" class="form-label fw-medium text-secondary">Nombre del Cargo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-briefcase"></i></span>
                                <input type="text" name="cargo" id="cargo" class="form-control px-3" placeholder="Ej. Gerente de Operaciones, Recepcionista" required>
                            </div>
                            <div class="form-text text-muted small mt-1.5">
                                <i class="fas fa-info-circle me-1"></i>Asegúrese de usar nombres claros y descriptivos.
                            </div>
                        </div>

                        <!-- Botones de Acción inferior -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top border-light">
                            <a href="{{ route('cargos.index') }}" class="btn btn-light border px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                                <i class="fas fa-save me-1.5"></i>Guardar Cargo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection