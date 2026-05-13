@extends('layouts.ap')

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Comunicado</h2>

    {{-- EXPLICACIÓN: Formulario para editar un comunicado existente --}}
    {{-- El método PUT se usa para actualizar recursos RESTful --}}
    <form method="POST" action="{{ route('comunicados.update', $comunicado->id) }}">
        @csrf {{-- Token de seguridad CSRF --}}
        @method('PUT') {{-- Simula un request PUT desde un formulario POST --}}

        {{-- CAMPO: Título --}}
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" 
                   class="form-control" 
                   name="titulo" 
                   value="{{ old('titulo', $comunicado->titulo) }}" 
                   required>
            {{-- old() recupera datos del formulario si hay error, sino usa el valor actual --}}
        </div>

        {{-- CAMPO: Contenido --}}
        <div class="mb-3">
            <label for="contenido" class="form-label">Contenido</label>
            <textarea class="form-control" 
                      name="contenido" 
                      rows="5" 
                      required>{{ old('contenido', $comunicado->contenido) }}</textarea>
        </div>

        {{-- CAMPO: Tipo de comunicado --}}
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" name="tipo" required>
                <option value="Informativo" 
                        {{ old('tipo', $comunicado->tipo) === 'Informativo' ? 'selected' : '' }}>
                    Informativo
                </option>
                <option value="Urgente" 
                        {{ old('tipo', $comunicado->tipo) === 'Urgente' ? 'selected' : '' }}>
                    Urgente
                </option>
            </select>
        </div>

        {{-- ✅ CAMPO NUEVO: Destinatarios --}}
        {{-- EXPLICACIÓN: Muestra los destinatarios actuales del comunicado --}}
        <div class="mb-3">
            <label for="destinatarios" class="form-label">Destinatarios</label>
            <select class="form-select" name="destinatarios" required>
                <option value="Todos" 
                        {{ old('destinatarios', $comunicado->destinatarios) === 'Todos' ? 'selected' : '' }}>
                    Todos (Residentes y Empleados)
                </option>
                <option value="Residentes" 
                        {{ old('destinatarios', $comunicado->destinatarios) === 'Residentes' ? 'selected' : '' }}>
                    Solo Residentes
                </option>
                <option value="Empleados" 
                        {{ old('destinatarios', $comunicado->destinatarios) === 'Empleados' ? 'selected' : '' }}>
                    Solo Empleados
                </option>
            </select>
        </div>

        {{-- CAMPO: Fecha de publicación --}}
        <div class="mb-3">
            <label for="fecha_publicacion" class="form-label">Fecha y Hora de Publicación</label>
            <input type="datetime-local" 
                   name="fecha_publicacion" 
                   class="form-control"
                   value="{{ old('fecha_publicacion', $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('Y-m-d\TH:i') : '') }}">
            {{-- Formatea la fecha al formato que requiere datetime-local --}}
        </div>

        {{-- BOTONES --}}
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('comunicados.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection