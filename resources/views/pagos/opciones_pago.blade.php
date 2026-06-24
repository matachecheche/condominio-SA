@extends('plantilla')

@section('title', 'Procesar Pago')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Procesar Pago de Obligación</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item text-capitalize"><a href="#" onclick="window.history.back(); return false;" class="text-decoration-none">Mis {{ $tipo }}s</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagar</li>
                </ol>
            </nav>
        </div>
        <a href="#" onclick="window.history.back(); return false;" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Regresar</span>
        </a>
    </div>

    <!-- Detalle de la Obligación -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill fw-medium text-uppercase fs-7 mb-2 d-inline-block">
                        {{ $tipo === 'cuota' ? 'Cuota Ordinaria / Extraordinaria' : 'Sanción / Multa' }}
                    </span>
                    <h4 class="fw-bold text-dark mb-1">{{ $entidad->titulo ?? $entidad->motivo ?? 'N/D' }}</h4>
                    <p class="text-muted mb-0 small">Código de referencia asignado internamente: #{{ $entidad->id }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="text-secondary small fw-medium">Monto Total a Liquidar</div>
                    <div class="fs-2 fw-bold text-dark">Bs {{ number_format($entidad->monto, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pasarelas y Métodos de Pago Habilitados -->
    <div class="row g-4">
        
        <!-- Opción 1: Pago por QR Dinámico -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                        <i class="fas fa-qrcode text-primary"></i>
                        <span>Opción A: Pago Electrónico QR</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <p class="text-muted small mb-4">Escanea el código QR de Simple desde tu aplicación bancaria de preferencia y adjunta el comprobante digital emitido.</p>
                    
                    <!-- Contenedor del QR con marco -->
                    <div class="p-3 bg-white border border-light shadow-sm rounded-3 mb-4">
                        <img src="{{ $qrBase64 }}" width="200" height="200" alt="Código QR Simple" class="img-fluid d-block">
                    </div>

                    <!-- Formulario de subida de Comprobante QR -->
                    <form method="POST" enctype="multipart/form-data" class="w-100 mt-auto"
                        @if($tipo === 'cuota')
                            action="{{ route('pagos.qr') }}"
                        @elseif($tipo === 'multa')
                            action="{{ route('pagos.qr.multa') }}"
                        @endif>
                        @csrf

                        @if($tipo === 'cuota')
                            <input type="hidden" name="cuota_id" value="{{ $entidad->id }}">
                        @else
                            <input type="hidden" name="multa_id" value="{{ $entidad->id }}">
                        @endif

                        <div class="mb-3 text-start">
                            <label for="comprobante" class="form-label fw-medium text-secondary small">Cargar Comprobante de Transferencia <span class="text-danger">*</span></label>
                            <input type="file" name="comprobante" id="comprobante" class="form-control px-3" accept="image/*,application/pdf" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                            <i class="fas fa-cloud-upload-alt"></i> Enviar Comprobante para Verificación
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Opción 2: Pago Seguro con Tarjeta vía Stripe -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                        <i class="credit-card-icon fas fa-credit-card text-success"></i>
                        <span>Opción B: Pago en Línea (Débito / Crédito)</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center text-center justify-content-center">
                    <div class="mb-4 text-success opacity-75">
                        <i class="fab fa-cc-stripe fa-4x"></i>
                    </div>
                    <p class="text-muted small mb-4 px-md-3">
                        Utiliza nuestra pasarela integrada global cifrada con **Stripe**. Admite tarjetas de débito o crédito nacionales e internacionales con conciliación inmediata automática en el sistema.
                    </p>

                    @if($tipo === 'cuota')
                        <form method="POST" action="{{ route('pagos.stripe') }}" class="w-100 mt-auto">
                    @else
                        <form method="POST" action="{{ route('pagos.stripe.multa') }}" class="w-100 mt-auto">
                    @endif
                        @csrf
                        <input type="hidden" name="{{ $tipo }}_id" value="{{ $entidad->id }}">
                        
                        <button type="submit" class="btn btn-success w-100 py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                            <i class="fas fa-shield-alt"></i> Pagar Bs {{ number_format($entidad->monto, 2) }} de forma Inmediata
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection