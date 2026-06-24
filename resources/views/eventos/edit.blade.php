@extends('plantilla')

@section('title', 'Editar Evento')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Editar Evento</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('eventos.index') }}" class="text-decoration-none">Eventos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-edit me-2 text-primary"></i>Actualizar información del evento</h5>
                </div>
                <div class="card-body p-4">
                    
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('eventos.update', $evento->id) }}" method="POST">
                        @csrf @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Datos básicos -->
                            <div class="col-md-8">
                                <label class="form-label fw-bold text-secondary small">Nombre del evento</label>
                                <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $evento->nombre) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small">Estado</label>
                                <select name="estado" class="form-select border-primary" required>
                                    @foreach(['programado','en_curso','finalizado','cancelado'] as $est)
                                        <option value="{{ $est }}" {{ old('estado', $evento->estado) == $est ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $est)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Ubicación y Tiempo -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Lugar</label>
                                <input type="text" name="lugar" class="form-control" value="{{ old('lugar', $evento->lugar) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small">Fecha y Hora</label>
                                <input type="datetime-local" name="fecha_hora" class="form-control"
                                       value="{{ old('fecha_hora', $evento->fecha_hora->format('Y-m-d\TH:i')) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Cupo máximo (dejar vacío si es ilimitado)</label>
                                <input type="number" min="1" name="cupo_maximo" class="form-control" value="{{ old('cupo_maximo', $evento->cupo_maximo) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold text-secondary small">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion', $evento->descripcion) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-3">
                            <a href="{{ route('eventos.index') }}" class="btn btn-light px-4">Cancelar</a>
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