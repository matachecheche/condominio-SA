@extends('plantilla')

@section('title', 'Detalle de Cuota #' . ($cuota->id ?? ''))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Detalle de Cuota: #{{ $cuota->id }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cuotas.index') }}" class="text-decoration-none">Cuotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('cuotas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Contenedor Principal de Detalles -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Ficha de Información de Facturación</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row g-4">
                
                <!-- SECCIÓN 1: ESPECIFICACIONES DE LA OBLIGACIÓN -->
                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-info-circle me-2"></i>Información del Cargo
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Título:</div>
                        <div class="col-8 text-dark fw-bold">{{ $cuota->titulo }}</div>

                        <div class="col-4 text-secondary fw-medium small">Descripción:</div>
                        <div class="col-8 text-muted fs-7 lh-base">{{ $cuota->descripcion }}</div>

                        <div class="col-4 text-secondary fw-medium small">Monto Total:</div>
                        <div class="col-8 text-dark fw-bold fs-5">${{ number_format($cuota->monto, 2) }}</div>

                        <div class="col-4 text-secondary fw-medium small">Estado Comercial:</div>
                        <div class="col-8">
                            @php
                                $estado = strtolower($cuota->estado ?? 'pendiente');
                                $badgeClass = match($estado) {
                                    'pagado' => 'bg-success-subtle text-success border border-success-subtle',
                                    'pendiente' => 'bg-warning-subtle text-warning border border-warning-subtle text-dark',
                                    'activa' => 'bg-info-subtle text-info border border-info-subtle',
                                    'cancelada' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2.5 py-1 rounded-pill fw-medium fs-7 text-capitalize">
                                <i class="fas fa-circle fs-8 me-1 align-middle"></i>{{ $estado }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DESTINATARIO Y VENCIMIENTOS -->
                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-calendar-alt me-2"></i>Asignación y Fechas Clave
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Residente Asignado:</div>
                        <div class="col-8 text-dark fw-semibold">
                            {{ $cuota->residente->nombre_completo ?? 'N/A' }}
                        </div>
                        
                        <div class="col-4 text-secondary fw-medium small">Fecha de Emisión:</div>
                        <div class="col-8 text-muted">
                            {{ $cuota->fecha_emision ? \Carbon\Carbon::parse($cuota->fecha_emision)->translatedFormat('d \d\e F, Y') : '—' }}
                        </div>

                        <div class="col-4 text-secondary fw-medium small">Vencimiento:</div>
                        <div class="col-8 text-danger fw-medium">
                            {{ $cuota->fecha_vencimiento ? \Carbon\Carbon::parse($cuota->fecha_vencimiento)->translatedFormat('d \d\e F, Y') : '—' }}
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: OBSERVACIONES -->
                <div class="col-12">
                    <h6 class="text-primary fw-semibold mb-2 mt-2">
                        <i class="fas fa-comment-alt me-2"></i>Observaciones del Operador
                    </h6>
                    <div class="bg-light rounded-3 p-3 border border-light-subtle text-secondary small lh-base">
                        {{ $cuota->observacion ?? 'Sin observaciones adicionales registradas.' }}
                    </div>
                </div>
            </div>

            <!-- Botonera de Acciones Inferior -->
            <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top border-light">
                <a href="{{ route('cuotas.index') }}" class="btn btn-light border px-4">Volver al Listado</a>
                <a href="{{ route('cuotas.edit', $cuota->id) }}" class="btn btn-warning px-4 shadow-sm fw-medium text-dark">
                    <i class="fas fa-edit me-1.5"></i>Editar Datos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection