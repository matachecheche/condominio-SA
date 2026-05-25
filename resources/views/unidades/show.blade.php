@extends('plantilla')
@section('title', 'Unidad ' . $unidad->codigo)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Unidad: {{ $unidad->codigo }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}">Unidades</a></li>
        <li class="breadcrumb-item active">{{ $unidad->codigo }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> Detalle de Unidad Habitacional</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Código</dt><dd class="col-sm-9">{{ $unidad->codigo }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">
                    <span class="badge {{ $unidad->estado==='activa'?'bg-success':'bg-secondary' }}">{{ ucfirst($unidad->estado) }}</span>
                </dd>
                <dt class="col-sm-3">Tipo Ocupación</dt><dd class="col-sm-9">{{ $unidad->tipo_ocupacion }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">
                    {{ $unidad->residente ? $unidad->residente->nombre.' '.$unidad->residente->apellido : '—' }}
                </dd>
                <dt class="col-sm-3">Personas</dt><dd class="col-sm-9">{{ $unidad->personas_por_unidad }}</dd>
                <dt class="col-sm-3">Capacidad</dt><dd class="col-sm-9">{{ $unidad->capacidad }}</dd>
                <dt class="col-sm-3">Vehículos</dt><dd class="col-sm-9">{{ $unidad->vehiculos }}</dd>
                <dt class="col-sm-3">Placa</dt><dd class="col-sm-9">{{ $unidad->placa ?? '—' }}</dd>
                <dt class="col-sm-3">Marca</dt><dd class="col-sm-9">{{ $unidad->marca ?? '—' }}</dd>
                <dt class="col-sm-3">Mascotas</dt><dd class="col-sm-9">{{ $unidad->tiene_mascotas ? 'Sí' : 'No' }}</dd>
            </dl>
            <a href="{{ route('unidades.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            @can('editar unidades')
            <a href="{{ route('unidades.edit', $unidad->id) }}" class="btn btn-warning btn-sm">Editar</a>
            @endcan
        </div>
    </div>
</div>
@endsection
