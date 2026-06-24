@extends('plantilla')

@section('title', 'Lista de Mantenimientos')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Control de Mantenimientos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mantenimientos</li>
                </ol>
            </nav>
        </div>

        <div>
            <a href="{{ route('mantenimientos.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Mantenimiento</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-check-circle text-success fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    <!-- Panel de Búsqueda y Filtrado Avanzado -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light rounded-3">
            <form method="GET" action="{{ route('mantenimientos.index') }}" class="m-0">
                {{-- Mantener ordenamiento al buscar --}}
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif

                <div class="row g-2">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 px-2" placeholder="Buscar registros..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-sm-7 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted small fw-medium">Filtrar por</span>
                            <select name="filter" class="form-select">
                                <option value="descripcion" {{ request('filter') == 'descripcion' ? 'selected' : '' }}>Descripción</option>
                                <option value="usuario" {{ request('filter') == 'usuario' ? 'selected' : '' }}>Gestor (Usuario)</option>
                                <option value="empresa" {{ request('filter') == 'empresa' ? 'selected' : '' }}>Empresa Externa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-5 col-md-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-medium shadow-sm" type="submit">Aplicar Filtro</button>
                            @if(request('search') || request('filter'))
                                <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor de la Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            @php
                                $currentSort = request('sort');
                                $direction = request('direction') === 'asc' ? 'desc' : 'asc';
                                $sortIcon = function($field) use ($currentSort) {
                                    if ($currentSort !== $field) return '<i class="fas fa-sort text-muted opacity-50 ms-1"></i>';
                                    return request('direction') === 'asc' 
                                        ? '<i class="fas fa-sort-amount-up text-primary ms-1"></i>' 
                                        : '<i class="fas fa-sort-amount-down text-primary ms-1"></i>';
                                };
                            @endphp

                            <th class="py-3 px-4" style="width: 100px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'id', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    ID {!! $sortIcon('id') !!}
                                </a>
                            </th>
                            <th class="py-3" style="min-width: 220px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'descripcion', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Descripción {!! $sortIcon('descripcion') !!}
                                </a>
                            </th>
                            <th class="py-3" style="width: 130px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'monto', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Monto {!! $sortIcon('monto') !!}
                                </a>
                            </th>
                            <th class="py-3" style="width: 160px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'fecha_hora', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Fecha {!! $sortIcon('fecha_hora') !!}
                                </a>
                            </th>
                            <th class="py-3" style="width: 160px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'usuario', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Usuario {!! $sortIcon('usuario') !!}
                                </a>
                            </th>
                            <th class="py-3" style="width: 180px;">
                                <a href="{{ route('mantenimientos.index', array_merge(request()->all(), ['sort' => 'empresa', 'direction' => $direction])) }}" class="text-decoration-none text-secondary d-flex align-items-center">
                                    Empresa {!! $sortIcon('empresa') !!}
                                </a>
                            </th>
                            <th class="py-3" style="width: 120px;">Estado</th>
                            <th class="py-3 text-end px-4" style="width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mantenimientos as $m)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $m->id }}</td>
                            <td class="py-3 fw-bold text-dark">{{ $m->descripcion }}</td>
                            <td class="py-3 fw-semibold text-dark">Bs {{ number_format($m->monto, 2) }}</td>
                            <td class="py-3 text-muted small">
                                <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 text-secondary fs-7">{{ $m->usuario->name ?? '-' }}</td>
                            <td class="py-3 text-secondary fs-7 fw-medium">{{ $m->empresa?->nombre ?? '-' }}</td>
                            <td class="py-3">
                                @if($m->estado == 1)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                        <i class="fas fa-check-circle me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-medium fs-7">
                                        <i class="fas fa-minus-circle me-1"></i>Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <a href="{{ route('mantenimientos.edit', $m->id) }}" class="btn btn-outline-warning btn-sm px-2.5 text-dark d-inline-flex align-items-center gap-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>

                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalEliminar-{{ $m->id }}" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de Confirmación para Eliminación Coherente -->
                        <div class="modal fade" id="modalEliminar-{{ $m->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-3">
                                    <div class="modal-header border-bottom border-light py-3">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4 text-start">
                                        <p class="mb-1 text-dark">¿Está seguro de que desea eliminar permanentemente esta orden de mantenimiento?</p>
                                        <span class="text-muted small">ID Registro: <strong class="text-danger">#{{ $m->id }}</strong></span>
                                    </div>
                                    <div class="modal-footer border-top border-light py-2">
                                        <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('mantenimientos.destroy', $m->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 fw-medium btn-sm">Eliminar Registro</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-tools fs-2 d-block mb-2 opacity-50"></i>
                                No se encontraron registros de mantenimientos que coincidan con los criterios.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginación de Registros -->
        @if($mantenimientos->hasPages())
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $mantenimientos->appends(request()->all())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection