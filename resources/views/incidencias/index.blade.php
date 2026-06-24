@extends('plantilla')

@section('title', 'Incidencias')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Denuncias e Incidencias</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Incidencias</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('incidencias.create') }}" class="btn btn-primary shadow-sm px-3 d-inline-flex align-items-center gap-2">
            <i class="fas fa-plus-circle"></i> Nueva Incidencia
        </a>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light rounded-3">
            <form method="GET" action="{{ route('incidencias.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Seguimiento, título o residente..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach(['pendiente','en_revision','resuelto','cerrado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary px-4 shadow-sm" type="submit">Filtrar</button>
                    <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th class="py-3 px-4">N° Seguimiento</th>
                            <th class="py-3">Título</th>
                            <th class="py-3">Residente</th>
                            <th class="py-3">Prioridad</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Fecha</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidencias as $inc)
                        <tr>
                            <td class="py-3 px-4 text-primary fw-bold font-monospace">{{ $inc->numero_seguimiento }}</td>
                            <td class="py-3 fw-medium text-dark">{{ Str::limit($inc->titulo, 40) }}</td>
                            <td class="py-3 text-secondary">{{ $inc->residente->nombre }} {{ $inc->residente->apellido }}</td>
                            <td class="py-3">
                                @php $pc = ['baja'=>'bg-info','media'=>'bg-warning','alta'=>'bg-danger'] @endphp
                                <span class="badge {{ $pc[$inc->prioridad] ?? 'bg-secondary' }} text-white px-2 py-1 rounded-pill fs-7">{{ ucfirst($inc->prioridad) }}</span>
                            </td>
                            <td class="py-3">
                                @php $ec = ['pendiente'=>'bg-warning text-dark','en_revision'=>'bg-primary','resuelto'=>'bg-success','cerrado'=>'bg-secondary'] @endphp
                                <span class="badge {{ $ec[$inc->estado] ?? 'bg-secondary' }} px-2 py-1 rounded-pill fs-7">{{ ucfirst(str_replace('_',' ',$inc->estado)) }}</span>
                            </td>
                            <td class="py-3 text-secondary small">{{ $inc->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('incidencias.show', $inc->id) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('incidencias.edit', $inc->id) }}" class="btn btn-outline-warning btn-sm text-dark" title="Editar"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminarIncidencia({{ $inc->id }}, '{{ $inc->numero_seguimiento }}')" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="delete-form-{{ $inc->id }}" action="{{ route('incidencias.destroy', $inc->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron incidencias.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $incidencias->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
    @endif

    function confirmarEliminarIncidencia(id, num) {
        Swal.fire({
            title: '¿Eliminar incidencia?',
            text: `Se eliminará permanentemente el reporte ${num}`,
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