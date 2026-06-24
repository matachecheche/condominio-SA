@extends('plantilla')

@section('title', 'Nuevo Comunicado')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Publicar Nuevo Comunicado</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('comunicados.index') }}" class="text-decoration-none">Comunicados</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
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
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-bullhorn me-2"></i>Detalles del Comunicado</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('comunicados.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Título del comunicado</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" placeholder="Asunto principal" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Nivel de prioridad</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Informativo" {{ old('tipo') == 'Informativo' ? 'selected' : '' }}>Informativo</option>
                                    <option value="Urgente" {{ old('tipo') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Contenido</label>
                                <textarea name="contenido" class="form-control" rows="5" placeholder="Escriba el contenido del comunicado aquí..." required>{{ old('contenido') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Programar publicación</label>
                                <input type="datetime-local" name="fecha_publicacion" class="form-control" value="{{ old('fecha_publicacion') }}">
                                <div class="form-text small">Si se deja vacío, el comunicado se publicará inmediatamente tras guardar.</div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-paper-plane me-1"></i> Publicar Comunicado
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
                    <h6 class="fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>Buenas prácticas</h6>
                    <p class="small text-muted mb-0">
                        Asegúrese de que el título sea conciso y descriptivo. Si marca el comunicado como <strong>Urgente</strong>, este tendrá una distinción visual especial para los residentes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection