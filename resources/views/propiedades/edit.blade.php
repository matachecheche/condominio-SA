@extends('plantilla')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Editar Propiedad</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('propiedades.update', $propiedad->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" name="codigo" id="codigo" class="form-control" value="{{ old('codigo', $propiedad->codigo) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="Apartamento" {{ old('tipo', $propiedad->tipo) == 'Apartamento' ? 'selected' : '' }}>Apartamento</option>
                                    <option value="Casa" {{ old('tipo', $propiedad->tipo) == 'Casa' ? 'selected' : '' }}>Casa</option>
                                    <option value="Local" {{ old('tipo', $propiedad->tipo) == 'Local' ? 'selected' : '' }}>Local</option>
                                    <option value="Oficina" {{ old('tipo', $propiedad->tipo) == 'Oficina' ? 'selected' : '' }}>Oficina</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="disponible" {{ old('estado', $propiedad->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="ocupada" {{ old('estado', $propiedad->estado) == 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                                    <option value="activa" {{ old('estado', $propiedad->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion', $propiedad->ubicacion) }}" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
