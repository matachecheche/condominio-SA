@extends('plantilla')
@section('title', 'Editar Unidad')
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Unidad: {{ $unidad->codigo ?? '' }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}" class="text-decoration-none">Unidades</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('unidades.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-home text-primary"></i>
                <span>Modificar Datos de la Unidad Habitacional</span>
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

            <form action="{{ route('unidades.update', request()->segment(2)) }}" method="POST">
                @csrf 
                @method('PUT')
                
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Información General de la Unidad
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label fw-medium text-secondary">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" class="form-control px-3" value="{{ old('codigo', $unidad->codigo ?? '') }}" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label fw-medium text-secondary">Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select px-3" required>
                            <option value="activa" {{ old('estado', $unidad->estado ?? '') == 'activa' ? 'selected' : '' }}>Activa</option>
                            <option value="inactiva" {{ old('estado', $unidad->estado ?? '') == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label fw-medium text-secondary">Tipo de Ocupación <span class="text-danger">*</span></label>
                        <select name="tipo_ocupacion" class="form-select px-3" required>
                            <option value="Propietario" {{ old('tipo_ocupacion', $unidad->tipo_ocupacion ?? '') == 'Propietario' ? 'selected' : '' }}>Propietario</option>
                            <option value="Inquilino" {{ old('tipo_ocupacion', $unidad->tipo_ocupacion ?? '') == 'Inquilino' ? 'selected' : '' }}>Inquilino</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label fw-medium text-secondary">Capacidad Máxima <span class="text-danger">*</span></label>
                        <input type="number" name="capacidad" class="form-control px-3" value="{{ old('capacidad', $unidad->capacidad ?? 4) }}" min="1" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Personas por Unidad <span class="text-danger">*</span></label>
                        <input type="number" name="personas_por_unidad" class="form-control px-3" value="{{ old('personas_por_unidad', $unidad->personas_por_unidad ?? 1) }}" min="0" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Vehículos Permitidos <span class="text-danger">*</span></label>
                        <input type="number" name="vehiculos" class="form-control px-3" value="{{ old('vehiculos', $unidad->vehiculos ?? 0) }}" min="0" required>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <div class="form-check p-2 border rounded bg-light w-100 ps-4 ms-0 mb-0 d-flex align-items-center">
                            <input class="form-check-input cursor-pointer me-2" type="checkbox" name="tiene_mascotas" id="mascotas" value="1" 
                                   {{ old('tiene_mascotas', $unidad->tiene_mascotas ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-medium cursor-pointer" for="mascotas">¿Permite / Tiene mascotas?</label>
                        </div>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-car me-2"></i>Información del Vehículo Asignado (Opcional)
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-secondary">Placa del Vehículo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-credit-card"></i></span>
                            <input type="text" name="placa" class="form-control px-3" placeholder="Ej. ABC-123" value="{{ old('placa', $unidad->placa ?? '') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-secondary">Marca / Modelo del Vehículo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-car-side"></i></span>
                            <input type="text" name="marca" class="form-control px-3" placeholder="Ej. Toyota Corolla" value="{{ old('marca', $unidad->marca ?? '') }}">
                        </div>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-user-link me-2"></i>Responsable de la Unidad
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium text-secondary">Residente Asignado</label>
                        <select name="residente_id" class="form-select px-3">
                            <option value="">— Sin asignar / Unidad Vacía —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id', $unidad->residente_id ?? '') == $r->id ? 'selected' : '' }}>
                                {{ $r->apellido }}, {{ $r->nombre }} — (CI: {{ $r->ci }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('unidades.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Actualizar Unidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection