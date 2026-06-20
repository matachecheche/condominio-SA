@extends('plantilla')
@section('title', 'Reclamos Administrativos')
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
    <h1 class="mt-4">Reclamos Administrativos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reclamos</li>
    </ol>

    <div class="mb-3">
        <a href="{{ route('reclamos.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Reclamo
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-exclamation-circle me-1"></i> CU18 · Gestionar Reclamos Administrativos</div>
        <div class="card-body">
            <form method="GET" action="{{ route('reclamos.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="N° seguimiento, título o residente..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            @foreach(['pendiente','en_revision','resuelto','rechazado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Filtrar</button>
                        <a href="{{ route('reclamos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>N° Seguimiento</th>
                        <th>Título</th>
                        <th>Residente</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reclamos as $rec)
                    <tr>
                        <td><code>{{ $rec->numero_seguimiento }}</code></td>
                        <td>{{ Str::limit($rec->titulo, 40) }}</td>
                        <td>{{ $rec->residente->nombre }} {{ $rec->residente->apellido }}</td>
                        <td>
                            @php $ec = ['pendiente'=>'bg-warning text-dark','en_revision'=>'bg-primary','resuelto'=>'bg-success','rechazado'=>'bg-danger'] @endphp
                            <span class="badge {{ $ec[$rec->estado] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_',' ',$rec->estado)) }}</span>
                        </td>
                        <td>{{ $rec->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('reclamos.show', $rec->id) }}" class="btn btn-info">Ver</a>
                                <a href="{{ route('reclamos.edit', $rec->id) }}" class="btn btn-warning">Editar</a>
                                <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#delRec-{{ $rec->id }}">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="delRec-{{ $rec->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Eliminar Reclamo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">¿Eliminar el reclamo <strong>{{ $rec->numero_seguimiento }}</strong>?</div>
                            <div class="modal-footer">
                                <form action="{{ route('reclamos.destroy', $rec->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div></div>
                    </div>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">No hay reclamos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-2">
                {{ $reclamos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
