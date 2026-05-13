@extends('layouts.ap')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Comunicados</h1>

    {{-- EXPLICACIÓN: Muestra mensaje de éxito si se guardó correctamente --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- BOTÓN: Crear nuevo comunicado (solo para administradores) --}}
    {{-- Condición: No es residente ni empleado --}}
    @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
    <a href="{{ route('comunicados.create') }}" class="btn btn-primary mb-3">Nuevo Comunicado</a>
    @endif

    {{-- TABLA: Listado de comunicados --}}
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Título</th>
                <th>Tipo</th>
                <th>Destinatarios</th> {{-- ✅ NUEVA COLUMNA --}}
                <th>Contenido</th>
                <th>Autor</th>
                <th>Fecha de Publicación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- EXPLICACIÓN: Itera sobre cada comunicado para mostrar sus datos en una fila --}}
            @foreach ($comunicados as $comunicado)
                <tr>
                    <td>{{ $comunicado->titulo }}</td>
                    <td>
                        {{-- Badge de color según el tipo --}}
                        <span class="badge {{ $comunicado->tipo === 'Urgente' ? 'bg-danger' : 'bg-info' }}">
                            {{ $comunicado->tipo }}
                        </span>
                    </td>
                    
                    {{-- ✅ NUEVA CELDA: Mostrar destinatarios --}}
                    {{-- EXPLICACIÓN: Muestra a quién va dirigido el comunicado --}}
                    <td>
                        <span class="badge bg-secondary">
                            {{ $comunicado->destinatarios }}
                        </span>
                    </td>
                    
                    <td>{{ Str::limit($comunicado->contenido, 50) }}</td> {{-- Limita a 50 caracteres --}}
                    <td>{{ $comunicado->usuario->name ?? '---' }}</td> {{-- Nombre del autor --}}
                    <td>{{ $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('d/m/Y H:i') : 'Inmediato' }}</td>
                    
                    {{-- BOTONES: Editar y eliminar (solo para administradores) --}}
                    <td>
                        @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
                            <a href="{{ route('comunicados.edit', $comunicado->id) }}" 
                               class="btn btn-sm btn-warning">Editar</a>
                            
                            <form action="{{ route('comunicados.destroy', $comunicado->id) }}" 
                                  method="POST" 
                                  style="display:inline;">
                                @csrf {{-- Token CSRF --}}
                                @method('DELETE') {{-- Simula DELETE desde formulario POST --}}
                                <button class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este comunicado?')">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PAGINACIÓN: Enlaces para navegar entre páginas --}}
    <div class="d-flex justify-content-center">
        {{ $comunicados->links() }}
    </div>
</div>
@endsection