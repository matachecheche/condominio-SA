@extends('plantilla')
@section('title', 'Reclamo ' . $reclamo->numero_seguimiento)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Reclamo: {{ $reclamo->numero_seguimiento }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}">Reclamos</a></li>
        <li class="breadcrumb-item active">{{ $reclamo->numero_seguimiento }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-exclamation-circle me-1"></i> Detalle del Reclamo</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">N° Seguimiento</dt><dd class="col-sm-9"><code>{{ $reclamo->numero_seguimiento }}</code></dd>
                <dt class="col-sm-3">Título</dt><dd class="col-sm-9">{{ $reclamo->titulo }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">{{ $reclamo->residente->nombre }} {{ $reclamo->residente->apellido }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ ucfirst(str_replace('_',' ',$reclamo->estado)) }}</dd>
                <dt class="col-sm-3">Detalle</dt><dd class="col-sm-9">{{ $reclamo->contenido }}</dd>
                <dt class="col-sm-3">Registrado el</dt><dd class="col-sm-9">{{ $reclamo->created_at->format('d/m/Y H:i') }}</dd>
                @if($reclamo->respuesta_admin)
                <dt class="col-sm-3">Respuesta Admin</dt><dd class="col-sm-9">{{ $reclamo->respuesta_admin }}</dd>
                @endif
                @if($reclamo->atendioPor)
                <dt class="col-sm-3">Atendido por</dt><dd class="col-sm-9">{{ $reclamo->atendioPor->name }}</dd>
                <dt class="col-sm-3">Fecha atención</dt><dd class="col-sm-9">{{ $reclamo->fecha_atencion?->format('d/m/Y H:i') }}</dd>
                @endif
            </dl>
            <a href="{{ route('reclamos.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            <a href="{{ route('reclamos.edit', $reclamo->id) }}" class="btn btn-warning btn-sm">Editar</a>
        </div>
    </div>
</div>
@endsection
