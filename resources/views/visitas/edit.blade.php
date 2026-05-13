
{{-- resources/views/visitas/edit.blade.php --}}
@extends('layouts.ap')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i> Editar Visita
                        <span class="badge bg-primary ms-2">{{ $visita->codigo }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    
                    {{-- Alerta si la visita está próxima a iniciar --}}
                    @if(now()->diffInHours($visita->fecha_inicio) < 2 && now() < $visita->fecha_inicio)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> 
                            <strong>Atención:</strong> Esta visita inicia en menos de 2 horas.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Errores encontrados:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('visitas.update', $visita) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Residente --}}
                        <div class="mb-3">
                            <label for="residente_id" class="form-label">
                                <i class="fas fa-user"></i> Residente
                            </label>
                            <select name="residente_id" id="residente_id" class="form-select" required
                                    @can('gestionar visitas') 
                                        @if($residentes->count() == 1) readonly @endif
                                    @endcan>
                                <option value="">-- Seleccionar Residente --</option>
                                @foreach($residentes as $residente)
                                    <option value="{{ $residente->id }}"
                                        {{ old('residente_id', $visita->residente_id) == $residente->id ? 'selected' : '' }}>
                                        {{ $residente->nombre_completo }}
                                        @if($residente->unidad)
                                            - {{ $residente->unidad }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @can('gestionar visitas')
                                @if($residentes->count() == 1)
                                    <small class="text-muted">Solo puedes crear visitas para ti mismo</small>
                                @endif
                            @endcan
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Nombre del Visitante --}}
                                <div class="mb-3">
                                    <label for="nombre_visitante" class="form-label">
                                        <i class="fas fa-id-card"></i> Nombre del Visitante
                                    </label>
                                    <input type="text"
                                           name="nombre_visitante"
                                           id="nombre_visitante"
                                           class="form-control"
                                           value="{{ old('nombre_visitante', $visita->nombre_visitante) }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- CI del Visitante --}}
                                <div class="mb-3">
                                    <label for="ci_visitante" class="form-label">
                                        <i class="fas fa-id-badge"></i> CI del Visitante
                                    </label>
                                    <input type="text"
                                           name="ci_visitante"
                                           id="ci_visitante"
                                           class="form-control"
                                           value="{{ old('ci_visitante', $visita->ci_visitante) }}"
                                           required>
                                </div>
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="mb-3">
                            <label for="motivo" class="form-label">
                                <i class="fas fa-comment"></i> Motivo de la Visita
                            </label>
                            <select name="motivo" id="motivo" class="form-select" required>
                                <option value="">-- Seleccionar Motivo --</option>
                                <option value="Visita familiar" {{ old('motivo', $visita->motivo) == 'Visita familiar' ? 'selected' : '' }}>
                                    👨‍👩‍👧‍👦 Visita familiar
                                </option>
                                <option value="Servicio técnico" {{ old('motivo', $visita->motivo) == 'Servicio técnico' ? 'selected' : '' }}>
                                    🔧 Servicio técnico
                                </option>
                                <option value="Delivery" {{ old('motivo', $visita->motivo) == 'Delivery' ? 'selected' : '' }}>
                                    📦 Delivery
                                </option>
                                <option value="Visita social" {{ old('motivo', $visita->motivo) == 'Visita social' ? 'selected' : '' }}>
                                    👋 Visita social
                                </option>
                                <option value="Entrega de documentos" {{ old('motivo', $visita->motivo) == 'Entrega de documentos' ? 'selected' : '' }}>
                                    📄 Entrega de documentos
                                </option>
                                <option value="Reunión de trabajo" {{ old('motivo', $visita->motivo) == 'Reunión de trabajo' ? 'selected' : '' }}>
                                    💼 Reunión de trabajo
                                </option>
                                <option value="Otro" {{ old('motivo', $visita->motivo) == 'Otro' ? 'selected' : '' }}>
                                    ❓ Otro
                                </option>
                                
                                {{-- Si el motivo actual no está en las opciones predefinidas, mostrarlo --}}
                                @php
                                    $motivosPredefinidos = ['Visita familiar', 'Servicio técnico', 'Delivery', 'Visita social', 'Entrega de documentos', 'Reunión de trabajo', 'Otro'];
                                    $motivoActual = old('motivo', $visita->motivo);
                                @endphp
                                
                                @if($motivoActual && !in_array($motivoActual, $motivosPredefinidos))
                                    <option value="{{ $motivoActual }}" selected>{{ $motivoActual }}</option>
                                @endif
                            </select>
                        </div>

                        {{-- ✅ NUEVO CAMPO: ACOMPAÑANTE (EDITAR) --}}
                        {{-- EXPLICACIÓN: Permite cambiar si el visitante va acompañado --}}
                        <div class="mb-3">
                            <label for="acompanante" class="form-label">
                                <i class="fas fa-users"></i> ¿El visitante va acompañado?
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="acompanante" 
                                       id="acompanante" 
                                       value="1"
                                       {{ old('acompanante', $visita->acompanante) ? 'checked' : '' }}>
                                <label class="form-check-label" for="acompanante">
                                    <span id="estadoAcompanante">
                                        @if(old('acompanante', $visita->acompanante))
                                            ✅ SÍ, va acompañado
                                        @else
                                            ❌ No, va solo
                                        @endif
                                    </span>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Indica si el visitante vendrá acompañado de otras personas
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Fecha y Hora de Inicio --}}
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Fecha y Hora de Inicio
                                    </label>
                                    <input type="datetime-local"
                                           name="fecha_inicio"
                                           id="fecha_inicio"
                                           class="form-control"
                                           value="{{ old('fecha_inicio', $visita->fecha_inicio->format('Y-m-d\TH:i')) }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- Fecha y Hora de Fin --}}
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar-check"></i> Fecha y Hora de Fin
                                    </label>
                                    <input type="datetime-local"
                                           name="fecha_fin"
                                           id="fecha_fin"
                                           class="form-control"
                                           value="{{ old('fecha_fin', $visita->fecha_fin->format('Y-m-d\TH:i')) }}"
                                           required>
                                </div>
                            </div>
                        </div>

                        {{-- Placa del Vehículo --}}
                        <div class="mb-3">
                            <label for="placa_vehiculo" class="form-label">
                                <i class="fas fa-car"></i> Placa del Vehículo (Opcional)
                            </label>
                            <input type="text"
                                   name="placa_vehiculo"
                                   id="placa_vehiculo"
                                   class="form-control"
                                   value="{{ old('placa_vehiculo', $visita->placa_vehiculo) }}"
                                   placeholder="Ej: ABC-123">
                        </div>

                        {{-- Información no editable --}}
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle"></i> Información de la Visita
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Código:</strong> 
                                                    <span class="badge bg-primary fs-6">{{ $visita->codigo }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1">
                                                    <strong>Estado:</strong> 
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock"></i> {{ ucfirst($visita->estado) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-0">
                                                    <strong>Creada:</strong> 
                                                    {{ $visita->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex gap-2 justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Actualizar Visita
                                </button>
                                <a href="{{ route('visitas.show', $visita) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('visitas.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Ver Todas las Visitas
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript para validación de fechas --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    fechaInicio.addEventListener('change', function() {
        fechaFin.min = this.value;
        if (fechaFin.value && fechaFin.value <= this.value) {
            // Agregar 1 hora automáticamente
            const inicio = new Date(this.value);
            inicio.setHours(inicio.getHours() + 1);
            fechaFin.value = inicio.toISOString().slice(0, 16);
        }
    });
});
</script>
<script>
    // Script para actualizar el texto del acompañante dinámicamente
    document.getElementById('acompanante').addEventListener('change', function() {
        const estado = this.checked ? '✅ SÍ, va acompañado' : '❌ No, va solo';
        document.getElementById('estadoAcompanante').textContent = estado;
    });
</script>

@endsection