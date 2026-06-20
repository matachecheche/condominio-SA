@extends('plantilla')
@section('title', 'Editar Reclamo')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Reclamo: {{ $reclamo->numero_seguimiento }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}">Reclamos</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-exclamation-circle me-1"></i> Actualizar Estado / Respuesta</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('reclamos.update', $reclamo->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo',$reclamo->titulo) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            @foreach(['pendiente','en_revision','resuelto','rechazado'] as $est)
                            <option value="{{ $est }}" {{ old('estado',$reclamo->estado)==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Detalle</label>
                        <textarea name="contenido" class="form-control" rows="3" required>{{ old('contenido',$reclamo->contenido) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residente</label>
                        <select name="residente_id" class="form-select" required>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id',$reclamo->residente_id)==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Respuesta del Administrador</label>
                        <textarea name="respuesta_admin" class="form-control" rows="3">{{ old('respuesta_admin',$reclamo->respuesta_admin) }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('reclamos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
