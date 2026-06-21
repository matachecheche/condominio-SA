<?php

namespace App\Services\Reportes;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integración con OpenAI para los reportes por voz o texto (CU12, punto 3).
 *
 *  - transcribir(): convierte audio en texto usando Whisper.
 *  - interpretar(): convierte una instrucción en lenguaje natural en una
 *    "especificación de reporte" en JSON (tabla, columnas, filtros, orden…).
 *
 * Importante (seguridad): el modelo NO genera SQL. Devuelve únicamente un JSON
 * restringido al catálogo de tablas/columnas permitidas (ReporteSchema). Esa
 * especificación se valida y se ejecuta con el constructor seguro de consultas,
 * evitando inyección SQL o acceso a datos no autorizados.
 */
class OpenAiReportService
{
    private string $apiKey;
    private string $chatModel;
    private string $whisperModel;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey       = (string) config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->chatModel    = (string) config('services.openai.model', 'gpt-4o-mini');
        $this->whisperModel = (string) config('services.openai.whisper_model', 'whisper-1');
        $this->baseUrl      = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
    }

    public function configurado(): bool
    {
        return $this->apiKey !== '';
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->timeout((int) config('services.openai.timeout', 60))
            ->acceptJson();
    }

    /**
     * Transcribe un archivo de audio a texto con Whisper.
     *
     * @param string $rutaArchivo Ruta absoluta del audio (webm, mp3, wav, m4a…)
     * @param string $idioma      Código ISO del idioma (es por defecto).
     */
    public function transcribir(string $rutaArchivo, string $nombreOriginal = 'audio.webm', string $idioma = 'es'): string
    {
        if (! $this->configurado()) {
            throw new \RuntimeException('No se ha configurado la clave OPENAI_API_KEY en el archivo .env.');
        }

        if (! is_file($rutaArchivo)) {
            throw new \RuntimeException('No se encontró el archivo de audio a transcribir.');
        }

        $respuesta = $this->http()
            ->attach('file', file_get_contents($rutaArchivo), $nombreOriginal)
            ->post($this->baseUrl . '/audio/transcriptions', [
                'model'    => $this->whisperModel,
                'language' => $idioma,
            ]);

        if ($respuesta->failed()) {
            Log::error('Whisper error', ['status' => $respuesta->status(), 'body' => $respuesta->body()]);
            throw new \RuntimeException('Error al transcribir el audio: ' . $this->mensajeError($respuesta->json()));
        }

        return trim((string) $respuesta->json('text', ''));
    }

    /**
     * Interpreta una instrucción en lenguaje natural y devuelve la
     * especificación del reporte (ya validada contra la lista blanca).
     *
     * @return array{
     *   tabla:string, columnas:array, relaciones:array, filtros:array,
     *   ordenar_por:?string, direccion:string, limite:?int,
     *   titulo:string, explicacion:string
     * }
     */
    public function interpretar(string $instruccion): array
    {
        if (! $this->configurado()) {
            throw new \RuntimeException('No se ha configurado la clave OPENAI_API_KEY en el archivo .env.');
        }

        $instruccion = trim($instruccion);
        if ($instruccion === '') {
            throw new \RuntimeException('La instrucción está vacía.');
        }

        $respuesta = $this->http()->post($this->baseUrl . '/chat/completions', [
            'model'           => $this->chatModel,
            'temperature'     => 0,
            'response_format' => ['type' => 'json_object'],
            'messages'        => [
                ['role' => 'system', 'content' => $this->promptSistema()],
                ['role' => 'user',   'content' => $instruccion],
            ],
        ]);

        if ($respuesta->failed()) {
            Log::error('OpenAI chat error', ['status' => $respuesta->status(), 'body' => $respuesta->body()]);
            throw new \RuntimeException('Error al interpretar la instrucción: ' . $this->mensajeError($respuesta->json()));
        }

        $contenido = $respuesta->json('choices.0.message.content', '');
        $spec      = $this->decodificarJson($contenido);

        if (! is_array($spec) || empty($spec['tabla'])) {
            throw new \RuntimeException('No se pudo entender la consulta. Intenta reformularla, por ejemplo: "pagos aprobados de este mes".');
        }

        return $this->validarSpec($spec);
    }

    /**
     * Valida y normaliza la especificación devuelta por la IA contra el
     * catálogo permitido. Descarta cualquier tabla/columna no autorizada.
     */
    public function validarSpec(array $spec): array
    {
        $tabla = $spec['tabla'] ?? '';

        if (! ReporteSchema::existe($tabla)) {
            throw new \RuntimeException("La consulta hace referencia a datos no disponibles para reportes (tabla: «{$tabla}»).");
        }

        $columnasDisp = ReporteSchema::columnas($tabla);
        $basesValidas = array_keys($columnasDisp['base']);
        $relsValidas  = array_keys($columnasDisp['relaciones']);

        $columnas = array_values(array_intersect((array) ($spec['columnas'] ?? []), $basesValidas));
        $relaciones = array_values(array_intersect((array) ($spec['relaciones'] ?? []), $relsValidas));

        // Filtros: solo columnas reales y operadores permitidos.
        $filtros = [];
        foreach ((array) ($spec['filtros'] ?? []) as $f) {
            $col = $f['columna'] ?? null;
            $op  = strtolower($f['operador'] ?? '=');
            if ($col && in_array($col, $basesValidas, true) && in_array($op, ReporteSchema::OPERADORES, true)) {
                $filtros[] = [
                    'columna'  => $col,
                    'operador' => $op,
                    'valor'    => $f['valor']  ?? null,
                    'valor2'   => $f['valor2'] ?? null,
                ];
            }
        }

        $ordenarPor = $spec['ordenar_por'] ?? null;
        if ($ordenarPor && ! in_array($ordenarPor, $basesValidas, true)) {
            $ordenarPor = null;
        }

        $limite = isset($spec['limite']) ? (int) $spec['limite'] : null;
        if ($limite !== null && $limite <= 0) {
            $limite = null;
        }

        return [
            'tabla'       => $tabla,
            'columnas'    => $columnas,
            'relaciones'  => $relaciones,
            'filtros'     => $filtros,
            'ordenar_por' => $ordenarPor,
            'direccion'   => strtolower($spec['direccion'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
            'limite'      => $limite,
            'titulo'      => $spec['titulo'] ?? ('Reporte de ' . (ReporteSchema::meta($tabla)['label'] ?? $tabla)),
            'explicacion' => $spec['explicacion'] ?? '',
        ];
    }

    /** Construye el prompt de sistema con el catálogo de datos permitido. */
    private function promptSistema(): string
    {
        $hoy = now()->toDateString();
        $catalogo = $this->catalogoParaIA();

        return <<<PROMPT
Eres un asistente que traduce instrucciones en español sobre un sistema de administración de condominios a una especificación de reporte en JSON.

Fecha actual: {$hoy}.

SOLO puedes usar las siguientes tablas y columnas (no inventes otras):
{$catalogo}

Devuelve EXCLUSIVAMENTE un objeto JSON válido con esta forma:
{
  "tabla": "<clave de tabla de la lista>",
  "columnas": ["<columnas base a mostrar>"],
  "relaciones": ["<claves de relación a mostrar, si aplica>"],
  "filtros": [{"columna": "<columna base>", "operador": "=|!=|>|>=|<|<=|like|between|in", "valor": "<valor>", "valor2": "<solo para between>"}],
  "ordenar_por": "<columna base o null>",
  "direccion": "asc|desc",
  "limite": <numero o null>,
  "titulo": "<título descriptivo del reporte>",
  "explicacion": "<breve explicación en español de lo que muestra el reporte>"
}

Reglas:
- Usa solo claves de tabla y nombres de columnas EXACTOS de la lista.
- Para fechas relativas (hoy, este mes, este año, última semana) calcula los valores concretos usando la fecha actual y usa el operador "between" sobre la columna de fecha adecuada.
- Para búsquedas de texto parcial usa "like".
- Si no se piden columnas específicas, deja "columnas" vacío (se mostrarán todas).
- Si la instrucción no corresponde a ninguna tabla disponible, igual elige la más cercana y explica la limitación en "explicacion".
- No añadas texto fuera del JSON.
PROMPT;
    }

    /** Catálogo compacto (tablas, columnas y relaciones) para el prompt. */
    private function catalogoParaIA(): string
    {
        $lineas = [];
        foreach (ReporteSchema::principales() as $key => $meta) {
            try {
                $cols = ReporteSchema::columnas($key);
            } catch (\Throwable $e) {
                continue;
            }
            $base = implode(', ', array_keys($cols['base']));
            $rels = array_keys($cols['relaciones']);
            $relTxt = $rels ? ' | relaciones: ' . implode(', ', $rels) : '';
            $fecha = $meta['date_column'] ? " | fecha: {$meta['date_column']}" : '';
            $lineas[] = "- {$key} ({$meta['label']}): {$base}{$relTxt}{$fecha}";
        }
        return implode("\n", $lineas);
    }

    private function decodificarJson(string $contenido): ?array
    {
        $contenido = trim($contenido);
        // Quita posibles vallas de código ```json ... ```
        $contenido = preg_replace('/^```(json)?\s*|\s*```$/m', '', $contenido) ?? $contenido;

        $data = json_decode($contenido, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        // Intento de rescate: extraer el primer bloque {...}.
        if (preg_match('/\{.*\}/s', $contenido, $m)) {
            $data = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        return null;
    }

    private function mensajeError($json): string
    {
        if (is_array($json) && isset($json['error']['message'])) {
            return $json['error']['message'];
        }
        return 'respuesta inesperada del servicio de IA.';
    }
}
