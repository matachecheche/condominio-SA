@extends('plantilla')

@section('title', 'Editar Comunicado')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Comunicado</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('comunicados.index') }}" class="text-decoration-none">Comunicados</a></li>
                <li class="breadcrumb-item active">Editar</li>
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
                    <h5 class="mb-0 fw-bold text-warning"><i class="fas fa-edit me-2"></i>Modificar Información</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('comunicados.update', $comunicado->id) }}" method="POST">
                        @csrf @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Título del comunicado</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $comunicado->titulo) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Nivel de prioridad</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Informativo" {{ old('tipo', $comunicado->tipo) == 'Informativo' ? 'selected' : '' }}>Informativo</option>
                                    <option value="Urgente" {{ old('tipo', $comunicado->tipo) == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Contenido</label>
                                <textarea name="contenido" class="form-control" rows="5" required>{{ old('contenido', $comunicado->contenido) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Programar fecha de publicación</label>
                                <input type="datetime-local" name="fecha_publicacion" class="form-control" 
                                    value="{{ old('fecha_publicacion', $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text small">Deje vacío si desea que se publique inmediatamente o mantener la configuración actual.</div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-warning text-white px-4 shadow-sm">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('comunicados.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral informativa -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-history me-2"></i>Auditoría</h6>
                    <p class="small text-muted mb-0">
                        Última modificación realizada: <strong>{{ $comunicado->updated_at->format('d/m/Y H:i') }}</strong>. 
                        Cualquier cambio realizado aquí será visible inmediatamente para los residentes una vez guardado.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection