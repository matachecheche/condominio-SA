<?php
/**
 * Modelo EmpresaExterna
 * 
 * EXPLICACIÓN GENERAL:
 * Este modelo representa la tabla 'empresa_externas' en la base de datos.
 * Define:
 * - Los campos que se pueden asignar masivamente ($fillable)
 * - Los tipos de datos para casteo ($casts)
 * - Las relaciones con otras tablas (si las hay)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaExterna extends Model
{
    use HasFactory; // Permite usar factories para generar datos de prueba

    /**
     * $fillable - Campos asignables masivamente
     * 
     * EXPLICACIÓN: Define qué campos pueden ser asignados mediante:
     * EmpresaExterna::create(['campo' => 'valor']);
     * o
     * $empresa->update(['campo' => 'valor']);
     * 
     * ✅ Se agregó 'calificacion' a esta lista
     */
    protected $fillable = [
        'nombre',           // Nombre de la empresa
        'servicio',         // Tipo de servicio
        'telefono',         // Teléfono
        'correo',           // Correo electrónico
        'direccion',        // Dirección
        'calificacion',     // ✅ NUEVO: Calificación de 1-5 estrellas
        'observacion',      // Observaciones
    ];

    /**
     * $casts - Casteo de tipos
     * 
     * EXPLICACIÓN:
     * Define cómo Laravel debe convertir los valores al leerlos de la BD
     * Por ejemplo, 'calificacion' se leerá como integer
     */
    protected $casts = [
        'calificacion' => 'integer', // Convertir a entero
    ];

    /**
     * RELACIONES CON OTRAS TABLAS
     * 
     * EXPLICACIÓN:
     * Las relaciones definen cómo se conecta este modelo con otros.
     * Permiten acceder a datos relacionados de forma fácil.
     */

    /**
     * Relación: Una empresa tiene muchos mantenimientos
     * 
     * EXPLICACIÓN:
     * $empresa->mantenimientos devuelve todos los mantenimientos
     * que han sido realizados por esta empresa
     * Ejemplo: $empresa->mantenimientos->count()
     */
    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'empresaExterna_id');
    }

    /**
     * Método: obtenerEstrellas()
     * 
     * EXPLICACIÓN:
     * Método personalizado que convierte la calificación numérica
     * a un formato legible (Ej: "⭐⭐⭐⭐⭐")
     * 
     * Ejemplo de uso:
     * {{ $empresa->obtenerEstrellas() }}
     */
    public function obtenerEstrellas()
    {
        return str_repeat('⭐', $this->calificacion);
    }

    /**
     * Método: esConfiable()
     * 
     * EXPLICACIÓN:
     * Método que retorna true si la empresa tiene calificación >= 4
     * Útil para filtros de confiabilidad
     * 
     * Ejemplo de uso:
     * if ($empresa->esConfiable()) { ... }
     */
    public function esConfiable()
    {
        return $this->calificacion >= 4;
    }
}