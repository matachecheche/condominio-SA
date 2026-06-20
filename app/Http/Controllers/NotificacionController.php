<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Residente;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    use BitacoraTrait;

    public function index(Request $request)
    {
        $query = Notificacion::with('residente')->latest('fecha_hora');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('titulo', 'like', "%$s%")
                  ->orWhere('contenido', 'like', "%$s%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $notificaciones = $query->paginate(15);
        return view('notificaciones.index', compact('notificaciones'));
    }

    public function create()
    {
        $residentes = Residente::orderBy('apellido')->get();
        return view('notificaciones.create', compact('residentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo'       => 'required|string|max:255',
            'contenido'    => 'required|string',
            'tipo'         => 'required|in:Urgente,Informativa,Recordatorio',
            'destinatario' => 'required|in:todos,individual',
            'residente_id' => 'required_if:destinatario,individual|nullable|exists:residentes,id',
        ]);

        if ($request->destinatario === 'individual') {
            Notificacion::create([
                'titulo'       => $request->titulo,
                'contenido'    => $request->contenido,
                'tipo'         => $request->tipo,
                'fecha_hora'   => now(),
                'residente_id' => $request->residente_id,
                'enviada_por'  => Auth::id(),
            ]);
            $this->registrarEnBitacora('Envió una notificación individual.');
            return redirect()->route('notificaciones.index')
                ->with('success', 'Notificación enviada al residente seleccionado.');
        }

        $residentes = Residente::pluck('id');
        foreach ($residentes as $residenteId) {
            Notificacion::create([
                'titulo'       => $request->titulo,
                'contenido'    => $request->contenido,
                'tipo'         => $request->tipo,
                'fecha_hora'   => now(),
                'residente_id' => $residenteId,
                'enviada_por'  => Auth::id(),
            ]);
        }

        $this->registrarEnBitacora('Envió una notificación a todos los residentes (' . $residentes->count() . ').');

        return redirect()->route('notificaciones.index')
            ->with('success', 'Notificación enviada a todos los residentes (' . $residentes->count() . ').');
    }

    public function show(Notificacion $notificacion)
    {
        return view('notificaciones.show', compact('notificacion'));
    }

    public function marcarLeida(Notificacion $notificacion)
    {
        $notificacion->update(['leida' => true]);
        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    public function destroy(Notificacion $notificacion)
    {
        $titulo = $notificacion->titulo;
        $notificacion->delete();
        $this->registrarEnBitacora('Eliminó la notificación: ' . $titulo);
        return redirect()->route('notificaciones.index')->with('success', 'Notificación eliminada.');
    }
}
