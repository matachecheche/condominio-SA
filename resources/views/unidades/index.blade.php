@extends('plantilla')

@section('title', 'Unidades Habitacionales')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
@if(session('success'))
<script>
    Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2000,timerProgressBar:true})
        .fire({icon:'success',title:"{{ session('success') }}"});
</script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4">Unidades Habitacionales</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Unidades</li>
    </ol>

    @can('crear unidades')
    <div class="mb-3">
        <a href="{{ route('unidades.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva Unidad
        </a>
    </div>
    @endcan

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> CU13 · Vincular Residente con Unidad Habitacional</div>
        <div class="card-body table-responsive">
            <form method="GET" action="{{ route('unidades.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Buscar por código o residente..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Buscar</button>
                        <a href="{{ route('unidades.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Residente asignado</th>
                        <th>Tipo ocupación</th>
                        <th>Personas</th>
                        <th>Vehículos</th>
                        <th>Mascotas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td><strong>{{ $u->codigo }}</strong></td>
                        <td>
                            @if($u->residente)
                                {{ $u->residente->nombre }} {{ $u->residente->apellido }}
                            @else
                                <span class="text-muted fst-italic">Sin asignar</span>
                            @endif
                        </td>
                        <td>{{ $u->tipo_ocupacion }}</td>
                        <td>{{ $u->personas_por_unidad }}</td>
                        <td>{{ $u->vehiculos }}</td>
                        <td>{{ $u->tiene_mascotas ? 'Sí' : 'No' }}</td>
                        <td>
                            <span class="badge {{ $u->estado === 'activa' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($u->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('ver unidades')
                                <a href="{{ route('unidades.show', $u->id) }}" class="btn btn-info">Ver</a>
                                @endcan
                                @can('editar unidades')
                                <a href="{{ route('unidades.edit', $u->id) }}" class="btn btn-warning">Editar</a>
                                @endcan
                                @can('eliminar unidades')
                                <button type="button" class="btn btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#delModal-{{ $u->id }}">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    @can('eliminar unidades')
                    <div class="modal fade" id="delModal-{{ $u->id }}" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Eliminar Unidad</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">¿Eliminar la unidad <strong>{{ $u->codigo }}</strong>?</div>
                            <div class="modal-footer">
                                <form action="{{ route('unidades.destroy', $u->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div></div>
                    </div>
                    @endcan
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">No hay unidades registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-2">
                {{ $unidades->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
