@extends('plantilla')

@section('title', 'Editar Visita - ' . $visita->codigo)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Programación de Visita</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('visitas.index') }}" class="text-decoration-none">Visitas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver a la Lista</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                    <i class="fas fa-edit text-primary"></i>
                    <span>Modificar Autorización de Ingreso</span>
                </div>
                <span class="badge bg-primary px-3 py-2 rounded-pill font-monospace fs-7 shadow-sm">CÓDIGO: {{ $visita->codigo }}</span>
            </div>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <!-- Alerta Preventiva de Proximidad Horaria -->
            @if(now()->diffInHours($visita->fecha_inicio) < 2 && now() < $visita->fecha_inicio)
                <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-history text-warning fs-5"></i>
                    <div><strong>Atención de Operación:</strong> Esta visita está programada para iniciar en menos de 2 horas. Modifique con precaución.</div>
                </div>
            @endif

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

            <form action="{{ route('visitas.update', $visita) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Sección: Datos del Residente Anfitrión -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-home me-2"></i>Residente Responsable (Anfitrión)
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="residente_id" class="form-label fw-medium text-secondary">Seleccionar Residente <span class="text-danger">*</span></label>
                        <select name="residente_id" id="residente_id" class="form-select px-3" required
                                @can('gestionar visitas') @if($residentes->count() == 1) readonly style="pointer-events: none; background-color: #e9ecef;" @endif @endcan>
                            <option value="">-- Seleccionar Residente --</option>
                            @foreach($residentes as $residente)
                                <option value="{{ $residente->id }}"
                                    {{ old('residente_id', $visita->residente_id) == $residente->id ? 'selected' : '' }}>
                                    {{ $residente->nombre_completo }} @if($residente->unidad) (Unidad: {{ $residente->unidad }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @can('gestionar visitas')
                            @if($residentes->count() == 1)
                                <div class="form-text text-muted small"><i class="fas fa-info-circle me-1"></i>Perfil de Residente bloqueado. Solo puedes gestionar tus visitas asignadas.</div>
                            @endif
                        @endcan
                    </div>
                </div>

                <!-- Sección: Información del Visitante -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-id-card me-2"></i>Información del Visitante
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="nombre_visitante" class="form-label fw-medium text-secondary">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_visitante" id="nombre_visitante" class="form-control px-3"
                               value="{{ old('nombre_visitante', $visita->nombre_visitante) }}" required placeholder="Ej: Carlos Mendoza">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="ci_visitante" class="form-label fw-medium text-secondary">Documento de Identidad (CI) <span class="text-danger">*</span></label>
                        <input type="text" name="ci_visitante" id="ci_visitante" class="form-control px-3 font-monospace"
                               value="{{ old('ci_visitante', $visita->ci_visitante) }}" required placeholder="Ej: 8765432">
                    </div>
                    <div class="col-12">
                        <label for="motivo" class="form-label fw-medium text-secondary">Motivo de Acceso / Justificación <span class="text-danger">*</span></label>
                        <select name="motivo" id="motivo" class="form-select px-3" required>
                            <option value="">-- Seleccionar Motivo --</option>
                            <option value="Visita familiar" {{ old('motivo', $visita->motivo) == 'Visita familiar' ? 'selected' : '' }}>👨‍👩‍👧‍👦 Visita familiar</option>
                            <option value="Servicio técnico" {{ old('motivo', $visita->motivo) == 'Servicio técnico' ? 'selected' : '' }}>🔧 Servicio técnico</option>
                            <option value="Delivery" {{ old('motivo', $visita->motivo) == 'Delivery' ? 'selected' : '' }}>📦 Delivery</option>
                            <option value="Visita social" {{ old('motivo', $visita->motivo) == 'Visita social' ? 'selected' : '' }}>👋 Visita social</option>
                            <option value="Entrega de documentos" {{ old('motivo', $visita->motivo) == 'Entrega de documentos' ? 'selected' : '' }}>📄 Entrega de documentos</option>
                            <option value="Reunión de trabajo" {{ old('motivo', $visita->motivo) == 'Reunión de trabajo' ? 'selected' : '' }}>💼 Reunión de trabajo</option>
                            <option value="Otro" {{ old('motivo', $visita->motivo) == 'Otro' ? 'selected' : '' }}>❓ Otro</option>
                            
                            @php
                                $motivosPredefinidos = ['Visita familiar', 'Servicio técnico', 'Delivery', 'Visita social', 'Entrega de documentos', 'Reunión de trabajo', 'Otro'];
                                $motivoActual = old('motivo', $visita->motivo);
                            @endphp
                            @if($motivoActual && !in_array($motivoActual, $motivosPredefinidos))
                                <option value="{{ $motivoActual }}" selected>{{ $motivoActual }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Sección: Logística de Tiempos y Transporte -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-clock me-2"></i>Vigencia Horaria y Transporte
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="fecha_inicio" class="form-label fw-medium text-secondary">Fecha y Hora Autorizada de Entrada <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control px-3"
                               value="{{ old('fecha_inicio', $visita->fecha_inicio->format('Y-m-d\TH:i')) }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="fecha_fin" class="form-label fw-medium text-secondary">Fecha y Hora Límite de Salida <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control px-3"
                               value="{{ old('fecha_fin', $visita->fecha_fin->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-12">
                        <label for="placa_vehiculo" class="form-label fw-medium text-secondary">Placa Vehicular (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-car"></i></span>
                            <input type="text" name="placa_vehiculo" id="placa_vehiculo" class="form-control px-2 font-monospace text-uppercase"
                                   value="{{ old('placa_vehiculo', $visita->placa_vehiculo) }}" placeholder="Ej: 4567-XYZ">
                        </div>
                    </div>
                </div>

                <!-- Bloque Informativo del Estado Actual de la Operación -->
                <div class="card bg-light border-0 rounded-3 mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-semibold small">
                            <i class="fas fa-info-circle text-primary"></i>
                            <span>Metadatos Internos de Auditoría</span>
                        </div>
                        <div class="row g-2 text-secondary small">
                            <div class="col-6 col-sm-4">
                                <strong>Token Único:</strong> <span class="text-dark font-monospace">{{ $visita->codigo }}</span>
                            </div>
                            <div class="col-6 col-sm-4">
                                <strong>Estado Actual:</strong> 
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5 rounded-pill fw-medium">
                                    {{ ucfirst($visita->estado) }}
                                </span>
                            </div>
                            <div class="col-12 col-sm-4">
                                <strong>Registro Base:</strong> {{ $visita->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción Formulario -->
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4 pt-3 border-top border-light">
                    <div class="d-flex gap-2 order-2 order-sm-1">
                        <button type="submit" class="btn btn-success px-4 shadow-sm fw-medium">
                            <i class="fas fa-save me-1.5"></i>Actualizar Visita
                        </button>
                        <a href="{{ route('visitas.show', $visita) }}" class="btn btn-light border px-4">Cancelar</a>
                    </div>
                    <div class="order-1 order-sm-2">
                        <a href="{{ route('visitas.index') }}" class="btn btn-outline-primary px-3 w-100">
                            <i class="fas fa-list me-1.5"></i>Lista Completa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lógica Dinámica de Frontend para el Control de Fechas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
        if (fechaFin.value && fechaFin.value <= this.value) {
            const inicio = new Date(this.value);
            inicio.setHours(inicio.getHours() + 1); // Desfase preventivo de 1 hora
            fechaFin.value = inicio.toISOString().slice(0, 16);
        }
    });
});
</script>
@endsection