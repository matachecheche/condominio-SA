#!/bin/bash
# ============================================================================
# Script para crear el nuevo caso de uso: Gestión de Propiedades
# Ejecutar desde la raíz del proyecto Laravel (Lubuntu / Linux)
# ============================================================================

# Colores para la terminal
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

SUCCESS="${GREEN}✓${NC}"
ERROR="${RED}✗${NC}"

echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════════════════╗"
echo "║                   CREANDO GESTIÓN DE PROPIEDADES                         ║"
echo "║                 Sistema de Condominio - Laravel                          ║"
echo "╚══════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Verificar que estamos en la raíz de Laravel
if [ ! -f "artisan" ]; then
    echo -e "${ERROR} Error: No se encontró artisan. Ejecute este script desde la raíz del proyecto."
    exit 1
fi

echo -e "[INFO] Iniciando creación de archivos...\n"

# ── PASO 1: Crear Modelo ────────────────────────────────────────────────────
echo "[1/8] Creando Modelo Propiedad..."
if [ ! -f "app/Models/Propiedad.php" ]; then
cat << 'EOF' > app/Models/Propiedad.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $fillable = [
        'codigo',
        'tipo',
        'descripcion',
        'ubicacion',
        'estado',
        'residente_id',
    ];

    /**
     * Relación: Una propiedad pertenece a un residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Scope: Propiedades activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope: Propiedades ocupadas
     */
    public function scopeOcupadas($query)
    {
        return $query->where('estado', 'ocupada');
    }

    /**
     * Scope: Propiedades disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
}
EOF
    echo -e "${SUCCESS} Modelo creado: app/Models/Propiedad.php"
else
    echo -e "${YELLOW}[!] Archivo ya existe: app/Models/Propiedad.php${NC}"
fi

# ── PASO 2: Crear Controlador ───────────────────────────────────────────────
echo "[2/8] Creando Controlador PropiedadController..."
if [ ! -f "app/Http/Controllers/PropiedadController.php" ]; then
cat << 'EOF' > app/Http/Controllers/PropiedadController.php
<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use App\Models\Residente;
use Illuminate\Http\Request;
use App\Traits\BitacoraTrait;
use Exception;

class PropiedadController extends Controller
{
    use BitacoraTrait;

    /**
     * Mostrar lista de propiedades
     */
    public function index(Request $request)
    {
        $query = Propiedad::with('residente');

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%$search%")
                    ->orWhere('ubicacion', 'like', "%$search%")
                    ->orWhere('tipo', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%")
                    ->orWhereHas('residente', function ($res) use ($search) {
                        $res->where('nombre', 'like', "%$search%")
                            ->orWhere('apellido', 'like', "%$search%");
                    });
            });
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por ubicación
        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', 'like', "%{$request->ubicacion}%");
        }

        $propiedades = $query->orderBy('codigo', 'asc')->paginate(10);

        return view('propiedades.index', compact('propiedades'));
    }

    /**
     * Mostrar formulario para crear propiedad
     */
    public function create()
    {
        $residentes = Residente::all();
        return view('propiedades.create', compact('residentes'));
    }

    /**
     * Guardar nueva propiedad
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:propiedades,codigo',
                'tipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'ubicacion' => 'required|string|max:255',
                'estado' => 'required|in:disponible,ocupada,activa,mantenimiento',
                'residente_id' => 'nullable|exists:residentes,id',
            ]);

            $propiedad = Propiedad::create($validated);
            $this->registrarEnBitacora('Propiedad creada: ' . $propiedad->codigo, $propiedad->id);

            return redirect()->route('propiedades.index')
                ->with('success', 'Propiedad registrada correctamente.');
        } catch (Exception $e) {
            $this->registrarEnBitacora('Error al crear propiedad: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Mostrar detalles de la propiedad
     */
    public function show(Propiedad $propiedad)
    {
        return view('propiedades.show', compact('propiedad'));
    }

    /**
     * Mostrar formulario para editar propiedad
     */
    public function edit(Propiedad $propiedad)
    {
        $residentes = Residente::all();
        return view('propiedades.edit', compact('propiedad', 'residentes'));
    }

    /**
     * Actualizar propiedad
     */
    public function update(Request $request, Propiedad $propiedad)
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:propiedades,codigo,' . $propiedad->id,
                'tipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'ubicacion' => 'required|string|max:255',
                'estado' => 'required|in:disponible,ocupada,activa,mantenimiento',
                'residente_id' => 'nullable|exists:residentes,id',
            ]);

            $propiedad->update($validated);
            $this->registrarEnBitacora('Propiedad actualizada: ' . $propiedad->codigo, $propiedad->id);

            return redirect()->route('propiedades.index')
                ->with('success', 'Propiedad actualizada correctamente.');
        } catch (Exception $e) {
            $this->registrarEnBitacora('Error al actualizar propiedad: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Eliminar propiedad
     */
    public function destroy(Propiedad $propiedad)
    {
        try {
            $codigo = $propiedad->codigo;
            $propiedad->delete();
            $this->registrarEnBitacora('Propiedad eliminada: ' . $codigo);

            return redirect()->route('propiedades.index')
                ->with('success', 'Propiedad eliminada correctamente.');
        } catch (Exception $e) {
            $this->registrarEnBitacora('Error al eliminar propiedad: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}
EOF
    echo -e "${SUCCESS} Controlador creado: app/Http/Controllers/PropiedadController.php"
else
    echo -e "${YELLOW}[!] Archivo ya existe: app/Http/Controllers/PropiedadController.php${NC}"
fi

# ── PASO 3: Crear Migración ────────────────────────────────────────────────
echo "[3/8] Creando Migración..."
if [ ! -f "database/migrations/2026_05_14_000000_create_propiedades_table.php" ]; then
cat << 'EOF' > database/migrations/2026_05_14_000000_create_propiedades_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('tipo');
            $table->text('descripcion')->nullable();
            $table->string('ubicacion');
            $table->enum('estado', ['disponible', 'ocupada', 'activa', 'mantenimiento'])->default('disponible');
            
            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();

            $table->index('codigo');
            $table->index('ubicacion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
EOF
    echo -e "${SUCCESS} Migración creada: database/migrations/2026_05_14_000000_create_propiedades_table.php"
else
    echo -e "${YELLOW}[!] Archivo ya existe: database/migrations/2026_05_14_000000_create_propiedades_table.php${NC}"
fi

# ── PASO 4: Crear carpeta de vistas si no existe ───────────────────────────
echo "[4/8] Creando carpeta de vistas..."
if [ ! -d "resources/views/propiedades" ]; then
    mkdir -p "resources/views/propiedades"
    echo -e "${SUCCESS} Carpeta creada: resources/views/propiedades"
else
    echo -e "${YELLOW}[!] Carpeta ya existe: resources/views/propiedades${NC}"
fi

# ── PASO 5: Crear vistas ─────────────────────────────────────────────────────
echo "[5/8] Creando vistas (index, create, show, edit)..."

# INDEX
if [ ! -f "resources/views/propiedades/index.blade.php" ]; then
cat << 'EOF' > resources/views/propiedades/index.blade.php
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
EOF
    echo -e "${SUCCESS} Vista creada: resources/views/propiedades/index.blade.php"
fi

# CREATE
if [ ! -f "resources/views/propiedades/create.blade.php" ]; then
cat << 'EOF' > resources/views/propiedades/create.blade.php
@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Registrar Nueva Propiedad</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('propiedades.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código de Propiedad <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: APTO-101" value="{{ old('codigo') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo de Propiedad <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Apartamento">Apartamento</option>
                                    <option value="Casa">Casa</option>
                                    <option value="Local">Local</option>
                                    <option value="Oficina">Oficina</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="activa">Activa</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" placeholder="Ej: Calle Principal #123" value="{{ old('ubicacion') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="residente_id" class="form-label">Residente Asignado</label>
                            <select name="residente_id" id="residente_id" class="form-select">
                                <option value="">-- Sin asignar --</option>
                                @foreach($residentes as $residente)
                                    <option value="{{ $residente->id }}">{{ $residente->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOF
    echo -e "${SUCCESS} Vista creada: resources/views/propiedades/create.blade.php"
fi

# SHOW
if [ ! -f "resources/views/propiedades/show.blade.php" ]; then
cat << 'EOF' > resources/views/propiedades/show.blade.php
@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-home"></i> Detalles de la Propiedad</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código</label>
                        <p class="fs-5">{{ $propiedad->codigo }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <p class="fs-5">{{ $propiedad->tipo }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ubicación</label>
                        <p class="fs-5">{{ $propiedad->ubicacion }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="fs-5">{{ ucfirst($propiedad->estado) }}</p>
                    </div>
                    @if($propiedad->residente)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Residente</label>
                            <p class="fs-5">{{ $propiedad->residente->nombre_completo }}</p>
                        </div>
                    @endif
                    @if($propiedad->descripcion)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <p>{{ $propiedad->descripcion }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('propiedades.edit', $propiedad->id) }}" class="btn btn-warning">Editar</a>
                        <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOF
    echo -e "${SUCCESS} Vista creada: resources/views/propiedades/show.blade.php"
fi

# EDIT
if [ ! -f "resources/views/propiedades/edit.blade.php" ]; then
cat << 'EOF' > resources/views/propiedades/edit.blade.php
@extends('layouts.ap')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Editar Propiedad</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('propiedades.update', $propiedad->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" name="codigo" id="codigo" class="form-control" value="{{ old('codigo', $propiedad->codigo) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="Apartamento" {{ old('tipo', $propiedad->tipo) == 'Apartamento' ? 'selected' : '' }}>Apartamento</option>
                                    <option value="Casa" {{ old('tipo', $propiedad->tipo) == 'Casa' ? 'selected' : '' }}>Casa</option>
                                    <option value="Local" {{ old('tipo', $propiedad->tipo) == 'Local' ? 'selected' : '' }}>Local</option>
                                    <option value="Oficina" {{ old('tipo', $propiedad->tipo) == 'Oficina' ? 'selected' : '' }}>Oficina</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="disponible" {{ old('estado', $propiedad->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="ocupada" {{ old('estado', $propiedad->estado) == 'ocupada' ? 'selected' : '' }}>Ocupada</option>
                                    <option value="activa" {{ old('estado', $propiedad->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" name="ubicacion" id="ubicacion" class="form-control" value="{{ old('ubicacion', $propiedad->ubicacion) }}" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('propiedades.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOF
    echo -e "${SUCCESS} Vista creada: resources/views/propiedades/edit.blade.php"
fi

echo ""
echo "[6/8] Modificando routes/web.php..."
if [ -f "routes/web.php" ]; then
    if ! grep -q "PropiedadController" "routes/web.php"; then
        # Backup
        cp "routes/web.php" "routes/web.php.backup"
        
        # Añadir el import justo después de <?php
        sed -i '/<?php/a use App\\Http\\Controllers\\PropiedadController;' "routes/web.php"
        echo -e "${SUCCESS} Import de PropiedadController añadido."

        # Buscar un comentario de CU existente e inyectar el nuevo resource
        if grep -q "// -- CU6" "routes/web.php"; then
            sed -i '/\/\/ -- CU6/a \n\/\/ -- CU21 — Gestión de Propiedades\nRoute::resource('\''propiedades'\'', PropiedadController::class);' "routes/web.php"
            echo -e "${SUCCESS} Ruta de propiedades agregada a routes/web.php"
        else
            # Fallback si no encuentra CU6: añadir al final
            echo -e "\n// -- CU21 — Gestión de Propiedades\nRoute::resource('propiedades', PropiedadController::class);" >> "routes/web.php"
            echo -e "${SUCCESS} Ruta de propiedades agregada al final de routes/web.php"
        fi
    else
        echo -e "${YELLOW}[!] PropiedadController ya está en routes/web.php${NC}"
    fi
else
    echo -e "${ERROR} No se encontró routes/web.php"
fi

echo ""
echo "[7/8] Modificando navigation-menu.blade.php..."
MENU_PATH="resources/views/components/navigation-menu.blade.php"

if [ -f "$MENU_PATH" ]; then
    if ! grep -q "CU21" "$MENU_PATH"; then
        cp "$MENU_PATH" "${MENU_PATH}.backup"
        
        # Inyectar el enlace al menú. Utilizamos awk para insertarlo después del bloque de CU13 o antes del final.
        awk '/CU13/{print; getline; print; getline; print; getline; print; print "\n<a class=\"nav-link d-flex align-items-center gap-2\" href=\"{{ route('\''propiedades.index'\'') }}\">\n    <div class=\"sb-nav-link-icon\"><i class=\"fas fa-home\"></i></div>\n    <span>CU21 · Gestión de Propiedades</span>\n</a>"; next} 1' "$MENU_PATH" > "${MENU_PATH}.tmp" && mv "${MENU_PATH}.tmp" "$MENU_PATH"
        
        echo -e "${SUCCESS} navigation-menu.blade.php modificado"
    else
        echo -e "${YELLOW}[!] CU21 ya existe en navigation-menu.blade.php${NC}"
    fi
else
    echo -e "${ERROR} No se encontró navigation-menu.blade.php"
fi

echo ""
echo "[8/8] Finalizando..."
echo ""

echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════════════════╗"
echo "║                        PROCESO COMPLETADO                                ║"
echo "╚══════════════════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}PRÓXIMOS PASOS:${NC}"
echo ""
echo "1. Ejecutar la migración:"
echo "   php artisan migrate"
echo ""
echo "2. Limpiar la cache de Laravel:"
echo "   php artisan cache:clear"
echo "   php artisan config:cache"
echo ""
echo "3. Ejecutar el servidor:"
echo "   php artisan serve"
echo ""
echo "Los archivos han sido creados exitosamente."
echo "Los backups se encuentran en:"
echo "  - routes/web.php.backup"
echo "  - resources/views/components/navigation-menu.blade.php.backup"
echo ""

read -p "Presione Enter para salir..."