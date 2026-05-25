<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'codigo',
        'placa',
        'marca',
        'capacidad',
        'estado',
        'personas_por_unidad',
        'tiene_mascotas',
        'vehiculos',
        'tipo_ocupacion',
        'residente_id',
    ];

    protected $casts = [
        'capacidad'           => 'integer',
        'personas_por_unidad' => 'integer',
        'tiene_mascotas'      => 'boolean',
        'vehiculos'           => 'integer',
    ];

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
