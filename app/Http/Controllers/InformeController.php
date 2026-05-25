<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\Pago;
use App\Models\Cuota;
use App\Models\Mantenimiento;
use App\Models\Comunicado;
use App\Models\Incidencia;
use App\Models\Unidad;
use App\Traits\BitacoraTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InformeController extends Controller
{
    use BitacoraTrait;

    /**
     * CU12 — Informe administrativo general
     */
    public function administrativo(Request $request)
    {
        $tipo  = $request->get('tipo', 'residentes');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        $datos = [];
        $titulo = '';

        switch ($tipo) {
            case 'residentes':
                $titulo = 'Informe de Residentes';
                $q = Residente::query();
                if ($request->filled('tipo_residente')) {
                    $q->where('tipo_residente', $request->tipo_residente);
                }
                $datos = $q->orderBy('apellido')->get();
                break;

            case 'unidades':
                $titulo = 'Informe de Unidades Habitacionales';
                $datos = Unidad::with('residente')->orderBy('codigo')->get();
                break;

            case 'mantenimientos':
                $titulo = 'Informe de Mantenimientos';
                $q = Mantenimiento::with(['usuario', 'empresa']);
                if ($desde && $hasta) {
                    $q->whereBetween('fecha_hora', [$desde, $hasta . ' 23:59:59']);
                }
                $datos = $q->orderByDesc('fecha_hora')->get();
                break;

            case 'incidencias':
                $titulo = 'Informe de Incidencias';
                $q = Incidencia::with('residente');
                if ($request->filled('estado')) {
                    $q->where('estado', $request->estado);
                }
                if ($desde && $hasta) {
                    $q->whereBetween('created_at', [$desde, $hasta . ' 23:59:59']);
                }
                $datos = $q->orderByDesc('created_at')->get();
                break;

            default:
                $datos = collect();
        }

        if ($request->has('exportar')) {
            $this->registrarEnBitacora("Exportó informe administrativo: $tipo");
        } else {
            $this->registrarEnBitacora("Consultó informe administrativo: $tipo");
        }

        return view('informes.administrativo', compact('datos', 'tipo', 'titulo', 'desde', 'hasta'));
    }

    /**
     * CU14 — Reporte de pagos
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
        $morosos = Residente::whereHas('cuotas', fn($q) => $q->where('estado', 'pendiente'))->get();

        $this->registrarEnBitacora('Generó reporte de pagos');
        return view('informes.pagos', compact('pagos', 'total', 'morosos'));
    }
}
