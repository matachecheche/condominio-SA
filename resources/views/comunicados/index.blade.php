@extends('plantilla')

@section('title', 'Comunicados')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Comunicados Institucionales</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Comunicados</li>
                </ol>
            </nav>
        </div>
        
        @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
            <a href="{{ route('comunicados.create') }}" class="btn btn-primary shadow-sm px-3 d-inline-flex align-items-center gap-2">
                <i class="fas fa-bullhorn"></i> Publicar Comunicado
            </a>
        @endif
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Título</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Contenido</th>
                            <th class="py-3">Autor</th>
                            <th class="py-3">Publicación</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comunicados as $comunicado)
                        <tr>
                            <td class="py-3 px-4 fw-bold text-dark">{{ $comunicado->titulo }}</td>
                            <td class="py-3">
                                <span class="badge {{ $comunicado->tipo === 'Urgente' ? 'bg-danger' : 'bg-info' }} text-white border px-2">
                                    {{ $comunicado->tipo }}
                                </span>
                            </td>
                            <td class="py-3 text-secondary">{{ Str::limit($comunicado->contenido, 45) }}</td>
                            <td class="py-3 text-secondary small">{{ $comunicado->usuario->name ?? '---' }}</td>
                            <td class="py-3 text-secondary small">
                                {{ $comunicado->fecha_publicacion ? $comunicado->fecha_publicacion->format('d/m/Y H:i') : 'Inmediato' }}
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-inline-flex gap-1">
                                    <!-- Botón de Ver Detalle -->
                                    <a href="{{ route('comunicados.show', $comunicado->id) }}" class="btn btn-outline-info btn-sm" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
                                        <a href="{{ route('comunicados.edit', $comunicado->id) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminarComunicado({{ $comunicado->id }}, '{{ $comunicado->titulo }}')" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="delete-form-{{ $comunicado->id }}" action="{{ route('comunicados.destroy', $comunicado->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay comunicados registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top border-light py-3 d-flex justify-content-center">
            {{ $comunicados->links() }}
        </div>
    </div>
</div>

<script>
    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
    @endif

    function confirmarEliminarComunicado(id, titulo) {
        Swal.fire({
            title: '¿Eliminar comunicado?',
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