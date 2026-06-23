@extends('layouts.ap')

@section('title', 'roles')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02) !important;
        transition: background-color 0.2s ease;
    }
    .btn {
        transition: all 0.2s ease-in-out;
    }
    .btn:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4 py-3">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 mb-4 gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1 fs-2">Roles y Permisos</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>

        @can('crear roles')
        <div>
            <a href="{{ route('roles.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Añadir nuevo rol</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Tarjeta del Listado -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-user-shield text-primary"></i>
                <span>Gestión de Roles del Sistema</span>
            </div>
            <span class="badge bg-light text-dark border fw-normal px-2.5 py-1.5 rounded-pill">{{ count($roles) }} roles</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Rol</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $item)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-dark">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1.5 rounded-2 fs-7 fw-medium">
                                    {{ $item->name }}
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end" role="group" aria-label="Acciones de rol">

                                    @can('editar roles')
                                    <form action="{{ route('roles.edit', ['role' => $item]) }}" method="get" class="m-0">
                                        <button type="submit" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1.5 text-dark" title="Editar rol">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-sm-inline">Editar</span>
                                        </button>
                                    </form>
                                    @endcan

                                    @can('eliminar roles')
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#confirmModal-{{ $item->id }}" title="Eliminar rol">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                    @endcan

                                </div>
                            </td>
                        </tr>

                        <!-- Modal de confirmación-->
                        @can('eliminar roles')
                        <div class="modal fade" id="confirmModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel-{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white py-3">
                                        <h5 class="modal-title d-flex align-items-center gap-2 fs-5 fw-semibold" id="confirmModalLabel-{{ $item->id }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>¿Confirmar eliminación?</span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4 px-3">
                                        <div class="text-danger mb-3">
                                            <i class="fas fa-shield-alt fa-3x opacity-75"></i>
                                        </div>
                                        <p class="mb-1 text-dark fw-medium">¿Estás seguro de que deseas eliminar este rol?</p>
                                        <p class="text-muted small px-3">Esta acción quitará los permisos asociados permanentemente del rol <strong>{{ $item->name }}</strong>.</p>
                                    </div>
                                    <div class="modal-footer bg-light border-0 py-2.5 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm px-3 border" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('roles.destroy', ['role' => $item->id]) }}" method="post" class="m-0">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm px-3 shadow-sm fw-medium">Confirmar Eliminación</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script>
    window.addEventListener('DOMContentLoaded', event => {
        const datatablesSimple = document.getElementById('datatablesSimple');
        if (datatablesSimple) {
            new simpleDatatables.DataTable(datatablesSimple, {
                labels: {
                    placeholder: "Buscar...",
                    perPage: "registros por página",
                    noRows: "No se encontraron registros",
                    info: "Mostrando {start} a {end} de {rows} registros",
                }
            });
        }
    });
</script>
@endpush