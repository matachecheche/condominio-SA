<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Residente;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitaController extends Controller
{
    protected $rol;
    protected $permisos;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $this->rol = $user->roles->pluck('name')->join(', ');
            
            //  OBTENER PERMISOS DESDE LA BASE DE DATOS
            $this->permisos = $this->obtenerPermisosUsuario($user);
            
            return $next($request);
        });
    }

    //  MÉTODO PARA OBTENER PERMISOS DESDE LA BD
    private function obtenerPermisosUsuario($user)
    {
        // Obtener permisos del usuario a través de sus roles
        $permisos = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', get_class($user))
            ->pluck('permissions.name')
            ->toArray();

        // También obtener permisos directos del usuario (si los hay)
        $permisosDirectos = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $user->id)
            ->where('model_has_permissions.model_type', get_class($user))
            ->pluck('permissions.name')
            ->toArray();

        return array_unique(array_merge($permisos, $permisosDirectos));
    }

    //  VERIFICAR PERMISOS DINÁMICAMENTE
    private function tienePermiso($permiso)
    {
        return in_array($permiso, $this->permisos);
    }

    //  VERIFICAR MÚLTIPLES PERMISOS (OR)
    private function tieneAlgunPermiso($permisos)
    {
        return !empty(array_intersect($permisos, $this->permisos));
    }

    //  LISTA DE VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function index(Request $request)
    {
        $user = Auth::user();

        //  VERIFICAR LOS 3 PERMISOS ESPECÍFICOS DE VISITAS
        if ($this->tienePermiso('administrar visitas')) {
            //  ADMIN: Ve TODAS las visitas de TODOS los residentes
            $titulo = "Administrar Visitas";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida']);
            
        } elseif ($this->tienePermiso('operar porteria')) {
            //  PORTERO: Ve todas las visitas para control de acceso
            $titulo = "Control de Acceso - Portería";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida']);
            
        } elseif ($this->tienePermiso('gestionar visitas')) {
            //  RESIDENTE: Solo SUS propias visitas (CRUD de su mismo ID)
            $titulo = "Mis Visitas";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida'])
                        ->whereHas('residente', function($q) use ($user) {
                            $q->where('email', $user->email);
                        });
        } else {
            //  Sin permisos de visitas
            abort(403, 'No tienes permisos para ver visitas');
        }

        // Aplicar filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                ->orWhere('nombre_visitante', 'LIKE', "%{$search}%")
                ->orWhere('ci_visitante', 'LIKE', "%{$search}%")
                ->orWhere('placa_vehiculo', 'LIKE', "%{$search}%")
                ->orWhere('motivo', 'LIKE', "%{$search}%")
                ->orWhereHas('residente', function($subQ) use ($search) {
                    $subQ->where('nombre_completo', 'LIKE', "%{$search}%");
                });
            });
        }

        $visitas = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('visitas.index', compact('visitas', 'titulo'));
    }

    // CREAR VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function create()
    {
        $user = Auth::user();
        
        if ($this->tienePermiso('administrar visitas')) {
            // 🔧 ADMIN: Puede crear visitas para CUALQUIER residente
            $residentes = Residente::all();
            
        } elseif ($this->tienePermiso('gestionar visitas')) {
            // RESIDENTE: Solo puede crear visitas para SÍ MISMO
            $residentes = Residente::where('email', $user->email)->get();
            if ($residentes->count() == 0) {  
                return redirect()->back()->with('error', 'Tu email no está registrado como residente.');
            }
        } else {
            // PORTERO y otros no pueden crear visitas
            abort(403, 'No tienes permisos para crear visitas');
        }
        
        return view('visitas.create', compact('residentes'));
    }

    // GUARDAR VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:255',
            'ci_visitante' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:now',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'placa_vehiculo' => 'nullable|string|max:20'
        ]);

        // VERIFICAR RESTRICCIONES SEGÚN PERMISOS
        if ($this->tienePermiso('gestionar visitas') && !$this->tienePermiso('administrar visitas')) {
            //  RESIDENTE: Solo puede crear visitas para SÍ MISMO
            $residente = Residente::find($request->residente_id);
            if ($residente->email !== $user->email) {
                return redirect()->back()
                    ->with('error', 'Solo puedes crear visitas para ti mismo')
                    ->withInput();
            }
        }

        $visita = Visita::create([
            'residente_id' => $request->residente_id,
            'nombre_visitante' => $request->nombre_visitante,
            'ci_visitante' => $request->ci_visitante,
            'placa_vehiculo' => $request->placa_vehiculo,
            'motivo' => $request->motivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'codigo' => $this->generarCodigo(),
            'estado' => 'pendiente'
        ]);

        $this->registrarBitacora(
            'CREAR_VISITA',
            "Visita creada para {$visita->nombre_visitante} (CI: {$visita->ci_visitante}) - Código: {$visita->codigo}",
            $visita->id
        );

        return redirect()->route('visitas.show', $visita)
            ->with('success', 'Visita registrada correctamente. Código: ' . $visita->codigo);
    }

    // VER DETALLES CON LOS 3 PERMISOS ESPECÍFICOS
    public function show($id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        
        //  CONTROL DE ACCESO SEGÚN LOS 3 PERMISOS
        if ($this->tienePermiso('administrar visitas') || $this->tienePermiso('operar porteria')) {
            // ADMIN o PORTERO: Pueden ver CUALQUIER visita
            // Sin restricciones
        } elseif ($this->tienePermiso('gestionar visitas')) {
            // 🏠 RESIDENTE: Solo SUS propias visitas
            if ($visita->residente->email !== $user->email) {
                abort(403, 'Esta visita no te pertenece');
            }
        } else {
            //  Sin permisos
            abort(403, 'No tienes permisos para ver esta visita');
        }
        
        $visita->load(['residente', 'userEntrada', 'userSalida']);
        return view('visitas.show', compact('visita'));
    }

    //  EDITAR VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function edit($id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        
        // Solo permitir editar visitas pendientes
        if ($visita->estado !== 'pendiente') {
            return redirect()->route('visitas.show', $visita)
                ->with('error', 'Solo se pueden editar visitas pendientes');
        }

        // CONTROL DE ACCESO SEGÚN LOS 3 PERMISOS
        if ($this->tienePermiso('administrar visitas')) {
            // ADMIN: Puede editar CUALQUIER visita
            $residentes = Residente::all();
        } elseif ($this->tienePermiso('gestionar visitas')) {
            //  RESIDENTE: Solo SUS propias visitas
            if ($visita->residente->email !== $user->email) {
                abort(403, 'Esta visita no te pertenece');
            }
            $residentes = Residente::where('email', $user->email)->get();
        } else {
            //  PORTERO y otros no pueden editar
            abort(403, 'No tienes permisos para editar visitas');
        }
        
        return view('visitas.edit', compact('visita', 'residentes'));
    }

    // ACTUALIZAR VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        
        // Solo permitir actualizar visitas pendientes
        if ($visita->estado !== 'pendiente') {
            return redirect()->route('visitas.show', $visita)
                ->with('error', 'Solo se pueden editar visitas pendientes');
        }

        //  CONTROL DE ACCESO SEGÚN LOS 3 PERMISOS
        if ($this->tienePermiso('gestionar visitas') && !$this->tienePermiso('administrar visitas')) {
            // 🏠 RESIDENTE: Solo SUS propias visitas
            if ($visita->residente->email !== $user->email) {
                abort(403, 'Esta visita no te pertenece');
            }
        }

        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:255',
            'ci_visitante' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:now',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'placa_vehiculo' => 'nullable|string|max:20'
        ]);

        $visita->update([
            'residente_id' => $request->residente_id,
            'nombre_visitante' => $request->nombre_visitante,
            'ci_visitante' => $request->ci_visitante,
            'placa_vehiculo' => $request->placa_vehiculo,
            'motivo' => $request->motivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        $this->registrarBitacora(
            'EDITAR_VISITA',
            "Visita editada - Visitante: {$visita->nombre_visitante}, CI: {$visita->ci_visitante}",
            $visita->id
        );

        return redirect()->route('visitas.show', $visita)
            ->with('success', 'Visita actualizada correctamente');
    }

    // ELIMINAR VISITAS CON LOS 3 PERMISOS ESPECÍFICOS
    public function destroy($id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        
        // Solo permitir eliminar visitas pendientes o rechazadas
        if (!in_array($visita->estado, ['pendiente', 'rechazada'])) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una visita en curso o finalizada');
        }

        // CONTROL DE ACCESO SEGÚN LOS 3 PERMISOS
        if ($this->tienePermiso('administrar visitas')) {
            // ADMIN: Puede eliminar CUALQUIER visita
            // Sin restricciones
        } elseif ($this->tienePermiso('gestionar visitas')) {
            // RESIDENTE: Solo SUS propias visitas
            if ($visita->residente->email !== $user->email) {
                abort(403, 'Esta visita no te pertenece');
            }
        } else {
            //  PORTERO y otros no pueden eliminar
            abort(403, 'No tienes permisos para eliminar visitas');
        }

        $nombreVisitante = $visita->nombre_visitante;
        $ciVisitante = $visita->ci_visitante;
        $codigo = $visita->codigo;

        $this->registrarBitacora(
            'ELIMINAR_VISITA',
            "Visita eliminada - Visitante: {$nombreVisitante}, CI: {$ciVisitante}, Código: {$codigo}",
            $visita->id
        );

        $visita->delete();

        return redirect()->route('visitas.index')
            ->with('success', 'Visita eliminada correctamente');
    }

    // VALIDAR CÓDIGO - SOLO PORTERO Y ADMIN
    public function mostrarValidarCodigo()
    {
        // Solo PORTERO y  ADMIN pueden validar códigos
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            abort(403, 'No tienes permisos para validar códigos');
        }
        
        return view('visitas.validar-codigo');
    }

    // VALIDAR CÓDIGO CON TOLERANCIA DE 30 MINUTOS
    public function validarCodigo(Request $request)
    {
        //  Solo PORTERO y  ADMIN pueden validar códigos
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para validar códigos'
            ]);
        }
        
        $request->validate([
            'codigo' => 'required|string|size:6',
            'ci_visitante' => 'required|string'
        ]);

        $visita = Visita::where('codigo', $request->codigo)
            ->where('ci_visitante', $request->ci_visitante)
            ->where('estado', 'pendiente')
            ->with('residente')
            ->first();

        if (!$visita) {
            $this->registrarBitacora(
                'VALIDACION_FALLIDA',
                "Intento de validación fallido - Código: {$request->codigo}, CI: {$request->ci_visitante}"
            );

            return response()->json([
                'success' => false,
                'message' => 'Código incorrecto o CI no coincide'
            ]);
        }

        // VALIDAR HORARIO CON TOLERANCIA DE 30 MINUTOS
        $ahora = Carbon::now();
        $inicioConTolerancia = Carbon::parse($visita->fecha_inicio)->subMinutes(30);
        $finVisita = Carbon::parse($visita->fecha_fin);

        if ($ahora < $inicioConTolerancia || $ahora > $finVisita) {
            $mensaje = '';
            
            if ($ahora < $inicioConTolerancia) {
                $minutosAntes = $ahora->diffInMinutes($inicioConTolerancia);
                $mensaje = "Visita muy temprana. Debe esperar {$minutosAntes} minutos más";
            } elseif ($ahora > $finVisita) {
                $minutosTarde = $ahora->diffInMinutes($finVisita);
                $mensaje = "Visita expirada. Se pasó {$minutosTarde} minutos del horario autorizado";
            }

            $this->registrarBitacora(
                'INTENTO_FUERA_HORARIO',
                "Intento de ingreso fuera de horario - Visitante: {$visita->nombre_visitante}, Detalle: {$mensaje}",
                $visita->id
            );

            return response()->json([
                'success' => false,
                'message' => $mensaje
            ]);
        }

        $this->registrarBitacora(
            'VALIDACION_EXITOSA',
            "Código validado correctamente - Visitante: {$visita->nombre_visitante}, CI: {$request->ci_visitante}",
            $visita->id
        );

        return response()->json([
            'success' => true,
            'visita' => [
                'id' => $visita->id,
                'nombre_visitante' => $visita->nombre_visitante,
                'ci_visitante' => $visita->ci_visitante,
                'motivo' => $visita->motivo,
                'residente' => $visita->residente->nombre_completo,
                'placa_vehiculo' => $visita->placa_vehiculo,
                'fecha_inicio' => $visita->fecha_inicio,
                'fecha_fin' => $visita->fecha_fin
            ]
        ]);
    }

    // REGISTRAR ENTRADA - SOLO PORTERO Y ADMIN
    public function registrarEntrada(Request $request, $id)
    {
        $visita = Visita::findOrFail($id);
        
        // 🚪 Solo PORTERO y 🔧 ADMIN pueden registrar entradas
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            return redirect()->back()->with('error', 'No tienes permisos para registrar entradas');
        }
        
        if ($visita->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Esta visita ya fue procesada');
        }

        // VALIDAR HORARIO ANTES DE REGISTRAR ENTRADA
        $ahora = Carbon::now();
        $inicioConTolerancia = Carbon::parse($visita->fecha_inicio)->subMinutes(30);
        $finVisita = Carbon::parse($visita->fecha_fin);

        if ($ahora < $inicioConTolerancia || $ahora > $finVisita) {
            $mensaje = '';
            
            if ($ahora < $inicioConTolerancia) {
                $minutosAntes = $ahora->diffInMinutes($inicioConTolerancia);
                $mensaje = "Entrada muy temprana. Debe esperar {$minutosAntes} minutos más";
            } elseif ($ahora > $finVisita) {
                $minutosTarde = $ahora->diffInMinutes($finVisita);
                $mensaje = "Entrada tardía. Se pasó {$minutosTarde} minutos del horario autorizado";
            }

            $this->registrarBitacora(
                'ENTRADA_FUERA_HORARIO',
                "Intento de entrada fuera de horario - Visitante: {$visita->nombre_visitante}, Detalle: {$mensaje}",
                $visita->id
            );

            return redirect()->back()->with('error', $mensaje);
        }

        $visita->update([
            'estado' => 'en_curso',
            'hora_entrada' => Carbon::now(),
            'user_entrada_id' => Auth::id()
        ]);

        $this->registrarBitacora(
            'REGISTRAR_ENTRADA',
            "Entrada registrada - Visitante: {$visita->nombre_visitante}, CI: {$visita->ci_visitante}",
            $visita->id
        );

        return redirect()->route('visitas.show', $visita)
            ->with('success', 'Entrada registrada correctamente');
    }

    // REGISTRAR SALIDA - SOLO PORTERO Y ADMIN
    public function registrarSalida(Request $request, $id)
    {
        $visita = Visita::findOrFail($id);
        
        // Solo PORTERO y ADMIN pueden registrar salidas
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            return redirect()->back()->with('error', 'No tienes permisos para registrar salidas');
        }
        
        if ($visita->estado !== 'en_curso') {
            return redirect()->back()->with('error', 'No se puede registrar salida');
        }

        // VERIFICAR SI LA SALIDA ES DESPUÉS DEL HORARIO AUTORIZADO
        $ahora = Carbon::now();
        $finVisita = Carbon::parse($visita->fecha_fin);
        $observacionesExtra = '';

        if ($ahora > $finVisita) {
            $minutosTarde = $ahora->diffInMinutes($finVisita);
            $observacionesExtra = "SALIDA TARDÍA: {$minutosTarde} minutos después del horario autorizado. ";
            
            $this->registrarBitacora(
                'SALIDA_TARDIA',
                "Salida tardía registrada - Visitante: {$visita->nombre_visitante}, Tardanza: {$minutosTarde} minutos",
                $visita->id
            );
        }

        $observacionesFinales = $observacionesExtra . ($request->observaciones ?? '');

        $visita->update([
            'estado' => 'finalizada',
            'hora_salida' => Carbon::now(),
            'user_salida_id' => Auth::id(),
            'observaciones' => $observacionesFinales
        ]);

        $this->registrarBitacora(
            'REGISTRAR_SALIDA',
            "Salida registrada - Visitante: {$visita->nombre_visitante}, CI: {$visita->ci_visitante}",
            $visita->id
        );

        $mensaje = 'Salida registrada correctamente';
        if ($ahora > $finVisita) {
            $minutosTarde = $ahora->diffInMinutes($finVisita);
            $mensaje .= " (TARDÍA: {$minutosTarde} minutos)";
        }

        return redirect()->route('visitas.show', $visita)
            ->with('success', $mensaje);
    }

    // PANEL DE GUARDIA - SOLO PORTERO Y ADMIN
    public function panelGuardia()
    {
        // 🚪 Solo PORTERO y 🔧 ADMIN pueden acceder al panel
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            abort(403, 'No tienes permisos para acceder al Panel de Guardia');
        }
        
        $visitasEnCurso = Visita::where('estado', 'en_curso')
            ->whereNotNull('hora_entrada')
            ->with('residente')
            ->get();
            
        // Visitas "listas para ingresar ahora" (dentro de la ventana de
        // tolerancia que exige registrarEntrada(): 30 min antes de
        // fecha_inicio hasta fecha_fin).
        $visitasPendientes = Visita::where('estado', 'pendiente')
            ->where('fecha_inicio', '<=', Carbon::now()->addHours(2))
            ->where('fecha_inicio', '>=', Carbon::now()->subMinutes(30))
            ->with('residente')
            ->get();

        // Resto de visitas pendientes futuras que aún no entran en la
        // ventana de 2 horas. Antes el panel simplemente no las mostraba,
        // dando la impresión de que una visita recién registrada se
        // "perdía". Ahora se listan aparte para que el guardia/admin las
        // vea igual, aunque todavía no pueda registrar la entrada.
        $visitasPendientesProximas = Visita::where('estado', 'pendiente')
            ->where('fecha_inicio', '>', Carbon::now()->addHours(2))
            ->with('residente')
            ->orderBy('fecha_inicio')
            ->get();

        return view('visitas.panel-guardia', compact('visitasEnCurso', 'visitasPendientes', 'visitasPendientesProximas'));
    }

    // BUSCAR POR CÓDIGO - SOLO PORTERO Y ADMIN
    public function buscarPorCodigo(Request $request)
    {
        // 🚪 Solo PORTERO y 🔧 ADMIN pueden buscar códigos
        if (!$this->tieneAlgunPermiso(['operar porteria', 'administrar visitas'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos']);
        }
        
        $visita = Visita::where('codigo', $request->codigo)
            ->with('residente')
            ->first();

        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'Código no encontrado']);
        }

        return response()->json(['success' => true, 'visita' => $visita]);
    }

    // Generar código de 6 dígitos único
    private function generarCodigo()
    {
        do {
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Visita::where('codigo', $codigo)->exists());

        return $codigo;
    }

    // Método privado para registrar en bitácora
    /**
     * Registra una acción en la bitácora. Si el registro falla por
     * cualquier motivo (ej. el mensaje sigue siendo demasiado largo para
     * la columna), solo se loguea el error: la operación principal
     * (registrar salida, crear visita, etc.) no debe verse interrumpida
     * por un problema de auditoría secundario.
     */
    private function registrarBitacora($accion, $descripcion, $id_operacion = null)
    {
        try {
            Bitacora::create([
                'user_id' => Auth::id(),
                'accion' => $accion . ' - ' . $descripcion,
                'fecha_hora' => Carbon::now(),
                'id_operacion' => $id_operacion,
                'ip' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar en bitácora (VisitaController): ' . $e->getMessage());
        }
    }
}