<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Comunicado
 * 
 * EXPLICACIÓN: Este modelo representa la tabla 'comunicados' y define:
 * - Los campos que se pueden asignar masivamente ($fillable)
 * - Las relaciones con otras tablas (usuario)
 * - Los tipos de datos para casteo ($casts)
 */
class Comunicado extends Model
{
    use HasFactory;

    // ✅ CAMPOS QUE PUEDEN SER ASIGNADOS MASIVAMENTE
    // El campo 'destinatarios' se agregó aquí para que pueda ser guardado desde formularios
    protected $fillable = [
        'titulo',           // Título del comunicado
        'contenido',        // Contenido del comunicado
        'tipo',             // Tipo: Urgente o Informativo
        'destinatarios',    // ✅ NUEVO: A quién va dirigido
        'fecha_publicacion', // Cuándo se publica
        'usuario_id',       // Quién lo crea
        'notificado'        // Si ya fue notificado
    ];

    // ✅ CASTEO DE TIPOS
    // Convierte automáticamente la fecha a objeto DateTime de Laravel
    protected $casts = [
        'fecha_publicacion' => 'datetime',
    ];

    /**
     * Relación: Un comunicado pertenece a un usuario
     * EXPLICACIÓN: Define que cada comunicado está asociado a un usuario que lo creó
     */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}