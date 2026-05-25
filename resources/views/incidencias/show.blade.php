@extends('plantilla')
@section('title', 'Incidencia ' . $incidencia->numero_seguimiento)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Incidencia: {{ $incidencia->numero_seguimiento }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
        <li class="breadcrumb-item active">{{ $incidencia->numero_seguimiento }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> Detalle de Incidencia</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">N° Seguimiento</dt><dd class="col-sm-9"><code>{{ $incidencia->numero_seguimiento }}</code></dd>
                <dt class="col-sm-3">Título</dt><dd class="col-sm-9">{{ $incidencia->titulo }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">{{ $incidencia->residente->nombre }} {{ $incidencia->residente->apellido }}</dd>
                <dt class="col-sm-3">Prioridad</dt><dd class="col-sm-9">{{ ucfirst($incidencia->prioridad) }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ ucfirst(str_replace('_',' ',$incidencia->estado)) }}</dd>
                <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">{{ $incidencia->descripcion }}</dd>
                <dt class="col-sm-3">Registrada el</dt><dd class="col-sm-9">{{ $incidencia->created_at->format('d/m/Y H:i') }}</dd>
                @if($incidencia->respuesta_admin)
                <dt class="col-sm-3">Respuesta Admin</dt><dd class="col-sm-9">{{ $incidencia->respuesta_admin }}</dd>
                @endif
                @if($incidencia->atendioPor)
                <dt class="col-sm-3">Atendido por</dt><dd class="col-sm-9">{{ $incidencia->atendioPor->name }}</dd>
                <dt class="col-sm-3">Fecha atención</dt><dd class="col-sm-9">{{ $incidencia->fecha_atencion?->format('d/m/Y H:i') }}</dd>
                @endif
            </dl>
            <a href="{{ route('incidencias.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            <a href="{{ route('incidencias.edit', $incidencia->id) }}" class="btn btn-warning btn-sm">Editar</a>
        </div>
    </div>
</div>
@endsection
