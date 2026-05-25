@extends('plantilla')
@section('title', 'Incidencias')
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
    <h1 class="mt-4">Denuncias e Incidencias</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Incidencias</li>
    </ol>

    <div class="mb-3">
        <a href="{{ route('incidencias.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva Incidencia
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> CU16 · Gestionar Denuncias / Reportes de Incidencias</div>
        <div class="card-body">
            <form method="GET" action="{{ route('incidencias.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="N° seguimiento, título o residente..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            @foreach(['pendiente','en_revision','resuelto','cerrado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Filtrar</button>
                        <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>N° Seguimiento</th>
                        <th>Título</th>
                        <th>Residente</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidencias as $inc)
                    <tr>
                        <td><code>{{ $inc->numero_seguimiento }}</code></td>
                        <td>{{ Str::limit($inc->titulo, 40) }}</td>
                        <td>{{ $inc->residente->nombre }} {{ $inc->residente->apellido }}</td>
                        <td>
                            @php $pc = ['baja'=>'bg-info','media'=>'bg-warning text-dark','alta'=>'bg-danger'] @endphp
                            <span class="badge {{ $pc[$inc->prioridad] ?? 'bg-secondary' }}">{{ ucfirst($inc->prioridad) }}</span>
                        </td>
                        <td>
                            @php $ec = ['pendiente'=>'bg-warning text-dark','en_revision'=>'bg-primary','resuelto'=>'bg-success','cerrado'=>'bg-secondary'] @endphp
                            <span class="badge {{ $ec[$inc->estado] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_',' ',$inc->estado)) }}</span>
                        </td>
                        <td>{{ $inc->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('incidencias.show', $inc->id) }}" class="btn btn-info">Ver</a>
                                <a href="{{ route('incidencias.edit', $inc->id) }}" class="btn btn-warning">Editar</a>
                                <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#delInc-{{ $inc->id }}">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="delInc-{{ $inc->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Eliminar Incidencia</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">¿Eliminar la incidencia <strong>{{ $inc->numero_seguimiento }}</strong>?</div>
                            <div class="modal-footer">
                                <form action="{{ route('incidencias.destroy', $inc->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div></div>
                    </div>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay incidencias registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-2">
                {{ $incidencias->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
