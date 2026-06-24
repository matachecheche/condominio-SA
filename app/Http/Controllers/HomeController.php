<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\BitacoraTrait;
use App\Models\Residente;
use App\Models\Empleado;

class HomeController extends Controller
{
    use BitacoraTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->registrarEnBitacora('Usuario accedió al panel de control');
        return view('panel.index');
    }

    public function login()
    {
        $this->registrarEnBitacora('Usuario inició sesión');
    }

    public function buscarGlobal(Request $request)
    {
        $query = trim($request->input('query'));

        if (!$query) {
            return redirect()->route('panel');
        }

        $queryMinuscula = mb_strtolower($query);

        // 1. DIRECCIONAMIENTO DIRECTO POR PALABRAS CLAVE (Módulos)
        if (str_contains($queryMinuscula, 'propie') || str_contains($queryMinuscula, 'casa') || str_contains($queryMinuscula, 'lote')) {
            return redirect()->route('propiedades.index');
        }
        if (str_contains($queryMinuscula, 'residen') || str_contains($queryMinuscula, 'vecin')) {
            return redirect()->route('residentes.index');
        }
        if (str_contains($queryMinuscula, 'emplea') || str_contains($queryMinuscula, 'trabaja') || str_contains($queryMinuscula, 'cargo')) {
            return redirect()->route('empleados.index');
        }
        if (str_contains($queryMinuscula, 'pago') || str_contains($queryMinuscula, 'cuota') || str_contains($queryMinuscula, 'recibo')) {
            return redirect()->route('pagos.index');
        }
        if (str_contains($queryMinuscula, 'multa') || str_contains($queryMinuscula, 'sancion')) {
            return redirect()->route('multas.index');
        }
        if (str_contains($queryMinuscula, 'visi') || str_contains($queryMinuscula, 'guardia') || str_contains($queryMinuscula, 'codigo')) {
            return redirect()->route('visitas.index');
        }
        if (str_contains($queryMinuscula, 'inciden') || str_contains($queryMinuscula, 'denun')) {
            return redirect()->route('incidencias.index');
        }
        if (str_contains($queryMinuscula, 'comunica') || str_contains($queryMinuscula, 'aviso')) {
            return redirect()->route('comunicados.index');
        }
        if (str_contains($queryMinuscula, 'bitacora') || str_contains($queryMinuscula, 'histor')) {
            return redirect()->route('bitacora.index');
        }

        // 2. BUSQUEDA POR PERSONA (Si escribe un nombre, apellido o CI)
        // Intenta buscar en Residentes
        $residenteExiste = Residente::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('apellido', 'LIKE', "%{$query}%")
            ->orWhere('ci', 'LIKE', "%{$query}%")
            ->first();

        if ($residenteExiste) {
            // Te direcciona a la lista de residentes aplicando el filtro por el nombre buscado
            return redirect()->route('residentes.index', ['search' => $query]);
        }

        // Intenta buscar en Empleados
        $empleadoExiste = Empleado::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('apellido', 'LIKE', "%{$query}%")
            ->orWhere('ci', 'LIKE', "%{$query}%")
            ->first();

        if ($empleadoExiste) {
            return redirect()->route('empleados.index', ['search' => $query]);
        }

        // 3. SI NO ENCUENTRA COINCIDENCIA DIRECTA
        // Por defecto, lo enviamos al panel de propiedades o residentes pasándole la búsqueda para que no rompa
        $this->registrarEnBitacora("Usuario realizó una búsqueda global sin redirección directa: '{$query}'");
        return redirect()->route('propiedades.index', ['search' => $query]);
    }
}