@extends('plantilla')
@section('title', 'Enviar Notificación')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Enviar Notificación a Residentes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('notificaciones.index') }}">Notificaciones</a></li>
        <li class="breadcrumb-item active">Nueva</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-bell me-1"></i> Nueva Notificación</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('notificaciones.store') }}" method="POST" id="formNotificacion">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="Informativa" {{ old('tipo')=='Informativa'?'selected':'' }}>Informativa</option>
                            <option value="Urgente" {{ old('tipo')=='Urgente'?'selected':'' }}>Urgente</option>
                            <option value="Recordatorio" {{ old('tipo')=='Recordatorio'?'selected':'' }}>Recordatorio</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Contenido <span class="text-danger">*</span></label>
                        <textarea name="contenido" class="form-control" rows="4" required>{{ old('contenido') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Destinatario <span class="text-danger">*</span></label>
                        <select name="destinatario" class="form-select" id="destinatarioSelect" required>
                            <option value="todos" {{ old('destinatario','todos')=='todos'?'selected':'' }}>Todos los residentes</option>
                            <option value="individual" {{ old('destinatario')=='individual'?'selected':'' }}>Un residente específico</option>
                        </select>
                    </div>
                    <div class="col-md-8" id="residenteWrapper" style="{{ old('destinatario')=='individual' ? '' : 'display:none;' }}">
                        <label class="form-label">Residente</label>
                        <select name="residente_id" class="form-select">
                            <option value="">— Seleccionar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Enviar Notificación</button>
                    <a href="{{ route('notificaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('destinatarioSelect').addEventListener('change', function () {
        document.getElementById('residenteWrapper').style.display = this.value === 'individual' ? '' : 'none';
    });
</script>
@endsection
