@extends('plantilla')

@section('title', 'Resultados de Búsqueda')

@section('content')
<div class="container-fluid px-4 py-4" style="background:#0b1120; min-height:100vh; color:#e2e8f0;">
    <div class="mb-4">
        <h2 class="fw-bold text-white mb-1 fs-3">Resultados para: "{{ $query }}"</h2>
        <p class="text-secondary small">Búsqueda rápida en todo el condominio</p>
    </div>

    @if($residentes->isNotEmpty())
        <div class="card bg-dark border-secondary mb-4 shadow">
            <div class="card-header bg-dark border-secondary text-success fw-bold">
                <i class="fas fa-user text-success me-2"></i> Residentes Encontrados
            </div>
            <div class="list-group list-group-flush bg-dark">
                @foreach($residentes as $res)
                    <a href="{{ route('residentes.index') }}?search={{ $res->nombre_completo }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">{{ $res->nombre_completo }}</h6>
                            <span class="badge bg-success">Ir al módulo</span>
                        </div>
                        <small class="text-muted">CI: {{ $res->ci }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($empleados->isNotEmpty())
        <div class="card bg-dark border-secondary mb-4 shadow">
            <div class="card-header bg-dark border-secondary text-info fw-bold">
                <i class="fas fa-id-badge text-info me-2"></i> Empleados Encontrados
            </div>
            <div class="list-group list-group-flush bg-dark">
                @foreach($empleados as $emp)
                    <a href="{{ route('empleados.index') }}?search={{ $emp->nombre_completo }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">{{ $emp->nombre_completo }}</h6>
                            <span class="badge bg-info">Ir al módulo</span>
                        </div>
                        <small class="text-muted">CI: {{ $emp->ci }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($propiedades->isNotEmpty())
        <div class="card bg-dark border-secondary mb-4 shadow">
            <div class="card-header bg-dark border-secondary text-warning fw-bold">
                <i class="fas fa-home text-warning me-2"></i> Propiedades Encontradas
            </div>
            <div class="list-group list-group-flush bg-dark">
                @foreach($propiedades as $prop)
                    <a href="{{ route('propiedades.index') }}?search={{ $prop->nro_casa }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">Nro. de Casa / Departamento: {{ $prop->nro_casa }}</h6>
                            <span class="badge bg-warning text-dark">Ir al módulo</span>
                        </div>
                        <small class="text-muted">Código Interno: {{ $prop->codigo ?? 'S/N' }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($residentes->isEmpty() && $empleados->isEmpty() && $propiedades->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-search-minus text-secondary mb-3" style="font-size: 3rem;"></i>
            <h4 class="text-white">No se encontraron resultados</h4>
            <p class="text-muted">Prueba buscando por nombres exactos, números de CI o el número de lote/casa.</p>
            <a href="{{ route('panel') }}" class="btn btn-outline-light btn-sm mt-2">Volver al inicio</a>
        </div>
    @endif
</div>
@endsection