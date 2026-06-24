@extends('plantilla')

@section('title', 'Gestión de Propiedades')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Gestión de Propiedades</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Propiedades</li>
                </ol>
            </nav>
        </div>

        <div>
            <a href="{{ route('propiedades.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Nueva Propiedad</span>
            </a>
        </div>
    </div>

    <!-- Notificaciones del Sistema -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-check-circle text-success fs-5"></i>
            <div>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Contenedor Principal de la Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-building text-primary"></i>
                <span>Listado General de Inmuebles</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 70px;">ID</th>
                            <th class="py-3">Código</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Ubicación</th>
                            <th class="py-3">Residente Asignado</th>
                            <th class="py-3" style="width: 130px;">Estado</th>
                            <th class="py-3 text-end px-4" style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($propiedades as $propiedad)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $propiedad->id }}</td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border border-secondary-subtle px-2.5 py-1.5 rounded fw-bold fs-7">
                                    {{ $propiedad->codigo }}
                                </span>
                            </td>
                            <td class="py-3 text-muted fs-7 text-capitalize">{{ strtolower($propiedad->tipo ?? '—') }}</td>
                            <td class="py-3 text-dark text-truncate" style="max-width: 200px;">{{ $propiedad->ubicacion }}</td>
                            <td class="py-3 fw-semibold text-dark">
                                @if($propiedad->residente)
                                    {{ $propiedad->residente->nombre_completo }}
                                @else
                                    <span class="text-muted fst-italic fs-7 fw-normal">Sin asignar</span>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1.5 rounded-pill fw-medium fs-7 text-capitalize">
                                    {{ strtolower($propiedad->estado) }}
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <a href="{{ route('propiedades.show', $propiedad->id) }}" class="btn btn-outline-info btn-sm px-2.5 d-inline-flex align-items-center gap-1" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                        <span class="d-none d-sm-inline">Ver</span>
                                    </a>
                                    
                                    <a href="{{ route('propiedades.edit', $propiedad->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1 text-dark" title="Editar propiedad">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1"
                                            data-bs-toggle="modal" data-bs-target="#delPropModal-{{ $propiedad->id }}" title="Eliminar propiedad">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de Eliminación Estilizado individual -->
                        <div class="modal fade" id="delPropModal-{{ $propiedad->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-3">
                                    <div class="modal-header border-bottom border-light py-3">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4 text-start">
                                        <p class="mb-1 text-dark">¿Está seguro de que desea eliminar permanentemente este registro de propiedad?</p>
                                        <span class="text-muted small">Código de Propiedad: <strong class="text-danger">{{ $propiedad->codigo }}</strong></span>
                                    </div>
                                    <div class="modal-footer border-top border-light py-2">
                                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('propiedades.destroy', $propiedad->id) }}" method="POST" class="m-0">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 fw-medium">Eliminar Propiedad</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="fas fa-building fa-3x opacity-50 mb-3"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-1">No se encontraron propiedades</h6>
                                <p class="text-muted small mb-0">No hay registros de propiedades guardados actualmente en la base de datos.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginación de la Tabla -->
        <div class="card-footer bg-white py-3 border-top border-light">
            <div class="d-flex justify-content-center">
                {{ $propiedades->links() }}
            </div>
        </div>
    </div>
</div>
@endsection