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
    /**
     * Indica si el residente tiene al menos una cuota vencida y no pagada.
     * Se utiliza para restringir el acceso a servicios como reservas de
     * áreas comunes mientras exista morosidad (ver caso Reserva-002).
     *
     * @return bool
     */
    public function tieneMorosidad(): bool
    {
        return $this->cuotas()
            ->where('estado', '!=', 'pagado')
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->exists();
    }
}
