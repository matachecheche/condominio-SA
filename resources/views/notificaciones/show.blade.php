@extends('plantilla')
@section('title', 'Notificación: ' . $notificacion->titulo)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Notificación: {{ $notificacion->titulo }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('notificaciones.index') }}">Notificaciones</a></li>
        <li class="breadcrumb-item active">{{ $notificacion->titulo }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-bell me-1"></i> Detalle de la Notificación</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Título</dt><dd class="col-sm-9">{{ $notificacion->titulo }}</dd>
                <dt class="col-sm-3">Tipo</dt><dd class="col-sm-9">{{ $notificacion->tipo }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">{{ $notificacion->residente ? $notificacion->residente->nombre.' '.$notificacion->residente->apellido : 'Todos los residentes' }}</dd>
                <dt class="col-sm-3">Contenido</dt><dd class="col-sm-9">{{ $notificacion->contenido }}</dd>
                <dt class="col-sm-3">Fecha y hora</dt><dd class="col-sm-9">{{ \Carbon\Carbon::parse($notificacion->fecha_hora)->format('d/m/Y H:i') }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ $notificacion->leida ? 'Leída' : 'Pendiente de lectura' }}</dd>
            </dl>
            <a href="{{ route('notificaciones.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            @if(!$notificacion->leida)
            <form action="{{ route('notificaciones.marcar-leida', $notificacion->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Marcar como leída</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
