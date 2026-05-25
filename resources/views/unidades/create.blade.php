@extends('plantilla')
@section('title', 'Nueva Unidad')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Nueva Unidad Habitacional</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}">Unidades</a></li>
        <li class="breadcrumb-item active">Nueva</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> Registrar Unidad</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul></div>
            @endif

            <form action="{{ route('unidades.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select" required>
                            <option value="activa" {{ old('estado','activa')=='activa'?'selected':'' }}>Activa</option>
                            <option value="inactiva" {{ old('estado')=='inactiva'?'selected':'' }}>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Ocupación <span class="text-danger">*</span></label>
                        <select name="tipo_ocupacion" class="form-select" required>
                            <option value="Propietario" {{ old('tipo_ocupacion','Propietario')=='Propietario'?'selected':'' }}>Propietario</option>
                            <option value="Inquilino" {{ old('tipo_ocupacion')=='Inquilino'?'selected':'' }}>Inquilino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad',4) }}" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Personas por unidad <span class="text-danger">*</span></label>
                        <input type="number" name="personas_por_unidad" class="form-control" value="{{ old('personas_por_unidad',1) }}" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehículos <span class="text-danger">*</span></label>
                        <input type="number" name="vehiculos" class="form-control" value="{{ old('vehiculos',0) }}" min="0" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="tiene_mascotas" id="mascotas" value="1"
                                   {{ old('tiene_mascotas') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mascotas">Tiene mascotas</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Placa vehículo</label>
                        <input type="text" name="placa" class="form-control" value="{{ old('placa') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Marca vehículo</label>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Vincular Residente</label>
                        <select name="residente_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }} — CI: {{ $r->ci }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Un residente activo sólo puede tener una unidad activa.</small>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('unidades.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
