@extends('plantilla')

@section('title', 'Catálogo de Áreas Comunes')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<!-- SweetAlert2 Toast para mensajes de éxito -->
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let message = "{{ session('success') }}";
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
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
        });
    </script>
@endif

<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Catálogo de Áreas Comunes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Áreas Comunes</li>
                </ol>
            </nav>
        </div>

        {{-- Mostrar botón NUEVA ÁREA COMÚN solo si NO es residente --}}
        @if(auth()->check() && !auth()->user()->residente_id)
            <div>
                <a href="{{ route('areas-comunes.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Área Común</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-building text-primary"></i>
                <span>Espacios y Sectores Compartidos Habilitados</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesReservas" class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 70px;">ID</th>
                            <th class="py-3">Nombre del Área</th>
                            <th class="py-3">Costo por Hora</th>
                            <th class="py-3" style="width: 140px;">Estado</th>
                            @if(auth()->check() && !auth()->user()->residente_id)
                                <th class="py-3 text-end px-4" style="width: 200px;">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areasComunes as $area)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $area->id }}</td>
                            <td class="py-3 fw-bold text-dark">{{ $area->nombre }}</td>
                            <td class="py-3 text-dark fw-semibold">Bs {{ number_format($area->monto, 2) }}</td>
                            <td class="py-3">
                                @php
                                    $estado = strtolower($area->estado);
                                    $badgeStyle = match($estado) {
                                        'activo' => 'bg-success-subtle text-success border border-success-subtle',
                                        'inactivo' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        'mantenimiento' => 'bg-warning-subtle text-warning border border-warning-subtle text-dark',
                                        default => 'bg-light-subtle text-dark border'
                                    };
                                @endphp
                                <span class="badge {{ $badgeStyle }} px-2.5 py-1 rounded-pill fw-medium fs-7 text-capitalize">
                                    @if($estado === 'activo') <i class="fas fa-check-circle me-1"></i>
                                    @elseif($estado === 'mantenimiento') <i class="fas fa-tools me-1"></i>
                                    @else <i class="fas fa-minus-circle me-1"></i> @endif
                                    {{ $area->estado }}
                                </span>
                            </td>
                            
                            {{-- Mostrar acciones solo si NO es residente --}}
                            @if(auth()->check() && !auth()->user()->residente_id)
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <a href="{{ route('areas-comunes.edit', $area->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1 text-dark" title="Editar área">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                  
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1" 
                                            data-bs-toggle="modal" data-bs-target="#confirmarEliminarArea-{{ $area->id }}" title="Eliminar área">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>

                        @if(auth()->check() && !auth()->user()->residente_id)
                        <!-- Modal de Confirmación Estilizado -->
                        <div class="modal fade" id="confirmarEliminarArea-{{ $area->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-3">
                                    <div class="modal-header border-bottom border-light py-3">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4 text-start">
                                        <p class="mb-1 text-dark">¿Está seguro de que desea remover esta área común del catálogo del condominio?</p>
                                        <span class="text-muted small">Nombre: <strong class="text-danger">{{ $area->nombre }}</strong></span>
                                    </div>
                                    <div class="modal-footer border-top border-light py-2">
                                        <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('areas-comunes.destroy', $area->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 fw-medium btn-sm">Eliminar Registro</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
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
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('datatablesReservas');
        if (table) {
            new simpleDatatables.DataTable(table, {
                labels: {
                    placeholder: "Buscar área...",
                    perPage: "registros por página",
                    noRows: "No se encontraron áreas comunes",
                    info: "Mostrando {start} a {end} de {rows} espacios",
                }
            });
        }
    });
</script>
@endpush