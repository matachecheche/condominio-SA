@extends('plantilla')
@section('title', 'Unidad ' . ($unidad->codigo ?? ''))
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Unidad: {{ $unidad->codigo ?? '' }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}" class="text-decoration-none">Unidades</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $unidad->codigo ?? 'Detalle' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('unidades.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-home text-primary"></i>
                <span>Ficha de Información General</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="row g-4">
                
                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-info-circle me-2"></i>Especificaciones del Inmueble
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Código Interno:</div>
                        <div class="col-8 text-dark fw-bold">{{ $unidad->codigo ?? '—' }}</div>

                        <div class="col-4 text-secondary fw-medium small">Estado Operativo:</div>
                        <div class="col-8">
                            @if(($unidad->estado ?? '') === 'activa')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-medium fs-7">
                                <i class="fas fa-circle fs-8 me-1 align-middle"></i>Activa
                            </span>
                            @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill fw-medium fs-7">
                                <i class="fas fa-circle fs-8 me-1 align-middle"></i>Inactiva
                            </span>
                            @endif
                        </div>

                        <div class="col-4 text-secondary fw-medium small">Tipo de Ocupación:</div>
                        <div class="col-8 text-dark text-capitalize">{{ strtolower($unidad->tipo_ocupacion ?? '—') }}</div>

                        <div class="col-4 text-secondary fw-medium small">Ocupantes Actuales:</div>
                        <div class="col-8 text-dark"><span class="fw-bold">{{ $unidad->personas_por_unidad ?? '0' }}</span> Habitantes</div>

                        <div class="col-4 text-secondary fw-medium small">Capacidad Máxima:</div>
                        <div class="col-8 text-muted">{{ $unidad->capacidad ?? '—' }} Personas</div>

                        <div class="col-4 text-secondary fw-medium small">Permite Mascotas:</div>
                        <div class="col-8">
                            @if($unidad->tiene_mascotas ?? false)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5">Sí</span>
                            @else
                                <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2.5">No</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-user-tie me-2"></i>Residente Responsable
                    </h6>
                    <div class="row g-2 mb-4">
                        <div class="col-4 text-secondary fw-medium small">Nombre Completo:</div>
                        <div class="col-8 text-dark fw-semibold">
                            @if($unidad->residente ?? false)
                                {{ $unidad->residente->nombre }} {{ $unidad->residente->apellido }}
                            @else
                                <span class="text-muted fst-italic fw-normal fs-7">Sin residente asignado</span>
                            @endif
                        </div>
                        
                        @if($unidad->residente ?? false)
                        <div class="col-4 text-secondary fw-medium small">Documento (CI):</div>
                        <div class="col-8 text-muted">{{ $unidad->residente->ci }}</div>
                        <div class="col-4 text-secondary fw-medium small">Email de Contacto:</div>
                        <div class="col-8 text-muted fs-7">{{ $unidad->residente->email }}</div>
                        @endif
                    </div>

                    <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                        <i class="fas fa-car me-2"></i>Vehículo Autorizado
                    </h6>
                    <div class="row g-2">
                        <div class="col-4 text-secondary fw-medium small">Límite de Vehículos:</div>
                        <div class="col-8 text-dark">{{ $unidad->vehiculos ?? '0' }} Permitido(s)</div>

                        <div class="col-4 text-secondary fw-medium small">Placa de Control:</div>
                        <div class="col-8 text-dark fw-semibold text-uppercase">{{ $unidad->placa ?? '—' }}</div>

                        <div class="col-4 text-secondary fw-medium small">Marca / Modelo:</div>
                        <div class="col-8 text-muted text-capitalize">{{ strtolower($unidad->marca ?? '—') }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top border-light">
                <a href="{{ route('unidades.index') }}" class="btn btn-light border px-4">Volver al Listado</a>
                @can('editar unidades')
                <a href="{{ route('unidades.edit', $unidad->id ?? request()->segment(2)) }}" class="btn btn-warning px-4 shadow-sm fw-medium text-dark">
                    <i class="fas fa-edit me-1.5"></i>Editar Unidad
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection