@extends('plantilla')

@section('title', 'Editar Reserva')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Reserva</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}" class="text-decoration-none">Reservas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver a Reservas</span>
        </a>
    </div>

    <!-- Card Principal del Formulario -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-calendar-edit text-primary"></i>
                <span>Modificar Programación de Espacio Común</span>
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

            <form action="{{ route('reservas.update', $reserva->id) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-clock me-2"></i>Asignación de Espacio y Tiempo
                </h6>
                
                <div class="row g-3 mb-4">
                    <!-- Selección de Área Común -->
                    <div class="col-12 col-md-6">
                        <label for="area_comun_id" class="form-label fw-medium text-secondary">Área Común <span class="text-danger">*</span></label>
                        <select name="area_comun_id" id="area_comun_id" class="form-select px-3" required>
                            <option value="">-- Selecciona un área común --</option>
                            @foreach ($areasComunes as $area)
                                <option value="{{ $area->id }}" data-monto="{{ $area->monto }}"
                                    {{ old('area_comun_id', $reserva->area_comun_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->nombre }} (Bs {{ number_format($area->monto, 2) }}/HR)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Selección de Fecha -->
                    <div class="col-12 col-md-6">
                        <label for="fecha" class="form-label fw-medium text-secondary">Fecha de Reserva <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="fecha" class="form-control px-3"
                            value="{{ old('fecha', $reserva->fecha->format('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Hora de Inicio -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="hora_inicio" class="form-label fw-medium text-secondary">Hora Inicio <span class="text-danger">*</span></label>
                        <select name="hora_inicio" id="hora_inicio" class="form-select px-3" required>
                            <option value="">-- Selecciona hora inicio --</option>
                        </select>
                    </div>

                    <!-- Hora de Finalización -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="hora_fin" class="form-label fw-medium text-secondary">Hora Fin <span class="text-danger">*</span></label>
                        <select name="hora_fin" id="hora_fin" class="form-select px-3" required>
                            <option value="">-- Selecciona hora fin --</option>
                        </select>
                    </div>

                    <!-- Monto Total Calculado Estilizado -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-medium text-secondary">Monto Total Estimado</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Bs</span>
                            <input type="text" id="monto_total" class="form-control px-2 fw-bold text-dark bg-light" readonly
                                value="{{ old('monto_total', number_format($reserva->monto_total, 2)) }}">
                        </div>
                    </div>
                </div>

                <h6 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                    <i class="fas fa-comment-alt me-2"></i>Detalles Adicionales
                </h6>

                <!-- Campo de Observaciones -->
                <div class="mb-4">
                    <label for="observacion" class="form-label fw-medium text-secondary">Observación / Notas Especiales</label>
                    <textarea name="observacion" id="observacion" class="form-control px-3 py-2" rows="3" placeholder="Indique requerimientos extras o aclaraciones sobre el uso de la reserva...">{{ old('observacion', $reserva->observacion) }}</textarea>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('reservas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-sync-alt me-1.5"></i>Actualizar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const areaSelect = document.getElementById('area_comun_id');
    const fechaInput = document.getElementById('fecha');
    const horaInicioSelect = document.getElementById('hora_inicio');
    const horaFinSelect = document.getElementById('hora_fin');
    const montoTotalInput = document.getElementById('monto_total');

    // Valores actuales formateados HH:mm
    const horaInicioActual = "{{ old('hora_inicio', \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i')) }}";
    const horaFinActual = "{{ old('hora_fin', \Carbon\Carbon::parse($reserva->hora_fin)->format('H:i')) }}";

    // Monto guardado desde servidor
    const montoGuardado = parseFloat(montoTotalInput.value.replace(',', ''));

    async function cargarHorasDisponibles() {
        const areaId = areaSelect.value;
        const fecha = fechaInput.value;
        if (!areaId || !fecha) {
            horaInicioSelect.innerHTML = '<option value="">-- Selecciona hora inicio --</option>';
            horaFinSelect.innerHTML = '<option value="">-- Selecciona hora fin --</option>';
            montoTotalInput.value = '0.00';
            return;
        }
        try {
            const response = await fetch(`/api/horas-libres?area_comun_id=${areaId}&fecha=${fecha}`);
            if (!response.ok) throw new Error('Error al cargar horas');
            let horas = await response.json();

            // Añadir todas las medias horas desde 8:00 hasta 20:30 para asegurar disponibilidad completa
            const horasCompletas = [];
            for (let h = 8; h <= 20; h++) {
                horasCompletas.push(`${h.toString().padStart(2, '0')}:00`);
                horasCompletas.push(`${h.toString().padStart(2, '0')}:30`);
            }

            // Asegurar que todas las horas completas estén en el array 'horas'
            horasCompletas.forEach(hora => {
                if (!horas.includes(hora)) horas.push(hora);
            });

            // Añadir horas actuales para que siempre aparezcan (editar reserva)
            if (!horas.includes(horaInicioActual)) horas.push(horaInicioActual);
            if (!horas.includes(horaFinActual)) horas.push(horaFinActual);

            // Ordenar horas cronológicamente
            horas.sort((a, b) => {
                const [ah, am] = a.split(':').map(Number);
                const [bh, bm] = b.split(':').map(Number);
                return ah !== bh ? ah - bh : am - bm;
            });

            horaInicioSelect.innerHTML = '<option value="">-- Selecciona hora inicio --</option>';
            horaFinSelect.innerHTML = '<option value="">-- Selecciona hora fin --</option>';

            horas.forEach(hora => {
                let optionInicio = document.createElement('option');
                optionInicio.value = hora;
                optionInicio.textContent = hora;
                horaInicioSelect.appendChild(optionInicio);

                let optionFin = document.createElement('option');
                optionFin.value = hora;
                optionFin.textContent = hora;
                horaFinSelect.appendChild(optionFin);
            });

            // Setear valores actuales
            horaInicioSelect.value = horaInicioActual;
            horaFinSelect.value = horaFinActual;

        } catch (error) {
            console.error(error);
        }
    }

    function calcularMontoTotal() {
        const areaOption = areaSelect.options[areaSelect.selectedIndex];
        if (!areaOption || !horaInicioSelect.value || !horaFinSelect.value) {
            montoTotalInput.value = '0.00';
            return;
        }

        const montoHora = parseFloat(areaOption.dataset.monto);
        if (isNaN(montoHora) || montoHora < 0) {
            montoTotalInput.value = '0.00';
            return;
        }

        const [hiH, hiM] = horaInicioSelect.value.split(':').map(Number);
        const [hfH, hfM] = horaFinSelect.value.split(':').map(Number);

        const inicioMin = hiH * 60 + hiM;
        const finMin = hfH * 60 + hfM;

        const duracionMinutos = finMin - inicioMin;

        if (duracionMinutos <= 0) {
            montoTotalInput.value = '0.00';
            return;
        }

        const duracionHoras = duracionMinutos / 60;
        const total = montoHora * duracionHoras;

        montoTotalInput.value = total.toFixed(2);
    }

    areaSelect.addEventListener('change', () => {
        cargarHorasDisponibles();
        calcularMontoTotal();
    });

    fechaInput.addEventListener('change', () => {
        cargarHorasDisponibles();
        calcularMontoTotal();
    });

    horaInicioSelect.addEventListener('change', () => {
        calcularMontoTotal();
    });

    horaFinSelect.addEventListener('change', () => {
        calcularMontoTotal();
    });

    // Inicializar la carga y cálculo
    cargarHorasDisponibles().then(() => {
        // Solo recalcular si el monto guardado es 0 para no sobreescribir monto previo
        if (montoGuardado <= 0) {
            calcularMontoTotal();
        }
    });
});
</script>
@endpush