@extends('plantilla')

@section('title', 'Realizar Pago')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Realizar Pago de Multa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('multas.index') }}" class="text-decoration-none">Multas</a></li>
                <li class="breadcrumb-item active">Pago</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <!-- Resumen -->
                    <div class="alert alert-light border-0 mb-4">
                        <h5 class="fw-bold text-dark mb-2">Detalle de la Multa</h5>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">Motivo:</span>
                            <span class="fw-medium">{{ $multa->motivo }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">Monto a pagar:</span>
                            <span class="fw-bold text-primary fs-5">Bs. {{ number_format($multa->monto, 2) }}</span>
                        </div>
                    </div>

                    <!-- Pago QR -->
                    <div class="text-center mb-4">
                        <p class="text-secondary small fw-bold">ESCANEAR CÓDIGO QR PARA PAGO</p>
                        <div class="p-3 border rounded-3 bg-light d-inline-block">
                            <img src="{{ $qrBase64 }}" alt="QR de pago" class="img-fluid" style="max-width: 250px;">
                        </div>
                    </div>

                    <form action="{{ route('pagos-multa.qr') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <input type="hidden" name="multa_id" value="{{ $multa->id }}">
                        
                        <label class="form-label fw-bold small text-secondary">Subir comprobante de transferencia:</label>
                        <input type="file" name="comprobante" accept="image/*,.pdf" required class="form-control mb-3">
                        
                        <button type="submit" class="btn btn-primary w-100 shadow-sm py-2">
                            <i class="fas fa-paper-plane me-2"></i> Enviar Comprobante
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Pago Stripe -->
                    <div class="text-center">
                        <form action="{{ route('pagos-multa.stripe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="multa_id" value="{{ $multa->id }}">
                            <button type="submit" class="btn btn-success w-100 py-2 shadow-sm">
                                <i class="fab fa-stripe me-2"></i> Pagar con Stripe
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection