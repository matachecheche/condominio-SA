<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residente extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'email',
        'tipo_residente'
    ];

    public function cuotas()
    {
        return $this->hasMany(\App\Models\Cuota::class);
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function multas()
    {
        return $this->hasMany(Multa::class);
    }

    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }

    public function reclamos()
    {
        return $this->hasMany(Reclamo::class);
    }
}
