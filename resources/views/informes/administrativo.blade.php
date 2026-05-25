@extends('plantilla')
@section('title', 'Informe Administrativo')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Informes Administrativos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Informes</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-file-alt me-1"></i> CU12 · Generar Informe Administrativo</div>
        <div class="card-body">
            <form method="GET" action="{{ route('informes.administrativo') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de informe</label>
                        <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="residentes" {{ request('tipo','residentes')=='residentes'?'selected':'' }}>Residentes</option>
                            <option value="unidades"   {{ request('tipo')=='unidades'?'selected':'' }}>Unidades Habitacionales</option>
                            <option value="mantenimientos" {{ request('tipo')=='mantenimientos'?'selected':'' }}>Mantenimientos</option>
                            <option value="incidencias" {{ request('tipo')=='incidencias'?'selected':'' }}>Incidencias</option>
                        </select>
                    </div>
                    @if(in_array(request('tipo','-'), ['mantenimientos','incidencias']))
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                    </div>
                    @endif
                    @if(request('tipo')=='incidencias')
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach(['pendiente','en_revision','resuelto','cerrado'] as $est)
                            <option value="{{ $est }}" {{ request('estado')==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    @if(request('tipo')=='residentes')
                    <div class="col-md-2">
                        <label class="form-label">Tipo residente</label>
                        <select name="tipo_residente" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="Propietario" {{ request('tipo_residente')=='Propietario'?'selected':'' }}>Propietario</option>
                            <option value="Inquilino"   {{ request('tipo_residente')=='Inquilino'?'selected':'' }}>Inquilino</option>
                        </select>
                    </div>
                    @endif
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">Generar</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </form>

            <h5 class="mb-3">{{ $titulo }}</h5>

            @if($datos->isEmpty())
                <div class="alert alert-info">No se encontraron registros para los filtros seleccionados.</div>
            @else
                @if(request('tipo','residentes') === 'residentes')
                <table class="table table-sm table-striped">
                    <thead class="table-dark"><tr><th>#</th><th>Nombre</th><th>Apellido</th><th>CI</th><th>Email</th><th>Tipo</th></tr></thead>
                    <tbody>
                        @foreach($datos as $r)
                        <tr><td>{{ $loop->iteration }}</td><td>{{ $r->nombre }}</td><td>{{ $r->apellido }}</td>
                            <td>{{ $r->ci }}</td><td>{{ $r->email }}</td><td>{{ $r->tipo_residente }}</td></tr>
                        @endforeach
                    </tbody>
                </table>

                @elseif(request('tipo') === 'unidades')
                <table class="table table-sm table-striped">
                    <thead class="table-dark"><tr><th>#</th><th>Código</th><th>Residente</th><th>Tipo Ocup.</th><th>Estado</th><th>Personas</th><th>Vehículos</th></tr></thead>
                    <tbody>
                        @foreach($datos as $u)
                        <tr><td>{{ $loop->iteration }}</td><td>{{ $u->codigo }}</td>
                            <td>{{ $u->residente?->nombre.' '.$u->residente?->apellido ?? '—' }}</td>
                            <td>{{ $u->tipo_ocupacion }}</td>
                            <td>{{ ucfirst($u->estado) }}</td>
                            <td>{{ $u->personas_por_unidad }}</td><td>{{ $u->vehiculos }}</td></tr>
                        @endforeach
                    </tbody>
                </table>

                @elseif(request('tipo') === 'mantenimientos')
                <table class="table table-sm table-striped">
                    <thead class="table-dark"><tr><th>#</th><th>Descripción</th><th>Empresa</th><th>Monto (Bs)</th><th>Fecha</th><th>Estado</th></tr></thead>
                    <tbody>
                        @foreach($datos as $m)
                        <tr><td>{{ $loop->iteration }}</td><td>{{ Str::limit($m->descripcion,50) }}</td>
                            <td>{{ $m->empresa?->nombre ?? '—' }}</td>
                            <td>{{ number_format($m->monto,2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($m->fecha_hora)->format('d/m/Y') }}</td>
                            <td>{{ $m->estado == 1 ? 'Activo' : 'Inactivo' }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="3"><strong>Total:</strong></td>
                        <td><strong>Bs {{ number_format($datos->sum('monto'),2) }}</strong></td><td colspan="2"></td></tr></tfoot>
                </table>

                @elseif(request('tipo') === 'incidencias')
                <table class="table table-sm table-striped">
                    <thead class="table-dark"><tr><th>#</th><th>N° Seguimiento</th><th>Título</th><th>Residente</th><th>Prioridad</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach($datos as $inc)
                        <tr><td>{{ $loop->iteration }}</td><td><code>{{ $inc->numero_seguimiento }}</code></td>
                            <td>{{ Str::limit($inc->titulo,40) }}</td>
                            <td>{{ $inc->residente->nombre }} {{ $inc->residente->apellido }}</td>
                            <td>{{ ucfirst($inc->prioridad) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ',$inc->estado)) }}</td>
                            <td>{{ $inc->created_at->format('d/m/Y') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <p class="text-muted small mt-2">Total de registros: {{ $datos->count() }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
