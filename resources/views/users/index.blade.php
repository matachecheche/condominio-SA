@extends('plantilla')

@section('title', 'Usuarios')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Estilos personalizados rápidos para mejorar transiciones y detalles --}}
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
@if (session('success'))
<script>
    let message = "{{ session('success') }}"
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: "success",
        title: message
    });
</script>
@endif

<div class="container-fluid px-4">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1 fs-2">Usuarios</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Usuarios</li>
                </ol>
            </nav>
        </div>
        
        @can('crear usuarios')
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo usuario</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Contenedor Principal (Card) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-users text-primary"></i>
                <span>Listado de Usuarios Registrados</span>
            </div>
            <span class="badge bg-light text-dark border fw-normal px-2.5 py-1.5 rounded-pill">{{ count($users) }} usuarios</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Usuario</th>
                            <th class="py-3">Correo Electrónico</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold shadow-sm" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                </div>
                            </td>
                            <td class="py-3 text-muted">
                                <i class="far fa-envelope me-1.5 text-muted opacity-50"></i>{{ $user->email }}
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end" role="group" aria-label="Acciones de usuario">
                                    @can('editar usuarios')
                                    <form action="{{ route('users.edit', ['user' => $user]) }}" method="GET" class="m-0">
                                        <button type="submit" class="btn btn-outline-primary btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-sm-inline">Editar</span>
                                        </button>
                                    </form>
                                    @endcan
                                    
                                    <form action="" method="GET" hidden>
                                        <button type="submit" class="btn btn-warning btn-sm">Cambiar password</button>
                                    </form>
                                    
                                    @can('eliminar usuarios')
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#confimarModal-{{ $user->id }}" title="Eliminar usuario">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de Confirmación -->
                        @can('eliminar usuarios')
                        <div class="modal fade" id="confimarModal-{{ $user->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confimarModalLabel-{{ $user->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm-custom">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white py-3">
                                        <h5 class="modal-title d-flex align-items-center gap-2 fs-5 fw-semibold" id="confimarModalLabel-{{ $user->id }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>¿Confirmar eliminación?</span>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center py-4 px-3">
                                        <div class="text-danger mb-3">
                                            <i class="fas fa-user-times fa-3x"></i>
                                        </div>
                                        <p class="mb-1 text-dark fw-medium">¿Estás seguro de que deseas eliminar este usuario?</p>
                                        <p class="text-muted small px-2">Esta acción removerá permanentemente el registro de <strong>{{ $user->name }}</strong> del sistema.</p>
                                    </div>
                                    <div class="modal-footer bg-light border-0 py-2.5 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm px-3 border" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('users.destroy', ['user' => $user->id]) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-danger btn-sm px-3 shadow-sm fw-medium">Eliminar Registro</button>
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
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
{{-- Remplazamos el inicializador básico por uno con etiquetas personalizadas en español --}}
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