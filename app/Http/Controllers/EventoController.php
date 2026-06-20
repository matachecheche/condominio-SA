<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    use BitacoraTrait;

    public function index(Request $request)
    {
        $query = Evento::with('organizador');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%$s%")
                  ->orWhere('lugar', 'like', "%$s%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $eventos = $query->orderBy('fecha_hora', 'desc')->paginate(15);
        return view('eventos.index', compact('eventos'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'lugar'       => 'required|string|max:255',
            'fecha_hora'  => 'required|date',
            'cupo_maximo' => 'nullable|integer|min:1',
        ]);

        $evento = Evento::create([
            'nombre'         => $request->nombre,
            'descripcion'    => $request->descripcion,
            'lugar'          => $request->lugar,
            'fecha_hora'     => $request->fecha_hora,
            'cupo_maximo'    => $request->cupo_maximo,
            'organizador_id' => Auth::id(),
        ]);

        $this->registrarEnBitacora('Creó el evento comunitario: ' . $evento->nombre, $evento->id);

        return redirect()->route('eventos.index')->with('success', 'Evento comunitario registrado correctamente.');
    }

    public function show(Evento $evento)
    {
        $evento->load('organizador');
        return view('eventos.show', compact('evento'));
    }

    public function edit(Evento $evento)
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'lugar'       => 'required|string|max:255',
            'fecha_hora'  => 'required|date',
            'cupo_maximo' => 'nullable|integer|min:1',
            'estado'      => 'required|in:programado,en_curso,finalizado,cancelado',
        ]);

        $evento->update($request->only([
            'nombre', 'descripcion', 'lugar', 'fecha_hora', 'cupo_maximo', 'estado',
        ]));

        $this->registrarEnBitacora('Actualizó el evento comunitario: ' . $evento->nombre, $evento->id);

        return redirect()->route('eventos.index')->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Evento $evento)
    {
        $nombre = $evento->nombre;
        $evento->delete();
        $this->registrarEnBitacora('Eliminó el evento comunitario: ' . $nombre);
        return redirect()->route('eventos.index')->with('success', 'Evento eliminado.');
    }
}
