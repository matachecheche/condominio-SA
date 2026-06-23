@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Editar Cargo</h2>
            <p class="text-muted small mb-0">Modifique el nombre o el estado del puesto laboral seleccionado.</p>
        </div>
        <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver al listado</span>
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
        <i class="fas fa-exclamation-circle text-danger mt-1 fs-5"></i>
        <div>
            <span class="fw-bold d-block mb-1">Por favor corrige los siguientes errores:</span>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                        <i class="fas fa-edit text-primary"></i>
                        <span>Información del Puesto</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('cargos.update', $cargo->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="cargo" class="form-label fw-medium text-secondary">Nombre del Cargo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-briefcase"></i></span>
                                <input type="text" name="cargo" id="cargo" class="form-control px-3" value="{{ old('cargo', $cargo->cargo) }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium text-secondary mb-2">Estado del Cargo</label>
                            <div class="p-2 border rounded bg-light d-flex gap-3">
                                <div class="form-check form-check-inline mb-0 ms-2">
                                    <input class="form-check-input cursor-pointer" type="radio" name="estado" id="estado_activo" value="1" {{ old('estado', $cargo->estado) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark fw-medium cursor-pointer" for="estado_activo">Activo</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input cursor-pointer" type="radio" name="estado" id="estado_inactivo" value="0" {{ old('estado', $cargo->estado) == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark fw-medium cursor-pointer" for="estado_inactivo">Inactivo</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top border-light">
                            <a href="{{ route('cargos.index') }}" class="btn btn-light border px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                                <i class="fas fa-save me-1.5"></i>Actualizar Cargo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection