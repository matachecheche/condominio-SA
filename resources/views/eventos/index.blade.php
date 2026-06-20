@extends('plantilla')
@section('title', 'Eventos Comunitarios')
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
    <h1 class="mt-4">Eventos Comunitarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Eventos</li>
    </ol>

    <div class="mb-3">
        <a href="{{ route('eventos.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Evento
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-calendar-star me-1"></i> CU19 · Gestionar Eventos Comunitarios</div>
        <div class="card-body">
            <form method="GET" action="{{ route('eventos.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nombre o lugar..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            @foreach(['programado','en_curso','finalizado','cancelado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Filtrar</button>
                        <a href="{{ route('eventos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Lugar</th>
                        <th>Fecha y Hora</th>
                        <th>Cupo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $ev)
                    <tr>
                        <td>{{ Str::limit($ev->nombre, 40) }}</td>
                        <td>{{ $ev->lugar }}</td>
                        <td>{{ $ev->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $ev->cupo_maximo ?? 'Sin límite' }}</td>
                        <td>
                            @php $ec = ['programado'=>'bg-primary','en_curso'=>'bg-warning text-dark','finalizado'=>'bg-success','cancelado'=>'bg-danger'] @endphp
                            <span class="badge {{ $ec[$ev->estado] ?? 'bg-secondary' }}">{{ ucfirst(str_replace('_',' ',$ev->estado)) }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('eventos.show', $ev->id) }}" class="btn btn-info">Ver</a>
                                <a href="{{ route('eventos.edit', $ev->id) }}" class="btn btn-warning">Editar</a>
                                <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#delEv-{{ $ev->id }}">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="delEv-{{ $ev->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Eliminar Evento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">¿Eliminar el evento <strong>{{ $ev->nombre }}</strong>?</div>
                            <div class="modal-footer">
                                <form action="{{ route('eventos.destroy', $ev->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div></div>
                    </div>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">No hay eventos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-2">
                {{ $eventos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
