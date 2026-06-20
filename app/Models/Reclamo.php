<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_seguimiento',
        'titulo',
        'contenido',
        'estado',
        'respuesta_admin',
        'residente_id',
        'atendido_por',
        'fecha_atencion',
    ];

    protected $casts = [
        'fecha_atencion' => 'datetime',
    ];

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function atendioPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    protected static function booted(): void
    {
        static::creating(function (Reclamo $reclamo) {
            if (empty($reclamo->numero_seguimiento)) {
                $reclamo->numero_seguimiento = 'REC-' . strtoupper(uniqid());
            }
        });
    }
}
