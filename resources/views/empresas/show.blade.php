@extends('layouts.ap')

@section('content')
<div class="container mt-4">
    {{-- EXPLICACIÓN: Página que muestra el detalle completo de una empresa --}}
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>{{ $empresa->nombre }}</h2>
        </div>
        
        <div class="card-body">
            {{-- Fila 1: ID y Servicio --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>ID:</strong>
                    <p>{{ $empresa->id }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Servicio:</strong>
                    <p>{{ $empresa->servicio }}</p>
                </div>
            </div>

            {{-- Fila 2: Teléfono y Correo --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Teléfono:</strong>
                    <p>
                        @if($empresa->telefono)
                            <a href="tel:{{ $empresa->telefono }}">{{ $empresa->telefono }}</a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <strong>Correo:</strong>
                    <p>
                        @if($empresa->correo)
                            <a href="mailto:{{ $empresa->correo }}">{{ $empresa->correo }}</a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Fila 3: Dirección --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Dirección:</strong>
                    <p>
                        @if($empresa->direccion)
                            {{ $empresa->direccion }}
                        @else
                            <span class="text-muted">No especificada</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- ✅ NUEVA INFORMACIÓN: Calificación --}}
            {{-- EXPLICACIÓN: Muestra la calificación con estrellas y descripción --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Calificación:</strong>
                    <p>
                        <span style="font-size: 1.5rem; margin-right: 10px;">
                            {!! str_repeat('⭐', $empresa->calificacion) !!}
                        </span>
                        <span class="badge 
                            @if($empresa->calificacion >= 5) bg-success
                            @elseif($empresa->calificacion >= 4) bg-info
                            @elseif($empresa->calificacion >= 3) bg-warning text-dark
                            @else bg-danger
                            @endif">
                            {{ $empresa->calificacion }}/5
                        </span>
                        
                        {{-- Descripción de confiabilidad --}}
                        @if($empresa->esConfiable())
                            <span class="badge bg-success" style="margin-left: 10px;">✓ Empresa Confiable</span>
                        @else
                            <span class="badge bg-warning text-dark" style="margin-left: 10px;">⚠ Revisar referencias</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Separador --}}
            <hr>

            {{-- Fila 4: Observación --}}
            @if($empresa->observacion)
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Observación:</strong>
                    <p class="p-3 bg-light rounded">{{ $empresa->observacion }}</p>
                </div>
            </div>
            @endif

            {{-- Fila 5: Información de registro --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Registrado:</strong>
                    <p>{{ \Carbon\Carbon::parse($empresa->created_at)->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Última actualización:</strong>
                    <p>{{ \Carbon\Carbon::parse($empresa->updated_at)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-warning btn-sm">
                Editar
            </a>
            <a href="{{ route('empresas.index') }}" class="btn btn-secondary btn-sm">
                ← Volver al Listado
            </a>
        </div>
    </div>
</div>
@endsection