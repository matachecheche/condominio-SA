<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    // app/Models/Cuota.php
    protected $fillable = [
        'titulo', // ← ESTA LÍNEA ES CLAVE
        'descripcion',
        'fecha_emision',
        'fecha_vencimiento',
        'monto',
        'estado',
        'residente_id',
        'tipo_cuota_id',
        'user_id',
        'observacion',
    ];

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
    public function tipoCuota()
    {
        return $this->belongsTo(TipoCuota::class);
    }

    // Devuelve true si la cuota tiene al menos un pago
    public function estaPagada()
    {
        return $this->pagos()->exists();
    }

    public function multas()
    {
        return $this->hasMany(Multa::class);
    }

    /**
     * Calcula el monto de la multa por mora según la fecha en que se realiza
     * el pago, comparada con la fecha de vencimiento de la cuota.
     *
     * Regla de negocio (según documento de pruebas Pago-001):
     *   50 Bs. de multa por cada bloque de 10 días de atraso (o fracción).
     *
     * @param  \DateTimeInterface|string  $fechaPago
     * @return float
     */
    public function calcularMultaPorMora($fechaPago): float
    {
        $fechaPago = $fechaPago instanceof \Carbon\Carbon
            ? $fechaPago
            : \Carbon\Carbon::parse($fechaPago);

        $vencimiento = \Carbon\Carbon::parse($this->fecha_vencimiento);

        if ($fechaPago->lessThanOrEqualTo($vencimiento)) {
            return 0.0;
        }

        $diasAtraso = $vencimiento->diffInDays($fechaPago);
        $bloquesDeMora = (int) ceil($diasAtraso / 10);

        return $bloquesDeMora * 50.0;
    }

    /**
     * Aplica (crea) la multa por mora correspondiente a esta cuota, si el
     * pago se realizó después de la fecha de vencimiento. No duplica la
     * multa si ya existe una multa asociada a esta cuota.
     *
     * @param  \DateTimeInterface|string  $fechaPago
     * @return \App\Models\Multa|null
     */
    public function aplicarMultaSiCorresponde($fechaPago): ?Multa
    {
        $montoMulta = $this->calcularMultaPorMora($fechaPago);

        if ($montoMulta <= 0) {
            return null;
        }

        if ($this->multas()->exists()) {
            return $this->multas()->latest('id')->first();
        }

        return $this->multas()->create([
            'motivo'       => 'Mora en el pago de la cuota: ' . $this->titulo,
            'monto'        => $montoMulta,
            'fechaEmision' => now()->toDateString(),
            'fechaLimite'  => now()->addDays(10)->toDateString(),
            'estado'       => 'pendiente',
            'residente_id' => $this->residente_id,
        ]);
    }
}
