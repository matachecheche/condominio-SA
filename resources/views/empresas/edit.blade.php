@extends('layouts.ap')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Empresa</h2>

    {{-- EXPLICACIÓN: Mostrar errores de validación si los hay --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO para editar empresa --}}
    {{-- El método PUT se usa para actualizar recursos RESTful --}}
    <form action="{{ route('empresas.update', $empresa->id) }}" method="POST">
        @csrf {{-- Token de seguridad CSRF --}}
        @method('PUT') {{-- Simula un request PUT desde un formulario POST --}}

        {{-- CAMPO 1: Nombre --}}
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" 
                   name="nombre" 
                   class="form-control" 
                   value="{{ old('nombre', $empresa->nombre) }}" 
                   required>
            {{-- old() recupera datos del formulario si hay error, sino usa el valor actual --}}
        </div>

        {{-- CAMPO 2: Servicio --}}
        <div class="mb-3">
            <label class="form-label">Servicio</label>
            <input type="text" 
                   name="servicio" 
                   class="form-control" 
                   value="{{ old('servicio', $empresa->servicio) }}" 
                   required>
        </div>

        {{-- CAMPO 3: Teléfono --}}
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" 
                   name="telefono" 
                   class="form-control" 
                   value="{{ old('telefono', $empresa->telefono) }}">
        </div>

        {{-- CAMPO 4: Correo --}}
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" 
                   name="correo" 
                   class="form-control" 
                   value="{{ old('correo', $empresa->correo) }}">
        </div>

        {{-- CAMPO 5: Dirección --}}
        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <textarea name="direccion" 
                      class="form-control" 
                      rows="2">{{ old('direccion', $empresa->direccion) }}</textarea>
        </div>

        {{-- ✅ CAMPO NUEVO 6: Calificación --}}
        {{-- EXPLICACIÓN: Campo para actualizar la calificación de la empresa --}}
        <div class="mb-3">
            <label class="form-label">Calificación</label>
            <div class="d-flex gap-3 align-items-center">
                {{-- Input con rango deslizante --}}
                <input type="range" 
                       name="calificacion" 
                       class="form-range" 
                       id="calificacion"
                       min="1" 
                       max="5" 
                       value="{{ old('calificacion', $empresa->calificacion) }}"
                       required>
                {{-- Mostrar valor numérico --}}
                <span id="calificacionValue" class="badge bg-primary" style="min-width: 50px;">
                    {{ old('calificacion', $empresa->calificacion) }}/5
                </span>
                {{-- Mostrar estrellas --}}
                <span id="calificacionEstrellas" style="font-size: 1.5rem;">
                    {!! str_repeat('⭐', intval(old('calificacion', $empresa->calificacion))) !!}
                </span>
            </div>
            <small class="text-muted">Selecciona una calificación de 1 a 5 estrellas</small>
        </div>

        {{-- CAMPO 7: Observación --}}
        <div class="mb-3">
            <label class="form-label">Observación</label>
            <textarea name="observacion" 
                      class="form-control" 
                      rows="2">{{ old('observacion', $empresa->observacion) }}</textarea>
        </div>

        {{-- BOTONES --}}
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('empresas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

{{-- JavaScript para actualizar las estrellas dinámicamente --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('calificacion');
        const valueDisplay = document.getElementById('calificacionValue');
        const estrellasDisplay = document.getElementById('calificacionEstrellas');

        // Actualizar cuando cambie el rango
        input.addEventListener('input', function() {
            const valor = this.value;
            valueDisplay.textContent = valor + '/5';
            // Mostrar estrellas según el valor
            estrellasDisplay.textContent = '⭐'.repeat(valor);
        });
    });
</script>
@endsection