@extends('plantilla')

@section('title', 'Mis Cuotas')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Mis Cuotas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mis Cuotas</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Contenedor de la Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Estado de Cuentas y Obligaciones Pendientes</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Concepto / Detalle</th>
                            <th class="py-3">Monto Total</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Vencimiento</th>
                            <th class="py-3 text-end px-4" style="width: 180px;">Gestión de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cuotas as $cuota)
                        <tr>
                            <td class="py-3 px-4">
                                <div class="fw-bold text-dark mb-0.5">{{ $cuota->titulo }}</div>
                                <div class="text-muted small fs-7">{{ $cuota->descripcion }}</div>
                            </td>
                            <td class="py-3 text-dark fw-bold fs-6">Bs {{ number_format($cuota->monto, 2) }}</td>
                            <td class="py-3">
                                @if($cuota->estaPagada())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-medium fs-7">
                                        <i class="fas fa-check-circle me-1"></i>Pagada
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark px-2.5 py-1 rounded-pill fw-medium fs-7">
                                        <i class="fas fa-clock me-1"></i>Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-muted">
                                <i class="far fa-calendar-alt me-1 opacity-75"></i>{{ $cuota->fecha_vencimiento ? \Carbon\Carbon::parse($cuota->fecha_vencimiento)->translatedFormat('d \d\e F, Y') : '—' }}
                            </td>
                            <td class="py-3 text-end px-4">
                                @if(!$cuota->estaPagada())
                                    <a href="{{ route('pagos.create.cuota', ['cuota' => $cuota->id]) }}" class="btn btn-success btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm fw-medium">
                                        <i class="fas fa-credit-card"></i> Pagar
                                    </a>
                                @else
                                    @if($cuota->pagos && $cuota->pagos->first())
                                        <a href="{{ route('pagos.comprobante', $cuota->pagos->first()->id) }}" class="btn btn-outline-primary btn-sm px-2.5 d-inline-flex align-items-center gap-1.5" target="_blank">
                                            <i class="fas fa-receipt"></i> Comprobante
                                        </a>
                                    @else
                                        <span class="text-muted small fs-7 italic">Procesado</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-check fs-3 d-block mb-2 opacity-50"></i>
                                Al día. No registras cuotas asignadas ni obligaciones pendientes de pago.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginador Estilizado -->
        @if($cuotas->hasPages())
        <div class="card-footer bg-white border-top border-light py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-secondary small">
                    Mostrando el historial de facturación asignado a su unidad de residencia.
                </span>
                <div>
                    {{ $cuotas->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection