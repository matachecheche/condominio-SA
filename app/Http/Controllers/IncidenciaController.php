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
