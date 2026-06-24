@extends('plantilla') {{-- Ajusta si usas 'layouts.ap' o 'plantilla' --}}

@section('title', 'Bitácora del Sistema')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1 fs-2">Bitácora del Sistema</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item active">Bitácora</li>
            </ol>
        </nav>
    </div>

    <!-- Filtros (Opcional, muy útil para bitácoras) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light">
            <form method="GET" action="#" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Buscar usuario o acción</label>
                    <input type="text" name="search" class="form-control" placeholder="Ej: admin o login..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Usuario</th>
                            <th class="py-3">Acción</th>
                            <th class="py-3">Descripción</th>
                            <th class="py-3">Fecha y Hora</th>
                            <th class="py-3 px-4">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bitacoras as $log)
                        <tr>
                            <td class="py-3 px-4 fw-medium text-dark">
                                <i class="fas fa-user-circle me-2 text-primary"></i>{{ $log->usuario ?? 'Invitado' }}
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border">{{ $log->accion }}</span>
                            </td>
                            <td class="py-3 text-secondary small">{{ $log->descripcion ?? '-' }}</td>
                            <td class="py-3 text-secondary small">{{ $log->fecha_hora }}</td>
                            <td class="py-3 px-4 font-monospace small text-muted">{{ $log->ip ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No se encontraron registros en la bitácora.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Paginación -->
        @if($bitacoras->hasPages())
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $bitacoras->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection