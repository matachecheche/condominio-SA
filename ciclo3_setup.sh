#!/usr/bin/env bash
# =============================================================================
# CICLO 3 — Condominio San Diego
# CU12: Generar informes administrativos
# CU13: Vincular residente con unidad habitacional
# CU14: Generar reportes de pagos
# CU15: Registrar contratación de empresa externa  (ya existe en respaldo2;
#        este script la conecta al menú como ítem propio de Ciclo 3)
# CU16: Gestionar denuncias / reportes de incidencias
#
# Ejecutar desde la RAÍZ del proyecto Laravel:
#   bash ciclo3_setup.sh
# =============================================================================
set -e

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()  { echo -e "${GREEN}[+]${NC} $*"; }
warn()  { echo -e "${YELLOW}[!]${NC} $*"; }

# ─── Verificación mínima ──────────────────────────────────────────────────────
if [ ! -f artisan ]; then
  echo "ERROR: ejecuta este script desde la raíz del proyecto Laravel." >&2
  exit 1
fi

info "Iniciando implementación del Ciclo 3..."

# =============================================================================
# CU13 — Vincular residente con unidad habitacional
# Necesita: tabla unidades, modelo Unidad, UnidadController, vistas, rutas
# =============================================================================
info "CU13 — Creando migración de unidades..."
cat > database/migrations/$(date +%Y_%m_%d)_000001_create_unidades_table.php << 'MIGRATION'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->integer('capacidad')->default(4);
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->integer('personas_por_unidad')->default(1);
            $table->boolean('tiene_mascotas')->default(false);
            $table->integer('vehiculos')->default(0);
            $table->enum('tipo_ocupacion', ['Propietario', 'Inquilino'])->default('Propietario');
            $table->foreignId('residente_id')->nullable()->constrained('residentes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
MIGRATION

info "CU13 — Creando modelo Unidad (sobreescribe si existe)..."
mkdir -p app/Models
cat > app/Models/Unidad.php << 'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'codigo',
        'placa',
        'marca',
        'capacidad',
        'estado',
        'personas_por_unidad',
        'tiene_mascotas',
        'vehiculos',
        'tipo_ocupacion',
        'residente_id',
    ];

    protected $casts = [
        'capacidad'           => 'integer',
        'personas_por_unidad' => 'integer',
        'tiene_mascotas'      => 'boolean',
        'vehiculos'           => 'integer',
    ];

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
MODEL

info "CU13 — Actualizando modelo Residente para agregar relación unidades..."
# Agrega relación unidades() si no existe
if ! grep -q "function unidades" app/Models/Residente.php; then
  sed -i '/public function reclamos/i\    public function unidades()\n    {\n        return $this->hasMany(Unidad::class);\n    }\n' app/Models/Residente.php
fi

info "CU13 — Creando UnidadController..."
cat > app/Http/Controllers/UnidadController.php << 'CTRL'
<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Residente;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    use BitacoraTrait;

    public function index(Request $request)
    {
        $query = Unidad::with('residente');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('codigo', 'like', "%$s%")
                  ->orWhereHas('residente', fn($q) =>
                      $q->where('nombre', 'like', "%$s%")
                        ->orWhere('apellido', 'like', "%$s%")
                        ->orWhere('ci', 'like', "%$s%")
                  );
        }

        $unidades = $query->orderBy('codigo')->paginate(15);
        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('unidades.create', compact('residentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'             => 'required|string|max:20|unique:unidades,codigo',
            'capacidad'          => 'required|integer|min:1',
            'personas_por_unidad'=> 'required|integer|min:0',
            'vehiculos'          => 'required|integer|min:0',
            'tiene_mascotas'     => 'nullable|boolean',
            'estado'             => 'required|in:activa,inactiva',
            'tipo_ocupacion'     => 'required|in:Propietario,Inquilino',
            'residente_id'       => 'nullable|exists:residentes,id',
        ]);

        // Verificar que el residente no tenga otra unidad activa del mismo tipo
        if ($request->filled('residente_id') && $request->estado === 'activa') {
            $ocupada = Unidad::where('residente_id', $request->residente_id)
                             ->where('estado', 'activa')
                             ->first();
            if ($ocupada) {
                return back()->withErrors(['residente_id' => 'El residente ya tiene una unidad activa asignada.'])->withInput();
            }
        }

        $data = $request->all();
        $data['tiene_mascotas'] = $request->boolean('tiene_mascotas');
        $unidad = Unidad::create($data);

        $this->registrarEnBitacora('Creó unidad habitacional: ' . $unidad->codigo, $unidad->id);
        return redirect()->route('unidades.index')->with('success', 'Unidad creada correctamente.');
    }

    public function show(Unidad $unidad)
    {
        $unidad->load('residente');
        return view('unidades.show', compact('unidad'));
    }

    public function edit(Unidad $unidad)
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('unidades.edit', compact('unidad', 'residentes'));
    }

    public function update(Request $request, Unidad $unidad)
    {
        $request->validate([
            'codigo'             => 'required|string|max:20|unique:unidades,codigo,' . $unidad->id,
            'capacidad'          => 'required|integer|min:1',
            'personas_por_unidad'=> 'required|integer|min:0',
            'vehiculos'          => 'required|integer|min:0',
            'tiene_mascotas'     => 'nullable|boolean',
            'estado'             => 'required|in:activa,inactiva',
            'tipo_ocupacion'     => 'required|in:Propietario,Inquilino',
            'residente_id'       => 'nullable|exists:residentes,id',
        ]);

        if ($request->filled('residente_id') && $request->estado === 'activa') {
            $ocupada = Unidad::where('residente_id', $request->residente_id)
                             ->where('estado', 'activa')
                             ->where('id', '!=', $unidad->id)
                             ->first();
            if ($ocupada) {
                return back()->withErrors(['residente_id' => 'El residente ya tiene otra unidad activa asignada.'])->withInput();
            }
        }

        $data = $request->all();
        $data['tiene_mascotas'] = $request->boolean('tiene_mascotas');
        $unidad->update($data);

        $this->registrarEnBitacora('Actualizó unidad habitacional: ' . $unidad->codigo, $unidad->id);
        return redirect()->route('unidades.index')->with('success', 'Unidad actualizada correctamente.');
    }

    public function destroy(Unidad $unidad)
    {
        $codigo = $unidad->codigo;
        $unidad->delete();
        $this->registrarEnBitacora('Eliminó unidad habitacional: ' . $codigo, $unidad->id);
        return redirect()->route('unidades.index')->with('success', 'Unidad eliminada correctamente.');
    }
}
CTRL

info "CU13 — Creando vistas de unidades..."
mkdir -p resources/views/unidades

cat > resources/views/unidades/index.blade.php << 'BLADE'
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
BLADE

cat > resources/views/unidades/create.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Nueva Unidad')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Nueva Unidad Habitacional</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}">Unidades</a></li>
        <li class="breadcrumb-item active">Nueva</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> Registrar Unidad</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul></div>
            @endif

            <form action="{{ route('unidades.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select" required>
                            <option value="activa" {{ old('estado','activa')=='activa'?'selected':'' }}>Activa</option>
                            <option value="inactiva" {{ old('estado')=='inactiva'?'selected':'' }}>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Ocupación <span class="text-danger">*</span></label>
                        <select name="tipo_ocupacion" class="form-select" required>
                            <option value="Propietario" {{ old('tipo_ocupacion','Propietario')=='Propietario'?'selected':'' }}>Propietario</option>
                            <option value="Inquilino" {{ old('tipo_ocupacion')=='Inquilino'?'selected':'' }}>Inquilino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad',4) }}" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Personas por unidad <span class="text-danger">*</span></label>
                        <input type="number" name="personas_por_unidad" class="form-control" value="{{ old('personas_por_unidad',1) }}" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehículos <span class="text-danger">*</span></label>
                        <input type="number" name="vehiculos" class="form-control" value="{{ old('vehiculos',0) }}" min="0" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="tiene_mascotas" id="mascotas" value="1"
                                   {{ old('tiene_mascotas') ? 'checked' : '' }}>
                            <label class="form-check-label" for="mascotas">Tiene mascotas</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Placa vehículo</label>
                        <input type="text" name="placa" class="form-control" value="{{ old('placa') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Marca vehículo</label>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Vincular Residente</label>
                        <select name="residente_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }} — CI: {{ $r->ci }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Un residente activo sólo puede tener una unidad activa.</small>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="{{ route('unidades.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
BLADE

cat > resources/views/unidades/edit.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Editar Unidad')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Unidad: {{ $unidad->codigo }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}">Unidades</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> Editar Unidad Habitacional</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul></div>
            @endif
            <form action="{{ route('unidades.update', $unidad->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" class="form-control" value="{{ old('codigo',$unidad->codigo) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="activa" {{ old('estado',$unidad->estado)=='activa'?'selected':'' }}>Activa</option>
                            <option value="inactiva" {{ old('estado',$unidad->estado)=='inactiva'?'selected':'' }}>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Ocupación</label>
                        <select name="tipo_ocupacion" class="form-select" required>
                            <option value="Propietario" {{ old('tipo_ocupacion',$unidad->tipo_ocupacion)=='Propietario'?'selected':'' }}>Propietario</option>
                            <option value="Inquilino" {{ old('tipo_ocupacion',$unidad->tipo_ocupacion)=='Inquilino'?'selected':'' }}>Inquilino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacidad</label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad',$unidad->capacidad) }}" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Personas por unidad</label>
                        <input type="number" name="personas_por_unidad" class="form-control" value="{{ old('personas_por_unidad',$unidad->personas_por_unidad) }}" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vehículos</label>
                        <input type="number" name="vehiculos" class="form-control" value="{{ old('vehiculos',$unidad->vehiculos) }}" min="0" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="tiene_mascotas" id="mascotas" value="1"
                                   {{ old('tiene_mascotas',$unidad->tiene_mascotas) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mascotas">Tiene mascotas</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Placa</label>
                        <input type="text" name="placa" class="form-control" value="{{ old('placa',$unidad->placa) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" value="{{ old('marca',$unidad->marca) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Residente asignado</label>
                        <select name="residente_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}"
                                {{ old('residente_id',$unidad->residente_id)==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }} — CI: {{ $r->ci }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('unidades.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
BLADE

cat > resources/views/unidades/show.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Unidad ' . $unidad->codigo)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Unidad: {{ $unidad->codigo }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades.index') }}">Unidades</a></li>
        <li class="breadcrumb-item active">{{ $unidad->codigo }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-home me-1"></i> Detalle de Unidad Habitacional</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Código</dt><dd class="col-sm-9">{{ $unidad->codigo }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">
                    <span class="badge {{ $unidad->estado==='activa'?'bg-success':'bg-secondary' }}">{{ ucfirst($unidad->estado) }}</span>
                </dd>
                <dt class="col-sm-3">Tipo Ocupación</dt><dd class="col-sm-9">{{ $unidad->tipo_ocupacion }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">
                    {{ $unidad->residente ? $unidad->residente->nombre.' '.$unidad->residente->apellido : '—' }}
                </dd>
                <dt class="col-sm-3">Personas</dt><dd class="col-sm-9">{{ $unidad->personas_por_unidad }}</dd>
                <dt class="col-sm-3">Capacidad</dt><dd class="col-sm-9">{{ $unidad->capacidad }}</dd>
                <dt class="col-sm-3">Vehículos</dt><dd class="col-sm-9">{{ $unidad->vehiculos }}</dd>
                <dt class="col-sm-3">Placa</dt><dd class="col-sm-9">{{ $unidad->placa ?? '—' }}</dd>
                <dt class="col-sm-3">Marca</dt><dd class="col-sm-9">{{ $unidad->marca ?? '—' }}</dd>
                <dt class="col-sm-3">Mascotas</dt><dd class="col-sm-9">{{ $unidad->tiene_mascotas ? 'Sí' : 'No' }}</dd>
            </dl>
            <a href="{{ route('unidades.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            @can('editar unidades')
            <a href="{{ route('unidades.edit', $unidad->id) }}" class="btn btn-warning btn-sm">Editar</a>
            @endcan
        </div>
    </div>
</div>
@endsection
BLADE

# =============================================================================
# CU16 — Gestionar denuncias / reportes de incidencias
# =============================================================================
info "CU16 — Creando migración de incidencias..."
cat > database/migrations/$(date +%Y_%m_%d)_000002_create_incidencias_table.php << 'MIGRATION'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('numero_seguimiento')->unique();
            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('estado', ['pendiente', 'en_revision', 'resuelto', 'cerrado'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->text('respuesta_admin')->nullable();
            $table->foreignId('residente_id')->constrained('residentes')->cascadeOnDelete();
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_atencion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
MIGRATION

info "CU16 — Creando modelo Incidencia..."
cat > app/Models/Incidencia.php << 'MODEL'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_seguimiento',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'respuesta_admin',
        'residente_id',
        'atendido_por',
        'fecha_atencion',
    ];

    protected $casts = [
        'fecha_atencion' => 'datetime',
    ];

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function atendioPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    protected static function booted(): void
    {
        static::creating(function (Incidencia $inc) {
            $inc->numero_seguimiento = 'INC-' . strtoupper(uniqid());
        });
    }
}
MODEL

info "CU16 — Creando IncidenciaController..."
cat > app/Http/Controllers/IncidenciaController.php << 'CTRL'
<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\Residente;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaController extends Controller
{
    use BitacoraTrait;

    public function index(Request $request)
    {
        $query = Incidencia::with(['residente', 'atendioPor']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('titulo', 'like', "%$s%")
                  ->orWhere('numero_seguimiento', 'like', "%$s%")
                  ->orWhereHas('residente', fn($r) =>
                      $r->where('nombre', 'like', "%$s%")->orWhere('apellido', 'like', "%$s%")
                  );
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $incidencias = $query->orderByDesc('created_at')->paginate(15);
        return view('incidencias.index', compact('incidencias'));
    }

    public function create()
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('incidencias.create', compact('residentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'prioridad'    => 'required|in:baja,media,alta',
            'residente_id' => 'required|exists:residentes,id',
        ]);

        $inc = Incidencia::create($request->only(['titulo','descripcion','prioridad','residente_id']));
        $this->registrarEnBitacora('Registró incidencia: ' . $inc->numero_seguimiento, $inc->id);

        return redirect()->route('incidencias.index')
            ->with('success', "Incidencia registrada. Número de seguimiento: {$inc->numero_seguimiento}");
    }

    public function show(Incidencia $incidencia)
    {
        $incidencia->load(['residente', 'atendioPor']);
        return view('incidencias.show', compact('incidencia'));
    }

    public function edit(Incidencia $incidencia)
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('incidencias.edit', compact('incidencia', 'residentes'));
    }

    public function update(Request $request, Incidencia $incidencia)
    {
        $request->validate([
            'titulo'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'prioridad'       => 'required|in:baja,media,alta',
            'estado'          => 'required|in:pendiente,en_revision,resuelto,cerrado',
            'respuesta_admin' => 'nullable|string',
            'residente_id'    => 'required|exists:residentes,id',
        ]);

        $data = $request->only(['titulo','descripcion','prioridad','estado','respuesta_admin','residente_id']);

        // Si cambia a resuelto/cerrado y no tiene atendido_por, asignar admin actual
        if (in_array($request->estado, ['resuelto','cerrado']) && ! $incidencia->atendido_por) {
            $data['atendido_por']   = Auth::id();
            $data['fecha_atencion'] = now();
        }

        $incidencia->update($data);
        $this->registrarEnBitacora('Actualizó incidencia: ' . $incidencia->numero_seguimiento, $incidencia->id);

        return redirect()->route('incidencias.index')->with('success', 'Incidencia actualizada correctamente.');
    }

    public function destroy(Incidencia $incidencia)
    {
        $num = $incidencia->numero_seguimiento;
        $incidencia->delete();
        $this->registrarEnBitacora('Eliminó incidencia: ' . $num, $incidencia->id);
        return redirect()->route('incidencias.index')->with('success', 'Incidencia eliminada.');
    }
}
CTRL

info "CU16 — Creando vistas de incidencias..."
mkdir -p resources/views/incidencias

cat > resources/views/incidencias/index.blade.php << 'BLADE'
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
BLADE

cat > resources/views/incidencias/create.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Nueva Incidencia')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Registrar Incidencia</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
        <li class="breadcrumb-item active">Nueva</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> Nueva Denuncia / Incidencia</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('incidencias.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prioridad <span class="text-danger">*</span></label>
                        <select name="prioridad" class="form-select" required>
                            <option value="baja" {{ old('prioridad')=='baja'?'selected':'' }}>Baja</option>
                            <option value="media" {{ old('prioridad','media')=='media'?'selected':'' }}>Media</option>
                            <option value="alta" {{ old('prioridad')=='alta'?'selected':'' }}>Alta</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción del problema <span class="text-danger">*</span></label>
                        <textarea name="descripcion" class="form-control" rows="4" required>{{ old('descripcion') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residente que reporta <span class="text-danger">*</span></label>
                        <select name="residente_id" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id')==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Registrar Incidencia</button>
                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
BLADE

cat > resources/views/incidencias/edit.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Editar Incidencia')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Incidencia: {{ $incidencia->numero_seguimiento }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> Actualizar Estado / Respuesta</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('incidencias.update', $incidencia->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo',$incidencia->titulo) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select" required>
                            @foreach(['baja','media','alta'] as $p)
                            <option value="{{ $p }}" {{ old('prioridad',$incidencia->prioridad)==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            @foreach(['pendiente','en_revision','resuelto','cerrado'] as $est)
                            <option value="{{ $est }}" {{ old('estado',$incidencia->estado)==$est?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$est)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion',$incidencia->descripcion) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residente</label>
                        <select name="residente_id" class="form-select" required>
                            @foreach($residentes as $r)
                            <option value="{{ $r->id }}" {{ old('residente_id',$incidencia->residente_id)==$r->id?'selected':'' }}>
                                {{ $r->apellido }}, {{ $r->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Respuesta del Administrador</label>
                        <textarea name="respuesta_admin" class="form-control" rows="3">{{ old('respuesta_admin',$incidencia->respuesta_admin) }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
BLADE

cat > resources/views/incidencias/show.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Incidencia ' . $incidencia->numero_seguimiento)
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Incidencia: {{ $incidencia->numero_seguimiento }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
        <li class="breadcrumb-item active">{{ $incidencia->numero_seguimiento }}</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-flag me-1"></i> Detalle de Incidencia</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">N° Seguimiento</dt><dd class="col-sm-9"><code>{{ $incidencia->numero_seguimiento }}</code></dd>
                <dt class="col-sm-3">Título</dt><dd class="col-sm-9">{{ $incidencia->titulo }}</dd>
                <dt class="col-sm-3">Residente</dt><dd class="col-sm-9">{{ $incidencia->residente->nombre }} {{ $incidencia->residente->apellido }}</dd>
                <dt class="col-sm-3">Prioridad</dt><dd class="col-sm-9">{{ ucfirst($incidencia->prioridad) }}</dd>
                <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">{{ ucfirst(str_replace('_',' ',$incidencia->estado)) }}</dd>
                <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">{{ $incidencia->descripcion }}</dd>
                <dt class="col-sm-3">Registrada el</dt><dd class="col-sm-9">{{ $incidencia->created_at->format('d/m/Y H:i') }}</dd>
                @if($incidencia->respuesta_admin)
                <dt class="col-sm-3">Respuesta Admin</dt><dd class="col-sm-9">{{ $incidencia->respuesta_admin }}</dd>
                @endif
                @if($incidencia->atendioPor)
                <dt class="col-sm-3">Atendido por</dt><dd class="col-sm-9">{{ $incidencia->atendioPor->name }}</dd>
                <dt class="col-sm-3">Fecha atención</dt><dd class="col-sm-9">{{ $incidencia->fecha_atencion?->format('d/m/Y H:i') }}</dd>
                @endif
            </dl>
            <a href="{{ route('incidencias.index') }}" class="btn btn-secondary btn-sm">Volver</a>
            <a href="{{ route('incidencias.edit', $incidencia->id) }}" class="btn btn-warning btn-sm">Editar</a>
        </div>
    </div>
</div>
@endsection
BLADE

# =============================================================================
# CU12 — Generar informes administrativos
# CU14 — Generar reportes de pagos
# Ambos van en InformeController
# =============================================================================
info "CU12 + CU14 — Creando InformeController..."
cat > app/Http/Controllers/InformeController.php << 'CTRL'
<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Mantenimiento;
use App\Models\Comunicado;
use App\Models\Incidencia;
use App\Models\Unidad;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InformeController extends Controller
{
    use BitacoraTrait;

    /**
     * CU12 — Informe administrativo general
     */
    public function administrativo(Request $request)
    {
        $tipo  = $request->get('tipo', 'residentes');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        $datos = [];
        $titulo = '';

        switch ($tipo) {
            case 'residentes':
                $titulo = 'Informe de Residentes';
                $q = Residente::query();
                if ($request->filled('tipo_residente')) {
                    $q->where('tipo_residente', $request->tipo_residente);
                }
                $datos = $q->orderBy('apellido')->get();
                break;

            case 'unidades':
                $titulo = 'Informe de Unidades Habitacionales';
                $datos = Unidad::with('residente')->orderBy('codigo')->get();
                break;

            case 'mantenimientos':
                $titulo = 'Informe de Mantenimientos';
                $q = Mantenimiento::with(['usuario', 'empresa']);
                if ($desde && $hasta) {
                    $q->whereBetween('fecha_hora', [$desde, $hasta . ' 23:59:59']);
                }
                $datos = $q->orderByDesc('fecha_hora')->get();
                break;

            case 'incidencias':
                $titulo = 'Informe de Incidencias';
                $q = Incidencia::with('residente');
                if ($request->filled('estado')) {
                    $q->where('estado', $request->estado);
                }
                if ($desde && $hasta) {
                    $q->whereBetween('created_at', [$desde, $hasta . ' 23:59:59']);
                }
                $datos = $q->orderByDesc('created_at')->get();
                break;

            default:
                $datos = collect();
        }

        if ($request->has('exportar')) {
            $this->registrarEnBitacora("Exportó informe administrativo: $tipo");
        } else {
            $this->registrarEnBitacora("Consultó informe administrativo: $tipo");
        }

        return view('informes.administrativo', compact('datos', 'tipo', 'titulo', 'desde', 'hasta'));
    }

    /**
     * CU14 — Reporte de pagos
     */
    public function pagos(Request $request)
    {
        $query = Pago::with(['cuota.residente', 'user']);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha_pago', [$request->desde, $request->hasta . ' 23:59:59']);
        } elseif ($request->filled('mes')) {
            [$anio, $mes] = explode('-', $request->mes);
            $query->whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes);
        }

        if ($request->filled('metodo')) {
            $query->where('metodo', $request->metodo);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos   = $query->orderByDesc('fecha_pago')->get();
        $total   = $pagos->sum('monto_pagado');
        $morosos = Residente::whereHas('cuotas', fn($q) => $q->where('estado', 'pendiente'))->get();

        $this->registrarEnBitacora('Generó reporte de pagos');
        return view('informes.pagos', compact('pagos', 'total', 'morosos'));
    }
}
CTRL

info "CU12 + CU14 — Creando vistas de informes..."
mkdir -p resources/views/informes

cat > resources/views/informes/administrativo.blade.php << 'BLADE'
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
BLADE

cat > resources/views/informes/pagos.blade.php << 'BLADE'
@extends('plantilla')
@section('title', 'Reporte de Pagos')
@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Reporte de Pagos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reporte de Pagos</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-receipt me-1"></i> CU14 · Generar Reporte de Pagos</div>
        <div class="card-body">
            <form method="GET" action="{{ route('informes.pagos') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Por mes</label>
                        <input type="month" name="mes" class="form-control form-control-sm" value="{{ request('mes') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Método</label>
                        <select name="metodo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="efectivo" {{ request('metodo')=='efectivo'?'selected':'' }}>Efectivo</option>
                            <option value="transferencia" {{ request('metodo')=='transferencia'?'selected':'' }}>Transferencia</option>
                            <option value="qr" {{ request('metodo')=='qr'?'selected':'' }}>QR</option>
                            <option value="stripe" {{ request('metodo')=='stripe'?'selected':'' }}>Stripe</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">Generar</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </form>

            {{-- Resumen --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center py-3">
                        <div class="fs-4 fw-bold">Bs {{ number_format($total, 2) }}</div>
                        <small>Total recaudado</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center py-3">
                        <div class="fs-4 fw-bold">{{ $pagos->count() }}</div>
                        <small>Pagos registrados</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center py-3">
                        <div class="fs-4 fw-bold">{{ $morosos->count() }}</div>
                        <small>Residentes con cuotas pendientes</small>
                    </div>
                </div>
            </div>

            <h6>Detalle de Pagos</h6>
            @if($pagos->isEmpty())
                <div class="alert alert-info">No se encontraron pagos con los filtros indicados.</div>
            @else
            <table class="table table-sm table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Fecha</th><th>Residente</th><th>Cuota</th>
                        <th>Monto (Bs)</th><th>Método</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
                        <td>{{ $p->cuota?->residente?->nombre }} {{ $p->cuota?->residente?->apellido }}</td>
                        <td>{{ $p->cuota?->titulo ?? 'N/A' }}</td>
                        <td>{{ number_format($p->monto_pagado, 2) }}</td>
                        <td>{{ ucfirst($p->metodo) }}</td>
                        <td>{{ ucfirst($p->estado) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <td colspan="4"><strong>Total</strong></td>
                        <td><strong>Bs {{ number_format($total, 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>

            @if($morosos->isNotEmpty())
            <h6 class="mt-4 text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Residentes Morosos</h6>
            <table class="table table-sm table-warning">
                <thead><tr><th>#</th><th>Nombre</th><th>CI</th><th>Email</th></tr></thead>
                <tbody>
                    @foreach($morosos as $m)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $m->nombre }} {{ $m->apellido }}</td>
                        <td>{{ $m->ci }}</td><td>{{ $m->email }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            @endif
            @endif
        </div>
    </div>
</div>
@endsection
BLADE

# =============================================================================
# CU15 — ya está en respaldo2 (EmpresaExternaController/vistas completas)
# Solo actualizamos el menú para diferenciarlo como CU15 propio del Ciclo 3
# =============================================================================
info "CU15 — Ya implementado en respaldo2. Solo se conecta en rutas/menú."

# =============================================================================
# RUTAS — agregar ciclo 3 a routes/web.php
# =============================================================================
info "Agregando rutas del Ciclo 3 a routes/web.php..."

# Verificar si ya existen las rutas para no duplicarlas
if ! grep -q "unidades" routes/web.php; then
cat >> routes/web.php << 'ROUTES'

// ── CICLO 3 ───────────────────────────────────────────────────────────────────

// ── CU13 — Unidades Habitacionales (vincular residente con unidad) ────────────
use App\Http\Controllers\UnidadController;
Route::middleware(['auth'])->group(function () {
    Route::resource('unidades', UnidadController::class);
});

// ── CU12 + CU14 — Informes administrativos y reportes de pagos ───────────────
use App\Http\Controllers\InformeController;
Route::middleware(['auth'])->group(function () {
    Route::get('/informes/administrativo', [InformeController::class, 'administrativo'])
         ->name('informes.administrativo');
    Route::get('/informes/pagos', [InformeController::class, 'pagos'])
         ->name('informes.pagos');
});

// ── CU16 — Incidencias y denuncias ────────────────────────────────────────────
use App\Http\Controllers\IncidenciaController;
Route::middleware(['auth'])->group(function () {
    Route::resource('incidencias', IncidenciaController::class);
});
ROUTES
  info "Rutas del Ciclo 3 agregadas."
else
  warn "Las rutas de unidades ya existen en web.php. Se omite para no duplicar."
fi

# =============================================================================
# PERMISOS — agregar permisos de Ciclo 3 al PermissionSeeder
# =============================================================================
info "Actualizando PermissionSeeder con permisos del Ciclo 3..."
# Insertar antes del cierre del array $permisos
if ! grep -q "ver incidencias" database/seeders/PermissionSeeder.php; then
  sed -i "/'ver bitacora',/a\\
\\            // Informes (CU12, CU14)\\n            'ver informes',\\n            'exportar informes',\\n            'ver reportes de pagos',\\n\\n            // Unidades (CU13)\\n            'ver unidades',\\n            'crear unidades',\\n            'editar unidades',\\n            'eliminar unidades',\\n\\n            // Incidencias (CU16)\\n            'ver incidencias',\\n            'crear incidencias',\\n            'editar incidencias',\\n            'eliminar incidencias'," \
    database/seeders/PermissionSeeder.php
  info "Permisos de Ciclo 3 agregados al PermissionSeeder."
else
  warn "Los permisos de Ciclo 3 ya existen. Se omite."
fi

# Dar permisos al rol Administrador (ya tiene todos via syncPermissions(Permission::all()))
# Residente: crear incidencias + ver sus incidencias
if ! grep -q "crear incidencias" database/seeders/RolesSeeder.php; then
  sed -i "/'ver calificaciones',/a\\            'ver incidencias',\n            'crear incidencias'," \
    database/seeders/RolesSeeder.php
  info "Permisos de incidencias agregados al rol Residente."
fi

# =============================================================================
# MENÚ — actualizar navigation-menu.blade.php para activar los enlaces Ciclo 3
# =============================================================================
info "Actualizando menú de navegación con enlaces del Ciclo 3..."

# CU13: cambiar <span> inactivo por <a> real
sed -i "s|<span class=\"nav-link d-flex align-items-center gap-2\" style=\"color:#334155; cursor:default;\">\s*<div class=\"sb-nav-link-icon\"><i class=\"fas fa-link\"></i></div>\s*<span>CU13 · Vincular residente-unidad</span>.*Ciclo 3.*</span>||g" \
  resources/views/components/navigation-menu.blade.php 2>/dev/null || true

# Reemplazar bloque CU13 placeholder con link real (usando Python para multiline)
python3 << 'PYEOF'
import re

with open('resources/views/components/navigation-menu.blade.php', 'r') as f:
    content = f.read()

# Replace CU13 placeholder
old = r'''<span class="nav-link d-flex align-items-center gap-2" style="color:#334155; cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-link"></i></div>
                    <span>CU13 · Vincular residente-unidad</span>
                    <small class="ms-auto" style="font-size:0.62rem; color:#334155;">Ciclo 3</small>
                </span>'''
new = '''<a class="nav-link d-flex align-items-center gap-2" href="{{ route(\'unidades.index\') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    <span>CU13 · Vincular Residente-Unidad</span>
                </a>'''
if old in content:
    content = content.replace(old, new)
    print("CU13 link updated")
else:
    print("CU13 placeholder not found (may already be set)")

# Replace CU16 placeholder
old16 = r'''<span class="nav-link d-flex align-items-center gap-2" style="color:#334155; cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-flag"></i></div>
                    <span>CU16 · Denuncias e incidencias</span>
                    <small class="ms-auto" style="font-size:0.62rem; color:#334155;">Ciclo 3</small>
                </span>'''
new16 = '''<a class="nav-link d-flex align-items-center gap-2" href="{{ route(\'incidencias.index\') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-flag"></i></div>
                    <span>CU16 · Incidencias y Denuncias</span>
                </a>'''
if old16 in content:
    content = content.replace(old16, new16)
    print("CU16 link updated")
else:
    print("CU16 placeholder not found (may already be set)")

# Replace CU12 placeholder
old12 = r'''<span class="nav-link d-flex align-items-center gap-2" style="color:#334155; cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                    <span>CU12 · Informes administrativos</span>
                    <small class="ms-auto" style="font-size:0.62rem; color:#334155;">Ciclo 3</small>
                </span>'''
new12 = '''<a class="nav-link d-flex align-items-center gap-2" href="{{ route(\'informes.administrativo\') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                    <span>CU12 · Informes Administrativos</span>
                </a>'''
if old12 in content:
    content = content.replace(old12, new12)
    print("CU12 link updated")
else:
    print("CU12 placeholder not found (may already be set)")

# Replace CU14 placeholder
old14 = r'''<span class="nav-link d-flex align-items-center gap-2" style="color:#334155; cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                    <span>CU14 · Reportes de pago</span>
                    <small class="ms-auto" style="font-size:0.62rem; color:#334155;">Ciclo 3</small>
                </span>'''
new14 = '''<a class="nav-link d-flex align-items-center gap-2" href="{{ route(\'informes.pagos\') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                    <span>CU14 · Reportes de Pagos</span>
                </a>'''
if old14 in content:
    content = content.replace(old14, new14)
    print("CU14 link updated")
else:
    print("CU14 placeholder not found (may already be set)")

with open('resources/views/components/navigation-menu.blade.php', 'w') as f:
    f.write(content)

print("Menu file written.")
PYEOF

# =============================================================================
# Seeder de Incidencias de muestra
# =============================================================================
info "Creando IncidenciaSeeder..."
cat > database/seeders/IncidenciaSeeder.php << 'SEEDER'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incidencia;
use App\Models\Residente;

class IncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $residentes = Residente::pluck('id')->toArray();
        if (empty($residentes)) return;

        $ejemplos = [
            ['titulo' => 'Ruido excesivo en la noche', 'descripcion' => 'Vecinos del bloque B generan ruido hasta altas horas.', 'prioridad' => 'alta', 'estado' => 'pendiente'],
            ['titulo' => 'Fuga de agua en área común', 'descripcion' => 'Hay una fuga en la manguera del jardín principal.', 'prioridad' => 'alta', 'estado' => 'en_revision'],
            ['titulo' => 'Luz del pasillo apagada', 'descripcion' => 'El pasillo del 3er piso no tiene iluminación.', 'prioridad' => 'media', 'estado' => 'resuelto'],
            ['titulo' => 'Problemas con el ascensor', 'descripcion' => 'El ascensor hace ruidos extraños al subir.', 'prioridad' => 'alta', 'estado' => 'en_revision'],
            ['titulo' => 'Basura no recogida', 'descripcion' => 'Los contenedores del estacionamiento no se vaciaron esta semana.', 'prioridad' => 'baja', 'estado' => 'pendiente'],
        ];

        foreach ($ejemplos as $e) {
            Incidencia::create([
                'titulo'       => $e['titulo'],
                'descripcion'  => $e['descripcion'],
                'prioridad'    => $e['prioridad'],
                'estado'       => $e['estado'],
                'residente_id' => $residentes[array_rand($residentes)],
            ]);
        }
    }
}
SEEDER

# Agregar al DatabaseSeeder si no está
if ! grep -q "IncidenciaSeeder" database/seeders/DatabaseSeeder.php; then
  sed -i '/call(\[/a\            \\Database\\Seeders\\IncidenciaSeeder::class,' database/seeders/DatabaseSeeder.php
  info "IncidenciaSeeder agregado al DatabaseSeeder."
fi

# =============================================================================
# Seeder de Unidades de muestra
# =============================================================================
info "Creando UnidadSeeder..."
cat > database/seeders/UnidadSeeder.php << 'SEEDER'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unidad;
use App\Models\Residente;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $residentes = Residente::orderBy('id')->get();

        for ($i = 1; $i <= 53; $i++) {
            $residente = $residentes->get($i - 1);
            Unidad::create([
                'codigo'              => 'U-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'estado'              => 'activa',
                'tipo_ocupacion'      => $i % 3 === 0 ? 'Inquilino' : 'Propietario',
                'capacidad'           => 4,
                'personas_por_unidad' => rand(1, 4),
                'vehiculos'           => rand(0, 2),
                'tiene_mascotas'      => (bool) rand(0, 1),
                'residente_id'        => $residente?->id,
            ]);
        }
    }
}
SEEDER

if ! grep -q "UnidadSeeder" database/seeders/DatabaseSeeder.php; then
  sed -i '/call(\[/a\            \\Database\\Seeders\\UnidadSeeder::class,' database/seeders/DatabaseSeeder.php
  info "UnidadSeeder agregado al DatabaseSeeder."
fi

# =============================================================================
# Agregar relación cuotas() a Residente si no existe (para InformeController)
# =============================================================================
if ! grep -q "function cuotas" app/Models/Residente.php; then
  sed -i "/public function getNombreCompletoAttribute/i\\    public function cuotas()\n    {\n        return \$this->hasMany(\\\\App\\\\Models\\\\Cuota::class);\n    }\n" \
    app/Models/Residente.php
  info "Relación cuotas() agregada a Residente."
fi

# =============================================================================
# Limpiar caché de Laravel
# =============================================================================
info "Limpiando caché de Laravel..."
php artisan config:clear   2>/dev/null || warn "config:clear falló (¿sin .env?)"
php artisan route:clear    2>/dev/null || warn "route:clear falló"
php artisan view:clear     2>/dev/null || warn "view:clear falló"
php artisan cache:clear    2>/dev/null || warn "cache:clear falló"

echo ""
echo -e "${GREEN}============================================================${NC}"
echo -e "${GREEN}  Ciclo 3 instalado correctamente.${NC}"
echo -e "${GREEN}============================================================${NC}"
echo ""
echo "Pasos finales (ejecutar manualmente):"
echo ""
echo "  1. Correr migraciones:"
echo "     php artisan migrate"
echo ""
echo "  2. Poblar datos de ejemplo (opcional):"
echo "     php artisan db:seed --class=PermissionSeeder"
echo "     php artisan db:seed --class=RolesSeeder"
echo "     php artisan db:seed --class=UnidadSeeder"
echo "     php artisan db:seed --class=IncidenciaSeeder"
echo ""
echo "  Casos de uso implementados:"
echo "  ✅ CU12 — Informes administrativos  →  GET /informes/administrativo"
echo "  ✅ CU13 — Vincular residente-unidad  →  GET /unidades"
echo "  ✅ CU14 — Reporte de pagos           →  GET /informes/pagos"
echo "  ✅ CU15 — Empresas externas          →  GET /empresas  (ya existía)"
echo "  ✅ CU16 — Incidencias / Denuncias    →  GET /incidencias"
