@extends('plantilla')

@section('title', 'Propiedad ' . ($propiedad->codigo ?? ''))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Propiedad: {{ $propiedad->codigo ?? '' }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('propiedades.index') }}" class="text-decoration-none">Propiedades</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $propiedad->codigo ?? 'Detalle' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Contenedor Principal de Detalles -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-building text-primary"></i>
                <span>Ficha de Información de la Propiedad</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row g-4">
                
                <!-- SECCIÓN 1: ESPECIFICACIONES DE LA PROPIEDAD -->
                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-info-circle me-2"></i>Ficha Técnica del Inmueble
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Código Identificador:</div>
                        <div class="col-8 text-dark fw-bold">{{ $propiedad->codigo ?? '—' }}</div>

                        <div class="col-4 text-secondary fw-medium small">Tipo de Inmueble:</div>
                        <div class="col-8 text-dark text-capitalize">{{ strtolower($propiedad->tipo ?? '—') }}</div>

                        <div class="col-4 text-secondary fw-medium small">Estado del Registro:</div>
                        <div class="col-8">
                            @php
                                $estado = strtolower($propiedad->estado ?? 'disponible');
                                $badgeClass = match($estado) {
                                    'disponible' => 'bg-success-subtle text-success border border-success-subtle',
                                    'ocupada' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'activa' => 'bg-info-subtle text-info border border-info-subtle',
                                    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill fw-medium fs-7 text-capitalize">
                                <i class="fas fa-circle fs-8 me-1 align-middle"></i>{{ $estado }}
                            </span>
                        </div>

                        <div class="col-4 text-secondary fw-medium small">Ubicación física:</div>
                        <div class="col-8 text-muted fs-7 lh-base">{{ $propiedad->ubicacion ?? '—' }}</div>
                    </div>
                </div>

                <!-- SECCIÓN 2: RESIDENTE ASIGNADO -->
                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-user-tie me-2"></i>Responsable Legal / Inquilino
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Nombre del Titular:</div>
                        <div class="col-8 text-dark fw-semibold">
                            @if($propiedad->residente)
                                {{ $propiedad->residente->nombre_completo }}
                            @else
                                <span class="text-muted fst-italic fw-normal fs-7">Sin residente asignado</span>
                            @endif
                        </div>
                        
                        @if($propiedad->residente)
                            <div class="col-4 text-secondary fw-medium small">Identificación / CI:</div>
                            <div class="col-8 text-muted">{{ $propiedad->residente->ci ?? '—' }}</div>
                            <div class="col-4 text-secondary fw-medium small">Correo Electrónico:</div>
                            <div class="col-8 text-muted fs-7">{{ $propiedad->residente->email ?? '—' }}</div>
                        @endif
                    </div>
                </div>

                <!-- SECCIÓN 3: NOTAS O DESCRIPCIONES -->
                @if($propiedad->descripcion)
                <div class="col-12">
                    <h6 class="text-primary fw-semibold mb-2 mt-2">
                        <i class="fas fa-comment-alt me-2"></i>Notas y Descripción Adicional
                    </h6>
                    <div class="bg-light rounded-3 p-3 border border-light-subtle text-secondary small lh-base">
                        {{ $propiedad->descripcion }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Botonera de Acciones Inferior -->
            <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top border-light">
                <a href="{{ route('propiedades.index') }}" class="btn btn-light border px-4">Volver al Listado</a>
                <a href="{{ route('propiedades.edit', $propiedad->id ?? request()->segment(2)) }}" class="btn btn-warning px-4 shadow-sm fw-medium text-dark">
                    <i class="fas fa-edit me-1.5"></i>Editar Propiedad
                </a>
            </div>
        </div>
    </div>
</div>
@endsection