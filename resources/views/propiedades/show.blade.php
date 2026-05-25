@extends('plantilla')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-home"></i> Detalles de la Propiedad</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código</label>
                        <p class="fs-5">{{ $propiedad->codigo }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <p class="fs-5">{{ $propiedad->tipo }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ubicación</label>
                        <p class="fs-5">{{ $propiedad->ubicacion }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="fs-5">{{ ucfirst($propiedad->estado) }}</p>
                    </div>
                    @if($propiedad->residente)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Residente</label>
                            <p class="fs-5">{{ $propiedad->residente->nombre_completo }}</p>
                        </div>
                    @endif
                    @if($propiedad->descripcion)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <p>{{ $propiedad->descripcion }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('propiedades.edit', $propiedad->id) }}" class="btn btn-warning">Editar</a>
                        <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
