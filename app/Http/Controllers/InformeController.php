<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\Pago;
use App\Services\Reportes\InformeEstatico;
use App\Services\Reportes\OpenAiReportService;
use App\Services\Reportes\ReporteExporter;
use App\Services\Reportes\ReporteSchema;
use App\Traits\BitacoraTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InformeController extends Controller
{
    use BitacoraTrait;

    /** Formatos descargables soportados por el CU12. */
    private const FORMATOS_DESCARGA = ['html', 'csv', 'xlsx', 'pdf'];

    /**
     * CU12 — Generar Informe Administrativo.
     *
     * Página principal con tres modos:
     *   1. Estático  — consultas predefinidas por tabla.
     *   2. Dinámico  — el usuario elige la tabla y las columnas.
     *   3. Voz/Texto — Whisper + OpenAI generan el reporte a partir de una
     *      instrucción hablada o escrita.
     *
     * También resuelve la previsualización y la descarga de los informes
     * estáticos (los modos dinámico y por voz se sirven vía endpoints JSON).
     */
    public function administrativo(Request $request)
    {
        // Catálogos para poblar la interfaz.
        $catalogoEstatico = InformeEstatico::catalogo();
        $catalogoTablas   = ReporteSchema::listado();

        // Compatibilidad con enlaces antiguos (?tipo=residentes).
        $reporte = $request->get('reporte', $request->get('tipo'));
        $formato = strtolower((string) $request->get('formato', ''));

        $reporteData   = null;
        $reporteActivo = null;
        $errorReporte  = null;

        if ($reporte && isset($catalogoEstatico[$reporte])) {
            $reporteActivo = $reporte;

            try {
                $reporteData = InformeEstatico::generar($reporte);

                // ¿Descarga directa?
                if (in_array($formato, self::FORMATOS_DESCARGA, true)) {
                    $this->registrarEnBitacora("Exportó informe administrativo «{$reporte}» en formato {$formato}");

                    return ReporteExporter::exportar($formato, [
                        'titulo'    => $reporteData['titulo'],
                        'subtitulo' => InformeEstatico::descripcion($reporte),
                        'headers'   => $reporteData['headers'],
                        'rows'      => $reporteData['rows'],
                        'meta'      => $reporteData['resumen'],
                    ]);
                }

                $this->registrarEnBitacora("Consultó informe administrativo «{$reporte}»");
            } catch (\Throwable $e) {
                // En lugar de un error 500 opaco, mostramos el problema en pantalla.
                report($e);
                $reporteData  = null;
                $errorReporte = $e->getMessage();
            }
        }

        return view('informes.administrativo', compact(
            'catalogoEstatico',
            'catalogoTablas',
            'reporteData',
            'reporteActivo',
            'errorReporte'
        ));
    }

    /**
     * [AJAX] Devuelve las columnas disponibles de una tabla (modo dinámico).
     */
    public function columnas(Request $request): JsonResponse
    {
        $tabla = (string) $request->get('tabla', '');

        if (! ReporteSchema::existe($tabla)) {
            return response()->json(['error' => 'Tabla no permitida.'], 422);
        }

        return response()->json([
            'tabla'    => $tabla,
            'columnas' => ReporteSchema::columnas($tabla),
        ]);
    }

    /**
     * Modo dinámico: genera el reporte a partir de la tabla y columnas
     * elegidas por el usuario. Devuelve JSON (previsualización) o un archivo
     * descargable según el formato solicitado.
     */
    public function dinamico(Request $request)
    {
        $validado = $request->validate([
            'tabla'        => 'required|string',
            'columnas'     => 'array',
            'columnas.*'   => 'string',
            'relaciones'   => 'array',
            'relaciones.*' => 'string',
            'filtros'      => 'array',
            'ordenar_por'  => 'nullable|string',
            'direccion'    => 'nullable|string',
            'limite'       => 'nullable|integer|min:1|max:100000',
            'formato'      => 'nullable|string',
        ]);

        if (! ReporteSchema::existe($validado['tabla'])) {
            return response()->json(['error' => 'Tabla no permitida.'], 422);
        }

        $data = ReporteSchema::construir(
            $validado['tabla'],
            $validado['columnas'] ?? [],
            $validado['relaciones'] ?? [],
            $validado['filtros'] ?? [],
            $validado['ordenar_por'] ?? null,
            $validado['direccion'] ?? 'asc',
            isset($validado['limite']) ? (int) $validado['limite'] : null
        );

        $formato = strtolower((string) ($validado['formato'] ?? ''));

        if (in_array($formato, self::FORMATOS_DESCARGA, true)) {
            $this->registrarEnBitacora("Exportó reporte dinámico de «{$validado['tabla']}» en formato {$formato}");

            return ReporteExporter::exportar($formato, [
                'titulo'  => $data['titulo'],
                'headers' => $data['headers'],
                'rows'    => $data['rows'],
            ]);
        }

        $this->registrarEnBitacora("Generó reporte dinámico de «{$validado['tabla']}»");

        return response()->json($data);
    }

    /**
     * [Voz] Transcribe un audio a texto usando Whisper.
     */
    public function transcribir(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => 'required|file|max:25600', // hasta 25 MB
        ]);

        $servicio = new OpenAiReportService();

        if (! $servicio->configurado()) {
            return response()->json([
                'error' => 'El reconocimiento de voz no está configurado. Define OPENAI_API_KEY en el archivo .env.',
            ], 503);
        }

        try {
            $archivo = $request->file('audio');
            $texto   = $servicio->transcribir(
                $archivo->getRealPath(),
                $archivo->getClientOriginalName() ?: 'audio.webm'
            );

            $this->registrarEnBitacora('Transcribió audio para reporte por voz');

            return response()->json(['texto' => $texto]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * [Voz/Texto] Interpreta una instrucción en lenguaje natural, genera la
     * especificación del reporte (validada) y devuelve la previsualización.
     */
    public function interpretar(Request $request): JsonResponse
    {
        $request->validate([
            'texto' => 'required|string|max:2000',
        ]);

        $servicio = new OpenAiReportService();

        if (! $servicio->configurado()) {
            return response()->json([
                'error' => 'La generación por IA no está configurada. Define OPENAI_API_KEY en el archivo .env.',
            ], 503);
        }

        try {
            $spec = $servicio->interpretar((string) $request->input('texto', ''));

            $data = ReporteSchema::construir(
                $spec['tabla'],
                $spec['columnas'],
                $spec['relaciones'],
                $spec['filtros'],
                $spec['ordenar_por'],
                $spec['direccion'],
                $spec['limite']
            );

            $this->registrarEnBitacora('Generó reporte por voz/texto con IA');

            return response()->json([
                'spec'        => $spec,
                'explicacion' => $spec['explicacion'] ?? '',
                'titulo'      => $spec['titulo'] ?? $data['titulo'],
                'headers'     => $data['headers'],
                'rows'        => $data['rows'],
                'count'       => $data['count'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * [Voz/Texto] Exporta el reporte generado por IA en el formato solicitado.
     * La especificación se vuelve a validar y la consulta se reconstruye en el
     * servidor (no se confía en filas enviadas por el cliente).
     */
    public function exportarIa(Request $request)
    {
        $validado = $request->validate([
            'tabla'        => 'required|string',
            'columnas'     => 'array',
            'relaciones'   => 'array',
            'filtros'      => 'array',
            'ordenar_por'  => 'nullable|string',
            'direccion'    => 'nullable|string',
            'limite'       => 'nullable|integer',
            'titulo'       => 'nullable|string',
            'formato'      => 'required|string',
        ]);

        if (! ReporteSchema::existe($validado['tabla'])) {
            return response()->json(['error' => 'Tabla no permitida.'], 422);
        }

        $formato = strtolower($validado['formato']);
        if (! in_array($formato, self::FORMATOS_DESCARGA, true)) {
            $formato = 'pdf';
        }

        $data = ReporteSchema::construir(
            $validado['tabla'],
            $validado['columnas'] ?? [],
            $validado['relaciones'] ?? [],
            $validado['filtros'] ?? [],
            $validado['ordenar_por'] ?? null,
            $validado['direccion'] ?? 'asc',
            isset($validado['limite']) ? (int) $validado['limite'] : null
        );

        $this->registrarEnBitacora("Exportó reporte por IA de «{$validado['tabla']}» en formato {$formato}");

        return ReporteExporter::exportar($formato, [
            'titulo'  => $validado['titulo'] ?: $data['titulo'],
            'headers' => $data['headers'],
            'rows'    => $data['rows'],
        ]);
    }

    /**
     * CU14 — Reporte de pagos (se mantiene sin cambios).
     */
    public function pagos(Request $request)
    {
        $query = Pago::with(['cuota.residente', 'user']);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha_pago', [$request->desde, $request->hasta . ' 23:59:59']);
        } elseif ($request->filled('mes')) {
            [$anio, $mes] = explode('-', $request->mes);
            $query->whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes);
        }

        if ($request->filled('metodo')) {
            $query->where('metodo', $request->metodo);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos   = $query->orderByDesc('fecha_pago')->get();
        $total   = $pagos->sum('monto_pagado');
        $morosos = Residente::whereHas('cuotas', fn ($q) => $q->where('estado', 'pendiente'))->get();

        $this->registrarEnBitacora('Generó reporte de pagos');

        return view('informes.pagos', compact('pagos', 'total', 'morosos'));
    }
}
