<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'lugar',
        'fecha_hora',
        'cupo_maximo',
        'estado',
        'organizador_id',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function organizador()
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }
}
