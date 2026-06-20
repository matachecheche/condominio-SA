@extends('plantilla')
@section('title', 'Nuevo Evento')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Registrar Evento Comunitario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('eventos.index') }}">Eventos</a></li>
        <li class="breadcrumb-item active">Nuevo</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-calendar-star me-1"></i> Nuevo Evento Comunitario</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('eventos.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre del evento <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cupo máximo</label>
                        <input type="number" min="1" name="cupo_maximo" class="form-control" value="{{ old('cupo_maximo') }}" placeholder="Sin límite">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lugar <span class="text-danger">*</span></label>
                        <input type="text" name="lugar" class="form-control" value="{{ old('lugar') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha y hora <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ old('fecha_hora') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Registrar Evento</button>
                    <a href="{{ route('eventos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
