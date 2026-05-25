@extends('plantilla')
@section('title', 'Reporte de Pagos')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Reporte de Pagos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reporte de Pagos</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-receipt me-1"></i> CU14 · Generar Reporte de Pagos</div>
        <div class="card-body">
            <form method="GET" action="{{ route('informes.pagos') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Por mes</label>
                        <input type="month" name="mes" class="form-control form-control-sm" value="{{ request('mes') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Método</label>
                        <select name="metodo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ request('metodo')=='efectivo'?'selected':'' }}>Efectivo</option>
                            <option value="transferencia" {{ request('metodo')=='transferencia'?'selected':'' }}>Transferencia</option>
                            <option value="qr" {{ request('metodo')=='qr'?'selected':'' }}>QR</option>
                            <option value="stripe" {{ request('metodo')=='stripe'?'selected':'' }}>Stripe</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">Generar</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </form>

            {{-- Resumen --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center py-3">
                        <div class="fs-4 fw-bold">Bs {{ number_format($total, 2) }}</div>
                        <small>Total recaudado</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center py-3">
                        <div class="fs-4 fw-bold">{{ $pagos->count() }}</div>
                        <small>Pagos registrados</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center py-3">
                        <div class="fs-4 fw-bold">{{ $morosos->count() }}</div>
                        <small>Residentes con cuotas pendientes</small>
                    </div>
                </div>
            </div>

            <h6>Detalle de Pagos</h6>
            @if($pagos->isEmpty())
                <div class="alert alert-info">No se encontraron pagos con los filtros indicados.</div>
            @else
            <table class="table table-sm table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Fecha</th><th>Residente</th><th>Cuota</th>
                        <th>Monto (Bs)</th><th>Método</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
                        <td>{{ $p->cuota?->residente?->nombre }} {{ $p->cuota?->residente?->apellido }}</td>
                        <td>{{ $p->cuota?->titulo ?? 'N/A' }}</td>
                        <td>{{ number_format($p->monto_pagado, 2) }}</td>
                        <td>{{ ucfirst($p->metodo) }}</td>
                        <td>{{ ucfirst($p->estado) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <td colspan="4"><strong>Total</strong></td>
                        <td><strong>Bs {{ number_format($total, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>

            @if($morosos->isNotEmpty())
            <h6 class="mt-4 text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Residentes Morosos</h6>
            <table class="table table-sm table-warning">
                <thead><tr><th>#</th><th>Nombre</th><th>CI</th><th>Email</th></tr></thead>
                <tbody>
                    @foreach($morosos as $m)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $m->nombre }} {{ $m->apellido }}</td>
                        <td>{{ $m->ci }}</td><td>{{ $m->email }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            @endif
        </div>
    </div>
</div>
@endsection
