@extends('layouts.ap')

@section('content')
<div class="container mt-4">
    {{-- EXPLICACIÓN: Página que muestra el detalle completo de un comunicado --}}
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>{{ $comunicado->titulo }}</h2>
        </div>
        
        <div class="card-body">
            {{-- Fila 1: Tipo de comunicado --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Tipo:</strong>
                </div>
                <div class="col-md-9">
                    <span class="badge {{ $comunicado->tipo === 'Urgente' ? 'bg-danger' : 'bg-info' }}">
                        {{ $comunicado->tipo }}
                    </span>
                </div>
            </div>

            {{-- ✅ FILA NUEVA: Destinatarios --}}
            {{-- EXPLICACIÓN: Muestra a quién está dirigido este comunicado --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Destinatarios:</strong>
                </div>
                <div class="col-md-9">
                    <span class="badge bg-secondary">
                        {{ $comunicado->destinatarios }}
                    </span>
                </div>
            </div>

            {{-- Fila 3: Fecha de publicación --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Fecha:</strong>
                </div>
                <div class="col-md-9">
                    {{ $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('d/m/Y H:i') : 'Inmediato' }}
                </div>
            </div>

            {{-- Fila 4: Autor --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Autor:</strong>
                </div>
                <div class="col-md-9">
                    {{ $comunicado->usuario->name ?? 'Sistema' }}
                </div>
            </div>

            {{-- Separador --}}
            <hr>

            {{-- Fila 5: Contenido completo --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Contenido:</strong>
                    <div class="mt-2 p-3 bg-light border rounded">
                        {{ nl2br($comunicado->contenido) }} {{-- nl2br: respeta saltos de línea --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('comunicados.index') }}" class="btn btn-secondary">
                ← Volver al Listado
            </a>
        </div>
    </div>
</div>
@endsection