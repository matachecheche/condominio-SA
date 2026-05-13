@extends('layouts.ap')

@section('content')
<div class="container">
    <h2 class="mb-4">Nuevo Comunicado</h2>

    {{-- EXPLICACIÓN: Este bloque muestra errores de validación si los hay --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO para crear comunicado --}}
    <form action="{{ route('comunicados.store') }}" method="POST">
        @csrf {{-- Token de seguridad CSRF --}}

        {{-- CAMPO: Título --}}
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" 
                   name="titulo" 
                   class="form-control" 
                   required 
                   placeholder="Ej: Mantenimiento de Piscina"
                   value="{{ old('titulo') }}">
            {{-- old('titulo') recupera el valor si hay error de validación --}}
        </div>

        {{-- CAMPO: Contenido --}}
        <div class="mb-3">
            <label for="contenido" class="form-label">Contenido</label>
            <textarea name="contenido" 
                      class="form-control" 
                      rows="4" 
                      required
                      placeholder="Escribe el contenido del comunicado...">{{ old('contenido') }}</textarea>
        </div>

        {{-- CAMPO: Tipo de comunicado --}}
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                <option value="">Seleccione un tipo</option>
                <option value="Informativo" {{ old('tipo') == 'Informativo' ? 'selected' : '' }}>
                    Informativo
                </option>
                <option value="Urgente" {{ old('tipo') == 'Urgente' ? 'selected' : '' }}>
                    Urgente
                </option>
            </select>
        </div>

        {{-- ✅ CAMPO NUEVO: Destinatarios --}}
        {{-- EXPLICACIÓN: Este campo define a quién va dirigido el comunicado --}}
        <div class="mb-3">
            <label for="destinatarios" class="form-label">Destinatarios</label>
            <select name="destinatarios" class="form-select" required>
                <option value="">Seleccione destinatarios</option>
                <option value="Todos" {{ old('destinatarios') == 'Todos' ? 'selected' : '' }}>
                    Todos (Residentes y Empleados)
                </option>
                <option value="Residentes" {{ old('destinatarios') == 'Residentes' ? 'selected' : '' }}>
                    Solo Residentes
                </option>
                <option value="Empleados" {{ old('destinatarios') == 'Empleados' ? 'selected' : '' }}>
                    Solo Empleados
                </option>
            </select>
            <small class="text-muted">Elige a quién va dirigido este comunicado</small>
        </div>

        {{-- CAMPO: Fecha de publicación (opcional) --}}
        <div class="mb-3">
            <label for="fecha_publicacion" class="form-label">Fecha y Hora de Publicación</label>
            <input type="datetime-local" 
                   name="fecha_publicacion" 
                   class="form-control" 
                   value="{{ old('fecha_publicacion') }}">
            <small class="text-muted">Si se deja vacío, se publicará inmediatamente.</small>
        </div>

        {{-- BOTONES --}}
        <button type="submit" class="btn btn-primary">Publicar Comunicado</button>
        <a href="{{ route('comunicados.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection