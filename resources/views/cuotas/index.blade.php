@extends('plantilla')

@section('title', 'Cuotas y Pagos')

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
            <h2 class="fw-bold text-dark mb-1 fs-2">Cuotas y Pagos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Cuotas</li>
                </ol>
            </nav>
        </div>

        <div>
            <a href="{{ route('cuotas.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Emitir Nueva Cuota</span>
            </a>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Historial General de Facturación</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 70px;">ID</th>
                            <th class="py-3">Residente</th>
                            <th class="py-3">Período</th>
                            <th class="py-3">Monto</th>
                            <th class="py-3" style="width: 130px;">Estado</th>
                            <th class="py-3 text-end px-4" style="width: 220px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cuotas as $cuota)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $cuota->id }}</td>
                            <td class="py-3 fw-semibold text-dark">
                                {{ $cuota->residente->nombre_completo ?? 'Sin asignar' }}
                            </td>
                            <td class="py-3 text-muted fs-7 text-capitalize">
                                {{ \Carbon\Carbon::parse($cuota->fecha)->translatedFormat('F Y') }}
                            </td>
                            <td class="py-3 text-dark fw-bold">
                                ${{ number_format($cuota->monto, 2) }}
                            </td>
                            <td class="py-3">
                                @php
                                    $estado = strtolower($cuota->estado ?? 'pendiente');
                                    $badgeClass = match($estado) {
                                        'pagado' => 'bg-success-subtle text-success border border-success-subtle',
                                        'pendiente' => 'bg-warning-subtle text-warning border border-warning-subtle text-dark',
                                        'activa' => 'bg-info-subtle text-info border border-info-subtle',
                                        'cancelada' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2.5 py-1.5 rounded-pill fw-medium fs-7 text-capitalize">
                                    <i class="fas fa-circle fs-8 me-1 align-middle"></i>{{ $estado }}
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    @can('ver cuotas')
                                    <a href="{{ route('cuotas.show', $cuota->id) }}" class="btn btn-outline-info btn-sm px-2.5 d-inline-flex align-items-center gap-1" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                        <span class="d-none d-sm-inline">Ver</span>
                                    </a>
                                    @endcan
                                   
                                    <a href="{{ route('cuotas.edit', $cuota->id) }}" class="btn btn-outline-warning btn-sm px-2.5 d-inline-flex align-items-center gap-1 text-dark" title="Editar cuota">
                                        <i class="fas fa-edit"></i>
                                        <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    
                                    <button type="button" class="btn btn-outline-danger btn-sm px-2.5 d-inline-flex align-items-center gap-1" 
                                            data-bs-toggle="modal" data-bs-target="#confirmarEliminar-{{ $cuota->id }}" title="Eliminar cuota">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal de Confirmación Estilizado -->
                        <div class="modal fade" id="confirmarEliminar-{{ $cuota->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-3">
                                    <div class="modal-header border-bottom border-light py-3">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4 text-start">
                                        <p class="mb-1 text-dark">¿Está seguro de que desea eliminar permanentemente este registro de cuota?</p>
                                        <span class="text-muted small">Asociado a la Unidad: <strong class="text-danger">{{ $cuota->unidad->codigo ?? 'N/A' }}</strong></span>
                                    </div>
                                    <div class="modal-footer border-top border-light py-2">
                                        <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('cuotas.destroy', $cuota->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger px-3 fw-medium btn-sm">Eliminar Cuota</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
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
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush