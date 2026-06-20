<?php

namespace App\Http\Controllers;

use App\Models\Reclamo;
use App\Models\Residente;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReclamoController extends Controller
{
    use BitacoraTrait;

    public function index(Request $request)
    {
        $query = Reclamo::with(['residente', 'atendioPor']);

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

        $reclamos = $query->orderByDesc('created_at')->paginate(15);
        return view('reclamos.index', compact('reclamos'));
    }

    public function create()
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('reclamos.create', compact('residentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'       => 'required|string|max:255',
            'contenido'    => 'required|string',
            'residente_id' => 'required|exists:residentes,id',
        ]);

        $reclamo = Reclamo::create($request->only(['titulo', 'contenido', 'residente_id']));
        $this->registrarEnBitacora('Registró reclamo administrativo: ' . $reclamo->numero_seguimiento, $reclamo->id);

        return redirect()->route('reclamos.index')
            ->with('success', "Reclamo registrado. Número de seguimiento: {$reclamo->numero_seguimiento}");
    }

    public function show(Reclamo $reclamo)
    {
        $reclamo->load(['residente', 'atendioPor']);
        return view('reclamos.show', compact('reclamo'));
    }

    public function edit(Reclamo $reclamo)
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('reclamos.edit', compact('reclamo', 'residentes'));
    }

    public function update(Request $request, Reclamo $reclamo)
    {
        $request->validate([
            'titulo'          => 'required|string|max:255',
            'contenido'       => 'required|string',
            'estado'          => 'required|in:pendiente,en_revision,resuelto,rechazado',
            'respuesta_admin' => 'nullable|string',
            'residente_id'    => 'required|exists:residentes,id',
        ]);

        $data = $request->only(['titulo', 'contenido', 'estado', 'respuesta_admin', 'residente_id']);

        if (in_array($request->estado, ['resuelto', 'rechazado']) && !$reclamo->atendido_por) {
            $data['atendido_por']   = Auth::id();
            $data['fecha_atencion'] = now();
        }

        $reclamo->update($data);
        $this->registrarEnBitacora('Actualizó reclamo: ' . $reclamo->numero_seguimiento, $reclamo->id);

        return redirect()->route('reclamos.index')->with('success', 'Reclamo actualizado correctamente.');
    }

    public function destroy(Reclamo $reclamo)
    {
        $num = $reclamo->numero_seguimiento;
        $reclamo->delete();
        $this->registrarEnBitacora('Eliminó reclamo: ' . $num, $reclamo->id);
        return redirect()->route('reclamos.index')->with('success', 'Reclamo eliminado.');
    }
}
