<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones'; 

    protected $fillable = [
        'contenido',
        'fecha_hora',
        'tipo',
        'titulo',
        'residente_id',
        'ruta',
        'leida',
        'enviada_por',
    ];

    public $timestamps = true; 

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }
    public function enviadoPor()
    {
        return $this->belongsTo(User::class, 'enviada_por');
    }
}
