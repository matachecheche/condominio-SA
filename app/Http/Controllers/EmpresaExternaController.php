<?php

namespace App\Http\Controllers;

use App\Models\EmpresaExterna;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;

/**
 * EmpresaExternaController
 * 
 * EXPLICACIÓN GENERAL:
 * Este controlador maneja todas las operaciones CRUD de empresas externas:
 * - index: Listar todas las empresas
 * - create: Mostrar formulario para crear
 * - store: Guardar nueva empresa
 * - show: Ver detalle de una empresa
 * - edit: Mostrar formulario para editar
 * - update: Guardar cambios
 * - destroy: Eliminar empresa
 */
class EmpresaExternaController extends Controller
{
    use BitacoraTrait; // Trait para registrar acciones en la bitácora

    /**
     * index()
     * 
     * EXPLICACIÓN:
     * Lista todas las empresas externas paginadas
     * Se ordena por más recientes primero
     */
    public function index()
    {
        // Obtener empresas ordenadas por más recientes, paginar de 10 en 10
        $empresas = EmpresaExterna::latest()->paginate(10);
        return view('empresas.index', compact('empresas'));
    }

    /**
     * create()
     * 
     * EXPLICACIÓN:
     * Muestra el formulario vacío para crear una nueva empresa
     */
    public function create()
    {
        return view('empresas.create');
    }

    /**
     * store(Request $request)
     * 
     * EXPLICACIÓN:
     * Guarda una nueva empresa en la base de datos
     * Valida que los datos sean correctos según las reglas
     * 
     * ✅ CAMBIO: Se agregó validación de 'calificacion'
     */
    public function store(Request $request)
    {
        // ✅ VALIDACIÓN CON EL NUEVO CAMPO
        $request->validate([
            'nombre'        => 'required|string|max:255', // Nombre obligatorio
            'servicio'      => 'required|string|max:255', // Servicio obligatorio
            'telefono'      => 'nullable|string|max:50', // Teléfono opcional
            'correo'        => 'nullable|email|max:100', // Email opcional y debe ser válido
            'direccion'     => 'nullable|string|max:255', // Dirección opcional
            'calificacion'  => 'required|integer|between:1,5', // ✅ NUEVO: Calificación entre 1-5
            'observacion'   => 'nullable|string', // Observación opcional
        ]);

        // ✅ Crear empresa con el nuevo campo
        $empresa = EmpresaExterna::create($request->all());
        
        // Registrar en la bitácora
        $this->registrarEnBitacora('Registró empresa externa: ' . $empresa->nombre, $empresa->id);

        return redirect()->route('empresas.index')->with('success', 'Empresa registrada correctamente.');
    }

    /**
     * show(EmpresaExterna $empresa)
     * 
     * EXPLICACIÓN:
     * Muestra el detalle completo de una empresa específica
     * Laravel inyecta automáticamente el objeto mediante Route Model Binding
     */
    public function show(EmpresaExterna $empresa)
    {
        return view('empresas.show', compact('empresa'));
    }

    /**
     * edit(EmpresaExterna $empresa)
     * 
     * EXPLICACIÓN:
     * Muestra el formulario pre-llenado con los datos de la empresa
     */
    public function edit(EmpresaExterna $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    /**
     * update(Request $request, EmpresaExterna $empresa)
     * 
     * EXPLICACIÓN:
     * Actualiza los datos de una empresa existente
     * Valida los mismos campos que en store()
     * 
     * ✅ CAMBIO: Se agregó validación de 'calificacion'
     */
    public function update(Request $request, EmpresaExterna $empresa)
    {
        // ✅ VALIDACIÓN CON EL NUEVO CAMPO
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'servicio'      => 'required|string|max:255',
            'telefono'      => 'nullable|string|max:50',
            'correo'        => 'nullable|email|max:100',
            'direccion'     => 'nullable|string|max:255',
            'calificacion'  => 'required|integer|between:1,5', // ✅ NUEVO: Validar calificación
            'observacion'   => 'nullable|string',
        ]);

        // ✅ Actualizar con el nuevo campo
        $empresa->update($request->all());
        
        // Registrar en la bitácora
        $this->registrarEnBitacora('Actualizó empresa externa: ' . $empresa->nombre, $empresa->id);

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * destroy(EmpresaExterna $empresa)
     * 
     * EXPLICACIÓN:
     * Elimina una empresa de la base de datos
     */
    public function destroy(EmpresaExterna $empresa)
    {
        // Guardar el nombre antes de eliminar
        $nombre = $empresa->nombre;
        $id     = $empresa->id;
        
        // Eliminar el registro
        $empresa->delete();
        
        // Registrar en la bitácora
        $this->registrarEnBitacora('Eliminó empresa externa: ' . $nombre, $id);

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada correctamente.');
    }
}