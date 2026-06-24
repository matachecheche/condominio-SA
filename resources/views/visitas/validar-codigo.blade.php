{{-- resources/views/visitas/validar-codigo.blade.php --}}
@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-key text-primary me-2"></i>Validar Código de Visitante
                    </h4>
                    <p class="text-muted small mb-0 mt-1">Verificación de acceso al condominio</p>
                </div>
                <div class="d-flex gap-2">
                    @can('operar porteria')
                        <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shield-alt me-1"></i>Panel Guardia
                        </a>
                    @endcan
                    <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>Visitas
                    </a>
                </div>
            </div>

            {{-- Alertas de sesión --}}
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-check-circle fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-exclamation-circle fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Banner de permisos --}}
            @can('operar porteria')
                <div class="alert border-0 mb-4 rounded-3 py-2 px-3 d-flex align-items-center gap-2"
                     style="background:#e8f4fd; color:#0c5993;">
                    <i class="fas fa-shield-alt"></i>
                    <span class="small"><strong>Portería activa:</strong> Puedes validar y registrar entradas/salidas.</span>
                </div>
            @else
                <div class="alert border-0 mb-4 rounded-3 py-2 px-3 d-flex align-items-center gap-2"
                     style="background:#fff8e1; color:#7a5c00;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="small"><strong>Acceso limitado:</strong> Solo puedes validar códigos. No puedes registrar entradas.</span>
                </div>
            @endcan

            <div class="row g-4">
                {{-- Columna izquierda: Formulario principal --}}
                <div class="col-lg-7">

                    {{-- Card formulario --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                                <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:13px;">1</span>
                                Ingrese los datos del visitante
                            </h6>

                            <form id="validarCodigoForm">
                                @csrf

                                <div class="mb-4">
                                    <label for="codigo" class="form-label text-secondary small fw-semibold text-uppercase ls-1 mb-2">
                                        Código de visita (6 dígitos)
                                    </label>
                                    <div class="position-relative">
                                        <input type="text"
                                               id="codigo"
                                               name="codigo"
                                               class="form-control form-control-lg text-center font-monospace fw-bold border-2"
                                               placeholder="• • • • • •"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               autocomplete="off"
                                               style="font-size:2rem; letter-spacing:0.6rem; border-radius:12px;"
                                               required>
                                        <div id="codigoIndicator" class="position-absolute end-0 top-50 translate-middle-y pe-3" style="display:none;">
                                            <i class="fas fa-check-circle text-success fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="form-text mt-1">
                                        <i class="fas fa-info-circle text-muted me-1"></i>Código proporcionado por el residente
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="ci_visitante" class="form-label text-secondary small fw-semibold text-uppercase mb-2">
                                        Cédula de identidad del visitante
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-2 border-end-0" style="border-radius:12px 0 0 12px;">
                                            <i class="fas fa-id-card text-muted"></i>
                                        </span>
                                        <input type="text"
                                               id="ci_visitante"
                                               name="ci_visitante"
                                               class="form-control border-2 border-start-0 ps-2"
                                               placeholder="Ej: 12345678"
                                               style="border-radius:0 12px 12px 0;"
                                               required>
                                    </div>
                                    <div class="form-text mt-1">
                                        <i class="fas fa-info-circle text-muted me-1"></i>Debe coincidir exactamente con el registro
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm" id="btnValidar" style="border-radius:10px;">
                                        <i class="fas fa-search me-2"></i>Validar Código
                                    </button>
                                    <button type="button" class="btn btn-light border btn-sm fw-medium" onclick="limpiarFormulario()" style="border-radius:10px;">
                                        <i class="fas fa-eraser me-1"></i>Limpiar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Resultado: Visitante validado --}}
                    <div id="resultadoValidacion" style="display:none;">
                        <div class="card border-0 shadow-sm rounded-4 border-start border-success border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-success">Visitante validado</h6>
                                        <span class="text-muted small">Datos verificados correctamente</span>
                                    </div>
                                </div>
                                <div id="datosVisitante" class="mb-3"></div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @can('operar porteria')
                                        <button id="btnRegistrarEntrada" class="btn btn-success fw-semibold flex-grow-1" style="border-radius:10px;">
                                            <i class="fas fa-sign-in-alt me-2"></i>Registrar Entrada
                                        </button>
                                    @else
                                        <div class="alert alert-warning border-0 rounded-3 py-2 px-3 mb-0 w-100 small">
                                            <i class="fas fa-lock me-1"></i>
                                            <strong>Sin permisos:</strong> Contacta al personal de portería.
                                        </div>
                                    @endcan
                                    <button onclick="limpiarFormulario()" class="btn btn-light border fw-medium" style="border-radius:10px;">
                                        <i class="fas fa-times me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Resultado: Error --}}
                    <div id="errorValidacion" style="display:none;">
                        <div class="card border-0 shadow-sm rounded-4 border-start border-danger border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                        <i class="fas fa-times"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-danger">Error de validación</h6>
                                        <span class="text-muted small">No se pudo verificar el acceso</span>
                                    </div>
                                </div>
                                <p id="mensajeError" class="text-dark mb-3"></p>
                                <button onclick="limpiarFormulario()" class="btn btn-outline-danger btn-sm fw-medium" style="border-radius:10px;">
                                    <i class="fas fa-redo me-1"></i>Intentar nuevamente
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Columna derecha: Info y stats --}}
                <div class="col-lg-5 d-flex flex-column gap-3">

                    {{-- Instrucciones --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-clipboard-list text-info"></i>
                                Instrucciones
                            </h6>
                            <ul class="list-unstyled mb-0 small text-secondary">
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle mt-1" style="width:18px;height:18px;min-width:18px;font-size:10px;display:flex;align-items:center;justify-content:center;">1</span>
                                    Solicite el código de 6 dígitos al visitante
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle mt-1" style="width:18px;height:18px;min-width:18px;font-size:10px;display:flex;align-items:center;justify-content:center;">2</span>
                                    Verifique la cédula de identidad física
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary rounded-circle mt-1" style="width:18px;height:18px;min-width:18px;font-size:10px;display:flex;align-items:center;justify-content:center;">3</span>
                                    Los datos deben coincidir exactamente
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle mt-1" style="width:18px;height:18px;min-width:18px;font-size:10px;display:flex;align-items:center;justify-content:center;">4</span>
                                    Valide que esté dentro del horario autorizado
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Tolerancias --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-clock text-warning"></i>
                                Horarios de tolerancia
                            </h6>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#e8f4fd;">
                                    <i class="fas fa-sign-in-alt text-primary"></i>
                                    <div><strong class="text-dark">Entrada:</strong> <span class="text-secondary">30 min antes del horario</span></div>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#e8f8ee;">
                                    <i class="fas fa-sign-out-alt text-success"></i>
                                    <div><strong class="text-dark">Salida:</strong> <span class="text-secondary">Hasta fin del horario programado</span></div>
                                </div>
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:#fff8e1;">
                                    <i class="fas fa-exclamation-circle text-warning"></i>
                                    <div><strong class="text-dark">Fuera de horario:</strong> <span class="text-secondary">Validar con residente</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Accesos rápidos --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-link text-secondary"></i>
                                Accesos rápidos
                            </h6>
                            <div class="d-grid gap-2">
                                @can('operar porteria')
                                    <a href="{{ route('visitas.panel-guardia') }}" class="btn btn-outline-primary btn-sm" style="border-radius:8px;">
                                        <i class="fas fa-shield-alt me-1"></i>Panel de Guardia
                                    </a>
                                @endcan
                                <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                                    <i class="fas fa-list me-1"></i>Listado de Visitas
                                </a>
                                @can('gestionar visitas')
                                    <a href="{{ route('visitas.create') }}" class="btn btn-outline-success btn-sm" style="border-radius:8px;">
                                        <i class="fas fa-plus me-1"></i>Nueva Visita
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas del día (solo portería) --}}
                    @can('operar porteria')
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-bar text-secondary"></i>
                                    Resumen del día
                                    <span class="badge bg-light text-secondary border ms-auto fw-normal" style="font-size:11px;">
                                        {{ now()->format('d/m/Y') }}
                                    </span>
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#e8f4fd;">
                                            <div class="fw-bold text-primary fs-4">
                                                {{ \App\Models\Visita::where('estado', 'en_curso')->count() }}
                                            </div>
                                            <div class="text-secondary" style="font-size:11px;">Dentro ahora</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#fff8e1;">
                                            <div class="fw-bold text-warning fs-4">
                                                {{ \App\Models\Visita::where('estado', 'pendiente')
                                                    ->whereBetween('fecha_inicio', [now(), now()->addHours(2)])
                                                    ->count() }}
                                            </div>
                                            <div class="text-secondary" style="font-size:11px;">Próximas 2h</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#e8f8ee;">
                                            <div class="fw-bold text-success fs-4">
                                                {{ \App\Models\Visita::whereDate('hora_entrada', today())->count() }}
                                            </div>
                                            <div class="text-secondary" style="font-size:11px;">Entradas hoy</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#fde8f0;">
                                            <div class="fw-bold text-danger fs-4">
                                                {{ \App\Models\Visita::whereDate('hora_salida', today())->count() }}
                                            </div>
                                            <div class="text-secondary" style="font-size:11px;">Salidas hoy</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('validarCodigoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const codigo = document.getElementById('codigo').value;
    const ci = document.getElementById('ci_visitante').value;
    const btnValidar = document.getElementById('btnValidar');
    
    if (codigo.length !== 6) {
        mostrarError('El código debe tener exactamente 6 dígitos');
        return;
    }
    
    if (!ci.trim()) {
        mostrarError('Debe ingresar la cédula de identidad');
        return;
    }
    
    btnValidar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Validando...';
    btnValidar.disabled = true;
    
    document.getElementById('resultadoValidacion').style.display = 'none';
    document.getElementById('errorValidacion').style.display = 'none';
    
    fetch('{{ route("visitas.validar-codigo") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ codigo: codigo, ci_visitante: ci })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarDatosVisitante(data.visita);
            showNotification('Código validado correctamente', 'success');
        } else {
            mostrarError(data.message);
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión. Intente nuevamente.');
        showNotification('Error de conexión', 'error');
    })
    .finally(() => {
        btnValidar.innerHTML = '<i class="fas fa-search me-2"></i>Validar Código';
        btnValidar.disabled = false;
    });
});

function mostrarDatosVisitante(visita) {
    const datosDiv = document.getElementById('datosVisitante');
    datosDiv.innerHTML = `
        <div class="row g-2">
            <div class="col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8f9fa;">
                    <p class="mb-2 small"><span class="text-muted">Visitante</span><br><strong class="text-dark">${visita.nombre_visitante}</strong></p>
                    <p class="mb-2 small"><span class="text-muted">CI</span><br><strong class="font-monospace text-dark">${visita.ci_visitante}</strong></p>
                    <p class="mb-0 small"><span class="text-muted">Motivo</span><br><span class="text-dark">${visita.motivo}</span></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="p-3 rounded-3 h-100" style="background:#f8f9fa;">
                    <p class="mb-2 small"><span class="text-muted">Residente</span><br><strong class="text-dark">${visita.residente}</strong></p>
                    ${visita.placa_vehiculo ? `<p class="mb-2 small"><span class="text-muted">Vehículo</span><br><span class="badge bg-light text-dark border font-monospace"><i class="fas fa-car me-1 text-muted"></i>${visita.placa_vehiculo}</span></p>` : ''}
                    <p class="mb-0 small"><span class="text-muted">Código</span><br><strong class="font-monospace text-primary fs-6">${document.getElementById('codigo').value}</strong></p>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('resultadoValidacion').style.display = 'block';
    document.getElementById('resultadoValidacion').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    const btnEntrada = document.getElementById('btnRegistrarEntrada');
    if (btnEntrada) {
        btnEntrada.onclick = function() {
            if (confirm(`¿Registrar entrada de ${visita.nombre_visitante}?`)) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registrando...';
                this.disabled = true;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("visitas.entrada", ":id") }}'.replace(':id', visita.id);
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        };
    }
}

function mostrarError(mensaje) {
    document.getElementById('mensajeError').textContent = mensaje;
    document.getElementById('errorValidacion').style.display = 'block';
    document.getElementById('errorValidacion').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function limpiarFormulario() {
    document.getElementById('codigo').value = '';
    document.getElementById('ci_visitante').value = '';
    document.getElementById('resultadoValidacion').style.display = 'none';
    document.getElementById('errorValidacion').style.display = 'none';
    document.getElementById('codigoIndicator').style.display = 'none';
    document.getElementById('codigo').classList.remove('border-success');
    document.getElementById('btnValidar').classList.remove('btn-success');
    document.getElementById('btnValidar').classList.add('btn-primary');
    document.getElementById('codigo').focus();
}

function showNotification(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed shadow-sm`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 12px; border: none;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('codigo').focus();
});

document.getElementById('codigo').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    const indicator = document.getElementById('codigoIndicator');
    if (this.value.length === 6) {
        indicator.style.display = 'block';
        this.classList.add('border-success');
        document.getElementById('ci_visitante').focus();
    } else {
        indicator.style.display = 'none';
        this.classList.remove('border-success');
    }
});

document.getElementById('ci_visitante').addEventListener('input', function() {
    const codigo = document.getElementById('codigo').value;
    const btnValidar = document.getElementById('btnValidar');
    if (codigo.length === 6 && this.value.trim().length > 0) {
        btnValidar.classList.remove('btn-primary');
        btnValidar.classList.add('btn-success');
    } else {
        btnValidar.classList.remove('btn-success');
        btnValidar.classList.add('btn-primary');
    }
});

document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault();
        limpiarFormulario();
    }
    if (e.key === 'Enter' && document.activeElement.id === 'ci_visitante') {
        document.getElementById('validarCodigoForm').dispatchEvent(new Event('submit'));
    }
});
</script>
@endsection