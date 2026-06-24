@extends('plantilla')

@section('title', 'Nueva Propiedad')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Nueva Propiedad</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('propiedades.index') }}" class="text-decoration-none">Propiedades</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nueva</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-building text-primary"></i>
                <span>Registrar Datos del Inmueble</span>
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

            <form action="{{ route('propiedades.store') }}" method="POST">
                @csrf
                
                <!-- SECCIÓN 1: ESPECIFICACIONES -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Características de la Propiedad
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="codigo" class="form-label fw-medium text-secondary">Código de Propiedad <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" id="codigo" class="form-control px-3" placeholder="Ej: APTO-101" value="{{ old('codigo') }}" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Tipo de Propiedad <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select px-3" required>
                            <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>— Seleccionar —</option>
                            <option value="Apartamento" {{ old('tipo') == 'Apartamento' ? 'selected' : '' }}>Apartamento</option>
                            <option value="Casa" {{ old('tipo') == 'Casa' ? 'selected' : '' }}>Casa</option>
                            <option value="Local" {{ old('tipo') == 'Local' ? 'selected' : '' }}>Local</option>
                            <option value="Oficina" {{ old('tipo') == 'Oficina' ? 'selected' : '' }}>Oficina</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label fw-medium text-secondary">Estado Operativo <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select px-3" required>
                            <option value="" disabled {{ old('estado') ? '' : 'selected' }}>— Seleccionar —</option>
                            <option value="disponible" {{ old('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="ocupada" {{ old('estado') == 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                            <option value="activa" {{ old('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="ubicacion" class="form-label fw-medium text-secondary">Ubicación / Dirección Exacta <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control px-3" placeholder="Ej: Calle Principal #123, Piso 2" value="{{ old('ubicacion') }}" required>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: ASIGNACIÓN Y ADICIONALES -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-user-link me-2"></i>Asignación y Detalles del Registro
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium text-secondary">Vincular Residente Titular</label>
                        <select name="residente_id" class="form-select px-3">
                            <option value="">— Sin asignar / Propiedad Vacía —</option>
                            @foreach($residentes as $residente)
                                <option value="{{ $residente->id }}" {{ old('residente_id') == $residente->id ? 'selected' : '' }}>
                                    {{ $residente->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-medium text-secondary">Descripción o Notas Adicionales</label>
                        <textarea name="descripcion" id="descripcion" class="form-control px-3 py-2" rows="4" placeholder="Especificaciones adicionales sobre la propiedad, estado de entrega, etc...">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <!-- Botones de Acción inferior -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('propiedades.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Guardar Propiedad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection