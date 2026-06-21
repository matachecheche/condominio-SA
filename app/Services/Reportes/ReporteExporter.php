<?php

namespace App\Services\Reportes;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exporta un conjunto de datos tabular a los formatos soportados por el CU12:
 * HTML, Excel (XLSX), CSV y PDF.
 *
 * Recibe siempre una estructura uniforme (título, encabezados y filas) sin
 * importar si el reporte vino del modo estático, dinámico o por voz/texto, de
 * modo que la lógica de exportación es única y reutilizable.
 */
class ReporteExporter
{
    /**
     * @param string $formato  html | csv | xlsx | pdf
     * @param array{titulo:string, subtitulo?:string, headers:array, rows:array, meta?:array} $payload
     */
    public static function exportar(string $formato, array $payload): Response
    {
        $titulo    = $payload['titulo']    ?? 'Informe administrativo';
        $subtitulo = $payload['subtitulo'] ?? '';
        $headers   = $payload['headers']   ?? [];
        $rows      = $payload['rows']      ?? [];
        $meta      = $payload['meta']      ?? [];

        return match (strtolower($formato)) {
            'csv'   => self::csv($titulo, $headers, $rows),
            'xlsx',
            'excel' => self::xlsx($titulo, $headers, $rows),
            'pdf'   => self::pdf($titulo, $subtitulo, $headers, $rows, $meta),
            default => self::html($titulo, $subtitulo, $headers, $rows, $meta),
        };
    }

    /** Nombre de archivo seguro con marca de tiempo. */
    private static function nombreArchivo(string $titulo, string $ext): string
    {
        $base = \Illuminate\Support\Str::slug($titulo) ?: 'informe';
        return $base . '_' . Carbon::now()->format('Ymd_His') . '.' . $ext;
    }

    /** HTML autocontenido descargable. */
    private static function html(string $titulo, string $subtitulo, array $headers, array $rows, array $meta): Response
    {
        $html = view('informes.export_html', compact('titulo', 'subtitulo', 'headers', 'rows', 'meta'))->render();

        return new Response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . self::nombreArchivo($titulo, 'html') . '"',
        ]);
    }

    /** PDF mediante DomPDF. */
    private static function pdf(string $titulo, string $subtitulo, array $headers, array $rows, array $meta): Response
    {
        $orientacion = count($headers) > 6 ? 'landscape' : 'portrait';

        $pdf = Pdf::loadView('informes.pdf', compact('titulo', 'subtitulo', 'headers', 'rows', 'meta'))
            ->setPaper('a4', $orientacion);

        return new Response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . self::nombreArchivo($titulo, 'pdf') . '"',
        ]);
    }

    /** CSV con BOM UTF-8 (se abre correctamente en Excel con tildes y ñ). */
    private static function csv(string $titulo, array $headers, array $rows): StreamedResponse
    {
        $nombre = self::nombreArchivo($titulo, 'csv');

        $callback = function () use ($headers, $rows) {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel detecte UTF-8.
            fwrite($salida, "\xEF\xBB\xBF");

            // Separador ; (estándar en Excel para configuraciones regionales ES).
            fputcsv($salida, $headers, ';');
            foreach ($rows as $fila) {
                $fila = array_map(static function ($v) {
                    if (is_bool($v)) {
                        return $v ? 'Sí' : 'No';
                    }
                    return $v === null ? '' : $v;
                }, $fila);
                fputcsv($salida, $fila, ';');
            }

            fclose($salida);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    /** XLSX nativo (sin dependencias externas). */
    private static function xlsx(string $titulo, array $headers, array $rows): Response
    {
        $hoja   = \Illuminate\Support\Str::limit(strip_tags($titulo), 28, '');
        $binary = SimpleXlsxWriter::generate($headers, $rows, $hoja ?: 'Informe');

        return new Response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . self::nombreArchivo($titulo, 'xlsx') . '"',
        ]);
    }
}
