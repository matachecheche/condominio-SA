@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Lista de Cargos</h2>
            <p class="text-muted small mb-0">Gestione los roles, puestos laborales y estados operativos asignables al personal.</p>
        </div>

        <div>
            <a href="{{ route('cargos.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Nuevo Cargo</span>
            </a>
        </div>
    </div>

    <!-- Alerta de Éxito -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fas fa-check-circle fs-5"></i>
        <span class="fw-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Contenedor de la Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-briefcase text-primary"></i>
                <span>Puestos y Funciones Registradas</span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 100px;">ID</th>
                            <th class="py-3">Cargo</th>
                            <th class="py-3" style="width: 150px;">Estado</th>
                            <th class="py-3 text-end px-4" style="width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cargos as $cargo)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $cargo->id }}</td>
                            <td class="py-3 fw-semibold text-dark">{{ $cargo->cargo }}</td>
                            <td class="py-3">
                                @if($cargo->estado)
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
                                    <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1.5 text-dark" title="Editar cargo">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    
                                    <form action="{{ route('cargos.destroy', $cargo->id) }}" method="POST" class="m-0 d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" onclick="return confirm('¿Está seguro de que desea eliminar este cargo de forma permanente?')" title="Eliminar cargo">
                                            <i class="fas fa-trash-alt"></i>
                                            <span class="d-none d-sm-inline">Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection