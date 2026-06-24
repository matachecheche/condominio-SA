@extends('plantilla')

@section('title', 'Comunicado: ' . $comunicado->titulo)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Detalle de Comunicado</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('comunicados.index') }}" class="text-decoration-none">Comunicados</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($comunicado->titulo, 25) }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('comunicados.index') }}" class="btn btn-outline-secondary shadow-sm px-3">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Contenido -->
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center">
                    <i class="fas fa-bullhorn text-primary me-2"></i>
                    <h5 class="mb-0 fw-bold text-dark">{{ $comunicado->titulo }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2 mb-4">
                        <span class="badge {{ $comunicado->tipo === 'Urgente' ? 'bg-danger' : 'bg-info text-white' }} px-3 py-2 rounded-pill">
                            {{ $comunicado->tipo }}
                        </span>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            <i class="fas fa-calendar-alt me-1"></i> 
                            {{ $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('d/m/Y H:i') : 'Publicado inmediatamente' }}
                        </span>
                    </div>

                    <div class="text-secondary fs-6 lh-lg">
                        {!! nl2br(e($comunicado->contenido)) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral: Acciones -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Opciones</h6>
                    <div class="d-grid gap-2">
                        @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
                            <a href="{{ route('comunicados.edit', $comunicado->id) }}" class="btn btn-warning text-white fw-bold">
                                <i class="fas fa-edit me-2"></i> Editar Comunicado
                            </a>
                        @endif
                        <a href="{{ route('comunicados.index') }}" class="btn btn-light border">
                            <i class="fas fa-list me-2"></i> Listado Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection