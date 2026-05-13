@extends('plantilla')

@section('title', 'Empresas Externas')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
{{-- EXPLICACIÓN: Mostrar mensaje de éxito si se guardó correctamente --}}
@if (session('success'))
    <script>
        let message = "{{ session('success') }}"
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: message
        });
    </script>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4">Empresas Externas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Empresas</li>
    </ol>

    {{-- BOTÓN: Registrar nueva empresa --}}
    @can('crear empresas')
    <div class="mb-4">
        <a href="{{ route('empresas.create') }}">
            <button type="button" class="btn btn-primary btn-sm">Registrar Nueva Empresa</button>
        </a>
    </div>
    @endcan

    {{-- TARJETA: Tabla de empresas --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Empresas Externas
        </div>
        <div class="card-body table-responsive">
            {{-- FORMULARIO: Filtros de búsqueda --}}
            <form method="GET" action="{{ route('empresas.index') }}" class="mb-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Buscar por nombre o servicio</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Ej: Jardinería, Seguridad" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-outline-primary" type="submit">Filtrar</button>
                    </div>
                </div>
            </form>

            {{-- TABLA: Listado de empresas --}}
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Servicio</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Calificación</th> {{-- ✅ NUEVA COLUMNA --}}
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- EXPLICACIÓN: Itera sobre cada empresa para mostrar sus datos en una fila --}}
                    @foreach($empresas as $empresa)
                    <tr>
                        <td>{{ $empresa->id }}</td>
                        <td>{{ $empresa->nombre }}</td>
                        <td>{{ $empresa->servicio }}</td>
                        <td>{{ $empresa->telefono }}</td>
                        <td>{{ $empresa->correo }}</td>
                        
                        {{-- ✅ NUEVA CELDA: Mostrar calificación con estrellas y color --}}
                        {{-- EXPLICACIÓN: Muestra la calificación con estrellas y un badge de color según el nivel --}}
                        <td>
                            <span title="Calificación: {{ $empresa->calificacion }}/5">
                                {!! str_repeat('⭐', $empresa->calificacion) !!}
                            </span>
                            <span class="badge 
                                @if($empresa->calificacion >= 5) bg-success
                                @elseif($empresa->calificacion >= 4) bg-info
                                @elseif($empresa->calificacion >= 3) bg-warning text-dark
                                @else bg-danger
                                @endif">
                                {{ $empresa->calificacion }}/5
                            </span>
                        </td>
                        
                        {{-- Botones de acciones --}}
                        <td>
                            <div class="btn-group" role="group">
                                @can('ver empresas')
                                <a href="{{ route('empresas.show', $empresa->id) }}" class="btn btn-info btn-sm">Ver</a>
                                @endcan
                                @can('editar empresas')
                                <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                @endcan
                                @can('eliminar empresas')
                                <button type="button" 
                                        class="btn btn-danger btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmarEliminar-{{ $empresa->id }}">
                                    Eliminar
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL: Confirmación de eliminación --}}
                    <div class="modal fade" 
                         id="confirmarEliminar-{{ $empresa->id }}" 
                         data-bs-backdrop="static" 
                         data-bs-keyboard="false" 
                         tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Eliminar empresa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Desea eliminar la empresa <strong>{{ $empresa->nombre }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <form action="{{ route('empresas.destroy', $empresa->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-primary btn-sm">Aceptar</button>
                                    </form>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="d-flex justify-content-center">
        {{ $empresas->links() }}
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush