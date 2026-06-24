@extends('plantilla')

@section('title', 'Reclamo ' . $reclamo->numero_seguimiento)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Detalle de Reclamo</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}" class="text-decoration-none">Reclamos</a></li>
                    <li class="breadcrumb-item active">{{ $reclamo->numero_seguimiento }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reclamos.index') }}" class="btn btn-light shadow-sm px-3">Volver</a>
            <a href="{{ route('reclamos.edit', $reclamo->id) }}" class="btn btn-warning shadow-sm px-3 text-white">Editar</a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Detalles principales -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="fw-bold text-dark">{{ $reclamo->titulo }}</h4>
                        <span class="badge bg-primary rounded-pill">{{ $reclamo->numero_seguimiento }}</span>
                    </div>
                    
                    <p class="text-secondary mb-4">{{ $reclamo->contenido }}</p>
                    
                    <div class="bg-light p-3 rounded-3 border">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-2">Información del Residente</h6>
                        <p class="mb-0 fw-medium">{{ $reclamo->residente->nombre }} {{ $reclamo->residente->apellido }}</p>
                    </div>
                </div>
            </div>

            <!-- Respuesta del Admin -->
            @if($reclamo->respuesta_admin)
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-comment-dots me-2"></i>Respuesta Administrativa</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-dark">{{ $reclamo->respuesta_admin }}</p>
                        <div class="d-flex align-items-center mt-3 pt-3 border-top small text-muted">
                            <i class="fas fa-user-check me-2"></i>
                            <span>Atendido por: <strong>{{ $reclamo->atendioPor->name ?? 'Sistema' }}</strong></span>
                            <span class="mx-2">|</span>
                            <span>{{ $reclamo->fecha_atencion?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna Derecha: Sidebar informativo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">Estado del Proceso</h6>
                    @php 
                        $statusColors = ['pendiente'=>'bg-warning text-dark','en_revision'=>'bg-info text-white','resuelto'=>'bg-success text-white','rechazado'=>'bg-danger text-white'];
                    @endphp
                    <div class="p-3 mb-3 rounded-3 text-center {{ $statusColors[$reclamo->estado] ?? 'bg-secondary' }}">
                        <h5 class="mb-0 fw-bold">{{ ucfirst(str_replace('_',' ',$reclamo->estado)) }}</h5>
                    </div>
                    
                    <div class="small text-secondary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Fecha de registro:</span>
                            <span class="fw-medium text-dark">{{ $reclamo->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection