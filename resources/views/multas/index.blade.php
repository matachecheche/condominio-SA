@extends('plantilla')

@section('title', 'Panel de Multas')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1 fs-2">Panel de Multas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item active">Multas</li>
                </ol>
            </nav>
        </div>
        
        @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
            <a href="{{ route('multas.create') }}" class="btn btn-primary shadow-sm px-3">
                <i class="fas fa-plus me-2"></i> Nueva Multa
            </a>
        @endif
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatablesMultas" class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="py-3 px-4">Usuario</th>
                            <th class="py-3">Motivo</th>
                            <th class="py-3 text-center">Monto (Bs.)</th>
                            <th class="py-3">Fechas</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3 text-end px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($multas as $multa)
                        <tr>
                            <td class="py-3 px-4 fw-medium text-dark">
                                {{ optional($multa->residente)->nombre_completo ?? optional($multa->empleado)->nombre_completo ?? 'N/A' }}
                            </td>
                            <td class="py-3 text-secondary">{{ $multa->motivo }}</td>
                            <td class="py-3 text-center fw-bold">{{ number_format($multa->monto, 2) }}</td>
                            <td class="py-3 small text-muted">
                                <div><small>Emitida:</small> {{ \Carbon\Carbon::parse($multa->fechaEmision)->format('d/m/Y') }}</div>
                                <div><small>Límite:</small> {{ \Carbon\Carbon::parse($multa->fechaLimite)->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 text-center">
                                @php 
                                    $states = ['pendiente'=>'bg-warning text-dark', 'pagada'=>'bg-success text-white', 'anulada'=>'bg-danger text-white', 'apelada'=>'bg-info text-white'];
                                @endphp
                                <span class="badge {{ $states[$multa->estado] ?? 'bg-secondary' }} px-2 py-1 rounded-pill">
                                    {{ ucfirst($multa->estado) }}
                                </span>
                            </td>
                            <td class="py-3 text-end px-4">
                                <div class="d-flex justify-content-end gap-1">
                                    @if(auth()->check() && (auth()->user()->residente_id || auth()->user()->empleado_id) && $multa->estado == 'pendiente')
                                        <a href="{{ route('pagos.create.multa', ['multa' => $multa->id]) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-money-bill-wave me-1"></i>Pagar</a>
                                    @endif
                                    
                                    @if(auth()->check() && (auth()->user()->residente_id || auth()->user()->empleado_id) && $multa->estado == 'pagada' && $multa->pagos->isNotEmpty())
                                        <a href="{{ route('pagos.comprobante', $multa->pagos->first()->id) }}" class="btn btn-outline-primary btn-sm" target="_blank"><i class="fas fa-file-invoice me-1"></i>Ver</a>
                                    @endif

                                    @if(auth()->check() && !auth()->user()->residente_id && !auth()->user()->empleado_id)
                                        <a href="{{ route('multas.edit', $multa->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmarEliminar({{ $multa->id }}, '{{ optional($multa->residente)->nombre_completo ?? 'Usuario' }}')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="delete-form-{{ $multa->id }}" action="{{ route('multas.destroy', $multa->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        new simpleDatatables.DataTable("#datatablesMultas", {
            labels: {
                placeholder: "Buscar...",
                perPage: "entradas por página",
                noRows: "No se encontraron registros",
                info: "Mostrando {start} a {end} de {rows} entradas",
            }
        });
    });

    @if(session('success'))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: "{{ session('success') }}", showConfirmButton: false, timer: 2000 });
    @endif

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar multa?',
            text: `Se anulará la multa de ${nombre}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { document.getElementById('delete-form-' + id).submit(); }
        });
    }
</script>
@endsection