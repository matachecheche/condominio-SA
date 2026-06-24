@extends('plantilla')

@section('title', 'Editar Reclamo ' . $reclamo->numero_seguimiento)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Reclamo: {{ $reclamo->numero_seguimiento }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}" class="text-decoration-none">Reclamos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-edit me-2 text-primary"></i>Actualizar Estado y Detalles</h5>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('reclamos.update', $reclamo->id) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <!-- Datos Generales -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Título del Reclamo</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $reclamo->titulo) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Detalle del Reclamo</label>
                                <textarea name="contenido" class="form-control" rows="3" required>{{ old('contenido', $reclamo->contenido) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Residente</label>
                                <select name="residente_id" class="form-select" required>
                                    @foreach($residentes as $r)
                                        <option value="{{ $r->id }}" {{ old('residente_id', $reclamo->residente_id) == $r->id ? 'selected' : '' }}>
                                            {{ $r->apellido }}, {{ $r->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Gestión Administrativa -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Estado Actual</label>
                                <select name="estado" class="form-select border-primary" required>
                                    @foreach(['pendiente','en_revision','resuelto','rechazado'] as $est)
                                        <option value="{{ $est }}" {{ old('estado', $reclamo->estado) == $est ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_',' ',$est)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Respuesta del Administrador</label>
                                <textarea name="respuesta_admin" class="form-control bg-light" rows="4" placeholder="Escribe aquí la resolución o seguimiento del caso...">{{ old('respuesta_admin', $reclamo->respuesta_admin) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-3">
                            <a href="{{ route('reclamos.index') }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection