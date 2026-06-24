@extends('plantilla')

@section('title', 'Eventos Comunitarios')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Eventos Comunitarios</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Eventos</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('eventos.create') }}" class="btn btn-primary shadow-sm px-3">
            <i class="fas fa-plus me-2"></i> Nuevo Evento
        </a>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 bg-light">
            <form method="GET" action="{{ route('eventos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Buscar evento</label>
                    <input type="text" name="search" class="form-control" placeholder="Nombre o lugar..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach(['programado','en_curso','finalizado','cancelado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary px-4" type="submit">Filtrar</button>
                    <a href="{{ route('eventos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th class="py-3 px-4">Nombre del Evento</th>
                            <th class="py-3">Lugar</th>
                            <th class="py-3">Fecha y Hora</th>
                            <th class="py-3 text-center">Cupo</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventos as $ev)
                        <tr>
                            <td class="py-3 px-4 fw-bold text-dark">{{ Str::limit($ev->nombre, 40) }}</td>
                            <td class="py-3 text-secondary">{{ $ev->lugar }}</td>
                            <td class="py-3 text-secondary">{{ $ev->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td class="py-3 text-center text-secondary">{{ $ev->cupo_maximo ?? '∞' }}</td>
                            <td class="py-3 text-center">
                                @php 
                                    $colors = ['programado'=>'bg-primary text-white','en_curso'=>'bg-warning text-dark','finalizado'=>'bg-success text-white','cancelado'=>'bg-danger text-white'];
                                @endphp
                                <span class="badge {{ $colors[$ev->estado] ?? 'bg-secondary' }} px-2 py-1 rounded-pill">
                                    {{ ucfirst(str_replace('_',' ',$ev->estado)) }}
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('eventos.show', $ev->id) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('eventos.edit', $ev->id) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminar({{ $ev->id }}, '{{ $ev->nombre }}')" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $ev->id }}" action="{{ route('eventos.destroy', $ev->id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay eventos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $eventos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
    @endif

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar Evento?',
            text: `Se borrará permanentemente: ${nombre}`,
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