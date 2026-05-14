@extends('layouts.ap')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="fas fa-home text-primary"></i> 
                Gestión de Propiedades
            </h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('propiedades.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Propiedad
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-barcode"></i> Código</th>
                        <th><i class="fas fa-building"></i> Tipo</th>
                        <th><i class="fas fa-map-marker-alt"></i> Ubicación</th>
                        <th><i class="fas fa-user"></i> Residente</th>
                        <th><i class="fas fa-info-circle"></i> Estado</th>
                        <th class="text-center"><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($propiedades as $propiedad)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $propiedad->id }}</span></td>
                            <td><strong>{{ $propiedad->codigo }}</strong></td>
                            <td>{{ $propiedad->tipo }}</td>
                            <td>{{ $propiedad->ubicacion }}</td>
                            <td>{{ $propiedad->residente->nombre_completo ?? 'Sin asignar' }}</td>
                            <td><span class="badge bg-info">{{ $propiedad->estado }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('propiedades.show', $propiedad->id) }}" class="btn btn-sm btn-outline-info">Ver</a>
                                <a href="{{ route('propiedades.edit', $propiedad->id) }}" class="btn btn-sm btn-outline-warning">Editar</a>
                                <form action="{{ route('propiedades.destroy', $propiedad->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No hay propiedades registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-center">
                {{ $propiedades->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
