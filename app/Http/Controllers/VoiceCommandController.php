<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoiceCommandController extends Controller
{
    /**
     * Asistente de voz por comandos de texto (sin IA externa): el navegador
     * transcribe la voz con la Web Speech API y este endpoint hace match del
     * texto contra el mapa de comandos de abajo para decidir a dónde navegar.
     */
    public function handle(Request $request)
    {
        $command = strtolower(trim((string) $request->input('command')));

        $commands = [
            // --- PANEL PRINCIPAL ---
            'ver inicio'            => ['url' => '/panel', 'message' => 'Volviendo al panel de control principal'],
            'ir a inicio'           => ['url' => '/panel', 'message' => 'Volviendo al panel de control principal'],
            'panel'                 => ['url' => '/panel', 'message' => 'Abriendo el panel de control'],
            'dashboard'             => ['url' => '/panel', 'message' => 'Abriendo el panel de control'],

            // --- CUOTAS, MULTAS Y PAGOS (CU7) ---
            'mis cuotas'            => ['url' => '/mis-cuotas', 'message' => 'Abriendo tus cuotas pendientes'],
            'ver cuotas'            => ['url' => '/cuotas', 'message' => 'Abriendo gestión de cuotas'],
            'cuotas'                => ['url' => '/cuotas', 'message' => 'Abriendo gestión de cuotas'],
            'tipos de cuota'        => ['url' => '/tipos-cuotas', 'message' => 'Abriendo tipos de cuota'],
            'ver multas'            => ['url' => '/multas', 'message' => 'Abriendo historial de multas'],
            'multas'                => ['url' => '/multas', 'message' => 'Abriendo historial de multas'],
            'ver pagos'             => ['url' => '/pagos', 'message' => 'Abriendo el listado de pagos'],
            'pagos'                 => ['url' => '/pagos', 'message' => 'Abriendo el listado de pagos'],
            'registrar pago'        => ['url' => '/pagos/create', 'message' => 'Abriendo formulario para registrar un pago'],

            // --- ÁREAS COMUNES Y RESERVAS (CU19) ---
            'áreas comunes'         => ['url' => '/areas-comunes', 'message' => 'Abriendo lista de áreas comunes'],
            'areas comunes'         => ['url' => '/areas-comunes', 'message' => 'Abriendo lista de áreas comunes'],
            'ver reservas'          => ['url' => '/reservas', 'message' => 'Abriendo módulo de reservas'],
            'reservas'              => ['url' => '/reservas', 'message' => 'Abriendo módulo de reservas'],

            // --- VISITAS Y GUARDIA ---
            'ver visitas'           => ['url' => '/visitas', 'message' => 'Abriendo control de visitas'],
            'visitas'               => ['url' => '/visitas', 'message' => 'Abriendo control de visitas'],
            'validar código'        => ['url' => '/validar-codigo', 'message' => 'Abriendo formulario de validación de código'],
            'validar codigo'        => ['url' => '/validar-codigo', 'message' => 'Abriendo formulario de validación de código'],
            'panel de guardia'      => ['url' => '/panel-guardia', 'message' => 'Abriendo panel de control de guardia'],

            // --- MANTENIMIENTO ---
            'mantenimientos'        => ['url' => '/mantenimientos', 'message' => 'Abriendo programación de mantenimientos'],

            // --- PERSONAL ---
            'ver cargos'            => ['url' => '/empleados/cargo', 'message' => 'Abriendo cargos de empleados'],
            'cargos'                => ['url' => '/empleados/cargo', 'message' => 'Abriendo cargos de empleados'],
            'empleados'             => ['url' => '/empleados', 'message' => 'Abriendo gestión del personal'],

            // --- ADMINISTRACIÓN Y AUDITORÍA ---
            'bitácora'              => ['url' => '/bitacora', 'message' => 'Abriendo la bitácora del sistema'],
            'bitacora'              => ['url' => '/bitacora', 'message' => 'Abriendo la bitácora del sistema'],
            'roles'                 => ['url' => '/roles', 'message' => 'Abriendo gestión de roles y permisos'],
            'usuarios'              => ['url' => '/users', 'message' => 'Abriendo lista de usuarios del sistema'],
            'residentes'            => ['url' => '/residentes', 'message' => 'Abriendo padrón de residentes'],
            'unidades'              => ['url' => '/unidades', 'message' => 'Abriendo listado de unidades'],
            'propiedades'           => ['url' => '/propiedades', 'message' => 'Abriendo listado de propiedades'],

            // --- COMUNICACIÓN (CU17/CU18/CU20) ---
            'notificaciones'        => ['url' => '/notificaciones', 'message' => 'Abriendo bandeja de notificaciones'],
            'comunicados'           => ['url' => '/comunicados', 'message' => 'Abriendo sección de comunicados'],
            'reclamos'              => ['url' => '/reclamos', 'message' => 'Abriendo gestión de reclamos'],
            'eventos'               => ['url' => '/eventos', 'message' => 'Abriendo eventos comunitarios'],
            'incidencias'           => ['url' => '/incidencias', 'message' => 'Abriendo registro de incidencias'],

            // --- INFORMES ---
            'informe administrativo' => ['url' => '/informes/administrativo', 'message' => 'Abriendo informe administrativo'],
            'informe de pagos'       => ['url' => '/informes/pagos', 'message' => 'Abriendo informe de pagos'],

            // --- EMPRESAS EXTERNAS ---
            'empresas'              => ['url' => '/empresas', 'message' => 'Abriendo empresas externas'],
        ];

        foreach ($commands as $key => $action) {
            if (str_contains($command, $key)) {
                return response()->json([
                    'success' => true,
                    'message' => $action['message'],
                    'redirect' => $action['url'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Comando no reconocido. Intenta con: "ver panel", "mis cuotas", "ver multas", "registrar pago", "ver reservas" o "ver reclamos".',
        ]);
    }
}
