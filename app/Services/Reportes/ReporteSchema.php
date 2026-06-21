<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Catálogo seguro de tablas para los reportes dinámicos y por voz/texto.
 *
 * Mantiene una lista blanca de las tablas/modelos sobre los que se permite
 * generar reportes, junto con metadatos (etiqueta, columna de fecha, relaciones
 * mostrables). Toda construcción de consultas pasa por aquí, de modo que ni el
 * usuario (reportes dinámicos) ni la IA (reportes por voz/texto) pueden tocar
 * tablas o columnas no autorizadas, ni inyectar SQL.
 */
class ReporteSchema
{
    /**
     * Columnas que nunca deben exponerse en un reporte.
     */
    private const COLUMNAS_OCULTAS = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'email_verified_at',
    ];

    /**
     * Operadores de filtro permitidos para reportes dinámicos.
     */
    public const OPERADORES = ['=', '!=', '>', '>=', '<', '<=', 'like', 'between', 'in'];

    /**
     * Lista blanca de tablas reportables.
     *
     * Cada entrada define:
     *  - model:       clase Eloquent
     *  - label:       nombre legible
     *  - date_column: columna de fecha para filtros por rango (o null)
     *  - relations:   columnas virtuales que resuelven un belongsTo:
     *                 claveVirtual => [relation, attr, label]
     */
    public static function principales(): array
    {
        return [
            'residentes' => [
                'model' => \App\Models\Residente::class,
                'label' => 'Residentes',
                'date_column' => 'created_at',
                'relations' => [],
            ],
            'propiedades' => [
                'model' => \App\Models\Propiedad::class,
                'label' => 'Propiedades',
                'date_column' => 'created_at',
                'relations' => [
                    'residente' => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                ],
            ],
            'unidades' => [
                'model' => \App\Models\Unidad::class,
                'label' => 'Unidades Habitacionales',
                'date_column' => 'created_at',
                'relations' => [
                    'residente' => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                ],
            ],
            'cuotas' => [
                'model' => \App\Models\Cuota::class,
                'label' => 'Cuotas',
                'date_column' => 'fecha_emision',
                'relations' => [
                    'residente'  => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                    'tipo_cuota' => ['relation' => 'tipoCuota', 'attr' => 'nombre', 'label' => 'Tipo de cuota'],
                ],
            ],
            'pagos' => [
                'model' => \App\Models\Pago::class,
                'label' => 'Pagos',
                'date_column' => 'fecha_pago',
                'relations' => [
                    'cuota'    => ['relation' => 'cuota', 'attr' => 'titulo', 'label' => 'Cuota'],
                    'registro' => ['relation' => 'user', 'attr' => 'name', 'label' => 'Registrado por'],
                ],
            ],
            'multas' => [
                'model' => \App\Models\Multa::class,
                'label' => 'Multas',
                'date_column' => 'fechaEmision',
                'relations' => [
                    'residente' => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                    'empleado'  => ['relation' => 'empleado', 'attr' => 'nombre', 'label' => 'Empleado'],
                ],
            ],
            'mantenimientos' => [
                'model' => \App\Models\Mantenimiento::class,
                'label' => 'Mantenimientos',
                'date_column' => 'fecha_hora',
                'relations' => [
                    'usuario' => ['relation' => 'usuario', 'attr' => 'name', 'label' => 'Usuario'],
                    'empresa' => ['relation' => 'empresa', 'attr' => 'nombre', 'label' => 'Empresa'],
                ],
            ],
            'incidencias' => [
                'model' => \App\Models\Incidencia::class,
                'label' => 'Incidencias',
                'date_column' => 'created_at',
                'relations' => [
                    'residente'    => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                    'atendido_por' => ['relation' => 'atendioPor', 'attr' => 'name', 'label' => 'Atendido por'],
                ],
            ],
            'reclamos' => [
                'model' => \App\Models\Reclamo::class,
                'label' => 'Reclamos',
                'date_column' => 'created_at',
                'relations' => [
                    'residente'    => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                    'atendido_por' => ['relation' => 'atendioPor', 'attr' => 'name', 'label' => 'Atendido por'],
                ],
            ],
            'visitas' => [
                'model' => \App\Models\Visita::class,
                'label' => 'Visitas',
                'date_column' => 'fecha_inicio',
                'relations' => [
                    'residente' => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                ],
            ],
            'reservas' => [
                'model' => \App\Models\Reserva::class,
                'label' => 'Reservas',
                'date_column' => 'fecha',
                'relations' => [
                    'area_comun' => ['relation' => 'areaComun', 'attr' => 'nombre', 'label' => 'Área común'],
                    'residente'  => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                ],
            ],
            'eventos' => [
                'model' => \App\Models\Evento::class,
                'label' => 'Eventos',
                'date_column' => 'fecha_hora',
                'relations' => [
                    'organizador' => ['relation' => 'organizador', 'attr' => 'name', 'label' => 'Organizador'],
                ],
            ],
            'comunicados' => [
                'model' => \App\Models\Comunicado::class,
                'label' => 'Comunicados',
                'date_column' => 'fecha_publicacion',
                'relations' => [
                    'autor' => ['relation' => 'usuario', 'attr' => 'name', 'label' => 'Autor'],
                ],
            ],
            'empleados' => [
                'model' => \App\Models\Empleado::class,
                'label' => 'Empleados',
                'date_column' => 'created_at',
                'relations' => [
                    'cargo' => ['relation' => 'cargo', 'attr' => 'cargo', 'label' => 'Cargo'],
                ],
            ],
            'area_comuns' => [
                'model' => \App\Models\AreaComun::class,
                'label' => 'Áreas Comunes',
                'date_column' => 'created_at',
                'relations' => [],
            ],
            'empresas_externas' => [
                'model' => \App\Models\EmpresaExterna::class,
                'label' => 'Empresas Externas',
                'date_column' => 'created_at',
                'relations' => [],
            ],
            'notificaciones' => [
                'model' => \App\Models\Notificacion::class,
                'label' => 'Notificaciones',
                'date_column' => 'fecha_hora',
                'relations' => [
                    'residente' => ['relation' => 'residente', 'attr' => 'nombre_completo', 'label' => 'Residente'],
                ],
            ],
            'tipos_cuotas' => [
                'model' => \App\Models\TipoCuota::class,
                'label' => 'Tipos de Cuota',
                'date_column' => 'created_at',
                'relations' => [],
            ],
        ];
    }

    /** ¿La tabla está en la lista blanca? */
    public static function existe(string $key): bool
    {
        return array_key_exists($key, self::principales());
    }

    /** Metadatos de una tabla reportable. */
    public static function meta(string $key): ?array
    {
        return self::principales()[$key] ?? null;
    }

    /** Lista de tablas (clave => etiqueta) para poblar selects. */
    public static function listado(): array
    {
        return array_map(fn ($t) => $t['label'], self::principales());
    }

    /**
     * Columnas disponibles para una tabla (introspección en tiempo real).
     *
     * @return array{base: array<string,string>, relaciones: array<string,string>}
     */
    public static function columnas(string $key): array
    {
        $meta = self::meta($key);
        if (! $meta) {
            return ['base' => [], 'relaciones' => []];
        }

        $tabla = (new $meta['model'])->getTable();
        $cols  = Schema::getColumnListing($tabla);

        $base = [];
        foreach ($cols as $col) {
            if (in_array($col, self::COLUMNAS_OCULTAS, true)) {
                continue;
            }
            $base[$col] = self::etiquetaColumna($col);
        }

        $relaciones = [];
        foreach ($meta['relations'] as $vkey => $rel) {
            $relaciones[$vkey] = $rel['label'];
        }

        return ['base' => $base, 'relaciones' => $relaciones];
    }

    /**
     * Construye y ejecuta un reporte dinámico de forma segura.
     *
     * @param array<int,string> $columnasBase  Columnas reales seleccionadas.
     * @param array<int,string> $columnasRel   Claves de relaciones seleccionadas.
     * @param array<int,array>  $filtros       [{columna, operador, valor, valor2?}]
     * @return array{titulo:string, headers:array<int,string>, rows:array<int,array<int,mixed>>, count:int}
     */
    public static function construir(
        string $key,
        array $columnasBase = [],
        array $columnasRel = [],
        array $filtros = [],
        ?string $ordenarPor = null,
        string $direccion = 'asc',
        ?int $limite = null
    ): array {
        $meta = self::meta($key);
        if (! $meta) {
            throw new \InvalidArgumentException("Tabla no permitida: {$key}");
        }

        /** @var \Illuminate\Database\Eloquent\Model $modelo */
        $modelo = $meta['model'];
        $tabla  = (new $modelo)->getTable();
        $colsValidas = Schema::getColumnListing($tabla);

        // Saneamos columnas base contra el esquema real y la lista de ocultas.
        $columnasBase = array_values(array_filter($columnasBase, fn ($c) =>
            in_array($c, $colsValidas, true) && ! in_array($c, self::COLUMNAS_OCULTAS, true)
        ));

        // Saneamos relaciones contra las definidas en la lista blanca.
        $columnasRel = array_values(array_filter($columnasRel, fn ($r) =>
            isset($meta['relations'][$r])
        ));

        // Si no se eligió ninguna columna, usamos todas las base.
        if (empty($columnasBase) && empty($columnasRel)) {
            foreach ($colsValidas as $c) {
                if (! in_array($c, self::COLUMNAS_OCULTAS, true)) {
                    $columnasBase[] = $c;
                }
            }
        }

        $query = $modelo::query();

        // Eager-load de relaciones necesarias.
        $eager = [];
        foreach ($columnasRel as $r) {
            $eager[] = $meta['relations'][$r]['relation'];
        }
        if ($eager) {
            $query->with(array_unique($eager));
        }

        // ── Filtros (solo sobre columnas reales y operadores permitidos) ──
        foreach ($filtros as $f) {
            $col = $f['columna'] ?? null;
            $op  = strtolower($f['operador'] ?? '=');
            $val = $f['valor'] ?? null;

            if (! $col || ! in_array($col, $colsValidas, true) || in_array($col, self::COLUMNAS_OCULTAS, true)) {
                continue;
            }
            if (! in_array($op, self::OPERADORES, true)) {
                continue;
            }

            if ($op === 'like') {
                $query->where($col, 'like', '%' . $val . '%');
            } elseif ($op === 'between') {
                $val2 = $f['valor2'] ?? null;
                if ($val !== null && $val !== '' && $val2 !== null && $val2 !== '') {
                    $query->whereBetween($col, [$val, $val2]);
                }
            } elseif ($op === 'in') {
                $items = is_array($val) ? $val : array_map('trim', explode(',', (string) $val));
                $query->whereIn($col, $items);
            } else {
                if ($val !== null && $val !== '') {
                    $query->where($col, $op, $val);
                }
            }
        }

        // ── Orden ──
        if ($ordenarPor && in_array($ordenarPor, $colsValidas, true)) {
            $direccion = strtolower($direccion) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($ordenarPor, $direccion);
        }

        // ── Límite ──
        if ($limite !== null && $limite > 0) {
            $query->limit(min($limite, 100000));
        }

        $registros = $query->get();

        // ── Construcción de encabezados ──
        $headers = [];
        foreach ($columnasBase as $c) {
            $headers[] = self::etiquetaColumna($c);
        }
        foreach ($columnasRel as $r) {
            $headers[] = $meta['relations'][$r]['label'];
        }

        // ── Construcción de filas ──
        $rows = [];
        foreach ($registros as $reg) {
            $fila = [];
            foreach ($columnasBase as $c) {
                $fila[] = self::formatear($reg->getAttribute($c));
            }
            foreach ($columnasRel as $r) {
                $rel    = $meta['relations'][$r];
                $objeto = $reg->{$rel['relation']};
                $fila[] = $objeto ? self::formatear($objeto->{$rel['attr']}) : '—';
            }
            $rows[] = $fila;
        }

        return [
            'titulo'  => 'Reporte de ' . $meta['label'],
            'headers' => $headers,
            'rows'    => $rows,
            'count'   => count($rows),
        ];
    }

    /** Normaliza un valor para mostrarlo en el reporte. */
    private static function formatear($valor): mixed
    {
        if ($valor === null) {
            return '—';
        }
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('d/m/Y H:i');
        }
        if ($valor instanceof \Illuminate\Support\Carbon) {
            return $valor->format('d/m/Y H:i');
        }
        return $valor;
    }

    /** Genera una etiqueta legible a partir del nombre de columna. */
    public static function etiquetaColumna(string $col): string
    {
        $overrides = [
            'id' => 'ID',
            'ci' => 'CI',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
            'residente_id' => 'ID Residente',
            'user_id' => 'ID Usuario',
            'usuario_id' => 'ID Usuario',
            'cuota_id' => 'ID Cuota',
            'multa_id' => 'ID Multa',
            'tipo_cuota_id' => 'ID Tipo Cuota',
            'area_comun_id' => 'ID Área Común',
            'empresaExterna_id' => 'ID Empresa',
            'cargo_empleado_id' => 'ID Cargo',
            'fechaEmision' => 'Fecha de emisión',
            'fechaLimite' => 'Fecha límite',
        ];

        if (isset($overrides[$col])) {
            return $overrides[$col];
        }

        $texto = str_replace('_', ' ', $col);
        $texto = preg_replace('/(?<!^)([A-Z])/', ' $1', $texto);
        return Str::ucfirst(trim($texto));
    }
}
