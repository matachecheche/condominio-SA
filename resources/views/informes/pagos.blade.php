@extends('plantilla')

@section('title', 'Reporte de Pagos')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Reporte de Pagos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Reporte de Pagos</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-outline-secondary shadow-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir Reporte
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light">
            <form method="GET" action="{{ route('informes.pagos') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Desde</label>
                    <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Por mes</label>
                    <input type="month" name="mes" class="form-control" value="{{ request('mes') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Método de pago</label>
                    <select name="metodo" class="form-select">
                        <option value="">Todos los métodos</option>
                        @foreach(['efectivo','transferencia','qr','stripe'] as $m)
                            <option value="{{ $m }}" {{ request('metodo')==$m?'selected':'' }}>{{ ucfirst($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary px-4 shadow-sm" type="submit">Generar Reporte</button>
                    <a href="{{ route('informes.pagos') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-primary-subtle text-primary rounded-3">
                <div class="small fw-bold text-uppercase">Total Recaudado</div>
                <div class="fs-3 fw-bold">Bs {{ number_format($total, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-info-subtle text-info rounded-3">
                <div class="small fw-bold text-uppercase">Pagos Registrados</div>
                <div class="fs-3 fw-bold">{{ $pagos->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-warning-subtle text-warning-emphasis rounded-3">
                <div class="small fw-bold text-uppercase">Residentes Morosos</div>
                <div class="fs-3 fw-bold">{{ $morosos->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ul me-2"></i>Detalle de transacciones</h5>
        </div>
        <div class="card-body p-0">
            @if($pagos->isEmpty())
                <div class="text-center py-5 text-muted">No se encontraron pagos con los filtros indicados.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-secondary">
                            <tr>
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3">Residente</th>
                                <th class="py-3">Cuota</th>
                                <th class="py-3 text-end">Monto (Bs)</th>
                                <th class="py-3">Método</th>
                                <th class="py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $p)
                            <tr>
                                <td class="py-3 px-4 text-secondary">{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
                                <td class="py-3">{{ $p->cuota?->residente?->nombre }} {{ $p->cuota?->residente?->apellido }}</td>
                                <td class="py-3 text-secondary">{{ $p->cuota?->titulo ?? 'N/A' }}</td>
                                <td class="py-3 text-end fw-bold">{{ number_format($p->monto_pagado, 2) }}</td>
                                <td class="py-3 text-secondary small">{{ ucfirst($p->metodo) }}</td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-success-subtle text-success rounded-pill">{{ ucfirst($p->estado) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold py-3">Total Acumulado:</td>
                                <td class="text-end fw-bold text-primary py-3">Bs {{ number_format($total, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($morosos->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 mt-4">
            <div class="card-header bg-danger-subtle text-danger py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Residentes Morosos</h5>
            </div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Nombre</th>
                            <th class="py-3">CI</th>
                            <th class="py-3">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($morosos as $m)
                        <tr>
                            <td class="py-3 px-4 fw-bold">{{ $m->nombre }} {{ $m->apellido }}</td>
                            <td class="py-3 text-secondary">{{ $m->ci }}</td>
                            <td class="py-3 text-secondary">{{ $m->email }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection