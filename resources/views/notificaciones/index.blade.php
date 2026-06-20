@extends('plantilla')
@section('title', 'Notificaciones a Residentes')
@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@section('content')
@if(session('success'))
<script>
    Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2500,timerProgressBar:true})
        .fire({icon:'success',title:@json(session('success'))});
</script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4">Notificaciones a Residentes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Notificaciones</li>
    </ol>

    <div class="mb-3">
        <a href="{{ route('notificaciones.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-paper-plane me-1"></i> Enviar Notificación
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-bell me-1"></i> CU17 · Enviar Notificaciones a Residentes</div>
        <div class="card-body">
            <form method="GET" action="{{ route('notificaciones.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Título o contenido..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="tipo" class="form-select form-select-sm">
                            <option value="">Todos los tipos</option>
                            @foreach(['Urgente','Informativa','Recordatorio'] as $t)
                            <option value="{{ $t }}" {{ request('tipo')==$t?'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Filtrar</button>
                        <a href="{{ route('notificaciones.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Residente</th>
                        <th>Tipo</th>
                        <th>Leída</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificaciones as $n)
                    <tr>
                        <td>{{ Str::limit($n->titulo, 40) }}</td>
                        <td>{{ $n->residente ? $n->residente->nombre.' '.$n->residente->apellido : 'Todos' }}</td>
                        <td>
                            @php $tc = ['Urgente'=>'bg-danger','Informativa'=>'bg-info','Recordatorio'=>'bg-warning text-dark'] @endphp
                            <span class="badge {{ $tc[$n->tipo] ?? 'bg-secondary' }}">{{ $n->tipo }}</span>
                        </td>
                        <td>
                            @if($n->leida)
                                <span class="badge bg-success">Leída</span>
                            @else
                                <span class="badge bg-secondary">Pendiente</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($n->fecha_hora)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('notificaciones.show', $n->id) }}" class="btn btn-info">Ver</a>
                                @if(!$n->leida)
                                <form action="{{ route('notificaciones.marcar-leida', $n->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Marcar leída</button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#delNot-{{ $n->id }}">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="delNot-{{ $n->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Eliminar Notificación</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">¿Eliminar la notificación <strong>{{ $n->titulo }}</strong>?</div>
                            <div class="modal-footer">
                                <form action="{{ route('notificaciones.destroy', $n->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div></div>
                    </div>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">No hay notificaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-2">
                {{ $notificaciones->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
