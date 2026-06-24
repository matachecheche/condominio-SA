@extends('plantilla')

@section('title', 'Editar Incidencia')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Incidencia</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}" class="text-decoration-none">Incidencias</a></li>
                <li class="breadcrumb-item active">Seguimiento #{{ $incidencia->numero_seguimiento }}</li>
            </ol>
        </nav>
    </div>

    <!-- Gestión de Errores -->
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-warning"><i class="fas fa-edit me-2"></i>Modificar Reporte</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('incidencias.update', $incidencia->id) }}" method="POST">
                        @csrf @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Título de la Incidencia</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $incidencia->titulo) }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-muted">Prioridad</label>
                                <select name="prioridad" class="form-select" required>
                                    @foreach(['baja','media','alta'] as $p)
                                        <option value="{{ $p }}" {{ old('prioridad', $incidencia->prioridad) == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-muted">Estado</label>
                                <select name="estado" class="form-select border-primary" required>
                                    @foreach(['pendiente','en_revision','resuelto','cerrado'] as $est)
                                        <option value="{{ $est }}" {{ old('estado', $incidencia->estado) == $est ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Descripción del problema</label>
                                <textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $incidencia->descripcion) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Residente asociado</label>
                                <select name="residente_id" class="form-select" required>
                                    @foreach($residentes as $r)
                                        <option value="{{ $r->id }}" {{ old('residente_id', $incidencia->residente_id) == $r->id ? 'selected' : '' }}>
                                            {{ $r->apellido }}, {{ $r->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Respuesta del Administrador</label>
                                <textarea name="respuesta_admin" class="form-control bg-light" rows="3" placeholder="Escriba aquí la resolución o seguimiento...">{{ old('respuesta_admin', $incidencia->respuesta_admin) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-warning text-white px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('incidencias.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información lateral -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-clock me-2"></i>Información del Registro</h6>
                    <p class="small text-muted mb-0">
                        Creado el: {{ $incidencia->created_at->format('d/m/Y H:i') }}<br>
                        Última edición: {{ $incidencia->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection