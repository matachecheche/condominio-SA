@extends('plantilla')

@section('title', 'Notificación: ' . $notificacion->titulo)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Detalle de Notificación</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('notificaciones.index') }}" class="text-decoration-none">Notificaciones</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($notificacion->titulo, 20) }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary shadow-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row g-4">
        <!-- Contenido principal -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center">
                    <i class="fas fa-envelope-open-text text-primary me-2"></i>
                    <h5 class="mb-0 fw-bold text-dark">{{ $notificacion->titulo }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase">Tipo</label>
                            <div><span class="badge bg-light text-dark border">{{ $notificacion->tipo }}</span></div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase">Estado</label>
                            <div>
                                <span class="badge {{ $notificacion->leida ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border px-2 py-1">
                                    {{ $notificacion->leida ? 'Leída' : 'Pendiente de lectura' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase">Fecha de envío</label>
                            <div class="text-dark">{{ \Carbon\Carbon::parse($notificacion->fecha_hora)->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-muted text-uppercase">Destinatario</label>
                        <div class="text-dark fw-medium fs-6">
                            <i class="fas fa-user-circle me-1 text-muted"></i> 
                            {{ $notificacion->residente ? $notificacion->residente->nombre.' '.$notificacion->residente->apellido : 'Todos los residentes' }}
                        </div>
                    </div>

                    <div>
                        <label class="small fw-bold text-muted text-uppercase">Contenido del mensaje</label>
                        <div class="bg-light p-4 rounded-3 border mt-1">
                            {{ $notificacion->contenido }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral: Acciones -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Gestión de lectura</h6>
                    @if(!$notificacion->leida)
                        <form action="{{ route('notificaciones.marcar-leida', $notificacion->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> Marcar como leída
                            </button>
                        </form>
                    @else
                        <button class="btn btn-outline-success w-100" disabled>
                            <i class="fas fa-check-double me-2"></i> Ya fue leída
                        </button>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('notificaciones.index') }}" class="btn btn-link text-decoration-none w-100 text-muted">Volver al listado</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection