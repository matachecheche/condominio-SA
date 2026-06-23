@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Lista de Empleados</h2>
            <p class="text-muted small mb-0">Gestione el personal administrativo, cargos y estados dentro del sistema.</p>
        </div>

        @can('crear empleados')
        <div>
            <a href="{{ route('empleados.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Empleado</span>
            </a>
        </div>
        @endcan
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fas fa-check-circle fs-5"></i>
        <span class="fw-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('empleados.index') }}" class="m-0">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0 px-3">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 px-2" placeholder="Buscar por nombre, apellido o documento..." value="{{ request('search') }}">
                    <button class="btn btn-primary px-4 fw-medium" type="submit">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 80px;">ID</th>
                            <th class="py-3">Nombre completo</th>
                            <th class="py-3">CI</th>
                            <th class="py-3">Cargo</th>
                            <th class="py-3">Fecha de Ingreso</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empleados as $empleado)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $empleado->id }}</td>
                            <td class="py-3 fw-semibold text-dark">
                                {{ $empleado->nombre }} {{ $empleado->apellido }}
                            </td>
                            <td class="py-3 text-muted">{{ $empleado->ci }}</td>
                            <td class="py-3">
                                <span class="badge bg-light text-secondary border fw-normal px-2.5 py-1.5 rounded-2">
                                    {{ $empleado->cargo?->cargo ?? 'Sin cargo' }}
                                </span>
                            </td>
                            <td class="py-3 text-muted">{{ $empleado->fecha_ingreso }}</td>
                            <td class="py-3">
                                @if($empleado->estado)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fw-medium fs-7">
                                    <i class="fas fa-circle fs-8 me-1 align-middle"></i>Activo
                                </span>
                                @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill fw-medium fs-7">
                                    <i class="fas fa-circle fs-8 me-1 align-middle"></i>Inactivo
                                </span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    @can('editar empleados')
                                    <a href="{{ route('empleados.edit', $empleado->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1.5 text-dark" title="Editar empleado">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    @endcan

                                    @can('eliminar empleados')
                                    <form action="{{ route('empleados.destroy', $empleado->id) }}" method="POST" class="m-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" onclick="return confirm('¿Está completamente seguro de que desea eliminar permanentemente a este empleado?')" title="Eliminar empleado">
                                            <i class="fas fa-trash-alt"></i>
                                            <span class="d-none d-sm-inline">Eliminar</span>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="fas fa-users-slash fa-3x opacity-50 mb-3"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-1">No se encontraron empleados</h6>
                                <p class="text-muted small mb-0">No hay registros cargados actualmente o no coinciden con los términos de búsqueda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $empleados->appends(['search' => request('search')])->links() }}
    </div>
</div>
@endsection