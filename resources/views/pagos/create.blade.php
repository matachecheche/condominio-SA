@extends('layouts.ap')

@section('content')
<div class="container">
    <h2 class="mb-4">Registrar Pago</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>¡Ups!</strong> Hay algunos errores:<br><br>
        <ul>
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('pagos.store') }}" method="POST" id="registrarPagoForm">
        @csrf

        {{-- Tipo de pago: cuota o multa --}}
        <div class="mb-3">
            <label class="form-label">Tipo de pago</label>
            <select id="tipoPago" class="form-select">
                <option value="cuota">Cuota</option>
                <option value="multa">Multa</option>
            </select>
        </div>

        {{-- Selección de cuota --}}
        <div class="mb-3" id="bloqueCuota">
            <label for="cuotaSelect" class="form-label">Cuota asociada</label>
            <select name="cuota_id" id="cuotaSelect" class="form-select">
                <option value="">-- Selecciona una cuota --</option>
                @foreach($cuotas as $cuota)
                <option value="{{ $cuota->id }}" data-monto="{{ $cuota->monto }}">
                    Cuota #{{ $cuota->id }} - {{ $cuota->residente->nombre_completo ?? 'Sin residente' }} (Bs {{ number_format($cuota->monto, 2) }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Selección de multa --}}
        <div class="mb-3 d-none" id="bloqueMulta">
            <label for="multaSelect" class="form-label">Multa asociada</label>
            <select name="multa_id" id="multaSelect" class="form-select">
                <option value="">-- Selecciona una multa --</option>
                @foreach($multas as $multa)
                <option value="{{ $multa->id }}" data-monto="{{ $multa->monto }}">
                    Multa #{{ $multa->id }} - {{ $multa->motivo }}
                    ({{ $multa->residente->nombre_completo ?? ($multa->empleado->nombre_completo ?? 'N/D') }})
                    (Bs {{ number_format($multa->monto, 2) }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Monto pagado (prellenado según la cuota/multa) --}}
        <div class="mb-3">
            <label class="form-label">Monto Pagado</label>
            <input type="number" step="0.01" name="monto_pagado" id="montoPagado" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de Pago</label>
            <input type="text" class="form-control" value="{{ now()->toDateString() }}" disabled>
            <input type="hidden" name="fecha_pago" value="{{ now()->toDateString() }}">
        </div>

        {{-- Método de pago --}}
        <div class="mb-3">
            <label class="form-label">Método de Pago</label>
            <select name="metodo" class="form-select" required>
                <option value="">-- Selecciona --</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="qr">QR</option>
                {{-- Nota: "Stripe" se registra automáticamente desde el flujo de pago
                     en línea (Mis Cuotas / Multas → Pagar con tarjeta), no desde aquí. --}}
            </select>
        </div>

        {{-- Observación opcional --}}
        <div class="mb-3">
            <label class="form-label">Observación (opcional)</label>
            <textarea name="observacion" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar Pago</button>
        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@section('scripts')
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
