<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $fillable = [
        'codigo',
        'tipo',
        'descripcion',
        'ubicacion',
        'estado',
        'residente_id',
    ];

    /**
     * Relación: Una propiedad pertenece a un residente
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Scope: Propiedades activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope: Propiedades ocupadas
     */
    public function scopeOcupadas($query)
    {
        return $query->where('estado', 'ocupada');
    }

    /**
     * Scope: Propiedades disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }
}
