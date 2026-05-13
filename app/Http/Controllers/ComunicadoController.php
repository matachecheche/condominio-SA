<?php

namespace App\Http\Controllers;

use App\Models\Comunicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacion;
use App\Traits\BitacoraTrait;
use Carbon\Carbon;

/**
 * ComunicadoController
 * 
 * EXPLICACIÓN: Este controlador maneja todas las operaciones CRUD de comunicados:
 * - index: Listar todos los comunicados
 * - create: Mostrar formulario para crear
 * - store: Guardar nuevo comunicado
 * - show: Ver detalle de un comunicado
 * - edit: Mostrar formulario para editar
 * - update: Guardar cambios
 * - destroy: Eliminar comunicado
 */
class ComunicadoController extends Controller
{
    use BitacoraTrait;

    /**
     * index()
     * 
     * EXPLICACIÓN: Lista todos los comunicados que ya han sido publicados
     * (fecha_publicacion debe ser menor o igual al ahora)
     * Se ordena por los más recientes primero (latest)
     * Se pagina de 10 en 10 registros
     */
    public function index()
    {
        $comunicados = Comunicado::with('usuario')
            ->where('fecha_publicacion', '<=', now())
            ->latest()
            ->paginate(10);

        return view('comunicados.index', compact('comunicados'));
    }

    /**
     * create()
     * 
     * EXPLICACIÓN: Muestra el formulario vacío para crear un nuevo comunicado
     */
    public function create()
    {
        return view('comunicados.create');
    }

    /**
     * store(Request $request)
     * 
     * EXPLICACIÓN: Guarda el nuevo comunicado en la base de datos
     * - Valida que los campos requeridos existan y sean correctos
     * - Crea el comunicado con los datos del formulario
     * - Crea una notificación para informar a los usuarios
     * - Registra en la bitácora
     * 
     * ✅ CAMBIO: Se agregó la validación de 'destinatarios'
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIÓN CON EL NUEVO CAMPO
        $request->validate([
            'titulo'               => 'required|string|max:255', // Título obligatorio
            'contenido'            => 'required|string',         // Contenido obligatorio
            'tipo'                 => 'required|in:Urgente,Informativo', // Tipo debe ser uno de estos valores
            'destinatarios'        => 'required|in:Todos,Residentes,Empleados', // ✅ NUEVO: Validar destinatarios
            'fecha_publicacion'    => 'nullable|date_format:Y-m-d\TH:i', // Fecha opcional
        ]);

        // Si la fecha está vacía, se publica inmediatamente
        // Si tiene valor, se convierte a objeto DateTime
        $fechaPublicacion = $request->filled('fecha_publicacion')
            ? Carbon::parse($request->fecha_publicacion)
            : now();

        // ✅ CREAR COMUNICADO CON EL NUEVO CAMPO
        $comunicado = Comunicado::create([
            'titulo'               => $request->titulo,
            'contenido'            => $request->contenido,
            'tipo'                 => $request->tipo,
            'destinatarios'        => $request->destinatarios, // ✅ NUEVO: Guardar destinatarios
            'fecha_publicacion'    => $fechaPublicacion,
            'usuario_id'           => Auth::id() // Quién lo crea
        ]);

        // Crear notificación para informar de nuevo comunicado
        Notificacion::create([
            'titulo'       => 'Nuevo Comunicado',
            'contenido'    => 'Se ha publicado un nuevo comunicado para todos los residentes.',
            'tipo'         => 'Informativa',
            'fecha_hora'   => now(),
            'residente_id' => null, // null = para todos
            'ruta'         => route('comunicados.index'),
        ]);

        // Registrar en la bitácora del sistema
        $this->registrarEnBitacora('Creó un nuevo comunicado.', auth()->id());

        // Redireccionar con mensaje de éxito
        return redirect()->route('comunicados.index')->with('success', 'Comunicado programado exitosamente.');
    }

    /**
     * show(Comunicado $comunicado)
     * 
     * EXPLICACIÓN: Muestra el detalle completo de un comunicado específico
     * El parámetro $comunicado es inyectado automáticamente por Laravel (Route Model Binding)
     */
    public function show(Comunicado $comunicado)
    {
        return view('comunicados.show', compact('comunicado'));
    }

    /**
     * edit(Comunicado $comunicado)
     * 
     * EXPLICACIÓN: Muestra el formulario pre-llenado con los datos del comunicado
     * para que el usuario pueda editarlo
     */
    public function edit(Comunicado $comunicado)
    {
        return view('comunicados.edit', compact('comunicado'));
    }

    /**
     * update(Request $request, Comunicado $comunicado)
     * 
     * EXPLICACIÓN: Actualiza los datos del comunicado en la base de datos
     * 
     * ✅ CAMBIO: Se agregó la validación de 'destinatarios' también en update
     */
    public function update(Request $request, Comunicado $comunicado)
    {
        // ✅ VALIDACIÓN CON EL NUEVO CAMPO
        $request->validate([
            'titulo'               => 'required|string|max:255',
            'contenido'            => 'required|string',
            'tipo'                 => 'required|in:Urgente,Informativo',
            'destinatarios'        => 'required|in:Todos,Residentes,Empleados', // ✅ NUEVO: Validar destinatarios
            'fecha_publicacion'    => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        // Si la fecha está vacía, se usa la fecha actual
        $fechaPublicacion = $request->filled('fecha_publicacion')
            ? Carbon::parse($request->fecha_publicacion)
            : now();

        // ✅ ACTUALIZAR CON EL NUEVO CAMPO
        $comunicado->update([
            'titulo'               => $request->titulo,
            'contenido'            => $request->contenido,
            'tipo'                 => $request->tipo,
            'destinatarios'        => $request->destinatarios, // ✅ NUEVO: Actualizar destinatarios
            'fecha_publicacion'    => $fechaPublicacion,
        ]);

        // Registrar en la bitácora
        $this->registrarEnBitacora('Actualizó un comunicado.', auth()->id());

        // Redireccionar con mensaje de éxito
        return redirect()->route('comunicados.index')->with('success', 'Comunicado actualizado exitosamente.');
    }

    /**
     * destroy(Comunicado $comunicado)
     * 
     * EXPLICACIÓN: Elimina el comunicado de la base de datos
     */
    public function destroy(Comunicado $comunicado)
    {
        $comunicado->delete(); // Eliminar el registro

        // Registrar en bitácora
        $this->registrarEnBitacora('Eliminó un comunicado.', auth()->id());

        // Redireccionar con mensaje de éxito
        return redirect()->route('comunicados.index')->with('success', 'Comunicado eliminado exitosamente.');
    }
}