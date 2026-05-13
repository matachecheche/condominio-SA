@extends('plantilla')

@section('title', 'Cuotas y Pagos')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
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
    <h1 class="mt-4">Cuotas y Pagos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Cuotas</li>
    </ol>

    <div class="mb-4">
        <a href="{{ route('cuotas.create') }}"><button type="button" class="btn btn-primary btn-sm">Emitir Nueva Cuota</button></a>
    </div>

    <!-- SECCIÓN DE FILTROS -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filtros
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('cuotas.index') }}" class="row g-3">
                <!-- Filtro por búsqueda de texto -->
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar (Nombre, Apellido, Unidad)</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Ingrese término...">
                </div>

                <!-- Filtro por estado -->
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">-- Todos --</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        <option value="pagado" {{ request('estado') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                    </select>
                </div>

                <!-- Filtro por tipo de tiempo -->
                <div class="col-md-3">
                    <label for="filtro_tiempo" class="form-label">Filtro por Tiempo</label>
                    <select name="filtro_tiempo" id="filtro_tiempo" class="form-select" onchange="mostrarCamposTiempo()">
                        <option value="">-- Selecciona --</option>
                        <option value="fecha" {{ request('filtro_tiempo') == 'fecha' ? 'selected' : '' }}>Por Rango de Fechas</option>
                        <option value="mes" {{ request('filtro_tiempo') == 'mes' ? 'selected' : '' }}>Por Mes</option>
                        <option value="semana" {{ request('filtro_tiempo') == 'semana' ? 'selected' : '' }}>Por Semana</option>
                        <option value="anio" {{ request('filtro_tiempo') == 'anio' ? 'selected' : '' }}>Por Año</option>
                    </select>
                </div>

                <!-- Campos condicionales para tiempo -->
                <div id="camposTiempo" class="col-md-12">
                    <!-- Rango de fechas -->
                    <div id="fechas" style="display:none;" class="row g-2">
                        <div class="col-md-3">
                            <label for="fecha_desde" class="form-label">Desde</label>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_hasta" class="form-label">Hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                        </div>
                    </div>

                    <!-- Mes -->
                    <div id="mes" style="display:none;" class="row g-2">
                        <div class="col-md-3">
                            <label for="mes" class="form-label">Mes</label>
                            <input type="month" name="mes" class="form-control" value="{{ request('mes') }}">
                        </div>
                    </div>

                    <!-- Semana -->
                    <div id="semana" style="display:none;" class="row g-2">
                        <div class="col-md-3">
                            <label for="semana" class="form-label">Semana</label>
                            <input type="week" name="semana" class="form-control" value="{{ request('semana') }}">
                        </div>
                    </div>

                    <!-- Año -->
                    <div id="anio" style="display:none;" class="row g-2">
                        <div class="col-md-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" name="anio" class="form-control" min="2000" max="2099" value="{{ request('anio') }}" placeholder="Ej: 2026">
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('cuotas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE CUOTAS -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla Cuotas
        </div>
        <div class="card-body table-responsive">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Residente</th>
                        <th>Mes</th>
                        <th>Monto</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cuotas as $cuota)
                    <tr>
                        <td>{{ $cuota->id }}</td>
                        <td>
                            @if($cuota->residente)
                                {{ $cuota->residente->nombre }} {{ $cuota->residente->apellido }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if(is_string($cuota->fecha_emision))
                                {{ \Carbon\Carbon::parse($cuota->fecha_emision)->format('M Y') }}
                            @else
                                {{ $cuota->fecha_emision->format('M Y') }}
                            @endif
                        </td>
                        <td>Bs {{ number_format($cuota->monto, 2) }}</td>
                        <td>
                            @if($cuota->categoria)
                                <span class="badge bg-secondary">{{ $cuota->categoria }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($cuota->estado === 'pendiente')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($cuota->estado === 'activa')
                                <span class="badge bg-info">Activa</span>
                            @elseif($cuota->estado === 'cancelada')
                                <span class="badge bg-danger">Cancelada</span>
                            @elseif($cuota->estado === 'pagado')
                                <span class="badge bg-success">Pagado</span>
                            @else
                                <span class="badge bg-secondary">{{ $cuota->estado }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('cuotas.show', $cuota->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('cuotas.edit', $cuota->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('cuotas.destroy', $cuota->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta cuota?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No hay cuotas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINACIÓN -->
    <div class="d-flex justify-content-center">
        {{ $cuotas->links() }}
    </div>
</div>

<script>
    function mostrarCamposTiempo() {
        const filtro = document.getElementById('filtro_tiempo').value;
        
        // Ocultar todos
        document.getElementById('fechas').style.display = 'none';
        document.getElementById('mes').style.display = 'none';
        document.getElementById('semana').style.display = 'none';
        document.getElementById('anio').style.display = 'none';
        
        // Mostrar el seleccionado
        if (filtro === 'fecha') {
            document.getElementById('fechas').style.display = 'flex';
        } else if (filtro === 'mes') {
            document.getElementById('mes').style.display = 'flex';
        } else if (filtro === 'semana') {
            document.getElementById('semana').style.display = 'flex';
        } else if (filtro === 'anio') {
            document.getElementById('anio').style.display = 'flex';
        }
    }
    
    // Ejecutar al cargar la página si hay un filtro seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        mostrarCamposTiempo();
    });
</script>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush

@endsection