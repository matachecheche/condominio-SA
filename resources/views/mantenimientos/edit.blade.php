@extends('plantilla')

@section('title', 'Editar Mantenimiento')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Mantenimiento</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}" class="text-decoration-none">Mantenimientos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver a la Lista</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-tools text-primary"></i>
                <span>Modificar Parámetros de la Orden de Trabajo #{{ $mantenimiento->id }}</span>
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

            <form method="POST" action="{{ route('mantenimientos.update', $mantenimiento->id) }}">
                @csrf
                @method('PUT')
                
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Detalles Principales
                </h6>
                
                <div class="row g-3 mb-4">
                    <!-- Descripción -->
                    <div class="col-12 col-md-8">
                        <label for="descripcion" class="form-label fw-medium text-secondary">Descripción del Trabajo <span class="text-danger">*</span></label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control px-3"
                               value="{{ old('descripcion', $mantenimiento->descripcion) }}" required placeholder="Ej: Reparación de luminarias en el área de piscina">
                    </div>

                    <!-- Estado del Mantenimiento -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-medium text-secondary d-block mb-2">Estado de Gestión</label>
                        <div class="pt-1.5">
                            <div class="form-check form-check-inline me-3">
                                <input class="form-check-input accent-success" type="radio" name="estado" id="estado_activo" value="1"
                                       {{ old('estado', $mantenimiento->estado) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium text-dark" for="estado_activo">🟢 Activo</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input accent-danger" type="radio" name="estado" id="estado_inactivo" value="0"
                                       {{ old('estado', $mantenimiento->estado) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium text-dark" for="estado_inactivo">🔴 Inactivo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-dollar-sign me-2"></i>Planificación y Logística Financiera
                </h6>

                <div class="row g-3 mb-4">
                    <!-- Fecha y Hora -->
                    <div class="col-12 col-md-6">
                        <label for="fecha_hora" class="form-label fw-medium text-secondary">Fecha y Hora Programada <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control px-3"
                               value="{{ old('fecha_hora', \Carbon\Carbon::parse($mantenimiento->fecha_hora)->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <!-- Monto -->
                    <div class="col-12 col-md-6">
                        <label for="monto" class="form-label fw-medium text-secondary">Monto del Servicio <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Bs</span>
                            <input type="number" step="0.01" name="monto" id="monto" class="form-control px-2 fw-semibold text-dark"
                                   value="{{ old('monto', $mantenimiento->monto) }}" required placeholder="0.00">
                        </div>
                    </div>

                    <!-- Asignación de Gestor (Usuario) -->
                    <div class="col-12 col-md-6">
                        <label for="usuario_id" class="form-label fw-medium text-secondary">Usuario Responsable / Supervisor <span class="text-danger">*</span></label>
                        <select name="usuario_id" id="usuario_id" class="form-select px-3" required>
                            <option value="">-- Seleccionar Usuario --</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}"
                                    {{ old('usuario_id', $mantenimiento->usuario_id) == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Asignación de Empresa Externa -->
                    <div class="col-12 col-md-6">
                        <label for="empresa_id" class="form-label fw-medium text-secondary">Empresa Proveedora (Opcional)</label>
                        <select name="empresa_id" id="empresa_id" class="form-select px-3">
                            <option value="">-- Ninguna (Mantenimiento Interno) --</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    {{ old('empresa_id', $mantenimiento->empresaExterna_id) == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Botones de Acción Formulario -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-sync-alt me-1.5"></i>Actualizar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection