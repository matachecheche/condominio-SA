@extends('plantilla')

@section('title', 'Nueva Área Común')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Nueva Área Común</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('areas-comunes.index') }}" class="text-decoration-none">Áreas Comunes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Crear</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('areas-comunes.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al catálogo</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-plus-circle text-primary"></i>
                <span>Formulario de Registro de Espacio</span>
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

            <form action="{{ route('areas-comunes.store') }}" method="POST">
                @csrf
                
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Información General del Espacio
                </h6>
                
                <div class="row g-3 mb-4">
                    <!-- Nombre del Área -->
                    <div class="col-12 col-md-8">
                        <label for="nombre" class="form-label fw-medium text-secondary">Nombre del Área Común <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control px-3" placeholder="Ej: Salón de Eventos, Churrasquera Norte..." value="{{ old('nombre') }}" required>
                    </div>
                    
                    <!-- Monto / Tarifa por Hora -->
                    <div class="col-12 col-md-4">
                        <label for="monto" class="form-label fw-medium text-secondary">Costo por Hora (Bs) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Bs</span>
                            <input type="number" step="0.01" name="monto" id="monto" class="form-control px-2 fw-semibold" placeholder="0.00" value="{{ old('monto') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('areas-comunes.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Guardar Área Común
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection