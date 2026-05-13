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
            if ($user) {
                $this->rol = $user->roles->pluck('name')->join(', ');
                $this->permisos = $this->obtenerPermisosUsuario($user);
            }
            return $next($request);
        });
    }

    private function obtenerPermisosUsuario($user)
    {
        $permisos = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', get_class($user))
            ->pluck('permissions.name')
            ->toArray();

        $permisosDirectos = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->where('model_has_permissions.model_id', $user->id)
            ->where('model_has_permissions.model_type', get_class($user))
            ->pluck('permissions.name')
            ->toArray();

        return array_unique(array_merge($permisos, $permisosDirectos));
    }

    private function tienePermiso($permiso)
    {
        return in_array($permiso, $this->permisos ?? []);
    }

    private function tieneAlgunPermiso($permisos)
    {
        return !empty(array_intersect($permisos, $this->permisos ?? []));
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($this->tienePermiso('administrar visitas')) {
            $titulo = "Administrar Visitas";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida']);
        } elseif ($this->tienePermiso('operar porteria')) {
            $titulo = "Control de Acceso - Portería";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida']);
        } elseif ($this->tienePermiso('gestionar visitas')) {
            $titulo = "Mis Visitas";
            $query = Visita::with(['residente', 'userEntrada', 'userSalida'])
                        ->whereHas('residente', function($q) use ($user) {
                            $q->where('email', $user->email);
                        });
        } else {
            abort(403, 'No tienes permisos para ver visitas');
        }

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

    public function create()
    {
        $user = Auth::user();
        if ($this->tienePermiso('administrar visitas')) {
            $residentes = Residente::all();
        } elseif ($this->tienePermiso('gestionar visitas')) {
            $residentes = Residente::where('email', $user->email)->get();
            if ($residentes->count() == 0) {  
                return redirect()->back()->with('error', 'Tu email no está registrado como residente.');
            }
        } else {
            abort(403);
        }
        return view('visitas.create', compact('residentes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // CORRECCIÓN: Quitamos 'required' de acompanante porque si es 0 no llega en el request
        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:255',
            'ci_visitante' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:now',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'placa_vehiculo' => 'nullable|string|max:20',
            'acompanante' => 'nullable', 
        ]);

        if ($this->tienePermiso('gestionar visitas') && !$this->tienePermiso('administrar visitas')) {
            $residente = Residente::find($request->residente_id);
            if ($residente->email !== $user->email) {
                return redirect()->back()->with('error', 'Solo puedes crear visitas para ti mismo')->withInput();
            }
        }

        $visita = Visita::create([
            'residente_id' => $request->residente_id,
            'nombre_visitante' => $request->nombre_visitante,
            'ci_visitante' => $request->ci_visitante,
            'placa_vehiculo' => $request->placa_vehiculo,
            'motivo' => $request->motivo,
            // CORRECCIÓN: Si el checkbox no viene, guardamos un false (0)
            'acompanante' => $request->has('acompanante'), 
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'codigo' => $this->generarCodigo(),
            'estado' => 'pendiente'
        ]);

        $this->registrarBitacora('CREAR_VISITA', "Visita creada - Código: {$visita->codigo}", $visita->id);
        return redirect()->route('visitas.show', $visita)->with('success', 'Visita registrada correctamente.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        if (!$this->tienePermiso('administrar visitas') && !$this->tienePermiso('operar porteria')) {
            if ($this->tienePermiso('gestionar visitas')) {
                if ($visita->residente->email !== $user->email) { abort(403); }
            } else { abort(403); }
        }
        $visita->load(['residente', 'userEntrada', 'userSalida']);
        return view('visitas.show', compact('visita'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        if ($visita->estado !== 'pendiente') { return redirect()->back(); }
        if ($this->tienePermiso('administrar visitas')) {
            $residentes = Residente::all();
        } elseif ($this->tienePermiso('gestionar visitas')) {
            if ($visita->residente->email !== $user->email) { abort(403); }
            $residentes = Residente::where('email', $user->email)->get();
        } else { abort(403); }
        return view('visitas.edit', compact('visita', 'residentes'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $visita = Visita::findOrFail($id);
        
        $request->validate([
            'residente_id' => 'required|exists:residentes,id',
            'nombre_visitante' => 'required|string|max:255',
            'ci_visitante' => 'required|string|max:20',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'placa_vehiculo' => 'nullable|string|max:20',
            'acompanante' => 'nullable',
        ]);

        $visita->update([
            'residente_id' => $request->residente_id,
            'nombre_visitante' => $request->nombre_visitante,
            'ci_visitante' => $request->ci_visitante,
            'placa_vehiculo' => $request->placa_vehiculo,
            'motivo' => $request->motivo,
            // CORRECCIÓN: Misma lógica para actualizar
            'acompanante' => $request->has('acompanante'), 
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        $this->registrarBitacora('EDITAR_VISITA', "Visita actualizada", $visita->id);
        return redirect()->route('visitas.show', $visita)->with('success', 'Actualizada correctamente');
    }

    public function destroy($id)
    {
        $visita = Visita::findOrFail($id);
        $visita->delete();
        return redirect()->route('visitas.index');
    }

    // --- MÉTODOS DE PORTERÍA ---
    public function mostrarValidarCodigo() { return view('visitas.validar-codigo'); }

    public function validarCodigo(Request $request) {
        $visita = Visita::where('codigo', $request->codigo)->where('ci_visitante', $request->ci_visitante)->where('estado', 'pendiente')->first();
        if (!$visita) return response()->json(['success' => false]);
        return response()->json(['success' => true, 'visita' => $visita]);
    }

    public function registrarEntrada(Request $request, $id) {
        $visita = Visita::findOrFail($id);
        $visita->update(['estado' => 'en_curso', 'hora_entrada' => Carbon::now(), 'user_entrada_id' => Auth::id()]);
        return redirect()->route('visitas.show', $visita);
    }

    public function registrarSalida(Request $request, $id) {
        $visita = Visita::findOrFail($id);
        $visita->update(['estado' => 'finalizada', 'hora_salida' => Carbon::now(), 'user_salida_id' => Auth::id()]);
        return redirect()->route('visitas.show', $visita);
    }

    public function panelGuardia() {
        $visitasEnCurso = Visita::where('estado', 'en_curso')->get();
        $visitasPendientes = Visita::where('estado', 'pendiente')->get();
        return view('visitas.panel-guardia', compact('visitasEnCurso', 'visitasPendientes'));
    }

    public function buscarPorCodigo(Request $request) {
        $visita = Visita::where('codigo', $request->codigo)->first();
        return response()->json(['success' => !!$visita, 'visita' => $visita]);
    }

    private function generarCodigo() {
        do { $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Visita::where('codigo', $codigo)->exists());
        return $codigo;
    }

    private function registrarBitacora($accion, $descripcion, $id_operacion = null) {
        Bitacora::create([
            'user_id' => Auth::id(),
            'accion' => $accion . ' - ' . $descripcion,
            'fecha_hora' => Carbon::now(),
            'id_operacion' => $id_operacion,
            'ip' => request()->ip(),
        ]);
    }
}