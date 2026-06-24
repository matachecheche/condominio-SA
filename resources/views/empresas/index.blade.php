@extends('plantilla')

@section('title', 'Empresas Externas')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Empresas Externas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Empresas</li>
                </ol>
            </nav>
        </div>
        @can('crear empresas')
            <a href="{{ route('empresas.create') }}" class="btn btn-primary shadow-sm px-3 d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i> Nueva Empresa
            </a>
        @endcan
    </div>

    <!-- Barra de Búsqueda -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light rounded-3">
            <form method="GET" action="{{ route('empresas.index') }}" class="m-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Buscar por nombre o servicio..." value="{{ request('search') }}">
                    <button class="btn btn-primary px-4 shadow-sm" type="submit">Buscar</button>
                    @if(request('search'))
                        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3">Nombre</th>
                            <th class="py-3">Servicio</th>
                            <th class="py-3">Contacto</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empresas as $empresa)
                        <tr>
                            <td class="py-3 px-4 text-secondary fw-semibold">#{{ $empresa->id }}</td>
                            <td class="py-3 fw-bold text-dark">{{ $empresa->nombre }}</td>
                            <td class="py-3 text-secondary">{{ $empresa->servicio }}</td>
                            <td class="py-3 fs-7">
                                <div class="text-dark"><i class="fas fa-phone-alt me-1 text-muted"></i>{{ $empresa->telefono }}</div>
                                <div class="text-muted"><i class="fas fa-envelope me-1"></i>{{ $empresa->correo }}</div>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-1">
                                    @can('ver empresas')
                                        <a href="{{ route('empresas.show', $empresa->id) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    @endcan
                                    @can('editar empresas')
                                        <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-outline-warning btn-sm text-dark" title="Editar"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('eliminar empresas')
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminar({{ $empresa->id }}, '{{ $empresa->nombre }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="delete-form-{{ $empresa->id }}" action="{{ route('empresas.destroy', $empresa->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron empresas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $empresas->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
    // SweetAlert para éxito
    @if (session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2000 });
    @endif

    // Función de eliminación profesional
    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar empresa?',
            text: `¿Estás seguro de eliminar a ${nombre}? Esta acción no se puede revertir.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection