@extends('plantilla')

@section('title', 'Panel de Control de Guardia')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado del Módulo de Operaciones -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">
                <i class="fas fa-shield-alt text-primary me-1"></i> Panel de Control Portería
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Terminal de Guardia</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
            <span id="countdownBadge" class="badge bg-light text-dark border border-secondary-subtle px-3 py-2 font-monospace fs-7 shadow-sm">
                <i class="fas fa-sync-alt fa-spin text-primary me-1"></i> Sincronizando...
            </span>
            <a href="{{ route('visitas.index') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                <i class="fas fa-list"></i>
                <span>Ver Historial</span>
            </a>
        </div>
    </div>

    <!-- Mensajes de Estado Flash -->
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-check-circle text-success fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Formulario Crítico de Validación de Códigos -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-primary border-4">
        <div class="card-header bg-white border-bottom border-light py-3">
            <div class="d-flex align-items-center gap-2 text-primary fw-semibold">
                <i class="fas fa-qrcode"></i>
                <span>Módulo de Validación Inmediata</span>
            </div>
        </div>
        <div class="card-body p-4 bg-light-subtle rounded-bottom-3">
            <form id="validarCodigoForm" class="m-0">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="codigo" class="form-label fw-medium text-secondary small">Código de Seguridad (6 Dígitos)</label>
                        <input type="text" id="codigo" name="codigo" class="form-control form-control-lg text-center font-monospace fw-bold tracking-widest text-primary fs-4 shadow-sm"
                               placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="ci_visitante" class="form-label fw-medium text-secondary small">Documento de Identidad del Visitante (CI)</label>
                        <input type="text" id="ci_visitante" name="ci_visitante" class="form-control form-control-lg px-3 shadow-sm font-monospace text-uppercase"
                               placeholder="Ej: 1234567 LP" required autocomplete="off">
                    </div>
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-shield-check"></i> Validar Acceso
                        </button>
                    </div>
                </div>
            </form>

            <!-- Bloque Desplegable de Resultados de Validación AJAX -->
            <div id="resultadoValidacion" class="mt-4" style="display: none;">
                <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-success mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-id-card-alt"></i> Datos Verificados del Registro
                        </h6>
                        <div id="datosVisitante" class="text-secondary mb-3 fs-7"></div>
                        <div class="d-flex gap-2">
                            <button id="btnRegistrarEntrada" class="btn btn-success px-4 shadow-sm fw-medium btn-sm">
                                <i class="fas fa-sign-in-alt me-1.5"></i>Autorizar Ingreso
                            </button>
                            <button id="btnCancelar" class="btn btn-light border px-3 btn-sm text-muted">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paneles de Monitoreo Operativo de Accesos -->
    <div class="row g-4">
        <!-- Columna: Visitantes Dentro del Condominio -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom border-light py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 fw-semibold text-info">
                        <i class="fas fa-user-check"></i>
                        <span>En Curso / Adentro del Predio</span>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">{{ $visitasEnCurso->count() }}</span>
                </div>
                <div class="card-body p-3 overflow-auto" style="max-height: 420px;">
                    @forelse($visitasEnCurso as $visita)
                        <div class="card bg-light border-0 rounded-3 mb-3 hover-shadow transition-sm">
                            <div class="card-body p-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-sm-8">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-info p-1.5 rounded-circle me-2"><i class="fas fa-user text-white fs-8"></i></span>
                                            <span class="fw-bold text-dark fs-6">{{ $visita->nombre_visitante }}</span>
                                        </div>
                                        <div class="text-secondary row g-1 fs-7 ps-4">
                                            <div class="col-12"><strong>CI:</strong> <span class="font-monospace">{{ $visita->ci_visitante }}</span></div>
                                            <div class="col-12 text-truncate"><strong>Destino:</strong> {{ $visita->residente->nombre_completo ?? 'N/D' }}</div>
                                            <div class="col-12 text-success fw-medium">
                                                <i class="fas fa-sign-in-alt me-1"></i>Ingreso: {{ $visita->hora_entrada ? $visita->hora_entrada->format('H:i') : 'No Marcado' }}
                                            </div>
                                            @if($visita->placa_vehiculo)
                                                <div class="col-12"><span class="badge bg-white text-dark border border-secondary-subtle font-monospace px-2 mt-1"><i class="fas fa-car me-1 text-muted"></i>{{ $visita->placa_vehiculo }}</span></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 text-sm-end d-flex d-sm-block gap-1 mt-2 mt-sm-0">
                                        <form action="{{ route('visitas.salida', $visita) }}" method="POST" class="d-block w-100 mb-1 m-0">
                                            @csrf
                                            <button class="btn btn-primary btn-sm w-100 fw-medium shadow-sm" type="submit"
                                                    onclick="return confirm('¿Confirmar salida para {{ $visita->nombre_visitante }}?')">
                                                <i class="fas fa-sign-out-alt me-1"></i>Salida
                                            </button>
                                        </form>
                                        <a href="{{ route('visitas.show', $visita) }}" class="btn btn-outline-info btn-sm w-100">
                                            <i class="fas fa-eye"></i> Detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fs-2 d-block mb-2 opacity-25"></i>
                            <p class="mb-0 fs-7 fw-medium">No se registran visitas activas dentro del predio.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna: Visitas Pendientes Inmediatas (Ventana de 2 Horas) -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom border-light py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 fw-semibold text-warning">
                        <i class="fas fa-hourglass-start"></i>
                        <span>Arribos Previstos (Próximas 2 Horas)</span>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold fs-7">{{ $visitasPendientes->count() }}</span>
                </div>
                <div class="card-body p-3 overflow-auto" style="max-height: 420px;">
                    @forelse($visitasPendientes as $visita)
                        <div class="card bg-light border-0 rounded-3 mb-3 border-start border-warning border-3">
                            <div class="card-body p-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-12 col-sm-8">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-warning text-dark p-1.5 rounded-circle me-2"><i class="fas fa-clock fs-8"></i></span>
                                            <span class="fw-bold text-dark fs-6">{{ $visita->nombre_visitante }}</span>
                                        </div>
                                        <div class="text-secondary row g-1 fs-7 ps-4">
                                            <div class="col-12"><strong>CI:</strong> <span class="font-monospace">{{ $visita->ci_visitante }}</span></div>
                                            <div class="col-12 text-truncate"><strong>Anfitrión:</strong> {{ $visita->residente->nombre_completo ?? 'N/D' }}</div>
                                            <div class="col-12 text-primary font-monospace fw-bold">Code: [ {{ $visita->codigo }} ]</div>
                                            <div class="col-12 text-warning fw-medium">
                                                <i class="fas fa-business-time me-1"></i>Rango: {{ $visita->fecha_inicio->format('H:i') }} a {{ $visita->fecha_fin->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 text-sm-end d-flex d-sm-block gap-1 mt-2 mt-sm-0">
                                        <form action="{{ route('visitas.entrada', $visita) }}" method="POST" class="d-block w-100 mb-1 m-0">
                                            @csrf
                                            <button class="btn btn-success btn-sm w-100 fw-medium shadow-sm" type="submit"
                                                    onclick="return confirm('¿Proceder con el registro de ingreso para {{ $visita->nombre_visitante }}?')">
                                                <i class="fas fa-sign-in-alt me-1"></i>Entrada
                                            </button>
                                        </form>
                                        <a href="{{ route('visitas.show', $visita) }}" class="btn btn-outline-info btn-sm w-100">
                                            <i class="fas fa-eye"></i> Ver Plan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-check fs-2 d-block mb-2 opacity-25"></i>
                            <p class="mb-0 fs-7 fw-medium">Sin programaciones agendadas para el rango horario actual.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bloque de Programaciones a Futuro Fuera de Rango -->
    @if($visitasPendientesProximas->count() > 0)
    <div class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-header bg-white border-bottom border-light py-3">
            <div class="d-flex align-items-center gap-2 fw-semibold text-secondary">
                <i class="fas fa-calendar-alt"></i>
                <span>Agendados a Mediano Plazo (Aún fuera de rango de tolerancia de ingreso)</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-secondary small">
                    <thead class="table-light text-uppercase fs-8">
                        <tr>
                            <th class="py-2 px-4">Visitante</th>
                            <th class="py-2">Residente Vinculado</th>
                            <th class="py-2">Código Token</th>
                            <th class="py-2">Inicio Programado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visitasPendientesProximas as $visita)
                        <tr>
                            <td class="py-2 px-4 fw-bold text-dark">{{ $visita->nombre_visitante }}</td>
                            <td class="py-2">{{ $visita->residente->nombre_completo ?? 'N/D' }}</td>
                            <td class="py-2 font-monospace fw-bold text-primary">{{ $visita->codigo }}</td>
                            <td class="py-2 text-dark">{{ $visita->fecha_inicio->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Bloque Inferior: Indicadores de Rendimiento y Tráfico Diario -->
    <div class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-secondary"></i> Métrica de Tránsito y Accesos Consolidados (Hoy)
            </h6>
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-primary rounded-3 text-white shadow-sm">
                        <i class="fas fa-users fs-4 mb-1"></i>
                        <h3 class="fw-bold mb-0">{{ $visitasEnCurso->count() }}</h3>
                        <div class="fs-8 opacity-75">Dentro Ahora</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-warning rounded-3 text-dark shadow-sm">
                        <i class="fas fa-hourglass-half fs-4 mb-1"></i>
                        <h3 class="fw-bold mb-0">{{ $visitasPendientes->count() }}</h3>
                        <div class="fs-8 text-dark-50">Por Arribar</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-success rounded-3 text-white shadow-sm">
                        <i class="fas fa-sign-in-alt fs-4 mb-1"></i>
                        <h3 class="fw-bold mb-0">{{ \App\Models\Visita::whereDate('hora_entrada', today())->count() }}</h3>
                        <div class="fs-8 opacity-75">Ingresos Totales</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-info rounded-3 text-white shadow-sm">
                        <i class="fas fa-sign-out-alt fs-4 mb-1"></i>
                        <h3 class="fw-bold mb-0">{{ \App\Models\Visita::whereDate('hora_salida', today())->count() }}</h3>
                        <div class="fs-8 opacity-75">Salidas Totales</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Interacción AJAX y Sincronización Automática de Terminal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sanitización y formateo automático de entradas del formulario
    const inputCodigo = document.getElementById('codigo');
    inputCodigo.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Envío asíncrono y verificación del Token del Visitante
    document.getElementById('validarCodigoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = inputCodigo.value;
        const ci = document.getElementById('ci_visitante').value;
        
        if (codigo.length !== 6) {
            showAlert('El código token debe poseer exactamente 6 dígitos numéricos.', 'warning');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i> Verificando...';
        submitBtn.disabled = true;
        
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
                showAlert('Código de acceso validado y autorizado de forma correcta.', 'success');
            } else {
                showAlert(data.message, 'danger');
                limpiarFormulario();
            }
        })
        .catch(error => {
            console.error('Error Terminal:', error);
            showAlert('Error crítico de red al validar con el servidor central.', 'danger');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    function mostrarDatosVisitante(visita) {
        const datosDiv = document.getElementById('datosVisitante');
        datosDiv.innerHTML = `
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="mb-1"><strong><i class="fas fa-user me-1 text-muted"></i> Nombre:</strong> <span class="text-dark font-medium">${visita.nombre_visitante}</span></div>
                    <div class="mb-1"><strong><i class="fas fa-id-card me-1 text-muted"></i> Documento CI:</strong> <span class="text-dark font-monospace">${visita.ci_visitante}</span></div>
                    <div><strong><i class="fas fa-home me-1 text-muted"></i> Destino Inmueble:</strong> <span class="text-dark">${visita.residente}</span></div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-1"><strong><i class="fas fa-comment me-1 text-muted"></i> Motivo Declarado:</strong> <span>${visita.motivo}</span></div>
                    ${visita.placa_vehiculo ? `<div><strong><i class="fas fa-car me-1 text-muted"></i> Placa Vehicular:</strong> <span class="badge bg-light text-dark border font-monospace">${visita.placa_vehiculo}</span></div>` : ''}
                </div>
            </div>
        `;
        
        document.getElementById('resultadoValidacion').style.display = 'block';
        
        document.getElementById('btnRegistrarEntrada').onclick = function() {
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
        };
    }

    function limpiarFormulario() {
        inputCodigo.value = '';
        document.getElementById('ci_visitante').value = '';
        document.getElementById('resultadoValidacion').style.display = 'none';
    }

    document.getElementById('btnCancelar').addEventListener('click', limpiarFormulario);

    // Render de Notificaciones Toast Flotantes limpias
    function showAlert(message, type) {
        const icon = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} shadow-lg border-0 rounded-3 position-fixed d-flex align-items-center gap-2`;
        alertDiv.style.cssText = 'top: 25px; right: 25px; z-index: 1060; min-width: 320px; animation: slideIn 0.3s ease-out;';
        alertDiv.innerHTML = `
            <i class="fas fa-${icon} fs-5"></i>
            <div class="small fw-medium me-4">${message}</div>
            <button type="button" class="btn-close ms-auto small" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 5500);
    }

    // Lógica del Cronómetro de Autorrefresco de Datos de Portería
    let countdown = 30;
    const badgeCountdown = document.getElementById('countdownBadge');
    
    const updateCountdown = () => {
        if (badgeCountdown) {
            badgeCountdown.innerHTML = `<i class="fas fa-clock text-primary me-1"></i> Actualiza en: ${countdown}s`;
        }
        countdown--;
        if (countdown < 0) {
            location.reload();
        }
    };
    
    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>

<style>
@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}
.transition-sm {
    transition: all 0.2s ease-in-out;
}
.fs-8 { font-size: 0.75rem; }
.tracking-widest { tracking-widest: 0.25rem; }
</style>
@endsection