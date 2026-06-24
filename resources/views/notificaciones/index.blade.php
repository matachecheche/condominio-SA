@extends('plantilla')

@section('title', 'Notificaciones')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Notificaciones a Residentes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Notificaciones</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('notificaciones.create') }}" class="btn btn-primary shadow-sm px-3 d-inline-flex align-items-center gap-2">
            <i class="fas fa-paper-plane"></i> Nueva Notificación
        </a>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light rounded-3">
            <form method="GET" action="{{ route('notificaciones.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Título o contenido..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        @foreach(['Urgente','Informativa','Recordatorio'] as $t)
                            <option value="{{ $t }}" {{ request('tipo')==$t?'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary px-4 shadow-sm" type="submit">Filtrar</button>
                    <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th class="py-3 px-4">Título</th>
                            <th class="py-3">Destinatario</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Fecha</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notificaciones as $n)
                        <tr>
                            <td class="py-3 px-4 fw-bold text-dark">{{ Str::limit($n->titulo, 40) }}</td>
                            <td class="py-3 text-secondary">{{ $n->residente ? $n->residente->nombre.' '.$n->residente->apellido : 'Todos los residentes' }}</td>
                            <td class="py-3">
                                @php $tc = ['Urgente'=>'bg-danger','Informativa'=>'bg-info','Recordatorio'=>'bg-warning text-dark'] @endphp
                                <span class="badge {{ $tc[$n->tipo] ?? 'bg-secondary' }} px-2 py-1 rounded-pill fs-7">{{ $n->tipo }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge {{ $n->leida ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border px-2 py-1 rounded-pill fs-7">
                                    {{ $n->leida ? 'Leída' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="py-3 text-secondary small">{{ \Carbon\Carbon::parse($n->fecha_hora)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('notificaciones.show', $n->id) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    @if(!$n->leida)
                                        <form action="{{ route('notificaciones.marcar-leida', $n->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Marcar leída"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminarNotificacion({{ $n->id }}, '{{ $n->titulo }}')" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="delete-form-{{ $n->id }}" action="{{ route('notificaciones.destroy', $n->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron notificaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $notificaciones->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
    @endif

    function confirmarEliminarNotificacion(id, titulo) {
        Swal.fire({
            title: '¿Eliminar notificación?',
            text: `Se eliminará permanentemente: ${titulo}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, borrar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection