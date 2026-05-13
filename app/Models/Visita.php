<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Visita
 * 
 * EXPLICACIÓN GENERAL:
 * Este modelo representa la tabla 'visitas' en la base de datos.
 * Define:
 * - Los campos que se pueden asignar masivamente ($fillable)
 * - Los tipos de datos para casteo ($casts)
 * - Las relaciones con otras tablas
 * - Métodos útiles (scopes)
 */
class Visita extends Model
{
    use HasFactory; // Permite usar factories para generar datos de prueba

    /**
     * $fillable - Campos asignables masivamente
     * 
     * EXPLICACIÓN: Define qué campos pueden ser asignados mediante:
     * Visita::create(['campo' => 'valor']);
     * o
     * $visita->update(['campo' => 'valor']);
     * 
     * ✅ Se agregó 'acompanante' a esta lista
     */
    protected $fillable = [
        'residente_id',      // ID del residente que recibe la visita
        'nombre_visitante',  // Nombre del visitante
        'ci_visitante',      // Cédula del visitante
        'placa_vehiculo',    // Placa del vehículo (opcional)
        'motivo',            // Motivo de la visita
        'acompanante',       // ✅ NUEVO: Si va acompañado o no
        'fecha_inicio',      // Fecha/hora de inicio
        'fecha_fin',         // Fecha/hora de fin
        'codigo',            // Código único
        'estado',            // Estado actual
        'hora_entrada',      // Hora en que ingresó
        'hora_salida',       // Hora en que salió
        'user_entrada_id',   // Usuario que registró entrada
        'user_salida_id',    // Usuario que registró salida
        'observaciones'      // Observaciones
    ];

    /**
     * $casts - Casteo de tipos
     * 
     * EXPLICACIÓN:
     * Define cómo Laravel debe convertir los valores al leerlos de la BD
     * Por ejemplo, 'acompanante' se leerá como boolean
     */
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
        'acompanante' => 'boolean', // ✅ NUEVO: Convertir a booleano
    ];

    /**
     * RELACIONES CON OTRAS TABLAS
     * 
     * EXPLICACIÓN:
     * Las relaciones definen cómo se conecta este modelo con otros.
     * Permiten acceder a datos relacionados de forma fácil.
     */

    /**
     * Relación: Una visita pertenece a un residente
     * 
     * EXPLICACIÓN:
     * $visita->residente devuelve el residente que recibe la visita
     * Ejemplo: $visita->residente->nombre_completo
     */
    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    /**
     * Relación: Una visita tiene un usuario que registra entrada
     * 
     * EXPLICACIÓN:
     * $visita->userEntrada devuelve el usuario (portero/admin) que registró la entrada
     */
    public function userEntrada()
    {
        return $this->belongsTo(User::class, 'user_entrada_id');
    }

    /**
     * Relación: Una visita tiene un usuario que registra salida
     * 
     * EXPLICACIÓN:
     * $visita->userSalida devuelve el usuario (portero/admin) que registró la salida
     */
    public function userSalida()
    {
        return $this->belongsTo(User::class, 'user_salida_id');
    }

    /**
     * SCOPES ÚTILES - Métodos para filtrar consultas
     * 
     * EXPLICACIÓN:
     * Los scopes son métodos que permiten filtrar el query builder
     * Ejemplo: Visita::pendientes()->get() devuelve todas las visitas pendientes
     */

    /**
     * Scope: Visitas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope: Visitas en curso
     */
    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'en_curso');
    }

    /**
     * Scope: Visitas finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'finalizada');
    }

    /**
     * Scope: Visitas con acompañante
     * 
     * EXPLICACIÓN: Filtra solo las visitas de personas que van acompañadas
     * Ejemplo: Visita::conAcompanante()->get()
     */
    public function scopeConAcompanante($query)
    {
        return $query->where('acompanante', true);
    }

    /**
     * Scope: Visitas sin acompañante
     * 
     * EXPLICACIÓN: Filtra solo las visitas de personas que van solas
     * Ejemplo: Visita::sinAcompanante()->get()
     */
    public function scopeSinAcompanante($query)
    {
        return $query->where('acompanante', false);
    }
}