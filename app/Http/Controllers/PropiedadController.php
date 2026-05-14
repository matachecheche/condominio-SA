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
