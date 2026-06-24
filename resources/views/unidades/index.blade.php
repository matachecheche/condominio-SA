@extends('plantilla')

@section('title', 'Unidades Habitacionales')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
@if(session('success'))
<script>
    Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2000,timerProgressBar:true})
        .fire({icon:'success',title:"{{ session('success') }}"});
</script>
@endif

<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Unidades Habitacionales</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Unidades</li>
                </ol>
            </nav>
        </div>

        @can('crear unidades')
        <div>
            <a href="{{ route('unidades.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Nueva Unidad</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Filtro de Búsqueda -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('unidades.index') }}" class="m-0">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-light text-muted border-end-0 px-3">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 px-2" 
                                   placeholder="Buscar por código o residente..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary px-3 fw-medium" type="submit">Buscar</button>
                        <a href="{{ route('unidades.index') }}" class="btn btn-light border px-3 text-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor de la Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-home text-primary"></i>
                <span>CU13 · Vincular Residente con Unidad Habitacional</span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 70px;">#</th>
                            <th class="py-3">Código</th>
                            <th class="py-3">Residente Asignado</th>
                            <th class="py-3">Tipo Ocupación</th>
                            <th class="py-3 text-center">Personas</th>
                            <th class="py-3 text-center">Vehículos</th>
                            <th class="py-3 text-center">Mascotas</th>
                            <th class="py-3" style="width: 130px;">Estado</th>
                            <th class="py-3 text-end px-4" style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unidades as $u)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $u->id }}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border border-secondary-subtle px-2.5 py-1.5 rounded fw-bold fs-7">{{ $u->codigo }}</span></td>
                            <td class="py-3 fw-semibold text-dark">
                                @if($u->residente)
                                    {{ $u->residente->nombre }} {{ $u->residente->apellido }}
                                @else
                                    <span class="text-muted fst-italic fs-7 fw-normal">Sin asignar</span>
                                @endif
                            </td>
                            <td class="py-3 text-muted fs-7 text-capitalize">{{ strtolower($u->tipo_ocupacion ?? 'N/A') }}</td>
                            <td class="py-3 text-center fw-medium text-dark">{{ $u->personas_por_unidad }}</td>
                            <td class="py-3 text-center fw-medium text-dark">{{ $u->vehiculos }}</td>
                            <td class="py-3 text-center">
                                @if($u->tiene_mascotas)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5">Sí</span>
                                @else
                                    <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2.5">No</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($u->estado === 'activa')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fw-medium fs-7">
                                    <i class="fas fa-circle fs-8 me-1 align-middle"></i>Activa
                                </span>
                                @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1.5 rounded-pill fw-medium fs-7">
                                    <i class="fas fa-circle fs-8 me-1 align-middle"></i>Inactiva
                                </span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    @can('ver unidades')
                                    <a href="{{ route('unidades.show', $u->id) }}" class="btn btn-outline-info btn-sm px-2.5 d-inline-flex align-items-center gap-1" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                        <span class="d-none d-sm-inline">Ver</span>
                                    </a>
                                    @endcan
                                    
                                    @can('editar unidades')
                                    <a href="{{ route('unidades.edit', $u->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1 text-dark" title="Editar unidad">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    @endcan
                                    
                                    @can('eliminar unidades')
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1"
                                            data-bs-toggle="modal" data-bs-target="#delModal-{{ $u->id }}" title="Eliminar unidad">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de Eliminación Estilizado -->
                        @can('eliminar unidades')
                        <div class="modal fade" id="delModal-{{ $u->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-3">
                                    <div class="modal-header border-bottom border-light py-3">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <p class="mb-1 text-dark">¿Está seguro de que desea eliminar permanentemente la unidad habitacional?</p>
                                        <span class="text-muted small">Código de Unidad: <strong class="text-danger">{{ $u->codigo }}</strong></span>
                                    </div>
                                    <div class="modal-footer border-top border-light py-2">
                                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('unidades.destroy', $u->id) }}" method="POST" class="m-0">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 fw-medium">Eliminar Unidad</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="fas fa-home fa-3x opacity-50 mb-3"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-1">No se encontraron unidades</h6>
                                <p class="text-muted small mb-0">No hay registros guardados actualmente o no coinciden con los términos de búsqueda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación Estilizada -->
    <div class="d-flex justify-content-center mt-4">
        {{ $unidades->appends(request()->query())->links() }}
    </div>
</div>
@endsection