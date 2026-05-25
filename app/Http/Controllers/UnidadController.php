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
