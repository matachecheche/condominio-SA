@extends('plantilla')

@section('title', 'Nuevo Reclamo')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Registrar Nuevo Reclamo</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reclamos.index') }}" class="text-decoration-none">Reclamos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle me-2 text-primary"></i>Información del Reclamo</h5>
                </div>
                <div class="card-body p-4">
                    
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reclamos.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Título del Reclamo <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" class="form-control form-control-lg {{ $errors->has('titulo') ? 'is-invalid' : '' }}" 
                                   value="{{ old('titulo') }}" placeholder="Ej: Falla en iluminación del pasillo" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Detalle del Reclamo <span class="text-danger">*</span></label>
                            <textarea name="contenido" class="form-control {{ $errors->has('contenido') ? 'is-invalid' : '' }}" 
                                      rows="5" placeholder="Describe detalladamente el motivo de tu reclamo..." required>{{ old('contenido') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small">Residente que reclama <span class="text-danger">*</span></label>
                            <select name="residente_id" class="form-select form-select-lg {{ $errors->has('residente_id') ? 'is-invalid' : '' }}" required>
                                <option value="">— Seleccionar residente —</option>
                                @foreach($residentes as $r)
                                    <option value="{{ $r->id }}" {{ old('residente_id') == $r->id ? 'selected' : '' }}>
                                        {{ $r->apellido }}, {{ $r->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('reclamos.index') }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> Registrar Reclamo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection