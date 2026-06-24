@extends('plantilla')

@section('title', 'Editar Cuota')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Cuota: #{{ $cuota->id }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cuotas.index') }}" class="text-decoration-none">Cuotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('cuotas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-edit text-primary"></i>
                <span>Modificar Parámetros de Facturación</span>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <!-- Alertas de Errores de Validación -->
            @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                <i class="fas fa-exclamation-circle text-danger mt-1 fs-5"></i>
                <div>
                    <span class="fw-bold d-block mb-1">Por favor corrige los siguientes errores:</span>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Pasamos el objeto $cuota entero para mapeo seguro de parámetros de ruta -->
            <form action="{{ route('cuotas.update', $cuota) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- SECCIÓN 1: DATOS GENERALES -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Detalles de la Obligación Financiera
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="titulo" class="form-label fw-medium text-secondary">Título del Cargo <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control px-3" value="{{ old('titulo', $cuota->titulo) }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="monto" class="form-label fw-medium text-secondary">Monto ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">$</span>
                            <input type="number" step="0.01" name="monto" id="monto" class="form-control px-3" value="{{ old('monto', $cuota->monto) }}" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-medium text-secondary">Descripción <span class="text-danger">*</span></label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control px-3" value="{{ old('descripcion', $cuota->descripcion) }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="fecha_emision" class="form-label fw-medium text-secondary">Fecha de Emisión <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_emision" id="fecha_emision" class="form-control px-3" value="{{ old('fecha_emision', $cuota->fecha_emision) }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="fecha_vencimiento" class="form-label fw-medium text-secondary">Fecha de Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control px-3" value="{{ old('fecha_vencimiento', $cuota->fecha_vencimiento) }}" required>
                    </div>
                </div>

                <!-- SECCIÓN 2: ESTADOS Y OBSERVACIONES -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-sync-alt me-2"></i>Estado y Seguimiento
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="estado" class="form-label fw-medium text-secondary">Estado Comercial de la Cuota <span class="text-danger">*</span></label>
                        <select name="estado" id="estado" class="form-select px-3">
                            <option value="pendiente" {{ old('estado', $cuota->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="activa" {{ old('estado', $cuota->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                            <option value="cancelada" {{ old('estado', $cuota->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            <option value="pagado" {{ old('estado', $cuota->estado) == 'pagado' ? 'selected' : '' }}>Pagado</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="observacion" class="form-label fw-medium text-secondary">Observación u Notas Adicionales</label>
                        <textarea name="observacion" id="observacion" class="form-control px-3 py-2" rows="3" placeholder="Anotaciones internas sobre cambios en montos, extensiones de fecha, etc...">{{ old('observacion', $cuota->observacion) }}</textarea>
                    </div>
                </div>

                <!-- Botones de Acción inferior -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('cuotas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Actualizar Cuota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection