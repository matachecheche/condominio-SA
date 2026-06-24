@extends('plantilla')

@section('title', 'Detalle de la Visita')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación Superior -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">
                <i class="fas fa-eye text-primary me-1"></i> Detalle y Trazabilidad de Visita
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('visitas.index') }}" class="text-decoration-none">Visitas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trazabilidad</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-sm-end">
            <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> <span>Volver al Listado</span>
            </a>
            @can('operar porteria')
                <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-info btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                    <i class="fas fa-shield-alt"></i> <span>Panel Guardia</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-check-circle text-success fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Card Principal de Información -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-info-circle text-primary fs-5"></i>
                <span>Resumen de Registro y Permiso de Ingreso</span>
                <span class="badge bg-primary font-monospace px-2.5 py-1 fs-8">{{ $visita->codigo }}</span>
            </div>
            <div>
                @switch($visita->estado)
                    @case('pendiente')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                            <i class="fas fa-clock me-1"></i> Pendiente de Arribo
                        </span>
                        @break
                    @case('en_curso')
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                            <i class="fas fa-play me-1"></i> En Curso (Dentro del Predio)
                        </span>
                        @break
                    @case('finalizada')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                            <i class="fas fa-check me-1"></i> Finalizada / Salida Registrada
                        </span>
                        @break
                    @case('rechazada')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fw-bold fs-7 rounded-pill">
                            <i class="fas fa-times me-1"></i> Acceso Rechazado
                        </span>
                        @break
                @endswitch
            </div>
        </div>
        
        <div class="card-body p-4 p-md-4">
            <div class="row g-4">
                <!-- Columna: Datos del Visitante -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-none bg-light-subtle rounded-3 h-100 border-start border-primary border-3">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-user me-2"></i>Identificación del Visitante
                            </h6>
                        </div>
                        <div class="card-body px-4 py-3 text-secondary small">
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-1">
                                    <i class="fas fa-hashtag text-primary me-1"></i> Token de Seguridad / Código:
                                </label>
                                <div class="fs-4 text-primary font-monospace fw-bold tracking-wider">{{ $visita->codigo }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-id-card text-info me-1"></i> Nombre Completo:
                                </label>
                                <div class="fs-6 text-dark fw-medium">{{ $visita->nombre_visitante }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-id-badge text-info me-1"></i> Documento de Identidad (CI):
                                </label>
                                <div class="fs-6 font-monospace text-dark">{{ $visita->ci_visitante }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-comment text-warning me-1"></i> Motivo Declarado:
                                </label>
                                <div class="fs-6 text-dark">{{ $visita->motivo }}</div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-car text-secondary me-1"></i> Vehículo Autorizado:
                                </label>
                                <div>
                                    @if($visita->placa_vehiculo)
                                        <span class="badge bg-white text-dark border font-monospace px-2.5 py-1 border-secondary-subtle">
                                            {{ $visita->placa_vehiculo }}
                                        </span>
                                    @else
                                        <em class="text-muted">Sin registro de vehículo asociado</em>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna: Datos de la Visita y Programación -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-none bg-light-subtle rounded-3 h-100 border-start border-success border-3">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                            <h6 class="mb-0 fw-bold text-success">
                                <i class="fas fa-calendar-alt me-2"></i>Detalles de Agenda y Destino
                            </h6>
                        </div>
                        <div class="card-body px-4 py-3 text-secondary small">
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-home text-success me-1"></i> Residente Anfitrión:
                                </label>
                                <div class="fs-6 text-dark fw-medium">
                                    {{ $visita->residente ? $visita->residente->nombre_completo : 'Sin asignar' }}
                                    @if($visita->residente && $visita->residente->unidad)
                                        <small class="text-muted fw-normal ms-1">| Inmueble: {{ $visita->residente->unidad }}</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-clock text-primary me-1"></i> Vigencia / Horario Programado:
                                </label>
                                <div class="fs-6 font-monospace text-dark">
                                    {{ $visita->fecha_inicio->format('d/m/Y H:i') }} <br class="d-sm-none">
                                    <span class="text-muted mx-1 d-none d-sm-inline">a</span> 
                                    {{ $visita->fecha_fin->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-info-circle text-info me-1"></i> Estado del Permiso:
                                </label>
                                <div class="fs-6 text-dark text-capitalize">{{ str_replace('_', ' ', $visita->estado) }}</div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label text-muted d-block fw-semibold mb-0.5">
                                    <i class="fas fa-calendar-plus text-secondary me-1"></i> Fecha de Creación del Token:
                                </label>
                                <div class="fs-7 font-monospace text-muted">{{ $visita->created_at->format('d/m/Y H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Trazabilidad de Entradas y Salidas -->
            @if($visita->hora_entrada || $visita->hora_salida)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border border-info-subtle rounded-3 shadow-none bg-white">
                            <div class="card-header bg-info-subtle border-0 py-2.5">
                                <h6 class="mb-0 fw-bold text-info fs-7">
                                    <i class="fas fa-history me-1.5"></i> Trazabilidad de Accesos - Control en Portería
                                </h6>
                            </div>
                            <div class="card-body p-3 small">
                                <div class="row g-3">
                                    @if($visita->hora_entrada)
                                        <div class="col-12 col-md-6">
                                            <div class="border-end-md pe-md-3">
                                                <div class="text-success fw-bold border-bottom border-light pb-1 mb-2 d-flex align-items-center gap-1.5">
                                                    <i class="fas fa-sign-in-alt"></i> Registro de Entrada Validado
                                                </div>
                                                <p class="mb-1 text-secondary">
                                                    <strong class="text-dark">Marca de Fecha y Hora:</strong> 
                                                    <span class="font-monospace">{{ $visita->hora_entrada->format('d/m/Y H:i:s') }}</span>
                                                </p>
                                                <p class="mb-0 text-secondary">
                                                    <strong class="text-dark">Registrado por Terminal:</strong> 
                                                    {{ $visita->userEntrada ? $visita->userEntrada->name : 'Autorización Sincronizada / Sistema' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($visita->hora_salida)
                                        <div class="col-12 col-md-6">
                                            <div class="ps-md-3 border-start-md">
                                                <div class="text-primary fw-bold border-bottom border-light pb-1 mb-2 d-flex align-items-center gap-1.5">
                                                    <i class="fas fa-sign-out-alt"></i> Registro de Salida Procesado
                                                </div>
                                                <p class="mb-1 text-secondary">
                                                    <strong class="text-dark">Marca de Fecha y Hora:</strong> 
                                                    <span class="font-monospace">{{ $visita->hora_salida->format('d/m/Y H:i:s') }}</span>
                                                </p>
                                                <p class="mb-0 text-secondary">
                                                    <strong class="text-dark">Registrado por Terminal:</strong> 
                                                    {{ $visita->userSalida ? $visita->userSalida->name : 'Autorización Sincronizada / Sistema' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($visita->observaciones)
                                    <hr class="my-3 border-light">
                                    <div class="bg-light-subtle p-2.5 rounded-2 border border-secondary-subtle/50">
                                        <h6 class="text-warning fw-bold fs-8 mb-1 d-flex align-items-center gap-1">
                                            <i class="fas fa-sticky-note"></i> Observaciones de Guardia:
                                        </h6>
                                        <p class="mb-0 text-secondary fs-7 fst-italic">{{ $visita->observaciones }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel de Gestión de Acciones -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h6 class="mb-0 fw-bold text-secondary d-flex align-items-center gap-2">
                <i class="fas fa-cogs text-secondary"></i> Interfaz de Control - Acciones Disponibles
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="d-flex gap-2 flex-wrap mb-3">
                @if($visita->estado == 'pendiente')
                    @can('operar porteria')
                        <form action="{{ route('visitas.entrada', $visita) }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button class="btn btn-success shadow-sm fw-medium btn-sm px-3"
                                    onclick="return confirm('¿Registrar el ingreso del visitante {{ $visita->nombre_visitante }}?')">
                                <i class="fas fa-sign-in-alt me-1"></i> Registrar Entrada
                            </button>
                        </form>
                    @endcan

                    @canany(['gestionar visitas', 'administrar visitas'])
                        <a href="{{ route('visitas.edit', $visita) }}" class="btn btn-warning shadow-sm fw-medium btn-sm px-3 text-dark">
                            <i class="fas fa-edit me-1"></i> Editar Visita
                        </a>
                    @endcanany
                @endif

                @if($visita->estado == 'en_curso')
                    @can('operar porteria')
                        <button type="button" class="btn btn-primary shadow-sm fw-medium btn-sm px-3" data-bs-toggle="modal" data-bs-target="#salidaModal">
                            <i class="fas fa-sign-out-alt me-1"></i> Registrar Salida
                        </button>
                    @endcan
                @endif

                {{-- Botoneras Comunes --}}
                <a href="{{ route('visitas.index') }}" class="btn btn-light border btn-sm px-3">
                    <i class="fas fa-arrow-left me-1"></i> Ir al Listado
                </a>

                @can('operar porteria')
                    <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-info btn-sm px-3">
                        <i class="fas fa-shield-alt me-1"></i> Terminal Guardia
                    </a>
                @endcan

                @can('administrar visitas')
                    <button class="btn btn-outline-dark btn-sm px-3" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimir Comprobante
                    </button>
                @endcan
            </div>

            <!-- Bloques Informativos Contextuales de Permisos -->
            @can('gestionar visitas')
                @if($visita->estado == 'pendiente' && $visita->residente_id == auth()->user()->residente?->id)
                    <div class="alert alert-info border-0 rounded-3 small mb-0 d-flex align-items-start gap-2 shadow-sm">
                        <i class="fas fa-info-circle text-primary mt-0.5"></i>
                        <div><strong>Gestión de Residente:</strong> Puedes editar este registro mientras no se encuentre marcado como activo (En curso). Asegúrate de compartir el token <strong>{{ $visita->codigo }}</strong> en caso de requerir el arribo exitoso.</div>
                    </div>
                @endif
            @endcan

            @can('operar porteria')
                @if($visita->estado == 'pendiente')
                    <div class="alert alert-warning border-0 rounded-3 small mb-0 d-flex align-items-start gap-2 shadow-sm">
                        <i class="fas fa-clock text-warning mt-0.5"></i>
                        <div><strong>Atención Portón:</strong> Acceso programado en estatus pendiente. El sistema validará ingreso dentro del rango de tolerancia establecido.</div>
                    </div>
                @elseif($visita->estado == 'en_curso')
                    <div class="alert alert-info border-0 rounded-3 small mb-0 d-flex align-items-start gap-2 shadow-sm">
                        <i class="fas fa-user-clock text-info mt-0.5"></i>
                        <div><strong>Visita dentro del perímetro:</strong> Recuerda ejecutar la marcación de salida en el momento en el que el visitante proceda a retirarse de la urbanización.</div>
                    </div>
                @endif
            @endcan
        </div>
    </div>

    <!-- Modal de Salida (Renderizado únicamente al interior del predio) -->
    @if($visita->estado == 'en_curso')
        @can('operar porteria')
            <div class="modal fade" id="salidaModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-3">
                        <div class="modal-header bg-primary text-white py-3 border-0">
                            <h5 class="modal-title fw-bold fs-5">
                                <i class="fas fa-sign-out-alt me-1.5"></i> Registrar Egreso / Salida
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('visitas.salida', $visita) }}" method="POST" class="m-0">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="alert alert-info border-0 rounded-3 mb-4 shadow-sm">
                                    <div class="row g-2 small">
                                        <div class="col-12 col-md-6">
                                            <p class="mb-0">
                                                <strong><i class="fas fa-user me-1"></i> Visitante:</strong> 
                                                <span class="fw-medium">{{ $visita->nombre_visitante }}</span>
                                            </p>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <p class="mb-0">
                                                <strong><i class="fas fa-id-card me-1"></i> CI:</strong> 
                                                <span class="font-monospace">{{ $visita->ci_visitante }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-0">
                                    <label for="observaciones" class="form-label fw-semibold text-secondary small">
                                        <i class="fas fa-sticky-note me-1"></i> Nota de Observación (Opcional)
                                    </label>
                                    <textarea name="observaciones" id="observaciones" class="form-control px-3 py-2 shadow-sm" rows="3" 
                                              placeholder="Ej: Retirado sin novedades ni desperfectos..."></textarea>
                                    <div class="form-text text-muted small mt-1">Registra cualquier evento importante, eventualidad o reporte extraordinario en la salida del predio.</div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light-subtle border-0 py-3 px-4">
                                <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary px-3 shadow-sm fw-medium btn-sm">
                                    <i class="fas fa-sign-out-alt me-1"></i> Confirmar Salida
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif
</div>

<style>
.tracking-wider { letter-spacing: 0.05rem; }
.fs-8 { font-size: 0.75rem; }
</style>
@endsection