@extends('plantilla')

@section('title', 'Nueva Multa')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Registrar Nueva Multa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('multas.index') }}" class="text-decoration-none">Multas</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Detalles de la infracción
                    </h5>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('multas.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Motivo de la Multa <span class="text-danger">*</span></label>
                                <input type="text" name="motivo" class="form-control" value="{{ old('motivo') }}" placeholder="Ej: Ruido excesivo después de las 22:00" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Monto (Bs.) <span class="text-danger">*</span></label>
                                <input type="number" name="monto" step="0.01" class="form-control" value="{{ old('monto') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Fecha de Emisión</label>
                                <input type="date" name="fechaEmision" class="form-control" value="{{ old('fechaEmision', now()->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Fecha Límite</label>
                                <input type="date" name="fechaLimite" class="form-control" value="{{ old('fechaLimite', now()->addDays(7)->format('Y-m-d')) }}" required>
                            </div>

                            <hr class="my-2">

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Residente (Opcional)</label>
                                <select name="residente_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($residentes as $res)
                                        <option value="{{ $res->id }}" {{ old('residente_id') == $res->id ? 'selected' : '' }}>
                                            {{ $res->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Empleado (Opcional)</label>
                                <select name="empleado_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($empleados as $emp)
                                        <option value="{{ $emp->id }}" {{ old('empleado_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info py-2 small mb-0">
                                    <i class="fas fa-info-circle me-2"></i> Por favor, seleccione <strong>solo uno</strong>: ya sea al Residente o al Empleado.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-3">
                            <a href="{{ route('multas.index') }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> Guardar Multa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection