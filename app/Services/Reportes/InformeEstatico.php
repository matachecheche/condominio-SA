<?php

namespace App\Services\Reportes;

use App\Models\Cuota;
use App\Models\Empleado;
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
 * Informes predefinidos (estáticos) del CU12.
 *
 * Son consultas ya elaboradas y listas para visualizar o descargar: NO usan
 * selectores ni filtros del usuario. Cada informe encapsula su propia lógica
 * (incluyendo rangos de fecha calculados internamente, como "del mes actual" o
 * "últimos 90 días"). Devuelven una estructura uniforme (titulo, headers, rows,
 * resumen) que consume tanto la vista como el exportador (HTML/Excel/CSV/PDF).
 */
class InformeEstatico
{
    /**
     * Catálogo de informes predefinidos, agrupados por categoría para mostrarse
     * como tarjetas en la interfaz.
     */
    public static function catalogo(): array
    {
        return [
            // ───────── Finanzas ─────────
            'resumen_financiero' => [
                'label' => 'Resumen Financiero',
                'descripcion' => 'Ingresos, egresos y balance general del condominio.',
                'icono' => 'fa-chart-pie', 'grupo' => 'Finanzas',
            ],
            'recaudacion_metodo' => [
                'label' => 'Recaudación por Método de Pago',
                'descripcion' => 'Cuánto se ha cobrado según cada método (efectivo, QR, Stripe…).',
                'icono' => 'fa-credit-card', 'grupo' => 'Finanzas',
            ],
            'morosos' => [
                'label' => 'Residentes Morosos',
                'descripcion' => 'Residentes con cuotas pendientes y el monto que adeudan.',
                'icono' => 'fa-user-clock', 'grupo' => 'Finanzas',
            ],
            'cuotas_vencidas' => [
                'label' => 'Cuotas Vencidas',
                'descripcion' => 'Cuotas pendientes cuya fecha de vencimiento ya pasó.',
                'icono' => 'fa-calendar-xmark', 'grupo' => 'Finanzas',
            ],
            'multas_pendientes' => [
                'label' => 'Multas Pendientes de Cobro',
                'descripcion' => 'Multas que aún no han sido pagadas.',
                'icono' => 'fa-gavel', 'grupo' => 'Finanzas',
            ],
            'pagos_mes' => [
                'label' => 'Pagos del Mes Actual',
                'descripcion' => 'Pagos registrados durante el mes en curso.',
                'icono' => 'fa-money-bill-wave', 'grupo' => 'Finanzas',
            ],

            // ───────── Operación ─────────
            'mantenimientos_trimestre' => [
                'label' => 'Mantenimientos (últimos 90 días)',
                'descripcion' => 'Mantenimientos realizados en el último trimestre y su costo.',
                'icono' => 'fa-tools', 'grupo' => 'Operación',
            ],
            'incidencias_abiertas' => [
                'label' => 'Incidencias Abiertas',
                'descripcion' => 'Incidencias pendientes o en revisión, por prioridad.',
                'icono' => 'fa-triangle-exclamation', 'grupo' => 'Operación',
            ],
            'reclamos_abiertos' => [
                'label' => 'Reclamos Abiertos',
                'descripcion' => 'Reclamos pendientes o en revisión sin resolver.',
                'icono' => 'fa-comment-dots', 'grupo' => 'Operación',
            ],
            'visitas_hoy' => [
                'label' => 'Visitas de Hoy',
                'descripcion' => 'Visitas programadas o registradas para el día de hoy.',
                'icono' => 'fa-id-badge', 'grupo' => 'Operación',
            ],
            'reservas_proximas' => [
                'label' => 'Reservas Próximas (30 días)',
                'descripcion' => 'Reservas de áreas comunes para los próximos 30 días.',
                'icono' => 'fa-calendar-check', 'grupo' => 'Operación',
            ],
            'eventos_proximos' => [
                'label' => 'Eventos Próximos',
                'descripcion' => 'Eventos comunitarios programados a futuro.',
                'icono' => 'fa-calendar-day', 'grupo' => 'Operación',
            ],

            // ───────── Comunidad y estructura ─────────
            'ocupacion_propiedades' => [
                'label' => 'Ocupación de Propiedades',
                'descripcion' => 'Cantidad de propiedades por estado (ocupada, disponible…).',
                'icono' => 'fa-building-circle-check', 'grupo' => 'Comunidad',
            ],
            'unidades_ocupacion' => [
                'label' => 'Unidades por Tipo de Ocupación',
                'descripcion' => 'Propietarios vs inquilinos, con personas y vehículos.',
                'icono' => 'fa-people-roof', 'grupo' => 'Comunidad',
            ],
            'directorio_residentes' => [
                'label' => 'Directorio de Residentes',
                'descripcion' => 'Listado completo de residentes con sus datos de contacto.',
                'icono' => 'fa-address-book', 'grupo' => 'Comunidad',
            ],
            'empleados_activos' => [
                'label' => 'Empleados Activos',
                'descripcion' => 'Personal actualmente activo con su cargo.',
                'icono' => 'fa-id-card', 'grupo' => 'Comunidad',
            ],
        ];
    }

    /**
     * Genera un informe predefinido por su clave.
     *
     * @return array{titulo:string, headers:array, rows:array, resumen:array, count:int}
     */
    public static function generar(string $key, array $f = []): array
    {
        $metodo = 'informe' . Str::studly($key);

        if (! method_exists(static::class, $metodo)) {
            return ['titulo' => 'Informe no disponible', 'headers' => [], 'rows' => [], 'resumen' => [], 'count' => 0];
        }

        $data = static::$metodo();
        $data['count']   = count($data['rows'] ?? []);
        $data['resumen'] = $data['resumen'] ?? [];

        return $data;
    }

    /** Descripción legible de un informe (para subtítulos/exportación). */
    public static function descripcion(string $key): string
    {
        return self::catalogo()[$key]['descripcion'] ?? '';
    }

    /* ───────────────────────── Helpers ───────────────────────── */

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

    private static function nombreResidente($residente): string
    {
        return $residente ? trim($residente->nombre . ' ' . $residente->apellido) : '—';
    }

    /* ═════════════════════════ Finanzas ═════════════════════════ */

    private static function informeResumenFinanciero(): array
    {
        $totalRecaudado   = (float) Pago::sum('monto_pagado');
        $numPagos         = Pago::count();
        $cuotasPendientes = (float) Cuota::where('estado', 'pendiente')->sum('monto');
        $multasPendientes = (float) Multa::where('estado', 'pendiente')->sum('monto');
        $totalMantenim    = (float) Mantenimiento::sum('monto');
        $balance          = $totalRecaudado - $totalMantenim;

        $rows = [
            ['Ingresos por pagos',           $numPagos . ' pagos', self::bs($totalRecaudado)],
            ['Cuotas pendientes de cobro',   '—',                  self::bs($cuotasPendientes)],
            ['Multas pendientes de cobro',   '—',                  self::bs($multasPendientes)],
            ['Egresos por mantenimientos',   '—',                  self::bs($totalMantenim)],
            ['Balance (ingresos − egresos)', '—',                  self::bs($balance)],
        ];

        return [
            'titulo'  => 'Resumen Financiero',
            'headers' => ['Concepto', 'Detalle', 'Monto'],
            'rows'    => $rows,
            'resumen' => [
                'Total recaudado' => self::bs($totalRecaudado),
                'Total egresos'   => self::bs($totalMantenim),
                'Balance'         => self::bs($balance),
            ],
        ];
    }

    private static function informeRecaudacionMetodo(): array
    {
        $pagos  = Pago::all();
        $grupos = $pagos->groupBy(fn ($p) => $p->metodo ?: 'Sin especificar');

        $rows = [];
        foreach ($grupos as $metodo => $items) {
            $rows[] = [ucfirst((string) $metodo), $items->count(), self::bs($items->sum('monto_pagado'))];
        }

        return [
            'titulo'  => 'Recaudación por Método de Pago',
            'headers' => ['Método', 'N° de pagos', 'Total recaudado'],
            'rows'    => $rows,
            'resumen' => [
                'Métodos distintos' => $grupos->count(),
                'Total general'     => self::bs($pagos->sum('monto_pagado')),
            ],
        ];
    }

    private static function informeMorosos(): array
    {
        $residentes = Residente::whereHas('cuotas', fn ($q) => $q->where('estado', 'pendiente'))
            ->with(['cuotas' => fn ($q) => $q->where('estado', 'pendiente')])
            ->get();

        $totalAdeudado = 0;
        $rows = $residentes->map(function ($r, $i) use (&$totalAdeudado) {
            $monto = $r->cuotas->sum('monto');
            $totalAdeudado += $monto;
            return [$i + 1, self::nombreResidente($r), $r->ci, $r->email, $r->cuotas->count(), self::bs($monto)];
        })->values()->all();

        return [
            'titulo'  => 'Residentes Morosos',
            'headers' => ['#', 'Residente', 'CI', 'Email', 'Cuotas pendientes', 'Monto adeudado'],
            'rows'    => $rows,
            'resumen' => [
                'Residentes morosos' => $residentes->count(),
                'Total adeudado'     => self::bs($totalAdeudado),
            ],
        ];
    }

    private static function informeCuotasVencidas(): array
    {
        $cuotas = Cuota::where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', Carbon::today())
            ->with('residente')
            ->orderBy('fecha_vencimiento')
            ->get();

        $rows = $cuotas->map(function ($c, $i) {
            $dias = (int) Carbon::parse($c->fecha_vencimiento)->diffInDays(Carbon::now());
            return [
                $i + 1, $c->titulo, self::nombreResidente($c->residente),
                self::bs($c->monto), self::fecha($c->fecha_vencimiento), $dias . ' días',
            ];
        })->values()->all();

        return [
            'titulo'  => 'Cuotas Vencidas',
            'headers' => ['#', 'Título', 'Residente', 'Monto', 'Venció el', 'Atraso'],
            'rows'    => $rows,
            'resumen' => [
                'Cuotas vencidas' => $cuotas->count(),
                'Total vencido'   => self::bs($cuotas->sum('monto')),
            ],
        ];
    }

    private static function informeMultasPendientes(): array
    {
        $multas = Multa::where('estado', 'pendiente')->with('residente')
            ->orderByDesc('fechaEmision')->get();

        $rows = $multas->map(fn ($m, $i) => [
            $i + 1, $m->motivo, self::nombreResidente($m->residente),
            self::bs($m->monto), self::fecha($m->fechaEmision), self::fecha($m->fechaLimite),
        ])->values()->all();

        return [
            'titulo'  => 'Multas Pendientes de Cobro',
            'headers' => ['#', 'Motivo', 'Residente', 'Monto', 'Emisión', 'Límite'],
            'rows'    => $rows,
            'resumen' => [
                'Multas pendientes' => $multas->count(),
                'Total pendiente'   => self::bs($multas->sum('monto')),
            ],
        ];
    }

    private static function informePagosMes(): array
    {
        $pagos = Pago::whereBetween('fecha_pago', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->with('cuota.residente')->orderByDesc('fecha_pago')->get();

        $rows = $pagos->map(fn ($p, $i) => [
            $i + 1, self::fecha($p->fecha_pago), self::nombreResidente($p->cuota?->residente),
            $p->cuota?->titulo ?? '—', self::bs($p->monto_pagado), ucfirst($p->metodo ?? '—'),
        ])->values()->all();

        return [
            'titulo'  => 'Pagos del Mes Actual (' . Carbon::now()->translatedFormat('F Y') . ')',
            'headers' => ['#', 'Fecha', 'Residente', 'Cuota', 'Monto', 'Método'],
            'rows'    => $rows,
            'resumen' => [
                'Pagos del mes'   => $pagos->count(),
                'Total del mes'   => self::bs($pagos->sum('monto_pagado')),
            ],
        ];
    }

    /* ═════════════════════════ Operación ═════════════════════════ */

    private static function informeMantenimientosTrimestre(): array
    {
        $mantenimientos = Mantenimiento::where('fecha_hora', '>=', Carbon::now()->subDays(90))
            ->with('empresa')->orderByDesc('fecha_hora')->get();

        $rows = $mantenimientos->map(fn ($m, $i) => [
            $i + 1, Str::limit($m->descripcion, 55), $m->empresa?->nombre ?? '—',
            self::bs($m->monto), self::fecha($m->fecha_hora, 'd/m/Y H:i'),
            $m->estado == 1 ? 'Activo' : 'Inactivo',
        ])->values()->all();

        return [
            'titulo'  => 'Mantenimientos de los últimos 90 días',
            'headers' => ['#', 'Descripción', 'Empresa', 'Monto', 'Fecha', 'Estado'],
            'rows'    => $rows,
            'resumen' => [
                'Mantenimientos'  => $mantenimientos->count(),
                'Total invertido' => self::bs($mantenimientos->sum('monto')),
            ],
        ];
    }

    private static function informeIncidenciasAbiertas(): array
    {
        $orden = ['alta' => 1, 'media' => 2, 'baja' => 3];
        $incidencias = Incidencia::whereIn('estado', ['pendiente', 'en_revision'])
            ->with('residente')->get()
            ->sortBy(fn ($inc) => $orden[$inc->prioridad] ?? 9)
            ->values();

        $rows = $incidencias->map(fn ($inc, $i) => [
            $i + 1, $inc->numero_seguimiento, Str::limit($inc->titulo, 45),
            self::nombreResidente($inc->residente), ucfirst($inc->prioridad),
            ucfirst(str_replace('_', ' ', $inc->estado)), self::fecha($inc->created_at),
        ])->values()->all();

        return [
            'titulo'  => 'Incidencias Abiertas',
            'headers' => ['#', 'N° Seguimiento', 'Título', 'Residente', 'Prioridad', 'Estado', 'Fecha'],
            'rows'    => $rows,
            'resumen' => ['Incidencias abiertas' => $incidencias->count()],
        ];
    }

    private static function informeReclamosAbiertos(): array
    {
        $reclamos = Reclamo::whereIn('estado', ['pendiente', 'en_revision'])
            ->with('residente')->orderByDesc('created_at')->get();

        $rows = $reclamos->map(fn ($rec, $i) => [
            $i + 1, $rec->numero_seguimiento, Str::limit($rec->titulo, 45),
            self::nombreResidente($rec->residente),
            ucfirst(str_replace('_', ' ', $rec->estado)), self::fecha($rec->created_at),
        ])->values()->all();

        return [
            'titulo'  => 'Reclamos Abiertos',
            'headers' => ['#', 'N° Seguimiento', 'Título', 'Residente', 'Estado', 'Fecha'],
            'rows'    => $rows,
            'resumen' => ['Reclamos abiertos' => $reclamos->count()],
        ];
    }

    private static function informeVisitasHoy(): array
    {
        $visitas = Visita::whereDate('fecha_inicio', Carbon::today())
            ->with('residente')->orderBy('fecha_inicio')->get();

        $rows = $visitas->map(fn ($v, $i) => [
            $i + 1, $v->nombre_visitante, $v->ci_visitante, self::nombreResidente($v->residente),
            Str::limit($v->motivo, 35), self::fecha($v->fecha_inicio, 'H:i'), ucfirst($v->estado),
        ])->values()->all();

        return [
            'titulo'  => 'Visitas de Hoy (' . Carbon::today()->format('d/m/Y') . ')',
            'headers' => ['#', 'Visitante', 'CI', 'Residente', 'Motivo', 'Hora', 'Estado'],
            'rows'    => $rows,
            'resumen' => ['Visitas de hoy' => $visitas->count()],
        ];
    }

    private static function informeReservasProximas(): array
    {
        $reservas = Reserva::whereDate('fecha', '>=', Carbon::today())
            ->whereDate('fecha', '<=', Carbon::today()->addDays(30))
            ->with(['areaComun', 'residente'])->orderBy('fecha')->get();

        $rows = $reservas->map(fn ($r, $i) => [
            $i + 1, $r->areaComun?->nombre ?? '—', self::nombreResidente($r->residente),
            self::fecha($r->fecha), substr((string) $r->hora_inicio, 0, 5),
            substr((string) $r->hora_fin, 0, 5), ucfirst($r->estado),
        ])->values()->all();

        return [
            'titulo'  => 'Reservas Próximas (30 días)',
            'headers' => ['#', 'Área común', 'Residente', 'Fecha', 'Inicio', 'Fin', 'Estado'],
            'rows'    => $rows,
            'resumen' => [
                'Reservas próximas' => $reservas->count(),
                'Monto estimado'    => self::bs($reservas->sum('monto_total')),
            ],
        ];
    }

    private static function informeEventosProximos(): array
    {
        $eventos = Evento::where('fecha_hora', '>=', Carbon::now())
            ->with('organizador')->orderBy('fecha_hora')->get();

        $rows = $eventos->map(fn ($e, $i) => [
            $i + 1, $e->nombre, $e->lugar, self::fecha($e->fecha_hora, 'd/m/Y H:i'),
            $e->cupo_maximo ?? 'Sin límite', ucfirst($e->estado),
        ])->values()->all();

        return [
            'titulo'  => 'Eventos Próximos',
            'headers' => ['#', 'Nombre', 'Lugar', 'Fecha', 'Cupo', 'Estado'],
            'rows'    => $rows,
            'resumen' => ['Eventos próximos' => $eventos->count()],
        ];
    }

    /* ═════════════════════════ Comunidad ═════════════════════════ */

    private static function informeOcupacionPropiedades(): array
    {
        $propiedades = Propiedad::all();
        $grupos = $propiedades->groupBy('estado');

        $rows = [];
        foreach ($grupos as $estado => $items) {
            $pct = $propiedades->count() ? round($items->count() * 100 / $propiedades->count(), 1) : 0;
            $rows[] = [ucfirst((string) $estado), $items->count(), $pct . ' %'];
        }

        return [
            'titulo'  => 'Ocupación de Propiedades',
            'headers' => ['Estado', 'Cantidad', 'Porcentaje'],
            'rows'    => $rows,
            'resumen' => ['Total propiedades' => $propiedades->count()],
        ];
    }

    private static function informeUnidadesOcupacion(): array
    {
        $unidades = Unidad::all();
        $grupos = $unidades->groupBy('tipo_ocupacion');

        $rows = [];
        foreach ($grupos as $tipo => $items) {
            $rows[] = [
                (string) $tipo, $items->count(),
                $items->sum('personas_por_unidad'), $items->sum('vehiculos'),
            ];
        }

        return [
            'titulo'  => 'Unidades por Tipo de Ocupación',
            'headers' => ['Tipo de ocupación', 'N° de unidades', 'Total personas', 'Total vehículos'],
            'rows'    => $rows,
            'resumen' => [
                'Total unidades'  => $unidades->count(),
                'Total personas'  => $unidades->sum('personas_por_unidad'),
                'Total vehículos' => $unidades->sum('vehiculos'),
            ],
        ];
    }

    private static function informeDirectorioResidentes(): array
    {
        $residentes = Residente::orderBy('apellido')->get();

        $rows = $residentes->map(fn ($r, $i) => [
            $i + 1, $r->nombre, $r->apellido, $r->ci, $r->email, $r->tipo_residente,
        ])->values()->all();

        return [
            'titulo'  => 'Directorio de Residentes',
            'headers' => ['#', 'Nombre', 'Apellido', 'CI', 'Email', 'Tipo'],
            'rows'    => $rows,
            'resumen' => ['Total residentes' => $residentes->count()],
        ];
    }

    private static function informeEmpleadosActivos(): array
    {
        $empleados = Empleado::where('estado', true)->with('cargo')->orderBy('apellido')->get();

        $rows = $empleados->map(fn ($e, $i) => [
            $i + 1, $e->nombre, $e->apellido, $e->ci, $e->cargo?->cargo ?? '—',
        ])->values()->all();

        return [
            'titulo'  => 'Empleados Activos',
            'headers' => ['#', 'Nombre', 'Apellido', 'CI', 'Cargo'],
            'rows'    => $rows,
            'resumen' => ['Empleados activos' => $empleados->count()],
        ];
    }
}
