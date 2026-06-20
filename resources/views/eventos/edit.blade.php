@extends('plantilla')
@section('title', 'Editar Evento')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Evento: {{ $evento->nombre }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('eventos.index') }}">Eventos</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-calendar-star me-1"></i> Actualizar Evento</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('eventos.update', $evento->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del evento</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre',$evento->nombre) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cupo máximo</label>
                        <input type="number" min="1" name="cupo_maximo" class="form-control" value="{{ old('cupo_maximo',$evento->cupo_maximo) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            @foreach(['programado','en_curso','finalizado','cancelado'] as $est)
                            <option value="{{ $est }}" {{ old('estado',$evento->estado)==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lugar</label>
                        <input type="text" name="lugar" class="form-control" value="{{ old('lugar',$evento->lugar) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha y hora</label>
                        <input type="datetime-local" name="fecha_hora" class="form-control"
                               value="{{ old('fecha_hora', $evento->fecha_hora->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion',$evento->descripcion) }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('eventos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
