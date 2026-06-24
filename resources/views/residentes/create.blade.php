@extends('layouts.ap')
@can('crear residentes')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Registrar Nuevo Residente</h2>
            <p class="text-muted small mb-0">Ingrese los datos personales, de contacto y tipo de habitante para darlo de alta en el sistema.</p>
        </div>
        <a href="{{ route('residentes.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
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

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('residentes.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-id-card me-2"></i>Datos Personales
                        </h5>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-medium text-secondary">Nombre(s)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-user"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control px-3" placeholder="Ej. Alejandro" value="{{ old('nombre') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label fw-medium text-secondary">Apellido(s)</label>
                            <input type="text" name="apellido" id="apellido" class="form-control px-3" placeholder="Ej. Suárez" value="{{ old('apellido') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="ci" class="form-label fw-medium text-secondary">Cédula de Identidad (CI)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-address-card"></i></span>
                                <input type="text" name="ci" id="ci" class="form-control px-3" placeholder="Ej. 7654321" value="{{ old('ci') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <h5 class="text-primary fw-semibold mb-3 pb-2 border-bottom border-light">
                            <i class="fas fa-home me-2"></i>Contacto y Residencia
                        </h5>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium text-secondary">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control px-3" placeholder="usuario@correo.com" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tipo_residente" class="form-label fw-medium text-secondary">Tipo de Residente</label>
                            <select name="tipo_residente" id="tipo_residente" class="form-select px-3" required>
                                <option value="" disabled selected>-- Seleccionar --</option>
                                <option value="Propietario" {{ old('tipo_residente') == 'Propietario' ? 'selected' : '' }}>Propietario</option>
                                <option value="Inquilino" {{ old('tipo_residente') == 'Inquilino' ? 'selected' : '' }}>Inquilino</option>
                                <option value="Otro" {{ old('tipo_residente') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="{{ route('residentes.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Registrar Residente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@endcan