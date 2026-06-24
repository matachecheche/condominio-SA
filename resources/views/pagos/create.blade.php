@extends('plantilla')

@section('title', 'Registrar Pago')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Pago Manual</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pagos.index') }}" class="text-decoration-none">Pagos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Registrar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('pagos.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-cash-register text-primary"></i>
                <span>Formulario de Ingreso de Caja</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                <i class="fas fa-exclamation-circle text-danger mt-1 fs-5"></i>
                <div>
                    <span class="fw-bold d-block mb-1">Por favor corrige los siguientes errores:</span>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form action="{{ route('pagos.store') }}" method="POST" id="registrarPagoForm">
                @csrf

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-filter me-2"></i>Clasificación del Ingreso
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="tipoPago" class="form-label fw-medium text-secondary">Tipo de Pago <span class="text-danger">*</span></label>
                        <select id="tipoPago" class="form-select px-3">
                            <option value="cuota">Cuota Ordinaria / Extraordinaria</option>
                            <option value="multa">Multa Administrativa</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6" id="bloqueCuota">
                        <label for="cuotaSelect" class="form-label fw-medium text-secondary">Cuota asociada <span class="text-danger">*</span></label>
                        <select name="cuota_id" id="cuotaSelect" class="form-select px-3">
                            <option value="">— Selecciona una cuota —</option>
                            @foreach($cuotas as $cuota)
                            <option value="{{ $cuota->id }}" data-monto="{{ $cuota->monto }}">
                                Cuota #{{ $cuota->id }} - {{ $cuota->residente->nombre_completo ?? 'Sin residente' }} (Bs {{ number_format($cuota->monto, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 d-none" id="bloqueMulta">
                        <label for="multaSelect" class="form-label fw-medium text-secondary">Multa asociada <span class="text-danger">*</span></label>
                        <select name="multa_id" id="multaSelect" class="form-select px-3">
                            <option value="">— Selecciona una multa —</option>
                            @foreach($multas as $multa)
                            <option value="{{ $multa->id }}" data-monto="{{ $multa->monto }}">
                                Multa #{{ $multa->id }} - {{ $multa->motivo }} ({{ $multa->residente->nombre_completo ?? ($multa->empleado->nombre_completo ?? 'N/D') }}) (Bs {{ number_format($multa->monto, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-money-bill-wave me-2"></i>Liquidación de Valores
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="montoPagado" class="form-label fw-medium text-secondary">Monto Cobrado (Bs) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Bs</span>
                            <input type="number" step="0.01" name="monto_pagado" id="montoPagado" class="form-control px-3 fw-bold text-dark" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Fecha de Operación</label>
                        <input type="text" class="form-control px-3 bg-light text-muted" value="{{ now()->toDateString() }}" disabled>
                        <input type="hidden" name="fecha_pago" value="{{ now()->toDateString() }}">
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Método de Pago <span class="text-danger">*</span></label>
                        <select name="metodo" class="form-select px-3" required>
                            <option value="">— Selecciona —</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="qr">Pago por QR</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="observacion" class="form-label fw-medium text-secondary">Observación / Glosa (Opcional)</label>
                    <textarea name="observacion" id="observacion" class="form-control px-3 py-2" rows="2" placeholder="Detalles de depósito, número de comprobante, observaciones de la validación física..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('pagos.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-check-circle me-1.5"></i>Registrar Transacción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoPago = document.getElementById('tipoPago');
    const bloqueCuota = document.getElementById('bloqueCuota');
    const bloqueMulta = document.getElementById('bloqueMulta');
    const cuotaSelect = document.getElementById('cuotaSelect');
    const multaSelect = document.getElementById('multaSelect');
    const montoInput = document.getElementById('montoPagado');

    function actualizarVisibilidad() {
        if (tipoPago.value === 'cuota') {
            bloqueCuota.classList.remove('d-none');
            bloqueMulta.classList.add('d-none');
            cuotaSelect.setAttribute('required', 'required');
            multaSelect.removeAttribute('required');
            multaSelect.value = '';
            montoInput.value = '';
        } else {
            bloqueMulta.classList.remove('d-none');
            bloqueCuota.classList.add('d-none');
            multaSelect.setAttribute('required', 'required');
            cuotaSelect.removeAttribute('required');
            cuotaSelect.value = '';
            montoInput.value = '';
        }
    }

    tipoPago.addEventListener('change', actualizarVisibilidad);
    actualizarVisibilidad();

    cuotaSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const monto = opt.getAttribute('data-monto');
        montoInput.value = monto ? parseFloat(monto).toFixed(2) : '';
    });

    multaSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const monto = opt.getAttribute('data-monto');
        montoInput.value = monto ? parseFloat(monto).toFixed(2) : '';
    });
});
</script>
@endsection