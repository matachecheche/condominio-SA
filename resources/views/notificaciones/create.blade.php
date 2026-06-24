@extends('plantilla')

@section('title', 'Nueva Notificación')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Enviar Nueva Notificación</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('notificaciones.index') }}" class="text-decoration-none">Notificaciones</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>
    </div>

    <!-- Gestión de Errores -->
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-paper-plane me-2"></i>Detalles del Mensaje</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('notificaciones.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Título del mensaje</label>
                                <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" placeholder="Asunto de la notificación" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tipo de notificación</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Informativa" {{ old('tipo')=='Informativa'?'selected':'' }}>Informativa</option>
                                    <option value="Urgente" {{ old('tipo')=='Urgente'?'selected':'' }}>Urgente</option>
                                    <option value="Recordatorio" {{ old('tipo')=='Recordatorio'?'selected':'' }}>Recordatorio</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Contenido del mensaje</label>
                                <textarea name="contenido" class="form-control" rows="5" placeholder="Escriba aquí el cuerpo de la notificación..." required>{{ old('contenido') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Destinatario</label>
                                <select name="destinatario" class="form-select" id="destinatarioSelect" required>
                                    <option value="todos" {{ old('destinatario','todos')=='todos'?'selected':'' }}>Todos los residentes</option>
                                    <option value="individual" {{ old('destinatario')=='individual'?'selected':'' }}>Un residente específico</option>
                                </select>
                            </div>
                            <div class="col-md-8" id="residenteWrapper" style="{{ old('destinatario')=='individual' ? '' : 'display:none;' }}">
                                <label class="form-label fw-bold small text-muted">Seleccionar residente</label>
                                <select name="residente_id" class="form-select">
                                    <option value="">— Buscar residente —</option>
                                    @foreach($residentes as $r)
                                        <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                            {{ $r->apellido }}, {{ $r->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-paper-plane me-1"></i> Enviar Notificación
                            </button>
                            <a href="{{ route('notificaciones.index') }}" class="btn btn-light border px-4">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary"><i class="fas fa-lightbulb me-2"></i>Consejo</h6>
                    <p class="small text-muted mb-0">
                        Si elige <strong>"Todos los residentes"</strong>, el sistema enviará la comunicación de forma masiva. Asegúrese de revisar la redacción antes de enviar.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('destinatarioSelect').addEventListener('change', function () {
        document.getElementById('residenteWrapper').style.display = (this.value === 'individual') ? 'block' : 'none';
    });
</script>
@endsection