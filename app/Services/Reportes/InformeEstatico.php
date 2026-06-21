<?php

namespace App\Services\Reportes;

use App\Models\AreaComun;
use App\Models\Comunicado;
use App\Models\Cuota;
use App\Models\Empleado;
use App\Models\EmpresaExterna;
use App\Models\Evento;
use App\Models\Incidencia;
use App\Models\Mantenimiento;
use App\Models\Multa;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Reclamo;
use App\Models\Reserva;
use App\Models\Residente;
use App\Models\Unidad;
use App\Models\Visita;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Informes estáticos del CU12: consultas predefinidas sobre las tablas del
 * sistema, con columnas ya seleccionadas, filtros frecuentes (rango de fechas,
 * estado, etc.) y totales cuando aplica.
 *
 * Cada informe devuelve la misma estructura uniforme (titulo, headers, rows,
 * resumen) que consume tanto la vista como el exportador (HTML/Excel/CSV/PDF).
 */
class InformeEstatico
{
    /**
     * Catálogo de informes estáticos disponibles (para construir el menú/select).
     *
     * filtros: claves de filtros que el formulario debe mostrar:
     *   rango | estado | tipo_residente | metodo
     */
    public static function catalogo(): array
    {
        return [
            'residentes'      => ['label' => 'Residentes',                 'icono' => 'fa-users',              'filtros' => ['tipo_residente']],
            'morosos'         => ['label' => 'Residentes morosos',         'icono' => 'fa-user-clock',         'filtros' => []],
            'unidades'        => ['label' => 'Unidades habitacionales',    'icono' => 'fa-building',           'filtros' => ['estado']],
            'propiedades'     => ['label' => 'Propiedades',                'icono' => 'fa-home',               'filtros' => ['estado']],
            'cuotas'          => ['label' => 'Cuotas',                     'icono' => 'fa-file-invoice-dollar', 'filtros' => ['rango', 'estado']],
            'pagos'           => ['label' => 'Pagos',                      'icono' => 'fa-money-bill-wave',    'filtros' => ['rango', 'metodo', 'estado']],
            'multas'          => ['label' => 'Multas',                     'icono' => 'fa-gavel',              'filtros' => ['rango', 'estado']],
            'mantenimientos'  => ['label' => 'Mantenimientos',            'icono' => 'fa-tools',              'filtros' => ['rango']],
            'incidencias'     => ['label' => 'Incidencias',               'icono' => 'fa-triangle-exclamation', 'filtros' => ['rango', 'estado']],
            'reclamos'        => ['label' => 'Reclamos',                  'icono' => 'fa-comment-dots',       'filtros' => ['rango', 'estado']],
            'visitas'         => ['label' => 'Visitas',                   'icono' => 'fa-id-badge',           'filtros' => ['rango', 'estado']],
            'reservas'        => ['label' => 'Reservas de áreas comunes',  'icono' => 'fa-calendar-check',     'filtros' => ['rango', 'estado']],
            'eventos'         => ['label' => 'Eventos comunitarios',       'icono' => 'fa-calendar-day',       'filtros' => ['rango', 'estado']],
            'comunicados'     => ['label' => 'Comunicados',               'icono' => 'fa-bullhorn',           'filtros' => ['rango']],
            'empleados'       => ['label' => 'Empleados',                 'icono' => 'fa-id-card',            'filtros' => []],
            'areas_comunes'   => ['label' => 'Áreas comunes',             'icono' => 'fa-map-location-dot',   'filtros' => []],
            'empresas'        => ['label' => 'Empresas externas',         'icono' => 'fa-truck-field',        'filtros' => []],
            'financiero'      => ['label' => 'Resumen financiero',         'icono' => 'fa-chart-pie',          'filtros' => ['rango']],
        ];
    }

    /**
     * Genera un informe estático.
     *
     * @param array $f Filtros: desde, hasta, estado, tipo_residente, metodo
     * @return array{titulo:string, headers:array, rows:array, resumen:array, count:int}
     */
    public static function generar(string $key, array $f = []): array
    {
        $metodo = 'informe' . Str::studly($key);

        if (! method_exists(static::class, $metodo)) {
            return ['titulo' => 'Informe no disponible', 'headers' => [], 'rows' => [], 'resumen' => [], 'count' => 0];
        }

        $data = static::$metodo($f);
        $data['count']   = count($data['rows'] ?? []);
        $data['resumen'] = $data['resumen'] ?? [];

        return $data;
    }

    /* ───────────────────────── Helpers ───────────────────────── */

    private static function rango($q, string $columna, array $f)
    {
        if (! empty($f['desde'])) {
            $q->where($columna, '>=', $f['desde']);
        }
        if (! empty($f['hasta'])) {
            $q->where($columna, '<=', $f['hasta'] . ' 23:59:59');
        }
        return $q;
    }

    private static function fecha($valor, string $formato = 'd/m/Y'): string
    {
        if (! $valor) {
            return '—';
        }
        try {
            return Carbon::parse($valor)->format($formato);
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    }

    private static function bs($monto): string
    {
        return 'Bs ' . number_format((float) $monto, 2, ',', '.');
    }

    /* ───────────────────────── Informes ───────────────────────── */

    private static function informeResidentes(array $f): array
    {
        $q = Residente::query();
        if (! empty($f['tipo_residente'])) {
            $q->where('tipo_residente', $f['tipo_residente']);
        }
        $datos = $q->orderBy('apellido')->get();

        $rows = $datos->map(fn ($r, $i) => [
            $i + 1, $r->nombre, $r->apellido, $r->ci, $r->email, $r->tipo_residente,
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Residentes',
            'headers' => ['#', 'Nombre', 'Apellido', 'CI', 'Email', 'Tipo'],
            'rows'    => $rows,
        ];
    }

    private static function informeMorosos(array $f): array
    {
        $residentes = Residente::whereHas('cuotas', fn ($q) => $q->where('estado', 'pendiente'))
            ->with(['cuotas' => fn ($q) => $q->where('estado', 'pendiente')])
            ->get();

        $totalAdeudado = 0;
        $rows = $residentes->map(function ($r, $i) use (&$totalAdeudado) {
            $monto = $r->cuotas->sum('monto');
            $totalAdeudado += $monto;
            return [
                $i + 1,
                $r->nombre . ' ' . $r->apellido,
                $r->ci,
                $r->email,
                $r->cuotas->count(),
                self::bs($monto),
            ];
        })->values()->all();

        return [
            'titulo'  => 'Informe de Residentes Morosos',
            'headers' => ['#', 'Residente', 'CI', 'Email', 'Cuotas pendientes', 'Monto adeudado'],
            'rows'    => $rows,
            'resumen' => [
                'Residentes morosos' => $residentes->count(),
                'Total adeudado'     => self::bs($totalAdeudado),
            ],
        ];
    }

    private static function informeUnidades(array $f): array
    {
        $q = Unidad::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        $datos = $q->orderBy('codigo')->get();

        $rows = $datos->map(fn ($u, $i) => [
            $i + 1,
            $u->codigo,
            $u->residente ? $u->residente->nombre . ' ' . $u->residente->apellido : '—',
            $u->tipo_ocupacion,
            ucfirst($u->estado),
            $u->personas_por_unidad,
            $u->tiene_mascotas ? 'Sí' : 'No',
            $u->vehiculos,
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Unidades Habitacionales',
            'headers' => ['#', 'Código', 'Residente', 'Ocupación', 'Estado', 'Personas', 'Mascotas', 'Vehículos'],
            'rows'    => $rows,
        ];
    }

    private static function informePropiedades(array $f): array
    {
        $q = Propiedad::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        $datos = $q->orderBy('codigo')->get();

        $rows = $datos->map(fn ($p, $i) => [
            $i + 1,
            $p->codigo,
            $p->tipo,
            $p->ubicacion,
            ucfirst($p->estado),
            $p->residente ? $p->residente->nombre . ' ' . $p->residente->apellido : '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Propiedades',
            'headers' => ['#', 'Código', 'Tipo', 'Ubicación', 'Estado', 'Residente'],
            'rows'    => $rows,
        ];
    }

    private static function informeCuotas(array $f): array
    {
        $q = Cuota::with(['residente', 'tipoCuota']);
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'fecha_emision', $f);
        $datos = $q->orderByDesc('fecha_emision')->get();

        $rows = $datos->map(fn ($c, $i) => [
            $i + 1,
            $c->titulo,
            $c->tipoCuota?->nombre ?? '—',
            self::bs($c->monto),
            self::fecha($c->fecha_emision),
            self::fecha($c->fecha_vencimiento),
            ucfirst($c->estado),
            $c->residente ? $c->residente->nombre . ' ' . $c->residente->apellido : '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Cuotas',
            'headers' => ['#', 'Título', 'Tipo', 'Monto', 'Emisión', 'Vencimiento', 'Estado', 'Residente'],
            'rows'    => $rows,
            'resumen' => ['Total emitido' => self::bs($datos->sum('monto'))],
        ];
    }

    private static function informePagos(array $f): array
    {
        $q = Pago::with(['cuota.residente', 'user']);
        self::rango($q, 'fecha_pago', $f);
        if (! empty($f['metodo'])) {
            $q->where('metodo', $f['metodo']);
        }
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        $datos = $q->orderByDesc('fecha_pago')->get();

        $rows = $datos->map(fn ($p, $i) => [
            $i + 1,
            self::fecha($p->fecha_pago),
            $p->cuota?->residente ? $p->cuota->residente->nombre . ' ' . $p->cuota->residente->apellido : '—',
            $p->cuota?->titulo ?? '—',
            self::bs($p->monto_pagado),
            ucfirst($p->metodo ?? '—'),
            ucfirst($p->estado ?? '—'),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Pagos',
            'headers' => ['#', 'Fecha', 'Residente', 'Cuota', 'Monto', 'Método', 'Estado'],
            'rows'    => $rows,
            'resumen' => [
                'Pagos registrados' => $datos->count(),
                'Total recaudado'   => self::bs($datos->sum('monto_pagado')),
            ],
        ];
    }

    private static function informeMultas(array $f): array
    {
        $q = Multa::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'fechaEmision', $f);
        $datos = $q->orderByDesc('fechaEmision')->get();

        $rows = $datos->map(fn ($m, $i) => [
            $i + 1,
            $m->motivo,
            self::bs($m->monto),
            self::fecha($m->fechaEmision),
            self::fecha($m->fechaLimite),
            ucfirst($m->estado),
            $m->residente ? $m->residente->nombre . ' ' . $m->residente->apellido : '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Multas',
            'headers' => ['#', 'Motivo', 'Monto', 'Emisión', 'Límite', 'Estado', 'Residente'],
            'rows'    => $rows,
            'resumen' => ['Total en multas' => self::bs($datos->sum('monto'))],
        ];
    }

    private static function informeMantenimientos(array $f): array
    {
        $q = Mantenimiento::with(['usuario', 'empresa']);
        self::rango($q, 'fecha_hora', $f);
        $datos = $q->orderByDesc('fecha_hora')->get();

        $rows = $datos->map(fn ($m, $i) => [
            $i + 1,
            Str::limit($m->descripcion, 60),
            $m->empresa?->nombre ?? '—',
            self::bs($m->monto),
            self::fecha($m->fecha_hora, 'd/m/Y H:i'),
            ucfirst($m->prioridad ?? 'media'),
            $m->estado == 1 ? 'Activo' : 'Inactivo',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Mantenimientos',
            'headers' => ['#', 'Descripción', 'Empresa', 'Monto', 'Fecha', 'Prioridad', 'Estado'],
            'rows'    => $rows,
            'resumen' => ['Total invertido' => self::bs($datos->sum('monto'))],
        ];
    }

    private static function informeIncidencias(array $f): array
    {
        $q = Incidencia::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'created_at', $f);
        $datos = $q->orderByDesc('created_at')->get();

        $rows = $datos->map(fn ($inc, $i) => [
            $i + 1,
            $inc->numero_seguimiento,
            Str::limit($inc->titulo, 45),
            $inc->residente ? $inc->residente->nombre . ' ' . $inc->residente->apellido : '—',
            ucfirst($inc->prioridad),
            ucfirst(str_replace('_', ' ', $inc->estado)),
            self::fecha($inc->created_at),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Incidencias',
            'headers' => ['#', 'N° Seguimiento', 'Título', 'Residente', 'Prioridad', 'Estado', 'Fecha'],
            'rows'    => $rows,
        ];
    }

    private static function informeReclamos(array $f): array
    {
        $q = Reclamo::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'created_at', $f);
        $datos = $q->orderByDesc('created_at')->get();

        $rows = $datos->map(fn ($rec, $i) => [
            $i + 1,
            $rec->numero_seguimiento,
            Str::limit($rec->titulo, 45),
            $rec->residente ? $rec->residente->nombre . ' ' . $rec->residente->apellido : '—',
            ucfirst(str_replace('_', ' ', $rec->estado)),
            self::fecha($rec->created_at),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Reclamos',
            'headers' => ['#', 'N° Seguimiento', 'Título', 'Residente', 'Estado', 'Fecha'],
            'rows'    => $rows,
        ];
    }

    private static function informeVisitas(array $f): array
    {
        $q = Visita::with('residente');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'fecha_inicio', $f);
        $datos = $q->orderByDesc('fecha_inicio')->get();

        $rows = $datos->map(fn ($v, $i) => [
            $i + 1,
            $v->nombre_visitante,
            $v->ci_visitante,
            $v->residente ? $v->residente->nombre . ' ' . $v->residente->apellido : '—',
            Str::limit($v->motivo, 40),
            self::fecha($v->fecha_inicio, 'd/m/Y H:i'),
            ucfirst($v->estado),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Visitas',
            'headers' => ['#', 'Visitante', 'CI', 'Residente', 'Motivo', 'Fecha inicio', 'Estado'],
            'rows'    => $rows,
        ];
    }

    private static function informeReservas(array $f): array
    {
        $q = Reserva::with(['areaComun', 'residente']);
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'fecha', $f);
        $datos = $q->orderByDesc('fecha')->get();

        $rows = $datos->map(fn ($r, $i) => [
            $i + 1,
            $r->areaComun?->nombre ?? '—',
            $r->residente ? $r->residente->nombre . ' ' . $r->residente->apellido : '—',
            self::fecha($r->fecha),
            substr((string) $r->hora_inicio, 0, 5),
            substr((string) $r->hora_fin, 0, 5),
            ucfirst($r->estado),
            self::bs($r->monto_total),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Reservas',
            'headers' => ['#', 'Área común', 'Residente', 'Fecha', 'Inicio', 'Fin', 'Estado', 'Monto'],
            'rows'    => $rows,
            'resumen' => ['Total reservas' => self::bs($datos->sum('monto_total'))],
        ];
    }

    private static function informeEventos(array $f): array
    {
        $q = Evento::with('organizador');
        if (! empty($f['estado'])) {
            $q->where('estado', $f['estado']);
        }
        self::rango($q, 'fecha_hora', $f);
        $datos = $q->orderByDesc('fecha_hora')->get();

        $rows = $datos->map(fn ($e, $i) => [
            $i + 1,
            $e->nombre,
            $e->lugar,
            self::fecha($e->fecha_hora, 'd/m/Y H:i'),
            $e->cupo_maximo ?? 'Sin límite',
            ucfirst($e->estado),
            $e->organizador?->name ?? '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Eventos Comunitarios',
            'headers' => ['#', 'Nombre', 'Lugar', 'Fecha', 'Cupo', 'Estado', 'Organizador'],
            'rows'    => $rows,
        ];
    }

    private static function informeComunicados(array $f): array
    {
        $q = Comunicado::with('usuario');
        self::rango($q, 'fecha_publicacion', $f);
        $datos = $q->orderByDesc('fecha_publicacion')->get();

        $rows = $datos->map(fn ($c, $i) => [
            $i + 1,
            $c->titulo,
            $c->tipo,
            self::fecha($c->fecha_publicacion, 'd/m/Y H:i'),
            $c->usuario?->name ?? '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Comunicados',
            'headers' => ['#', 'Título', 'Tipo', 'Publicación', 'Autor'],
            'rows'    => $rows,
        ];
    }

    private static function informeEmpleados(array $f): array
    {
        $datos = Empleado::with('cargo')->orderBy('apellido')->get();

        $rows = $datos->map(fn ($e, $i) => [
            $i + 1,
            $e->nombre,
            $e->apellido,
            $e->ci,
            $e->cargo?->cargo ?? '—',
            $e->estado ? 'Activo' : 'Inactivo',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Empleados',
            'headers' => ['#', 'Nombre', 'Apellido', 'CI', 'Cargo', 'Estado'],
            'rows'    => $rows,
        ];
    }

    private static function informeAreasComunes(array $f): array
    {
        $datos = AreaComun::orderBy('nombre')->get();

        $rows = $datos->map(fn ($a, $i) => [
            $i + 1,
            $a->nombre,
            self::bs($a->monto),
            ucfirst($a->estado),
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Áreas Comunes',
            'headers' => ['#', 'Nombre', 'Monto', 'Estado'],
            'rows'    => $rows,
        ];
    }

    private static function informeEmpresas(array $f): array
    {
        $datos = EmpresaExterna::orderBy('nombre')->get();

        $rows = $datos->map(fn ($e, $i) => [
            $i + 1,
            $e->nombre,
            $e->servicio,
            $e->telefono ?? '—',
            $e->correo ?? '—',
            $e->direccion ?? '—',
        ])->values()->all();

        return [
            'titulo'  => 'Informe de Empresas Externas',
            'headers' => ['#', 'Nombre', 'Servicio', 'Teléfono', 'Correo', 'Dirección'],
            'rows'    => $rows,
        ];
    }

    private static function informeFinanciero(array $f): array
    {
        $pagos = Pago::query();
        self::rango($pagos, 'fecha_pago', $f);
        $totalRecaudado = (clone $pagos)->sum('monto_pagado');
        $numPagos       = (clone $pagos)->count();

        $cuotasPendientes = Cuota::where('estado', 'pendiente')->sum('monto');
        $multasPendientes = Multa::where('estado', 'pendiente')->sum('monto');
        $gastoMantenim    = Mantenimiento::query();
        self::rango($gastoMantenim, 'fecha_hora', $f);
        $totalMantenim = $gastoMantenim->sum('monto');

        $balance = $totalRecaudado - $totalMantenim;

        $rows = [
            ['Ingresos por pagos',              $numPagos . ' pagos',  self::bs($totalRecaudado)],
            ['Cuotas pendientes de cobro',      '—',                   self::bs($cuotasPendientes)],
            ['Multas pendientes de cobro',      '—',                   self::bs($multasPendientes)],
            ['Egresos por mantenimientos',      '—',                   self::bs($totalMantenim)],
            ['Balance (ingresos − egresos)',    '—',                   self::bs($balance)],
        ];

        return [
            'titulo'  => 'Resumen Financiero',
            'headers' => ['Concepto', 'Detalle', 'Monto'],
            'rows'    => $rows,
            'resumen' => [
                'Total recaudado'  => self::bs($totalRecaudado),
                'Total egresos'    => self::bs($totalMantenim),
                'Balance'          => self::bs($balance),
            ],
        ];
    }
}
