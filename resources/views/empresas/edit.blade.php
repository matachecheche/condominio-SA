@extends('plantilla')

@section('title', 'Editar Empresa')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Empresa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('empresas.index') }}" class="text-decoration-none">Empresas</a></li>
                <li class="breadcrumb-item active">Editar: {{ $empresa->nombre }}</li>
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
                    <form action="{{ route('empresas.update', $empresa->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Nombre de la Empresa</label>
                                <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $empresa->nombre) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Servicio Prestado</label>
                                <input type="text" name="servicio" class="form-control" value="{{ old('servicio', $empresa->servicio) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Teléfono de Contacto</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $empresa->telefono) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" value="{{ old('correo', $empresa->correo) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Dirección Física</label>
                                <textarea name="direccion" class="form-control" rows="2">{{ old('direccion', $empresa->direccion) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Observaciones Adicionales</label>
                                <textarea name="observacion" class="form-control" rows="3">{{ old('observacion', $empresa->observacion) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-4 shadow-sm text-white">
                                <i class="fas fa-save me-1"></i> Actualizar Registro
                            </button>
                            <a href="{{ route('empresas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-history me-2"></i>Bitácora</h6>
                    <p class="small text-muted mb-0">
                        ID del registro: <strong>#{{ $empresa->id }}</strong><br>
                        Última actualización: {{ $empresa->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection