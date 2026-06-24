@extends('plantilla')

@section('title', 'Incidencia ' . $incidencia->numero_seguimiento)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Incidencia #{{ $incidencia->numero_seguimiento }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}" class="text-decoration-none">Incidencias</a></li>
                    <li class="breadcrumb-item active">{{ $incidencia->numero_seguimiento }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary shadow-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row g-4">
        <!-- Información de la Incidencia -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Información del Reporte</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6"><label class="small fw-bold text-muted text-uppercase">Título</label>
                            <div class="fw-medium text-dark">{{ $incidencia->titulo }}</div>
                        </div>
                        <div class="col-md-3"><label class="small fw-bold text-muted text-uppercase">Prioridad</label>
                            <div><span class="badge bg-light text-dark border">{{ ucfirst($incidencia->prioridad) }}</span></div>
                        </div>
                        <div class="col-md-3"><label class="small fw-bold text-muted text-uppercase">Estado</label>
                            <div><span class="badge bg-primary text-white">{{ ucfirst(str_replace('_',' ',$incidencia->estado)) }}</span></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold text-muted text-uppercase">Descripción</label>
                        <div class="bg-light p-3 rounded-3">{{ $incidencia->descripcion }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><label class="small fw-bold text-muted text-uppercase">Residente</label>
                            <div class="text-dark">{{ $incidencia->residente->nombre }} {{ $incidencia->residente->apellido }}</div>
                        </div>
                        <div class="col-md-6"><label class="small fw-bold text-muted text-uppercase">Registrada el</label>
                            <div class="text-dark">{{ $incidencia->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Respuesta Admin -->
            @if($incidencia->respuesta_admin || $incidencia->atendioPor)
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-success"><i class="fas fa-check-double me-2"></i>Seguimiento Administrativo</h5>
                </div>
                <div class="card-body p-4">
                    @if($incidencia->respuesta_admin)
                        <label class="small fw-bold text-muted text-uppercase">Respuesta</label>
                        <div class="alert alert-success bg-success-subtle border-0 mb-3">{{ $incidencia->respuesta_admin }}</div>
                    @endif
                    
                    @if($incidencia->atendioPor)
                        <div class="row">
                            <div class="col-md-6"><label class="small fw-bold text-muted text-uppercase">Atendido por</label>
                                <div class="text-dark">{{ $incidencia->atendioPor->name }}</div>
                            </div>
                            <div class="col-md-6"><label class="small fw-bold text-muted text-uppercase">Fecha de Atención</label>
                                <div class="text-dark">{{ $incidencia->fecha_atencion?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Acciones Laterales -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Acciones</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('incidencias.edit', $incidencia->id) }}" class="btn btn-warning text-white fw-bold">
                            <i class="fas fa-edit me-2"></i> Editar Registro
                        </a>
                        <a href="{{ route('incidencias.index') }}" class="btn btn-light border">
                            <i class="fas fa-list me-2"></i> Listado Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection