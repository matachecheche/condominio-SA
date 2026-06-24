@extends('plantilla')

@section('title', 'Panel de Reservas')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<!-- SweetAlert2 Toast Notificación de Éxito -->
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                title: "{{ session('success') }}"
            });
        });
    </script>
@endif

<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Panel de Reservas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reservas</li>
                </ol>
            </nav>
        </div>

        {{-- Botón para agendar (Habilitado para todos los roles) --}}
        <div>
            <a href="{{ route('reservas.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-calendar-plus"></i>
                <span>Agendar Nueva Reserva</span>
            </a>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-book-open text-primary"></i>
                <span>Historial y Planificación de Uso de Espacios</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesReservas" class="table table-hover align-middle mb-0 px-3">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 80px;">ID</th>
                            <th class="py-3">Área Común</th>
                            <th class="py-3">Costo Total</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Fecha</th>
                            <th class="py-3">Bloque Horario</th>
                            <th class="py-3">Residente</th>
                            <th class="py-3 text-center" style="width: 140px;">Control</th>
                            <th class="py-3 text-end px-4" style="width: 130px;">Inventario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservas as $reserva)
                            <tr>
                                <td class="py-3 px-4 fw-semibold text-secondary">#{{ $reserva->id }}</td>
                                <td class="py-3 fw-bold text-dark">{{ $reserva->areaComun->nombre ?? 'N/D' }}</td>
                                <td class="py-3 text-dark fw-semibold">Bs {{ number_format($reserva->areaComun->monto ?? 0, 2) }}</td>
                                <td class="py-3">
                                    @php
                                        $estado = strtolower($reserva->estado ?? '');
                                        $badgeStyle = match($estado) {
                                            'confirmada' => 'bg-success-subtle text-success border border-success-subtle',
                                            'pendiente' => 'bg-warning-subtle text-warning border border-warning-subtle text-dark',
                                            'cancelado' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            default => 'bg-light text-dark border'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeStyle }} px-2.5 py-1 rounded-pill fw-medium fs-7 text-capitalize">
                                        {{ $reserva->estado ?? 'N/D' }}
                                    </span>
                                </td>
                                <td class="py-3 text-muted">
                                    <i class="far fa-calendar-alt me-1 opacity-75"></i>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 text-secondary small fw-medium">
                                    <i class="far fa-clock me-1 opacity-75"></i>{{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($reserva->hora_fin)->format('H:i') }}
                                </td>
                                <td class="py-3 text-dark fs-7">{{ $reserva->residente->nombre ?? 'N/D' }}</td>
                                <td class="py-3 text-center">
                                    <div class="d-inline-flex gap-1.5 justify-content-center">
                                        {{-- Botón de Editar (Restaurado globalmente) --}}
                                        <a href="{{ route('reservas.edit', $reserva->id) }}" class="btn btn-outline-warning btn-sm p-1.5 rounded d-flex align-items-center justify-content-center" title="Editar parámetros">
                                            <i class="fas fa-edit fa-fw text-dark"></i>
                                        </a>
                                        
                                        {{-- Botón para desplegar Modal de Eliminación --}}
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1.5 rounded d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#confirmarEliminar-{{ $reserva->id }}" title="Remover Reserva">
                                            <i class="fas fa-trash-alt fa-fw"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 text-end px-4">
                                    <a href="{{ route('reservas.verificar-inventario', $reserva->id) }}" class="btn btn-outline-info btn-sm px-2.5 text-dark fw-medium d-inline-flex align-items-center gap-1">
                                        <i class="fas fa-boxes text-info"></i> Verificar
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal de Confirmación de Eliminación por Ítem -->
                            <div class="modal fade" id="confirmarEliminar-{{ $reserva->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-3">
                                        <div class="modal-header border-bottom border-light py-3">
                                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Cancelación
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-4 text-start">
                                            <p class="mb-1 text-dark">¿Está seguro de que desea eliminar la reserva de este espacio común?</p>
                                            <span class="text-muted small">ID de Solicitud: <strong class="text-danger">#{{ $reserva->id }}</strong></span>
                                        </div>
                                        <div class="modal-footer border-top border-light py-2">
                                            <button type="button" class="btn btn-light border px-3 btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('reservas.destroy', $reserva->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger px-3 fw-medium btn-sm">Eliminar Reserva</button>
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
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const table = document.getElementById('datatablesReservas');
            if (table) {
                new simpleDatatables.DataTable(table, {
                    labels: {
                        placeholder: "Buscar reserva...",
                        perPage: "registros por página",
                        noRows: "No se encontraron reservas registradas",
                        info: "Mostrando {start} a {end} de {rows} solicitudes",
                    }
                });
            }
        });
    </script>
@endpush