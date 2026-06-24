@extends('plantilla')

@section('title', 'Verificación de Inventario')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado y Navegación -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-3">Control e Inspección de Inventario</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}" class="text-decoration-none">Reservas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Verificación de Inventario</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span class="d-none d-sm-inline">Volver a Reservas</span>
        </a>
    </div>

    <!-- Resumen de la Reserva Asignada -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <span class="badge bg-info-subtle text-dark border border-info-subtle px-2.5 py-1 rounded-pill fw-medium text-uppercase fs-7 mb-2 d-inline-block">
                        Área de Inspección Requerida
                    </span>
                    <h4 class="fw-bold text-dark mb-1">{{ $reserva->areaComun->nombre }}</h4>
                    <p class="text-muted mb-0 small">ID de Reserva de Referencia: #{{ $reserva->id }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="text-secondary small fw-medium">Fecha de Uso Programado</div>
                    <div class="fs-5 fw-bold text-dark">
                        <i class="far fa-calendar-alt text-primary me-1"></i>
                        {{ \Carbon\Carbon::parse($reserva->fecha)->translatedFormat('d \d\e F, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario y Tabla de Activos Fijos -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-2 text-secondary fw-semibold">
                <i class="fas fa-boxes text-primary"></i>
                <span>Lista de Activos y Equipamiento Registrado</span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <form method="POST" action="{{ route('reservas.guardar-verificacion', $reserva->id) }}">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-secondary">
                            <tr>
                                <th class="py-3 px-4">Activo / Mobiliario</th>
                                <th class="py-3" style="width: 180px;">Estado Base</th>
                                <th class="py-3" style="width: 200px;">Condición Actual</th>
                                <th class="py-3 px-4">Observaciones y Glosa de Novedades</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reserva->areaComun->inventarios as $item)
                                <tr>
                                    <td class="py-3 px-4 fw-bold text-dark">
                                        {{ $item->nombre }}
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded text-capitalize">
                                            {{ $item->estado }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <select name="verificaciones[{{ $item->id }}][estado]" class="form-select form-select-sm px-3" required>
                                            <option value="ok" selected>🟢 OK / Operativo</option>
                                            <option value="faltante">🔴 Faltante</option>
                                            <option value="roto">🟡 Roto / Dañado</option>
                                            <option value="otro">🔵 Otro Diagnóstico</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4">
                                        <input type="text" name="verificaciones[{{ $item->id }}][observacion]" class="form-control form-control-sm px-3" placeholder="Ej: Rayaduras menores, falta control remoto...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
                                        Esta área común no tiene activos vinculados en el inventario actual.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Botones de Acción Formulario -->
                <div class="card-footer bg-white p-4 border-top border-light d-flex justify-content-end gap-2">
                    <a href="{{ route('reservas.index') }}" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4 shadow-sm fw-medium">
                        <i class="fas fa-check-double me-1.5"></i>Guardar Reporte de Verificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection