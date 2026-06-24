@extends('plantilla')

@section('title', 'Reclamos Administrativos')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Gestión de Reclamos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Reclamos</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('reclamos.create') }}" class="btn btn-primary shadow-sm px-3">
            <i class="fas fa-plus me-2"></i> Nuevo Reclamo
        </a>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light">
            <form method="GET" action="{{ route('reclamos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="N° seguimiento, título o nombre..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach(['pendiente','en_revision','resuelto','rechazado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary px-4" type="submit">Filtrar</button>
                    <a href="{{ route('reclamos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3">Fecha</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reclamos as $rec)
                        <tr>
                            <td class="py-3 px-4"><code class="text-primary fw-bold">{{ $rec->numero_seguimiento }}</code></td>
                            <td class="py-3 fw-medium">{{ Str::limit($rec->titulo, 40) }}</td>
                            <td class="py-3 text-secondary">{{ $rec->residente->nombre }} {{ $rec->residente->apellido }}</td>
                            <td class="py-3 text-center">
                                @php 
                                    $colors = ['pendiente'=>'bg-warning text-dark','en_revision'=>'bg-info text-white','resuelto'=>'bg-success text-white','rechazado'=>'bg-danger text-white'];
                                @endphp
                                <span class="badge {{ $colors[$rec->estado] ?? 'bg-secondary' }} px-2 py-1 rounded-pill">
                                    {{ ucfirst(str_replace('_',' ',$rec->estado)) }}
                                </span>
                            </td>
                            <td class="py-3 text-secondary">{{ $rec->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 text-end px-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('reclamos.show', $rec->id) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('reclamos.edit', $rec->id) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminar({{ $rec->id }}, '{{ $rec->numero_seguimiento }}')" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $rec->id }}" action="{{ route('reclamos.destroy', $rec->id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay reclamos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $reclamos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
    @endif

    function confirmarEliminar(id, folio) {
        Swal.fire({
            title: '¿Eliminar Reclamo?',
            text: `Se borrará permanentemente el registro ${folio}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection