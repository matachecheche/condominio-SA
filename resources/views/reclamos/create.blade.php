@extends('plantilla')
@section('title', 'Nuevo Reclamo')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Registrar Reclamo Administrativo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}">Reclamos</a></li>
        <li class="breadcrumb-item active">Nuevo</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-exclamation-circle me-1"></i> Nuevo Reclamo Administrativo</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('reclamos.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Detalle del reclamo <span class="text-danger">*</span></label>
                        <textarea name="contenido" class="form-control" rows="4" required>{{ old('contenido') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residente que reclama <span class="text-danger">*</span></label>
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
                    <button type="submit" class="btn btn-success">Registrar Reclamo</button>
                    <a href="{{ route('reclamos.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
