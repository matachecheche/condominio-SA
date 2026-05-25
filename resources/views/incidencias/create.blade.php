@extends('plantilla')
@section('title', 'Nueva Incidencia')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Registrar Incidencia</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
        <li class="breadcrumb-item active">Nueva</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> Nueva Denuncia / Incidencia</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('incidencias.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                        <select name="prioridad" class="form-select" required>
                            <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>Baja</option>
                            <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>Media</option>
                            <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>Alta</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción del problema <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residente que reporta <span class="text-danger">*</span></label>
                        <select name="residente_id" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Registrar Incidencia</button>
                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
