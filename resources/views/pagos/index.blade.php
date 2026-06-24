@extends('plantilla')

@section('title', 'Listado de Pagos')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Listado de Pagos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagos</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Notificaciones Dropdown Estilizado -->
            <div class="dropdown">
                <a href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="text-secondary position-relative p-2 rounded-circle bg-white shadow-sm border">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                        3
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg m-0 rounded-3 mt-2" aria-labelledby="notificacionesDropdown" style="width: 280px;">
                    <li>
                        <h6 class="dropdown-header text-uppercase fw-bold text-secondary px-3 py-2 fs-7">Notificaciones Recientes</h6>
                    </li>
                    <li><a class="dropdown-item py-2 px-3 small text-dark d-flex gap-2" href="#"><i class="fas fa-file-invoice text-primary mt-0.5"></i> Nueva cuota generada</a></li>
                    <li><a class="dropdown-item py-2 px-3 small text-dark d-flex gap-2" href="#"><i class="fas fa-check-circle text-success mt-0.5"></i> Tu pago fue registrado</a></li>
                    <li><hr class="dropdown-divider my-1 border-light"></li>
                    <li><a class="dropdown-item text-center small text-primary fw-medium py-1" href="#">Ver todas las alertas</a></li>
                </ul>
            </div>

            <a href="{{ route('pagos.create') }}" class="btn btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Registrar Pago Manual</span>
            </a>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-check-circle fs-5"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Panel de Filtros Avanzados -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-sliders-h text-primary"></i>
                <span>Parámetros de Búsqueda y Segmentación Cronológica</span>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('pagos.index') }}">
                <div class="row g-3 align-items-end">
                    
                    <!-- Búsqueda General -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-medium text-secondary small">Buscar residente o cuota</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control px-2" placeholder="Ej: Juan Pérez, Cuota #12" value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Filtro por Método de Pago -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-medium text-secondary small">Método de Pago</label>
                        <select name="metodo" class="form-select px-3">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ request('metodo') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="transferencia" {{ request('metodo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="qr" {{ request('metodo') == 'qr' ? 'selected' : '' }}>QR</option>
                        </select>
                    </div>

                    <!-- Selección de Tipo de Tiempo -->
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label fw-medium text-secondary small">Segmentación</label>
                        <select name="filtro_tiempo" id="filtro_tiempo" class="form-select px-3">
                            <option value="">— Sin Filtro —</option>
                            <option value="fecha" {{ request('filtro_tiempo') == 'fecha' ? 'selected' : '' }}>Por Rango Fechas</option>
                            <option value="mes" {{ request('filtro_tiempo') == 'mes' ? 'selected' : '' }}>Por Mes</option>
                            <option value="semana" {{ request('filtro_tiempo') == 'semana' ? 'selected' : '' }}>Por Semana</option>
                            <option value="anio" {{ request('filtro_tiempo') == 'anio' ? 'selected' : '' }}>Por Año</option>
                        </select>
                    </div>

                    <!-- Contenedores Dinámicos de Tiempos -->
                    <div class="col-6 col-sm-3 col-md-2" id="fechaDesdeContainer" style="display: none;">
                        <label class="form-label fw-medium text-secondary small">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control px-2" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-6 col-sm-3 col-md-2" id="fechaHastaContainer" style="display: none;">
                        <label class="form-label fw-medium text-secondary small">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control px-2" value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4" id="mesContainer" style="display: none;">
                        <label class="form-label fw-medium text-secondary small">Seleccionar Mes</label>
                        <input type="month" name="mes" class="form-control px-3" value="{{ request('mes') }}">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4" id="semanaContainer" style="display: none;">
                        <label class="form-label fw-medium text-secondary small">Seleccionar Semana</label>
                        <input type="week" name="semana" class="form-control px-3" value="{{ request('semana') }}">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4" id="anioContainer" style="display: none;">
                        <label class="form-label fw-medium text-secondary small">Año fiscal</label>
                        <input type="number" name="anio" class="form-control px-3" min="2000" max="{{ date('Y') + 1 }}" value="{{ request('anio') }}">
                    </div>

                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary px-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor de la Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4" style="width: 80px;">ID</th>
                            <th class="py-3">Obligación / Detalle</th>
                            <th class="py-3">Monto Recaudado</th>
                            <th class="py-3">Fecha de Pago</th>
                            <th class="py-3">Método</th>
                            <th class="py-3 px-4">Operador Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagos as $pago)
                        <tr>
                            <td class="py-3 px-4 fw-semibold text-secondary">#{{ $pago->id }}</td>
                            <td class="py-3 fw-bold text-dark">
                                @if($pago->cuota_id)
                                    <span class="text-primary"><i class="fas fa-file-invoice-dollar me-1"></i> Cuota #{{ $pago->cuota_id }}</span>
                                @elseif($pago->multa_id)
                                    <span class="text-danger"><i class="fas fa-gavel me-1"></i> Multa #{{ $pago->multa_id }}</span>
                                @else
                                    <span class="text-muted"><i class="fas fa-receipt me-1"></i> Transacción Ordinaria</span>
                                @endif
                            </td>
                            <td class="py-3 text-dark fw-bold fs-6">Bs {{ number_format($pago->monto_pagado, 2) }}</td>
                            <td class="py-3 text-muted">
                                <i class="far fa-calendar-alt me-1 opacity-75"></i>{{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->translatedFormat('d \d\e F, Y') : '—' }}
                            </td>
                            <td class="py-3">
                                @php
                                    $metodo = strtolower($pago->metodo ?? '');
                                    $badgeStyle = match($metodo) {
                                        'efectivo' => 'bg-success-subtle text-success border border-success-subtle',
                                        'transferencia' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'qr' => 'bg-info-subtle text-info border border-info-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                    };
                                @endphp
                                <span class="badge {{ $badgeStyle }} px-2.5 py-1 rounded-pill fw-medium text-capitalize fs-7">
                                    {{ $metodo ?: 'No Definido' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-secondary smallfw-medium">
                                <i class="far fa-user me-1 opacity-50"></i>{{ $pago->user->name ?? 'Sistema / N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
                                No se encontraron registros de pagos que coincidan con los criterios establecidos.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginador Estilizado Inferior -->
        @if($pagos->hasPages())
        <div class="card-footer bg-white border-top border-light py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-secondary small">
                    Mostrando registros de transacciones validadas en el libro diario.
                </span>
                <div>
                    {{ $pagos->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtro = document.getElementById('filtro_tiempo');

        const fechaDesde = document.getElementById('fechaDesdeContainer');
        const fechaHasta = document.getElementById('fechaHastaContainer');
        const mes = document.getElementById('mesContainer');
        const semana = document.getElementById('semanaContainer');
        const anio = document.getElementById('anioContainer');

        function actualizarCampos() {
            fechaDesde.style.display = 'none';
            fechaHasta.style.display = 'none';
            mes.style.display = 'none';
            semana.style.display = 'none';
            anio.style.display = 'none';

            const tipo = filtro.value;

            if (tipo === 'fecha') {
                fechaDesde.style.display = 'block';
                fechaHasta.style.display = 'block';
            } else if (tipo === 'mes') {
                mes.style.display = 'block';
            } else if (tipo === 'semana') {
                semana.style.display = 'block';
            } else if (tipo === 'anio') {
                anio.style.display = 'block';
            }
        }

        filtro.addEventListener('change', actualizarCampos);
        actualizarCampos();
    });
</script>
@endpush