@extends('plantilla')

@section('title', 'Registrar Nueva Visita')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Nueva Visita</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('visitas.index') }}" class="text-decoration-none">Visitas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Crear</li>
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
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-plus-circle text-success"></i>
                <span>Programación y Prefiltro de Autorización de Acceso</span>
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

            <form action="{{ route('visitas.store') }}" method="POST">
                @csrf

                <!-- Sección: Datos del Residente Anfitrión -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-home me-2"></i>Residente Responsable (Anfitrión)
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-medium text-secondary">Residente Asignado <span class="text-danger">*</span></label>
                        
                        @can('gestionar visitas')
                            @if($residentes->count() > 0)
                                <input type="text" class="form-control bg-light px-3 fw-medium text-dark" value="{{ $residentes->first()->nombre_completo }}" readonly>
                                <input type="hidden" name="residente_id" value="{{ $residentes->first()->id }}">
                                <div class="form-text text-muted small mt-1.5">
                                    <i class="fas fa-info-circle me-1"></i>Perfil de Residente detectado automáticamente. Solo puedes autorizar visitas para tu inmueble.
                                </div>
                            @else
                                <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-0 d-flex align-items-center gap-2" role="alert">
                                    <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
                                    <div>
                                        <strong>Atención del Sistema:</strong> No se encontró tu vinculación como residente. Contacta a administración.
                                        <div class="small text-muted mt-0.5">Identificador de cuenta: <strong>{{ auth()->user()->email }}</strong></div>
                                    </div>
                                </div>
                            @endif
                        @endcan
                        
                        @can('administrar visitas')
                            <select name="residente_id" id="residente_id" class="form-select px-3" required>
                                <option value="">-- Seleccionar Residente --</option>
                                @foreach($residentes as $residente)
                                    <option value="{{ $residente->id }}" {{ old('residente_id') == $residente->id ? 'selected' : '' }}>
                                        {{ $residente->nombre_completo }} @if($residente->unidad) - {{ $residente->unidad }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small mt-1.5">
                                <i class="fas fa-users-cog me-1"></i>Modo Administrador: Tienes permisos para agendar visitas a nombre de cualquier propietario o inquilino.
                            </div>
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
                               value="{{ old('nombre_visitante') }}" placeholder="Ingresa nombres y apellidos" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="ci_visitante" class="form-label fw-medium text-secondary">Documento de Identidad (CI) <span class="text-danger">*</span></label>
                        <input type="text" name="ci_visitante" id="ci_visitante" class="form-control px-3 font-monospace" 
                               value="{{ old('ci_visitante') }}" placeholder="Ej: 8765432" required>
                    </div>
                    <div class="col-12">
                        <label for="motivo" class="form-label fw-medium text-secondary">Motivo de la Visita <span class="text-danger">*</span></label>
                        <select name="motivo" id="motivo" class="form-select px-3" required>
                            <option value="">-- Seleccionar Motivo --</option>
                            <option value="Visita familiar" {{ old('motivo') == 'Visita familiar' ? 'selected' : '' }}>👨‍👩‍👧‍👦 Visita familiar</option>
                            <option value="Servicio técnico" {{ old('motivo') == 'Servicio técnico' ? 'selected' : '' }}>🔧 Servicio técnico</option>
                            <option value="Delivery" {{ old('motivo') == 'Delivery' ? 'selected' : '' }}>📦 Delivery</option>
                            <option value="Visita social" {{ old('motivo') == 'Visita social' ? 'selected' : '' }}>👋 Visita social</option>
                            <option value="Entrega de documentos" {{ old('motivo') == 'Entrega de documentos' ? 'selected' : '' }}>📄 Entrega de documentos</option>
                            <option value="Reunión de trabajo" {{ old('motivo') == 'Reunión de trabajo' ? 'selected' : '' }}>💼 Reunión de trabajo</option>
                            <option value="Otro" {{ old('motivo') == 'Otro' ? 'selected' : '' }}>❓ Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Sección: Logística de Tiempos y Transporte -->
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-clock me-2"></i>Vigencia Horaria y Transporte
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="fecha_inicio" class="form-label fw-medium text-secondary">Fecha y Hora de Inicio <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control px-3"
                               value="{{ old('fecha_inicio') }}" @can('gestionar visitas') min="{{ date('Y-m-d\TH:i') }}" @endcan required>
                        <div class="form-text text-muted small mt-1">
                            @can('gestionar visitas') <i class="fas fa-lock me-1"></i>Solo se permiten registros inmediatos o para fechas a futuro. @endcan
                            @can('administrar visitas') <i class="fas fa-info-circle me-1"></i>Permiso administrativo activo: Puedes registrar accesos retroactivos. @endcan
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="fecha_fin" class="form-label fw-medium text-secondary">Fecha y Hora de Fin / Expiración <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control px-3" value="{{ old('fecha_fin') }}" required>
                        <div class="form-text text-muted small mt-1"><i class="fas fa-history me-1"></i>El código caducará automáticamente superado este plazo.</div>
                    </div>
                    <div class="col-12">
                        <label for="placa_vehiculo" class="form-label fw-medium text-secondary">Placa del Vehículo (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-car"></i></span>
                            <input type="text" name="placa_vehiculo" id="placa_vehiculo" class="form-control px-2 font-monospace text-uppercase" 
                                   value="{{ old('placa_vehiculo') }}" placeholder="Ej: 4567-XYZ" maxlength="10">
                        </div>
                        <div class="form-text text-muted small mt-1">Llene este parámetro únicamente si el visitante ingresará a los parqueos interiores.</div>
                    </div>
                </div>

                <!-- Bloques Informativos Contextuales de Roles -->
                @can('gestionar visitas')
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                    <i class="fas fa-lightbulb text-info mt-1 fs-5"></i>
                    <div>
                        <span class="fw-bold d-block mb-1">Guía rápida de control de acceso:</span>
                        <ul class="mb-0 small ps-3">
                            <li>Al guardar, el sistema generará un **código de seguridad único de 6 dígitos**.</li>
                            <li>Deberás compartir dicho código con el visitante para agilizar su ingreso en portería.</li>
                            <li>Existe una tolerancia automática de ingreso de hasta **30 minutos antes** de la hora marcada.</li>
                        </ul>
                    </div>
                </div>
                @endcan

                @can('administrar visitas')
                <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                    <i class="fas fa-shield-alt text-success mt-1 fs-5"></i>
                    <div>
                        <span class="fw-bold d-block mb-1">Inyección de Privilegios Administrativos:</span>
                        <p class="mb-0 small text-secondary">Estás operando bajo el rol global de gestión. Los límites temporales regulares del módulo de residentes se encuentran omitidos.</p>
                    </div>
                </div>
                @endcan

                <!-- Botones de Acción Formulario -->
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4 pt-3 border-top border-light">
                    <div class="d-flex gap-2 order-2 order-sm-1">
                        <button type="submit" class="btn btn-success px-4 shadow-sm fw-medium">
                            <i class="fas fa-save me-1.5"></i>Registrar Visita
                        </button>
                        <a href="{{ route('visitas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    </div>
                    @can('operar porteria')
                    <div class="order-1 order-sm-2">
                        <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-info px-3 w-100 text-dark fw-medium">
                            <i class="fas fa-shield-alt me-1.5"></i>Panel Guardia
                        </a>
                    </div>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica e inyección de fecha mínima automática
    @can('gestionar visitas')
        @if($residentes->count() == 0)
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select, button[type="submit"]');
            inputs.forEach(input => {
                if (input.type !== 'button' && !input.classList.contains('btn-secondary') && !input.getAttribute('href')) {
                    input.disabled = true;
                }
            });
        @endif
    @endcan

    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    fechaInicio.addEventListener('change', function() {
        const valueInicio = this.value;
        if (valueInicio) {
            const inicio = new Date(valueInicio);
            inicio.setHours(inicio.getHours() + 1); // Agrega 1 hora por defecto
            fechaFin.min = valueInicio;
            
            if (!fechaFin.value || new Date(fechaFin.value) <= new Date(valueInicio)) {
                fechaFin.value = inicio.toISOString().slice(0, 16);
            }
        }
    });
    
    if (fechaInicio.value) {
        fechaInicio.dispatchEvent(new Event('change'));
    }
    
    // Formateo y sanitización de placa vehicular en tiempo real
    const placaInput = document.getElementById('placa_vehiculo');
    placaInput.addEventListener('input', function() {
        let valor = this.value.toUpperCase();
        valor = valor.replace(/[^A-Z0-9-]/g, ''); // Deja solo letras, números y guion medio
        this.value = valor;
    });

    // Validación preventiva en submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!fechaInicio.value || !fechaFin.value) return;
        
        const fInicio = new Date(fechaInicio.value);
        const fFin = new Date(fechaFin.value);
        
        if (fFin <= fInicio) {
            e.preventDefault();
            alert('La fecha y hora de fin debe ser estrictamente posterior a la de inicio.');
            return false;
        }
        
        const diferenciaHoras = (fFin - fInicio) / (1000 * 60 * 60);
        if (diferenciaHoras > 24) {
            if (!confirm('La visita programada excede las 24 horas de vigencia consecutivas. ¿Deseas guardar de todos modos?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endsection