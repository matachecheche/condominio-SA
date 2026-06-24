@extends('plantilla')

@section('title', 'Nueva Incidencia')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Registrar Nueva Incidencia</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}" class="text-decoration-none">Incidencias</a></li>
                <li class="breadcrumb-item active">Nueva</li>
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
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-flag me-2"></i>Detalles del Reporte</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('incidencias.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Título de la Incidencia</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" placeholder="Breve descripción del problema" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Nivel de Prioridad</label>
                                <select name="prioridad" class="form-select" required>
                                    <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>Baja</option>
                                    <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>Media</option>
                                    <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>Alta</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Residente que reporta</label>
                                <select name="residente_id" class="form-select" required>
                                    <option value="">— Seleccione un residente —</option>
                                    @foreach($residentes as $r)
                                        <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                            {{ $r->apellido }}, {{ $r->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Descripción detallada</label>
                                <textarea name="descripcion" class="form-control" rows="5" placeholder="Explique detalladamente los hechos ocurridos..." required>{{ old('descripcion') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Registrar Incidencia
                            </button>
                            <a href="{{ route('incidencias.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna Lateral Informativa -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>Consideraciones</h6>
                    <p class="small text-muted mb-0">
                        Al registrar esta incidencia, el estado se establecerá automáticamente como <strong>"Pendiente"</strong>. 
                        Asegúrese de describir los hechos con claridad para facilitar el seguimiento administrativo.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection