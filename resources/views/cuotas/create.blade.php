@extends('plantilla')

@section('title', 'Emitir Nueva Cuota')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Emitir Nueva Cuota</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cuotas.index') }}" class="text-decoration-none">Cuotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Emitir</li>
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
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Configuración de Cargos y Facturación</span>
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

            <form action="{{ route('cuotas.store') }}" method="POST">
                @csrf
                
                <!-- SECCIÓN 1: DATOS DE LA CUOTA -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-info-circle me-2"></i>Detalles de la Obligación
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="titulo" class="form-label fw-medium text-secondary">Título del Cargo <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control px-3" placeholder="Ej: Expensas Ordinarias" value="{{ old('titulo') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="monto" class="form-label fw-medium text-secondary">Monto ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">$</span>
                            <input type="number" step="0.01" name="monto" id="monto" class="form-control px-3" placeholder="0.00" value="{{ old('monto') }}" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-medium text-secondary">Descripción Breve <span class="text-danger">*</span></label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control px-3" placeholder="Ej: Cobro correspondiente al mantenimiento del mes en curso" value="{{ old('descripcion') }}" required>
                    </div>
                    <div class="col-12 col-sm-6 text-secondary">
                        <label class="form-label fw-medium">Tipo de Cuota <span class="text-danger">*</span></label>
                        <select name="tipo_cuota_id" class="form-select px-3" required>
                            <option value="">— Selecciona un tipo —</option>
                            @foreach ($tiposCuotas as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_cuota_id') == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre }} ({{ ucfirst($tipo->frecuencia) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 text-secondary">
                        <label class="form-label fw-medium">Estado Inicial <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select px-3">
                            <option value="pendiente" {{ old('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="activa" {{ old('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                            <option value="cancelada" {{ old('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="fecha_emision" class="form-label fw-medium text-secondary">Fecha de Emisión <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_emision" id="fecha_emision" class="form-control px-3" value="{{ old('fecha_emision') }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="fecha_vencimiento" class="form-label fw-medium text-secondary">Fecha de Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control px-3" value="{{ old('fecha_vencimiento') }}" required>
                    </div>
                </div>

                <!-- SECCIÓN 2: DESTINATARIOS -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-users me-2"></i>Segmentación de Destinatarios
                </h6>
                <div class="bg-light p-3 border rounded rounded-3 mb-4">
                    <label class="form-label fw-semibold text-dark mb-2.5">Emitir cuota a:</label>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <div class="form-check ms-1">
                            <input class="form-check-input cursor-pointer" type="radio" name="destino" value="todos" id="opcionTodos" {{ old('destino', 'todos') == 'todos' ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer text-dark fw-medium" for="opcionTodos">Todos los residentes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input cursor-pointer" type="radio" name="destino" value="grupo" id="opcionGrupo" {{ old('destino') == 'grupo' ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer text-dark fw-medium" for="opcionGrupo">Grupo por Rol</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input cursor-pointer" type="radio" name="destino" value="personalizado" id="opcionPersonalizado" {{ old('destino') == 'personalizado' ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer text-dark fw-medium" for="opcionPersonalizado">Residente específico</label>
                        </div>
                    </div>

                    <!-- SELECT de grupo (Rol) -->
                    <div class="mt-3 pt-2 border-top border-light-subtle" id="grupoContainer" style="display: none;">
                        <label for="grupo_rol" class="form-label fw-medium text-secondary">Selecciona el Grupo / Rol</label>
                        <select name="grupo_rol" id="grupo_rol" class="form-select px-3">
                            <option value="">— Seleccionar grupo —</option>
                            @foreach ($roles as $rol)
                            <option value="{{ $rol }}" {{ old('grupo_rol') == $rol ? 'selected' : '' }}>{{ ucfirst($rol) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SELECT de residente individual -->
                    <div class="mt-3 pt-2 border-top border-light-subtle" id="residenteContainer" style="display: none;">
                        <label for="residente_id" class="form-label fw-medium text-secondary">Selecciona el Residente Destinatario</label>
                        <select name="residente_id" id="residente_id" class="form-select px-3">
                            <option value="">— Seleccionar un residente —</option>
                            @foreach ($residentes as $residente)
                            <option value="{{ $residente->id }}" {{ old('residente_id') == $residente->id ? 'selected' : '' }}>
                                {{ $residente->nombre }} {{ $residente->apellido }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 3: OBSERVACIONES -->
                <div class="mb-2">
                    <label for="observacion" class="form-label fw-medium text-secondary">Observación (Opcional)</label>
                    <textarea name="observacion" id="observacion" class="form-control px-3 py-2" rows="2" placeholder="Notas internas o aclaraciones sobre el proceso de emisión...">{{ old('observacion') }}</textarea>
                </div>

                <!-- Botones de Acción inferior -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('cuotas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Guardar Cuota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="destino"]');
        const grupoContainer = document.getElementById('grupoContainer');
        const residenteContainer = document.getElementById('residenteContainer');

        function updateVisibility() {
            const checkedRadio = document.querySelector('input[name="destino"]:checked');
            const selected = checkedRadio ? checkedRadio.value : 'todos';
            grupoContainer.style.display = selected === 'grupo' ? 'block' : 'none';
            residenteContainer.style.display = selected === 'personalizado' ? 'block' : 'none';
        }

        radios.forEach(radio => {
            radio.addEventListener('change', updateVisibility);
        });

        updateVisibility();
    });
</script>
@endsection