@extends('plantilla')

@section('title', 'Evento: ' . $evento->nombre)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Detalle del Evento</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('eventos.index') }}" class="text-decoration-none">Eventos</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($evento->nombre, 20) }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('eventos.index') }}" class="btn btn-light shadow-sm px-3">Volver</a>
            <a href="{{ route('eventos.edit', $evento->id) }}" class="btn btn-warning shadow-sm px-3 text-white">Editar</a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-dark mb-3">{{ $evento->nombre }}</h3>
                    <p class="text-secondary mb-4">{{ $evento->descripcion ?? 'Sin descripción disponible.' }}</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-uppercase text-secondary fw-bold small">Lugar</label>
                            <p class="fw-medium mb-0"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $evento->lugar }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-uppercase text-secondary fw-bold small">Fecha y Hora</label>
                            <p class="fw-medium mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>{{ $evento->fecha_hora->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral (Sidebar) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <h6 class="text-uppercase text-secondary fw-bold small mb-3">Resumen Administrativo</h6>
                    
                    <!-- Estado -->
                    @php 
                        $colors = ['programado'=>'bg-primary text-white','en_curso'=>'bg-warning text-dark','finalizado'=>'bg-success text-white','cancelado'=>'bg-danger text-white'];
                    @endphp
                    <div class="p-3 mb-3 rounded-3 text-center {{ $colors[$evento->estado] ?? 'bg-secondary' }}">
                        <span class="fw-bold">{{ ucfirst(str_replace('_',' ',$evento->estado)) }}</span>
                    </div>

                    <div class="small text-secondary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Cupo máximo:</span>
                            <span class="fw-bold text-dark">{{ $evento->cupo_maximo ?? 'Sin límite' }}</span>
                        </div>
                        @if($evento->organizador)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Organizador:</span>
                            <span class="fw-medium text-dark">{{ $evento->organizador->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection