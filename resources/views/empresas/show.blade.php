@extends('plantilla')

@section('title', 'Detalle de Empresa')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Detalle de Empresa</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('empresas.index') }}" class="text-decoration-none">Empresas</a></li>
                    <li class="breadcrumb-item active">Detalle: {{ $empresa->nombre }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary shadow-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row g-4">
        <!-- Información Principal -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h5 class="mb-0 fw-bold text-dark">Información de la Empresa</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted text-uppercase">Nombre</label>
                            <div class="fs-5 fw-medium text-dark">{{ $empresa->nombre }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted text-uppercase">Servicio</label>
                            <div class="fs-5 fw-medium text-dark">{{ $empresa->servicio }}</div>
                        </div>
                    </div>

                    <hr class="text-light">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted text-uppercase"><i class="fas fa-phone-alt me-1"></i> Teléfono</label>
                            <div class="text-dark">{{ $empresa->telefono ?: 'No registrado' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted text-uppercase"><i class="fas fa-envelope me-1"></i> Correo</label>
                            <div class="text-dark">{{ $empresa->correo ?: 'No registrado' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase"><i class="fas fa-map-marker-alt me-1"></i> Dirección</label>
                        <div class="text-dark bg-light p-3 rounded-3">{{ $empresa->direccion ?: 'Sin dirección registrada' }}</div>
                    </div>

                    <div>
                        <label class="small fw-bold text-muted text-uppercase"><i class="fas fa-sticky-note me-1"></i> Observaciones</label>
                        <div class="text-dark p-3 border rounded-3">{{ $empresa->observacion ?: 'Sin observaciones' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral: Acciones rápidas -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Acciones Disponibles</h6>
                    <div class="d-grid gap-2">
                        @can('editar empresas')
                            <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-warning text-white fw-bold">
                                <i class="fas fa-edit me-2"></i> Editar Datos
                            </a>
                        @endcan
                        <a href="{{ route('empresas.index') }}" class="btn btn-light border">
                            <i class="fas fa-list me-2"></i> Ver Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection