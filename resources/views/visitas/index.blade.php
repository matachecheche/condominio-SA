@extends('plantilla')

@section('title', $titulo)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación Dinámica -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">{{ $titulo }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Control de Accesos</li>
                </ol>
            </nav>
        </div>

        <!-- Botones de Acción según Permisos ROL/MÓDULO -->
        <div class="d-flex flex-wrap gap-2">
            @canany(['gestionar visitas', 'administrar visitas'])
                <a href="{{ route('visitas.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Visita</span>
                </a>
            @endcanany

            @can('operar porteria')
                <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-shield-alt"></i>
                    <span>Panel Guardia</span>
                </a>
                <a href="{{ route('visitas.mostrar-validar-codigo') }}" class="btn btn-info text-dark px-3 fw-medium d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="fas fa-qrcode"></i>
                    <span>Validar Código</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Mensajes del Sistema -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-check-circle text-success fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <!-- Barra de Búsqueda y Filtrado -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light rounded-3">
            <form method="GET" action="{{ route('visitas.index') }}" class="m-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 px-2" placeholder="Buscar por código, visitante, documento CI o placa vehicular..." value="{{ request('search') }}">
                    <button class="btn btn-primary fw-medium px-4 shadow-sm" type="submit">Buscar</button>
                    @if(request('search'))
                        <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtro">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 70px;">ID</th>
                            <th class="py-3" style="width: 140px;">Código / Info</th>
                            <th class="py-3" style="min-width: 180px;">Visitante</th>
                            <th class="py-3" style="width: 110px;">CI</th>
                            @canany(['administrar visitas', 'operar porteria'])
                                <th class="py-3" style="min-width: 160px;">Residente</th>
                            @endcanany
                            <th class="py-3" style="max-width: 200px;">Motivo</th>
                            <th class="py-3" style="width: 130px;">Estado</th>
                            <th class="py-3" style="width: 130px;">Fecha Inicio</th>
                            <th class="py-3" style="width: 130px;">Fecha Fin</th>
                            <th class="py-3" style="width: 110px;">Placa</th>
                            <th class="py-3 text-end px-4" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($visitas as $visita)
                        @php
                            $rowClass = '';
                            if ($visita->estado == 'pendiente' && now() > $visita->fecha_fin) {
                                $rowClass = 'table-danger-subtle opacity-75';
                            } elseif ($visita->estado == 'pendiente' && now() > \Carbon\Carbon::parse($visita->fecha_fin)->subMinutes(30)) {
                                $rowClass = 'table-warning-subtle';
                            }
                        @endphp

                        <tr class="{{ $rowClass }}">
                            <td class="py-3 px-4 text-secondary fw-semibold">#{{ $visita->id }}</td>
                            <td class="py-3">
                                <span class="font-monospace fw-bold text-primary fs-6">{{ $visita->codigo }}</span>
                                
                                {{-- Alertas de tiempo relativas --}}
                                @if($visita->estado == 'pendiente')
                                    @if(now() > $visita->fecha_fin)
                                        <div class="mt-1 text-danger small fw-medium"><i class="fas fa-times-circle"></i> Expirada</div>
                                    @elseif(now() > \Carbon\Carbon::parse($visita->fecha_fin)->subMinutes(30))
                                        <div class="mt-1 text-warning small fw-medium"><i class="fas fa-hourglass-half"></i> Por expirar</div>
                                    @elseif(now() < \Carbon\Carbon::parse($visita->fecha_inicio)->subMinutes(30))
                                        <div class="mt-1 text-info small fw-medium"><i class="fas fa-clock"></i> Muy temprano</div>
                                    @elseif(now() < $visita->fecha_inicio)
                                        <div class="mt-1 text-success small fw-medium"><i class="fas fa-check-double"></i> Tolerancia OK</div>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3 fw-bold text-dark">{{ $visita->nombre_visitante }}</td>
                            <td class="py-3 text-secondary fw-medium">{{ $visita->ci_visitante }}</td>
                            
                            @canany(['administrar visitas', 'operar porteria'])
                                <td class="py-3 text-secondary fs-7 fw-medium">
                                    {{ $visita->residente ? $visita->residente->nombre_completo : '-' }}
                                </td>
                            @endcanany
                            
                            <td class="py-3 text-muted fs-7 text-wrap">{{ Str::limit($visita->motivo, 30) }}</td>
                            <td class="py-3">
                                @switch($visita->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                            <i class="fas fa-clock me-1"></i>Pendiente
                                        </span>
                                        @break
                                    @case('en_curso')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                            <i class="fas fa-running me-1"></i>En Curso
                                        </span>
                                        @break
                                    @case('finalizada')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                            <i class="fas fa-check-circle me-1"></i>Finalizada
                                        </span>
                                        @break
                                    @case('rechazada')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                            <i class="fas fa-ban me-1"></i>Rechazada
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="py-3 fs-7 text-secondary">
                                <div>{{ \Carbon\Carbon::parse($visita->fecha_inicio)->format('d/m/Y') }}</div>
                                <strong class="text-dark">{{ \Carbon\Carbon::parse($visita->fecha_inicio)->format('H:i') }}</strong>
                            </td>
                            <td class="py-3 fs-7 text-secondary">
                                <div>{{ \Carbon\Carbon::parse($visita->fecha_fin)->format('d/m/Y') }}</div>
                                <strong class="text-dark">{{ \Carbon\Carbon::parse($visita->fecha_fin)->format('H:i') }}</strong>
                            </td>
                            <td class="py-3">
                                @if($visita->placa_vehiculo)
                                    <span class="badge bg-light text-dark border border-secondary-subtle font-monospace px-2 py-1"><i class="fas fa-car me-1 text-muted"></i>{{ $visita->placa_vehiculo }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-1">
                                    {{-- Ver detalles --}}
                                    <a href="{{ route('visitas.show', $visita->id) }}" class="btn btn-outline-info btn-sm px-2.5" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($visita->estado == 'pendiente')
                                        @canany(['gestionar visitas', 'administrar visitas'])
                                            <a href="{{ route('visitas.edit', $visita) }}" class="btn btn-outline-warning btn-sm px-2.5 text-dark" title="Editar Parámetros">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcanany
                                        
                                        @can('operar porteria')
                                            @php
                                                $disableEntrada = now() < \Carbon\Carbon::parse($visita->fecha_inicio)->subMinutes(30) || now() > $visita->fecha_fin;
                                            @endphp
                                            <form action="{{ route('visitas.entrada', $visita) }}" method="POST" class="d-inline m-0">
                                                @csrf
                                                <button class="btn btn-success btn-sm px-2.5 shadow-sm" type="submit" 
                                                        onclick="return confirm('¿Registrar ingreso de {{ $visita->nombre_visitante }} ahora mismo?')"
                                                        @if($disableEntrada) disabled title="Fuera de rango de tolerancia" @else title="Marcar Entrada" @endif>
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </button>
                                            </form>
                                        @endcan

                                        {{-- Botón Eliminar — abre modal de confirmación --}}
                                        @canany(['gestionar visitas', 'administrar visitas'])
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm px-2.5"
                                                title="Anular Visita"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal{{ $visita->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endcanany
                                    @endif

                                    @if($visita->estado == 'en_curso')
                                        @can('operar porteria')
                                            <button type="button" class="btn btn-primary btn-sm px-2.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#salidaModal{{ $visita->id }}" title="Marcar Salida">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Confirmar Eliminación --}}
                        @canany(['gestionar visitas', 'administrar visitas'])
                            @if($visita->estado == 'pendiente')
                                <div class="modal fade" id="deleteModal{{ $visita->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-3">
                                            <div class="modal-header border-bottom border-light py-3">
                                                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                                                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <p class="mb-3 text-secondary">¿Estás seguro de que deseas eliminar permanentemente este registro de visita?</p>
                                                <div class="p-3 bg-light rounded-3 small">
                                                    <div class="mb-1">
                                                        <strong>Código:</strong>
                                                        <span class="font-monospace fw-bold text-primary ms-1">{{ $visita->codigo }}</span>
                                                    </div>
                                                    <div class="mb-1">
                                                        <strong>Visitante:</strong>
                                                        <span class="text-dark fw-medium ms-1">{{ $visita->nombre_visitante }}</span>
                                                    </div>
                                                    <div>
                                                        <strong>CI:</strong>
                                                        <span class="text-secondary ms-1">{{ $visita->ci_visitante }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top border-light py-2">
                                                <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('visitas.destroy', $visita) }}" method="POST" class="d-inline m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-medium">
                                                        <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endcanany

                        <!-- Modal para registrar salida -->
                        @if($visita->estado == 'en_curso')
                            @can('operar porteria')
                                <div class="modal fade" id="salidaModal{{ $visita->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-3">
                                            <form action="{{ route('visitas.salida', $visita) }}" method="POST" class="m-0">
                                                @csrf
                                                <div class="modal-header border-bottom border-light py-3">
                                                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                        <i class="fas fa-sign-out-alt text-primary"></i> Registrar Salida del Predio
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-4 text-start">
                                                    <div class="p-3 bg-light rounded-3 mb-3 small">
                                                        <div class="mb-1"><strong>Visitante:</strong> <span class="text-dark fw-medium">{{ $visita->nombre_visitante }}</span></div>
                                                        <div><strong>Documento CI:</strong> <span class="text-dark font-monospace">{{ $visita->ci_visitante }}</span></div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="form-label fw-medium text-secondary">Observaciones / Incidencias (Opcional)</label>
                                                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Salida sin novedades, se retira en vehículo alterno, etc."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top border-light py-2">
                                                    <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary px-3 fw-medium btn-sm">Confirmar Salida</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        @endif

                    @empty
                        <tr>
                            @php
                                $colspan = auth()->user()->canAny(['administrar visitas', 'operar porteria']) ? 11 : 10;
                            @endphp
                            <td colspan="{{ $colspan }}" class="text-center py-5 text-muted">
                                <i class="fas fa-id-badge fs-2 d-block mb-2 opacity-50"></i>
                                <p class="mb-2 fw-medium">No se encontraron registros de accesos o visitas.</p>
                                @canany(['gestionar visitas', 'administrar visitas'])
                                    <a href="{{ route('visitas.create') }}" class="btn btn-primary btn-sm mt-1 shadow-sm">
                                        <i class="fas fa-plus me-1"></i>Crear Primera Programación
                                    </a>
                                @endcanany
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación Corporativa -->
        @if($visitas->hasPages())
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $visitas->appends(['search' => request('search')])->links() }}
        </div>
        @endif
    </div>
</div>
@endsection