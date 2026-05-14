@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Registrar Nueva Propiedad</h5>
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

                    <form action="{{ route('propiedades.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código de Propiedad <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: APTO-101" value="{{ old('codigo') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo de Propiedad <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Apartamento">Apartamento</option>
                                    <option value="Casa">Casa</option>
                                    <option value="Local">Local</option>
                                    <option value="Oficina">Oficina</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="activa">Activa</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" placeholder="Ej: Calle Principal #123" value="{{ old('ubicacion') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="residente_id" class="form-label">Residente Asignado</label>
                            <select name="residente_id" id="residente_id" class="form-select">
                                <option value="">-- Sin asignar --</option>
                                @foreach($residentes as $residente)
                                    <option value="{{ $residente->id }}">{{ $residente->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
