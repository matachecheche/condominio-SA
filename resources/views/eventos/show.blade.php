@extends('plantilla')
@section('title', 'Evento: ' . $evento->nombre)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Evento: {{ $evento->nombre }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('eventos.index') }}">Eventos</a></li>
        <li class="breadcrumb-item active">{{ $evento->nombre }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-calendar-star me-1"></i> Detalle del Evento</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">{{ $evento->nombre }}</dd>
                <dt class="col-sm-3">Lugar</dt><dd class="col-sm-9">{{ $evento->lugar }}</dd>
                <dt class="col-sm-3">Fecha y hora</dt><dd class="col-sm-9">{{ $evento->fecha_hora->format('d/m/Y H:i') }}</dd>
                <dt class="col-sm-3">Cupo máximo</dt><dd class="col-sm-9">{{ $evento->cupo_maximo ?? 'Sin límite' }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ ucfirst(str_replace('_',' ',$evento->estado)) }}</dd>
                <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">{{ $evento->descripcion ?? '—' }}</dd>
                @if($evento->organizador)
                <dt class="col-sm-3">Organizado por</dt><dd class="col-sm-9">{{ $evento->organizador->name }}</dd>
                @endif
            </dl>
            <a href="{{ route('eventos.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            <a href="{{ route('eventos.edit', $evento->id) }}" class="btn btn-warning btn-sm">Editar</a>
        </div>
    </div>
</div>
@endsection
