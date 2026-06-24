@extends('plantilla')

@section('title', 'Editar Multa')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Multa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('multas.index') }}" class="text-decoration-none">Multas</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-edit me-2 text-warning"></i>Actualizar detalles de la multa
                    </h5>
                </div>
                <div class="card-body p-4">

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('multas.update', $multa->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <!-- Información de solo lectura -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Usuario afectado</label>
                                <input type="text" class="form-control bg-light" readonly
                                    value="{{ optional($multa->residente)->nombre_completo ?? optional($multa->empleado)->nombre_completo ?? 'N/A' }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Motivo de la Multa <span class="text-danger">*</span></label>
                                <input type="text" name="motivo" class="form-control" value="{{ old('motivo', $multa->motivo) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Monto (Bs.) <span class="text-danger">*</span></label>
                                <input type="number" name="monto" step="0.01" class="form-control" value="{{ old('monto', $multa->monto) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Estado Actual <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select border-warning" required>
                                    @foreach(['pendiente','pagada','anulada','apelada'] as $estado)
                                        <option value="{{ $estado }}" {{ old('estado', $multa->estado) === $estado ? 'selected' : '' }}>
                                            {{ ucfirst($estado) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Fecha de Emisión</label>
                                <input type="date" name="fechaEmision" class="form-control" 
                                    value="{{ old('fechaEmision', \Carbon\Carbon::parse($multa->fechaEmision)->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Fecha Límite</label>
                                <input type="date" name="fechaLimite" class="form-control" 
                                    value="{{ old('fechaLimite', \Carbon\Carbon::parse($multa->fechaLimite)->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-3">
                            <a href="{{ route('multas.index') }}" class="btn btn-light px-4">Cancelar</a>
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